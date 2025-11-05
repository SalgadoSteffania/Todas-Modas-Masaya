<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdCliente = intval($_POST['IdCliente'] ?? 0);
if ($IdCliente <= 0) {
  echo json_encode(['ok'=>false,'msg'=>'ID inválido']);
  exit;
}

$stmt = $conexion->prepare("DELETE FROM Cliente WHERE IdCliente = ?");
$stmt->bind_param("i", $IdCliente);

$ok = $stmt->execute();
echo json_encode(['ok'=>$ok, 'msg'=>$ok ? '' : 'No se pudo eliminar']);
