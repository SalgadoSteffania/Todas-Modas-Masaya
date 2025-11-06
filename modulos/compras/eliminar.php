<?php
session_start();
if (!isset($_SESSION['correo'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
}

require_once dirname(__DIR__, 2) . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $IdCompra = intval($_POST['IdCompra'] ?? 0);
    if ($IdCompra <= 0) {
        echo json_encode(['ok' => false, 'msg' => 'IdCompra inválido']);
        exit;
    }

    $conexion->begin_transaction();

    // Revertir stock
    $sqlDet = "SELECT IdProducto, Cantidad
               FROM Detalle_Compra
               WHERE IdCompra = ?";
    $stmtDet = $conexion->prepare($sqlDet);
    $stmtDet->bind_param("i", $IdCompra);
    $stmtDet->execute();
    $resDet = $stmtDet->get_result();

    $stmtUpd = $conexion->prepare(
        "UPDATE Producto SET Cantidad = Cantidad - ? WHERE IdProducto = ?"
    );
    while ($row = $resDet->fetch_assoc()) {
        $cant   = (int)$row['Cantidad'];
        $idProd = (int)$row['IdProducto'];
        $stmtUpd->bind_param("ii", $cant, $idProd);
        $stmtUpd->execute();
    }

    
    $stmtDel = $conexion->prepare("DELETE FROM Compra WHERE IdCompra = ?");
    $stmtDel->bind_param("i", $IdCompra);
    $stmtDel->execute();

    $conexion->commit();
    echo json_encode(['ok' => true, 'msg' => 'Compra eliminada con éxito']);

} catch (Exception $e) {
    try { $conexion->rollback(); } catch (Exception $e2) {}
    echo json_encode([
        'ok'  => false,
        'msg' => 'Error al eliminar compra: ' . $e->getMessage()
    ]);
}
