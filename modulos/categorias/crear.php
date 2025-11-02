<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$Descripcion = trim($_POST['Descripcion'] ?? '');
if ($Descripcion === '') { echo json_encode(['ok'=>false, 'msg'=>'La descripción es obligatoria']); exit; }

$stmt = $conexion->prepare("INSERT INTO Categoria (Descripcion) VALUES (?)");
$stmt->bind_param("s", $Descripcion);

$ok = $stmt->execute();
echo json_encode(['ok'=>$ok, 'msg'=>$ok?'':'No se pudo crear']);
