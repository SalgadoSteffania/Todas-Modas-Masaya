<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdCliente     = intval($_POST['IdCliente'] ?? 0);
$Nombre        = trim($_POST['Nombre'] ?? '');
$Apellido      = trim($_POST['Apellido'] ?? '');
$IdDepartamento= intval($_POST['IdDepartamento'] ?? 0);
$Direccion     = trim($_POST['Direccion'] ?? '');
$Telefono      = trim($_POST['Telefono'] ?? '');
$TipoCliente   = trim($_POST['TipoCliente'] ?? '');

if ($IdCliente <= 0 || $Nombre === '' || $Apellido === '' || $IdDepartamento <= 0) {
  echo json_encode(['ok'=>false,'msg'=>'Datos incompletos']);
  exit;
}

$stmt = $conexion->prepare(
  "UPDATE Cliente
   SET Nombre=?, Apellido=?, IdDepartamento=?, Direccion=?, Telefono=?, TipoCliente=?
   WHERE IdCliente=?"
);
$stmt->bind_param("ssisssi",
  $Nombre,
  $Apellido,
  $IdDepartamento,
  $Direccion,
  $Telefono,
  $TipoCliente,
  $IdCliente
);

$ok = $stmt->execute();
echo json_encode(['ok'=>$ok, 'msg'=>$ok ? '' : 'No se pudo actualizar']);
