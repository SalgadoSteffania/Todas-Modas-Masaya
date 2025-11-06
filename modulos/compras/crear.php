<?php
session_start();
if (!isset($_SESSION['correo'])) {
    http_response_code(401);
    exit;
}

require_once dirname(__DIR__, 2) . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {


    $IdUsuario = intval($_SESSION['IdUsuario'] ?? 0);

    if ($IdUsuario <= 0) {
        $correoSesion = $_SESSION['correo'] ?? '';
        if ($correoSesion !== '') {
            $stmtUser = $conexion->prepare(
                "SELECT IdUsuario FROM Usuario WHERE Correo = ? LIMIT 1"
            );
            $stmtUser->bind_param("s", $correoSesion);
            $stmtUser->execute();
            $resUser = $stmtUser->get_result();
            if ($rowUser = $resUser->fetch_assoc()) {
                $IdUsuario = intval($rowUser['IdUsuario']);
            }
        }
    }

    /* 2. Datos del formulario */
    $IdProveedor = intval($_POST['IdProveedor'] ?? 0);
    $Fecha       = trim($_POST['Fecha'] ?? '');
    if ($Fecha === '') {
        $Fecha = date('Y-m-d');
    }

    $productos  = $_POST['IdProducto']     ?? [];
    $cantidades = $_POST['Cantidad']       ?? [];
    $precios    = $_POST['PrecioUnitario'] ?? [];

    /* 3. Validaciones iniciales */
    if ($IdUsuario <= 0 || $IdProveedor <= 0) {
        throw new Exception('Usuario o proveedor inválido (IdUsuario=' . $IdUsuario . ', IdProveedor=' . $IdProveedor . ')');
    }

    if (!is_array($productos) || count($productos) === 0) {
        throw new Exception('Debe agregar al menos un producto');
    }

    if (count($productos) !== count($cantidades) ||
        count($productos) !== count($precios)) {
        throw new Exception('Los datos del detalle no coinciden');
    }

    $conexion->begin_transaction();

    // Insertar cabecera de compra
    $stmt = $conexion->prepare(
        "INSERT INTO Compra (IdUsuario, IdProveedor, Fecha)
         VALUES (?, ?, ?)"
    );
    $stmt->bind_param("iis", $IdUsuario, $IdProveedor, $Fecha);
    $stmt->execute();
    $IdCompra = $conexion->insert_id;

    // Preparar sentencias para detalle y stock
    $stmtDet = $conexion->prepare(
        "INSERT INTO Detalle_Compra (IdCompra, IdProducto, Cantidad, PrecioUnitario)
         VALUES (?, ?, ?, ?)"
    );
    $stmtUpd = $conexion->prepare(
        "UPDATE Producto SET Cantidad = Cantidad + ? WHERE IdProducto = ?"
    );

    for ($i = 0; $i < count($productos); $i++) {
        $idProd  = intval($productos[$i]);
        $cant    = intval($cantidades[$i]);
        $precio  = floatval($precios[$i]);

        if ($idProd <= 0 || $cant <= 0 || $precio < 0) {
            throw new Exception("Datos de producto inválidos en fila $i (IdProducto=$idProd, Cantidad=$cant, Precio=$precio)");
        }

        // Insertar detalle
        $stmtDet->bind_param("iiid", $IdCompra, $idProd, $cant, $precio);
        $stmtDet->execute(); 

        // Actualizar stock
        $stmtUpd->bind_param("ii", $cant, $idProd);
        $stmtUpd->execute();  
    }

    $conexion->commit();

    echo json_encode([
        'ok'  => true,
        'msg' => 'Compra registrada con éxito'
    ]);

} catch (Exception $e) {
    if ($conexion->errno) {
        // Si hay transacción abierta, intentar rollback
        try { $conexion->rollback(); } catch (Exception $e2) {}
    }
    echo json_encode([
        'ok'  => false,
        'msg' => 'Error en compra: ' . $e->getMessage()
    ]);
}
