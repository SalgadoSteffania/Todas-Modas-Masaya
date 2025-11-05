<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$Nombre        = trim($_POST['Nombre'] ?? '');
$Apellido      = trim($_POST['Apellido'] ?? '');
$IdDepartamento= intval($_POST['IdDepartamento'] ?? 0);
$Direccion     = trim($_POST['Direccion'] ?? '');
$Telefono      = trim($_POST['Telefono'] ?? '');
$TipoCliente   = trim($_POST['TipoCliente'] ?? '');

if ($Nombre === '' || $Apellido === '' || $IdDepartamento <= 0) {
  echo json_encode(['ok'=>false,'msg'=>'Nombre, Apellido y Departamento son obligatorios']);
  exit;
}

$stmt = $conexion->prepare(
  "INSERT INTO Cliente (Nombre, Apellido, IdDepartamento, Direccion, Telefono, TipoCliente)
   VALUES (?, ?, ?, ?, ?, ?)"
);
$stmt->bind_param("ssisss", $Nombre, $Apellido, $IdDepartamento, $Direccion, $Telefono, $TipoCliente);

$ok = $stmt->execute();
echo json_encode(['ok'=>$ok, 'msg'=>$ok ? '' : 'No se pudo crear']);
