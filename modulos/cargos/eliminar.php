<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdCargo = intval($_POST['IdCargo'] ?? 0);
if ($IdCargo <= 0) {
  echo json_encode(['ok'=>false, 'msg'=>'ID inválido']);
  exit;
}

$stmt = $conexion->prepare("DELETE FROM Cargo WHERE IdCargo = ?");
$stmt->bind_param("i", $IdCargo);

$ok = $stmt->execute();
echo json_encode(['ok'=>$ok, 'msg'=>$ok ? '' : 'No se pudo eliminar']);
