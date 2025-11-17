<?php
session_start();
if (!isset($_SESSION['correo'])) {
    http_response_code(401);
    exit('No autorizado');
}

require_once dirname(__DIR__, 2) . '/conexion.php';
require_once dirname(__DIR__, 2) . '/tcpdf/tcpdf.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$IdNomina = (int)($_GET['IdNomina'] ?? 0);
if ($IdNomina <= 0) {
    die('IdNomina inválido');
}

$sql = "SELECT
          n.IdNomina,
          n.Cedula,
          n.SalarioBasico,
          n.SalarioBruto,
          n.INNS,
          n.IR,
          n.DeduccionTotal,
          n.SalarioNeto,
          n.FechaRegistro,
          d.HorasExtras,
          d.Bonos,
          d.Incentivos,
          d.Prestamos,
          e.Nombre,
          e.Apellido,
          c.Nombre AS Cargo
        FROM Nomina n
        JOIN DetalleNomina d ON d.IdNomina = n.IdNomina
        JOIN Empleado e      ON e.Cedula   = n.Cedula
        JOIN Cargo    c      ON c.IdCargo  = e.IdCargo
        WHERE n.IdNomina = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $IdNomina);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    die('Nómina no encontrada');
}

// Valor horas extra
$valorDia   = $row['SalarioBasico'] / 30.0;
$valorHora  = $valorDia / 8.0;
$valorHE    = $valorHora * $row['HorasExtras'] * 2.0;

$pdf = new TCPDF();
$pdf->SetCreator('Toda Moda Masaya');
$pdf->SetAuthor('Sistema Web');
$pdf->SetTitle('Nómina #' . $IdNomina);
$pdf->SetMargins(20, 25, 20);
$pdf->AddPage();

$empleado   = $row['Nombre'] . ' ' . $row['Apellido'];
$cedula     = $row['Cedula'];
$cargo      = $row['Cargo'];
$fecha      = $row['FechaRegistro'];

$html = '
<h2 style="text-align:center; color:#e014ca;">Nómina de Empleado</h2>
<hr>
<h4>Datos del Empleado</h4>
<table border="0" cellspacing="2" cellpadding="3">
  <tr><td><strong>ID Nómina:</strong></td><td>'. $row['IdNomina'] .'</td></tr>
  <tr><td><strong>Cédula:</strong></td><td>'. $cedula .'</td></tr>
  <tr><td><strong>Empleado:</strong></td><td>'. $empleado .'</td></tr>
  <tr><td><strong>Cargo:</strong></td><td>'. $cargo .'</td></tr>
  <tr><td><strong>Fecha:</strong></td><td>'. $fecha .'</td></tr>
</table>

<br><h4>Detalle de Nómina</h4>
<table border="1" cellpadding="4">
  <thead>
    <tr style="background-color:#fce4ec;">
      <th><b>Concepto</b></th>
      <th><b>Monto (C$)</b></th>
    </tr>
  </thead>
  <tbody>
    <tr><td>Salario básico</td><td align="right">'. number_format($row['SalarioBasico'],2) .'</td></tr>
    <tr><td>Horas extras</td><td align="right">'. number_format($row['HorasExtras']) .'</td></tr>
    <tr><td>Valor de horas extras</td><td align="right">'. number_format($valorHE,2) .'</td></tr>
    <tr><td>Bonos</td><td align="right">'. number_format($row['Bonos'],2) .'</td></tr>
    <tr><td>Incentivos</td><td align="right">'. number_format($row['Incentivos'],2) .'</td></tr>
    <tr><td><strong>Salario bruto</strong></td><td align="right"><strong>'. number_format($row['SalarioBruto'],2) .'</strong></td></tr>
    <tr><td>INSS laboral (7%)</td><td align="right">'. number_format($row['INNS'],2) .'</td></tr>
    <tr><td>IR mensual</td><td align="right">'. number_format($row['IR'],2) .'</td></tr>
    <tr><td>Préstamos</td><td align="right">'. number_format($row['Prestamos'],2) .'</td></tr>
    <tr><td><strong>Deducción total</strong></td><td align="right"><strong>'. number_format($row['DeduccionTotal'],2) .'</strong></td></tr>
    <tr><td><strong>Salario neto</strong></td><td align="right"><strong>'. number_format($row['SalarioNeto'],2) .'</strong></td></tr>
  </tbody>
</table>

<br><br><i>Este documento ha sido generado por el sistema de nómina.</i>
';

$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('Nomina_' . $IdNomina . '.pdf', 'I');
