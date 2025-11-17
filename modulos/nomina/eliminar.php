<?php
session_start();
if (!isset($_SESSION['correo'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['ok'=>false,'msg'=>'No autorizado']);
    exit;
}

require_once dirname(__DIR__, 2) . '/conexion.php';
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $IdNomina = (int)($_POST['IdNomina'] ?? 0);
    if ($IdNomina <= 0) {
        echo json_encode(['ok'=>false,'msg'=>'IdNomina inválido']);
        exit;
    }

    $stmt = $conexion->prepare("DELETE FROM Nomina WHERE IdNomina=?");
    $stmt->bind_param("i", $IdNomina);
    $stmt->execute();

    echo json_encode(['ok'=>true,'msg'=>'Nómina eliminada']);

} catch (Exception $e) {
    echo json_encode(['ok'=>false,'msg'=>'Error: '.$e->getMessage()]);
}
