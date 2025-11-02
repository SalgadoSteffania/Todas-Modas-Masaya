<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdCategoria = intval($_POST['IdCategoria'] ?? 0);
$Descripcion = trim($_POST['Descripcion'] ?? '');

if ($IdCategoria <= 0 || $Descripcion === '') {
  echo json_encode(['ok'=>false, 'msg'=>'Datos incompletos']); exit;
}

$stmt = $conexion->prepare("UPDATE Categoria SET Descripcion=? WHERE IdCategoria=?");
$stmt->bind_param("si", $Descripcion, $IdCategoria);

$ok = $stmt->execute();
echo json_encode(['ok'=>$ok, 'msg'=>$ok?'':'No se pudo actualizar']);
