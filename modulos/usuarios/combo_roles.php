<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

$res = $conexion->query("SELECT IdRol, Descripcion FROM rol ORDER BY Descripcion");
$out = [];
while($r = $res->fetch_assoc()) $out[] = $r;


echo json_encode($out, JSON_UNESCAPED_UNICODE);
