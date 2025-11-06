<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');

$IdCompra    = intval($_POST['IdCompra'] ?? 0);
$IdProveedor = intval($_POST['IdProveedor'] ?? 0);
$Fecha       = trim($_POST['Fecha'] ?? '');
if ($Fecha === '') {
  $Fecha = date('Y-m-d');
}

$productos  = $_POST['IdProducto'] ?? [];
$cantidades = $_POST['Cantidad'] ?? [];
$precios    = $_POST['PrecioUnitario'] ?? [];

if ($IdCompra <= 0 || $IdProveedor <= 0) {
  echo json_encode(['ok'=>false,'msg'=>'Datos de compra inválidos']);
  exit;
}

if (!is_array($productos) || !count($productos)) {
  echo json_encode(['ok'=>false,'msg'=>'Debe agregar al menos un producto']);
  exit;
}

if (count($productos) !== count($cantidades) || count($productos) !== count($precios)) {
  echo json_encode(['ok'=>false,'msg'=>'Los datos del detalle no coinciden']);
  exit;
}

try {
  $conexion->begin_transaction();

  // 1. Revertir stock actual del detalle
  $sqlDet = "SELECT IdProducto, Cantidad FROM Detalle_Compra WHERE IdCompra = ?";
  $stmtDetOld = $conexion->prepare($sqlDet);
  $stmtDetOld->bind_param("i", $IdCompra);
  $stmtDetOld->execute();
  $resDet = $stmtDetOld->get_result();
  $stmtUpdStock = $conexion->prepare(
    "UPDATE Producto SET Cantidad = Cantidad - ? WHERE IdProducto = ?"
  );
  while ($row = $resDet->fetch_assoc()) {
    $cantOld = intval($row['Cantidad']);
    $idProd  = intval($row['IdProducto']);
    $stmtUpdStock->bind_param("ii", $cantOld, $idProd);
    $stmtUpdStock->execute();
  }

  // 2. Borrar detalle viejo
  $stmtDelDet = $conexion->prepare("DELETE FROM Detalle_Compra WHERE IdCompra = ?");
  $stmtDelDet->bind_param("i", $IdCompra);
  $stmtDelDet->execute();

  // 3. Actualizar 
  $stmtCab = $conexion->prepare(
    "UPDATE Compra SET IdProveedor = ?, Fecha = ? WHERE IdCompra = ?"
  );
  $stmtCab->bind_param("isi", $IdProveedor, $Fecha, $IdCompra);
  if (!$stmtCab->execute()) {
    throw new Exception('No se pudo actualizar la compra');
  }

  $stmtDetNew = $conexion->prepare(
    "INSERT INTO Detalle_Compra (IdCompra, IdProducto, Cantidad, PrecioUnitario)
     VALUES (?, ?, ?, ?)"
  );
  $stmtUpdNew = $conexion->prepare(
    "UPDATE Producto SET Cantidad = Cantidad + ? WHERE IdProducto = ?"
  );

  for ($i = 0; $i < count($productos); $i++) {
    $idProd  = intval($productos[$i]);
    $cant    = intval($cantidades[$i]);
    $precio  = floatval($precios[$i]);

    if ($idProd <= 0 || $cant <= 0 || $precio < 0) {
      throw new Exception('Datos de producto inválidos');
    }

    $stmtDetNew->bind_param("iiid", $IdCompra, $idProd, $cant, $precio);
    if (!$stmtDetNew->execute()) {
      throw new Exception('No se pudo guardar el detalle nuevo');
    }

    $stmtUpdNew->bind_param("ii", $cant, $idProd);
    if (!$stmtUpdNew->execute()) {
      throw new Exception('No se pudo actualizar el stock');
    }
  }

  $conexion->commit();
  echo json_encode(['ok'=>true,'msg'=>'Compra actualizada con éxito']);
} catch (Exception $e) {
  $conexion->rollback();
  echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]);
}
