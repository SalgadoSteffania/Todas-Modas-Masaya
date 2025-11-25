<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit; }
require_once dirname(__DIR__, 2) . '/conexion.php';


try {
    $tipo  = $_GET['tipo']  ?? '';
    $desde = $_GET['desde'] ?? '';
    $hasta = $_GET['hasta'] ?? '';

    $categoria = $_GET['categoria'] ?? '';
    $producto  = $_GET['producto']  ?? '';
    $proveedor = $_GET['proveedor'] ?? '';
    $cedula    = $_GET['cedula']    ?? '';
    $cliente   = $_GET['cliente']   ?? '';

    if ($desde === '' && $hasta === '') {
        $desde = '1900-01-01';
        $hasta = date('Y-m-d');
    } elseif ($desde === '' && $hasta !== '') {
        $desde = '1900-01-01';
    } elseif ($desde !== '' && $hasta === '') {
        $hasta = date('Y-m-d');
    }

    $cols = [];
    $rows = [];

    switch ($tipo) {
        case 'inventario':
            $cols = [
                'ID Producto',
                'Nombre',
                'Categoría',
                'Cant. Comprada',
                'Cant. disponible',
                'Cant. vendida',
                'Precio de venta (C$)',
                'Total ganado (C$)'
            ];

            $sql = "
              SELECT 
                p.IdProducto,
                p.Nombre,
                c.Descripcion AS Categoria,
                IFNULL(comp.CantComprada,0) AS CantComprada,
                p.Cantidad AS CantDisponible,
                IFNULL(vent.CantVendida,0) AS CantVendida,
                p.Precio_de_Venta,
                IFNULL(vent.TotalVendido,0) AS TotalGanado
              FROM producto p
              JOIN categoria c ON c.IdCategoria = p.IdCategoria
              LEFT JOIN (
                SELECT dc.IdProducto, SUM(dc.Cantidad) AS CantComprada
                FROM detalle_compra dc
                JOIN compra co ON co.IdCompra = dc.IdCompra
                WHERE co.Fecha BETWEEN ? AND ?
                GROUP BY dc.IdProducto
              ) comp ON comp.IdProducto = p.IdProducto
              LEFT JOIN (
                SELECT ds.IdProducto, SUM(ds.Cantidad) AS CantVendida, SUM(ds.Subtotal) AS TotalVendido
                FROM detalle_de_salida ds
                JOIN salida_de_stock s ON s.IdVenta = ds.IdVenta
                WHERE s.Fecha BETWEEN ? AND ?
                GROUP BY ds.IdProducto
              ) vent ON vent.IdProducto = p.IdProducto
              WHERE 1=1
            ";

            $types  = "ssss";
            $params = [$desde, $hasta, $desde, $hasta];

            if ($categoria !== '') {
                $sql   .= " AND p.IdCategoria = ? ";
                $types .= "i";
                $params[] = (int)$categoria;
            }
            if ($producto !== '') {
                $sql   .= " AND p.IdProducto = ? ";
                $types .= "i";
                $params[] = (int)$producto;
            }

            $sql .= " ORDER BY p.IdProducto ASC";

            $st = $conexion->prepare($sql);
            $st->bind_param($types, ...$params);
            $st->execute();
            $r = $st->get_result();
            while ($row = $r->fetch_assoc()) {
                $rows[] = [
                    (int)$row['IdProducto'],
                    $row['Nombre'],
                    $row['Categoria'],
                    (int)$row['CantComprada'],
                    (int)$row['CantDisponible'],
                    (int)$row['CantVendida'],
                    (float)$row['Precio_de_Venta'],
                    (float)$row['TotalGanado'],
                ];
            }
            break;

        case 'compras':
            $cols = [
                'Comprado por',
                'Fecha de compra',
                'Proveedor',
                'Producto',
                'Categoría',
                'Cant. comprada',
                'Precio de compra (C$)',
                'Total de compra (C$)'
            ];

            $sql = "
              SELECT
                u.Nombre_de_Usuario AS Comprador,
                co.Fecha,
                pr.Nombre AS Proveedor,
                p.Nombre  AS Producto,
                cat.Descripcion AS Categoria,
                dc.Cantidad,
                dc.PrecioUnitario,
                dc.Subtotal
              FROM compra co
              JOIN usuario u         ON u.IdUsuario    = co.IdUsuario
              JOIN proveedor pr      ON pr.IdProveedor = co.IdProveedor
              JOIN detalle_Compra dc ON dc.IdCompra    = co.IdCompra
              JOIN producto p        ON p.IdProducto   = dc.IdProducto
              JOIN categoria cat     ON cat.IdCategoria = p.IdCategoria
              WHERE co.Fecha BETWEEN ? AND ?
            ";

            $types  = "ss";
            $params = [$desde, $hasta];

            if ($proveedor !== '') {
                $sql   .= " AND co.IdProveedor = ? ";
                $types .= "i";
                $params[] = (int)$proveedor;
            }
            if ($producto !== '') {
                $sql   .= " AND p.IdProducto = ? ";
                $types .= "i";
                $params[] = (int)$producto;
            }

            $sql .= " ORDER BY co.Fecha ASC, co.IdCompra ASC";

            $st = $conexion->prepare($sql);
            $st->bind_param($types, ...$params);
            $st->execute();
            $r = $st->get_result();
            while ($row = $r->fetch_assoc()) {
                $rows[] = [
                    $row['Comprador'],
                    $row['Fecha'],
                    $row['Proveedor'],
                    $row['Producto'],
                    $row['Categoria'],
                    (int)$row['Cantidad'],
                    (float)$row['PrecioUnitario'],
                    (float)$row['Subtotal'],
                ];
            }
            break;

        case 'empleados':
            $cols = [
                'Cédula',
                'Nombre y Apellidos',
                'Cargo',
                'Salario básico (C$)',
                'Salario bruto (C$)',
                'Deducción total (C$)',
                'Salario neto (C$)'
            ];

            $sql = "
              SELECT
                n.Cedula,
                CONCAT(e.Nombre, ' ', e.Apellido) AS NombreCompleto,
                c.Nombre AS Cargo,
                n.SalarioBasico,
                n.SalarioBruto,
                n.DeduccionTotal,
                n.SalarioNeto
              FROM nomina n
              JOIN empleado e ON e.Cedula  = n.Cedula
              JOIN cargo    c ON c.IdCargo = e.IdCargo
              WHERE n.FechaRegistro BETWEEN ? AND ?
            ";

            $types  = "ss";
            $params = [$desde, $hasta];

            if ($cedula !== '') {
                $sql   .= " AND n.Cedula = ? ";
                $types .= "s";
                $params[] = $cedula;
            }

            $sql .= " ORDER BY n.FechaRegistro ASC, n.IdNomina ASC";

            $st = $conexion->prepare($sql);
            $st->bind_param($types, ...$params);
            $st->execute();
            $r = $st->get_result();

            while ($row = $r->fetch_assoc()) {
                $rows[] = [
                    $row['Cedula'],
                    $row['NombreCompleto'],
                    $row['Cargo'],
                    (float)$row['SalarioBasico'],
                    (float)$row['SalarioBruto'],
                    (float)$row['DeduccionTotal'],
                    (float)$row['SalarioNeto'],
                ];
            }
            break;

        case 'salidas':
            $cols = [
                'Realizado por',
                'Fecha de salida',
                'Cliente',
                'Método de pago',
                'Cant. productos vendidos',
                'Total de venta (C$)'
            ];

            $sql = "
              SELECT
                u.Nombre_de_Usuario AS Vendedor,
                s.Fecha,
                CONCAT(c.Nombre,' ',c.Apellido) AS Cliente,
                s.Metodo_de_pago,
                SUM(ds.Cantidad) AS CantProductos,
                SUM(ds.Subtotal) AS TotalVenta
              FROM salida_de_stock s
              JOIN usuario u ON u.IdUsuario = s.IdUsuario
              JOIN cliente c ON c.IdCliente = s.IdCliente
              JOIN detalle_de_salida ds ON ds.IdVenta = s.IdVenta
              WHERE s.Fecha BETWEEN ? AND ?
            ";

            $types  = "ss";
            $params = [$desde, $hasta];

            if ($cliente !== '') {
                $sql   .= " AND s.IdCliente = ? ";
                $types .= "i";
                $params[] = (int)$cliente;
            }

            $sql .= "
              GROUP BY s.IdVenta
              ORDER BY s.Fecha ASC, s.IdVenta ASC
            ";

            $st = $conexion->prepare($sql);
            $st->bind_param($types, ...$params);
            $st->execute();
            $r = $st->get_result();
            while ($row = $r->fetch_assoc()) {
                $rows[] = [
                    $row['Vendedor'],
                    $row['Fecha'],
                    $row['Cliente'],
                    $row['Metodo_de_pago'],
                    (int)$row['CantProductos'],
                    (float)$row['TotalVenta'],
                ];
            }
            break;

        default:
            echo json_encode(['ok' => false, 'msg' => 'Tipo de reporte inválido']);
            exit;
    }

    echo json_encode([
        'ok'      => true,
        'columns' => $cols,
        'rows'    => $rows
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error: '.$e->getMessage()]);
}
