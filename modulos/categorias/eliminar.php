<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdCategoria = intval($_POST['IdCategoria'] ?? 0);
if ($IdCategoria <= 0) { echo json_encode(['ok'=>false,'msg'=>'ID inválido']); exit; }

$stmt = $conexion->prepare("DELETE FROM Categoria WHERE IdCategoria=?");
$stmt->bind_param("i", $IdCategoria);

$ok = $stmt->execute();
echo json_encode(['ok'=>$ok, 'msg'=>$ok?'':'No se pudo eliminar']);
