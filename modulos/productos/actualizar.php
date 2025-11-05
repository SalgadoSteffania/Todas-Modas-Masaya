<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdProducto      = intval($_POST['IdProducto'] ?? 0);
$IdCategoria     = intval($_POST['IdCategoria'] ?? 0);
$Marca           = trim($_POST['Marca'] ?? '');
$Nombre          = trim($_POST['Nombre'] ?? '');
$Descripcion     = trim($_POST['Descripcion'] ?? '');
$Talla           = trim($_POST['Talla'] ?? '');
$Color           = trim($_POST['Color'] ?? '');
$Cantidad        = intval($_POST['Cantidad'] ?? 0);
$Precio_de_Venta = floatval($_POST['Precio_de_Venta'] ?? 0);

if ($IdProducto <= 0 || $IdCategoria <= 0 || $Nombre === '') {
  echo json_encode(['ok'=>false, 'msg'=>'Datos incompletos']);
  exit;
}

$stmt = $conexion->prepare(
  "UPDATE Producto
   SET IdCategoria=?, Marca=?, Nombre=?, Descripcion=?, Talla=?, Color=?, Cantidad=?, Precio_de_Venta=?
   WHERE IdProducto=?"
);

$stmt->bind_param(
  "isssssidi",
  $IdCategoria,
  $Marca,
  $Nombre,
  $Descripcion,
  $Talla,
  $Color,
  $Cantidad,
  $Precio_de_Venta,
  $IdProducto
);

$ok = $stmt->execute();
echo json_encode(['ok'=>$ok, 'msg'=>$ok ? '' : 'No se pudo actualizar']);
