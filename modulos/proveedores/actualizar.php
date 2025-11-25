<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$IdProveedor = intval($_POST['IdProveedor'] ?? 0);
$Nombre      = trim($_POST['Nombre'] ?? '');
$Telefono    = trim($_POST['Telefono'] ?? '');
$Email       = trim($_POST['Email'] ?? '');
$Direccion   = trim($_POST['Direccion'] ?? '');

if ($IdProveedor <= 0 || $Nombre === '') {
  echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']); exit;
}

$stmt = $conexion->prepare(
  "UPDATE proveedor
   SET Nombre = ?, Telefono = ?, Email = ?, Direccion = ?
   WHERE IdProveedor = ?"
);
$stmt->bind_param("ssssi", $Nombre, $Telefono, $Email, $Direccion, $IdProveedor);

$ok = $stmt->execute();
echo json_encode(['ok' => $ok, 'msg' => $ok ? '' : 'No se pudo actualizar']);
