<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdDepartamento = intval($_POST['IdDepartamento'] ?? 0);
if ($IdDepartamento <= 0) {
  echo json_encode(['ok'=>false, 'msg'=>'ID inválido']);
  exit;
}

$stmt = $conexion->prepare("DELETE FROM departamento WHERE IdDepartamento = ?");
$stmt->bind_param("i", $IdDepartamento);

$ok = $stmt->execute();
echo json_encode(['ok'=>$ok, 'msg'=>$ok ? '' : 'No se pudo eliminar']);
