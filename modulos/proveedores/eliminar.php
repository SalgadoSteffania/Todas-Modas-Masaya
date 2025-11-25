<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdProveedor = intval($_POST['IdProveedor'] ?? 0);
if ($IdProveedor <= 0) { echo json_encode(['ok'=>false, 'msg'=>'ID inválido']); exit; }

$stmt = $conexion->prepare("DELETE FROM proveedor WHERE IdProveedor = ?");
$stmt->bind_param("i", $IdProveedor);

$ok = $stmt->execute();
echo json_encode(['ok' => $ok, 'msg' => $ok ? '' : 'No se pudo eliminar']);
