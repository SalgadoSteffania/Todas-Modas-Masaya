<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT IdCategoria, Descripcion
        FROM Categoria
        ORDER BY IdCategoria DESC";
$res = $conexion->query($sql);

$data = [];
if ($res) {
  while ($row = $res->fetch_assoc()) $data[] = $row;
}
echo json_encode($data, JSON_UNESCAPED_UNICODE);
