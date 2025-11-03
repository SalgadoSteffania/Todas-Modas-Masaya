<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdDepartamento = intval($_POST['IdDepartamento'] ?? 0);
$Nombre         = trim($_POST['Nombre'] ?? '');

if ($IdDepartamento <= 0 || $Nombre === '') {
  echo json_encode(['ok'=>false, 'msg'=>'Datos incompletos']);
  exit;
}

$stmt = $conexion->prepare(
  "UPDATE Departamento
   SET Nombre = ?
   WHERE IdDepartamento = ?"
);
$stmt->bind_param("si", $Nombre, $IdDepartamento);

$ok = $stmt->execute();
echo json_encode(['ok'=>$ok, 'msg'=>$ok ? '' : 'No se pudo actualizar']);
