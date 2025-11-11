<?php
session_start();
if (!isset($_SESSION['correo'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'msg' => 'No autorizado']);
    exit;
}

require_once dirname(__DIR__, 2) . '/conexion.php';

header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $IdCompra = intval($_GET['IdCompra'] ?? ($_POST['IdCompra'] ?? 0));
    if ($IdCompra <= 0) {
        echo json_encode(['ok' => false, 'msg' => 'IdCompra inválido']);
        exit;
    }

    $sql = "SELECT
                c.IdCompra,
                c.Fecha,
                c.IdProveedor,
                u.Nombre_de_Usuario      AS Comprador,
                p.Nombre                 AS Proveedor,
                d.IdDetCompra,
                d.IdProducto,
                pr.Nombre                AS Producto,
                pr.Marca,
                pr.Talla,
                d.Cantidad,
                d.PrecioUnitario,
                d.Subtotal
            FROM Compra c
            JOIN Usuario        u  ON u.IdUsuario   = c.IdUsuario
            JOIN Proveedor      p  ON p.IdProveedor = c.IdProveedor
            JOIN Detalle_Compra d  ON d.IdCompra    = c.IdCompra
            JOIN Producto       pr ON pr.IdProducto = d.IdProducto
            WHERE c.IdCompra = ?
            ORDER BY d.IdDetCompra ASC";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $IdCompra);
    $stmt->execute();
    $res = $stmt->get_result();

    $cabecera = null;
    $detalles = [];
    $total    = 0;

    while ($row = $res->fetch_assoc()) {
        if ($cabecera === null) {
            $cabecera = [
                'IdCompra'    => $row['IdCompra'],
                'Fecha'       => $row['Fecha'],
                'IdProveedor' => $row['IdProveedor'],
                'Comprador'   => $row['Comprador'],
                'Proveedor'   => $row['Proveedor'],
            ];
        }

        $detalles[] = [
            'IdDetCompra'    => $row['IdDetCompra'],
            'IdProducto'     => $row['IdProducto'],
            'Producto'       => $row['Producto'],
            'Marca'          => $row['Marca'],           
            'Talla'          => $row['Talla'],
            'Cantidad'       => $row['Cantidad'],
            'PrecioUnitario' => $row['PrecioUnitario'],
            'Subtotal'       => $row['Subtotal'],
        ];

        $total += (float)$row['Subtotal'];
    }

    if ($cabecera === null) {
        echo json_encode(['ok' => false, 'msg' => 'Compra no encontrada']);
        exit;
    }

    echo json_encode([
        'ok'       => true,
        'cabecera' => $cabecera,
        'detalles' => $detalles,
        'total'    => $total
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode([
        'ok'  => false,
        'msg' => 'Error al obtener detalle: ' . $e->getMessage()
    ]);
}
