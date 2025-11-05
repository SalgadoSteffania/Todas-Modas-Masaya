<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');

$sql = "SELECT c.IdCliente,
               c.Nombre,
               c.Apellido,
               c.IdDepartamento,
               d.Nombre AS Departamento,
               c.Direccion,
               c.Telefono,
               c.TipoCliente
        FROM Cliente c
        JOIN Departamento d ON d.IdDepartamento = c.IdDepartamento
        ORDER BY c.IdCliente DESC";

$res = $conexion->query($sql);

$data = [];
if ($res) {
  while ($row = $res->fetch_assoc()) {
    $data[] = $row;
  }
}

echo json_encode($data, JSON_UNESCAPED_UNICODE);
