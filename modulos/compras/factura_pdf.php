<?php
session_start();
if (!isset($_SESSION['correo'])) {
    http_response_code(401);
    exit('No autorizado');
}

require_once dirname(__DIR__, 2) . '/conexion.php';
require_once dirname(__DIR__, 2) . '/TCPDF/tcpdf.php';



$IdCompra = intval($_GET['IdCompra'] ?? 0);
if ($IdCompra <= 0) {
    die('ID de compra inválido.');
}

$sqlCab = "SELECT 
              c.IdCompra,
              c.Fecha,
              u.Nombre_de_Usuario AS Comprador,
              p.Nombre   AS Proveedor,
              p.Telefono,
              p.Email,
              p.Direccion
            FROM compra c
            JOIN usuario   u ON u.IdUsuario   = c.IdUsuario
            JOIN proveedor p ON p.IdProveedor = c.IdProveedor
            WHERE c.IdCompra = ?";

$stmt = $conexion->prepare($sqlCab);
$stmt->bind_param("i", $IdCompra);
$stmt->execute();
$cab = $stmt->get_result()->fetch_assoc();

if (!$cab) {
    die('Compra no encontrada.');
}

$sqlDet = "SELECT 
              pr.Nombre AS Producto,
              pr.Marca,
              pr.Talla,
              d.Cantidad,
              d.PrecioUnitario,
              d.Subtotal
            FROM detalle_compra d
            JOIN producto pr ON pr.IdProducto = d.IdProducto
            WHERE d.IdCompra = ?";

$stmt2 = $conexion->prepare($sqlDet);
$stmt2->bind_param("i", $IdCompra);
$stmt2->execute();
$resDet = $stmt2->get_result();

$items = [];
$total = 0;
while ($row = $resDet->fetch_assoc()) {
    $total   += (float)$row['Subtotal'];
    $items[]  = $row;
}

/* ===== CREACIÓN DEL PDF ===== */
$pdf = new TCPDF();
$pdf->SetCreator('Toda Moda Masaya');
$pdf->SetAuthor('Sistema Web');
$pdf->SetTitle('Factura de Compra #' . $IdCompra);
$pdf->SetMargins(20, 25, 20);
$pdf->AddPage();

/* ===== CONTENIDO HTML ===== */
$html = '
<h2 style="text-align:center; color:#e014ca;">Factura de Compra</h2>
<hr>
<h4>Datos de la Compra</h4>
<table border="0" cellspacing="2" cellpadding="3">
  <tr><td><strong>ID Compra:</strong></td><td>' . $cab['IdCompra'] . '</td></tr>
  <tr><td><strong>Fecha:</strong></td><td>' . $cab['Fecha'] . '</td></tr>
  <tr><td><strong>Comprador:</strong></td><td>' . $cab['Comprador'] . '</td></tr>
  <tr><td><strong>Proveedor:</strong></td><td>' . $cab['Proveedor'] . '</td></tr>
  <tr><td><strong>Teléfono:</strong></td><td>' . $cab['Telefono'] . '</td></tr>
  <tr><td><strong>Email:</strong></td><td>' . $cab['Email'] . '</td></tr>
  <tr><td><strong>Dirección:</strong></td><td>' . $cab['Direccion'] . '</td></tr>
</table>

<br><h4>Detalle de Productos</h4>
<table border="1" cellpadding="4">
  <thead>
    <tr style="background-color:#fce4ec;">
      <th><b>Producto</b></th>
      <th><b>Marca</b></th>
      <th><b>Talla</b></th>
      <th><b>Cantidad</b></th>
      <th><b>Precio Unitario (C$)</b></th>
      <th><b>Subtotal (C$)</b></th>
    </tr>
  </thead>
  <tbody>';

foreach ($items as $it) {
    $html .= '
    <tr>
      <td>' . htmlspecialchars($it['Producto']) . '</td>
      <td>' . htmlspecialchars($it['Marca']) . '</td>
      <td>' . htmlspecialchars($it['Talla']) . '</td>
      <td align="center">' . $it['Cantidad'] . '</td>
      <td align="right">' . number_format($it['PrecioUnitario'], 2) . '</td>
      <td align="right">' . number_format($it['Subtotal'], 2) . '</td>
    </tr>';
}

$html .= '
  </tbody>
</table>
<br><h3 style="text-align:right;">Total: C$ ' . number_format($total, 2) . '</h3>
<br><br><i>Gracias por su compra.</i>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Factura_Compra_' . $IdCompra . '.pdf', 'I');
