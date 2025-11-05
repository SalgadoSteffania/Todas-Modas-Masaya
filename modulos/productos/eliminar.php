<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdProducto = intval($_POST['IdProducto'] ?? 0);
if ($IdProducto <= 0) {
  echo json_encode(['ok'=>false,'msg'=>'ID inválido']);
  exit;
}

$stmt = $conexion->prepare("DELETE FROM Producto WHERE IdProducto = ?");
$stmt->bind_param("i", $IdProducto);

$ok = $stmt->execute();
echo json_encode(['ok'=>$ok, 'msg'=>$ok ? '' : 'No se pudo eliminar']);
