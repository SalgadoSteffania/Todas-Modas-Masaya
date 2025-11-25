<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$Nombre    = trim($_POST['Nombre'] ?? '');
$Telefono  = trim($_POST['Telefono'] ?? '');
$Email     = trim($_POST['Email'] ?? '');
$Direccion = trim($_POST['Direccion'] ?? '');

if ($Nombre === '') {
  echo json_encode(['ok' => false, 'msg' => 'El nombre es obligatorio']); exit;
}

$stmt = $conexion->prepare(
  "INSERT INTO proveedor (Nombre, Telefono, Email, Direccion) VALUES (?, ?, ?, ?)"
);
$stmt->bind_param("ssss", $Nombre, $Telefono, $Email, $Direccion);

$ok = $stmt->execute();
echo json_encode(['ok' => $ok, 'msg' => $ok ? '' : 'No se pudo crear']);
