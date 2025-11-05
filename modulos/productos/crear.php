<?php
session_start();
if (!isset($_SESSION['correo'])) {
    http_response_code(401);
    exit;
}

require_once dirname(__DIR__, 2) . '/conexion.php';

$IdCategoria     = intval($_POST['IdCategoria'] ?? 0);
$Marca           = trim($_POST['Marca'] ?? '');
$Nombre          = trim($_POST['Nombre'] ?? '');
$Descripcion     = trim($_POST['Descripcion'] ?? '');
$Talla           = trim($_POST['Talla'] ?? '');
$Color           = trim($_POST['Color'] ?? '');
$Cantidad        = intval($_POST['Cantidad'] ?? 0);
$Precio_de_Venta = floatval($_POST['Precio_de_Venta'] ?? 0);

// Validaciones básicas
if ($IdCategoria <= 0 || $Nombre === '' || $Precio_de_Venta <= 0) {
    echo json_encode([
        'ok'  => false,
        'msg' => 'Categoría, nombre y precio son obligatorios'
    ]);
    exit;
}

$stmt = $conexion->prepare(
    "INSERT INTO Producto
     (IdCategoria, Marca, Nombre, Descripcion, Talla, Color, Cantidad, Precio_de_Venta)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
);


$stmt->bind_param(
    "isssssid",
    $IdCategoria,
    $Marca,
    $Nombre,
    $Descripcion,
    $Talla,
    $Color,
    $Cantidad,
    $Precio_de_Venta
);

$ok = $stmt->execute();

echo json_encode([
    'ok'  => $ok,
    'msg' => $ok ? '' : 'No se pudo crear'
]);
