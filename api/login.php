<?php
session_start();
require_once '../conexion.php';

$correo = $_POST['correo'] ?? '';
$pass   = md5($_POST['contrasena'] ?? '');

$sql = "SELECT Nombre, Correo FROM Usuario WHERE Correo=? AND Contrasena=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ss", $correo, $pass);
$stmt->execute();
$res = $stmt->get_result();

header('Content-Type: application/json; charset=utf-8');
if ($row = $res->fetch_assoc()) {
  $_SESSION['correo'] = $row['Correo'];
  $_SESSION['nombre'] = $row['Nombre'];
  echo json_encode(['ok'=>true]);
} else {
  echo json_encode(['ok'=>false, 'msg'=>'Credenciales inválidas']);
}
