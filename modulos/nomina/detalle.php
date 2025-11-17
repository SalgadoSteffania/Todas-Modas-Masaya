<?php
session_start();
if (!isset($_SESSION['correo'])) {
    http_response_code(401);
    echo json_encode(['ok'=>false,'msg'=>'No autorizado']);
    exit;
}

require_once dirname(__DIR__, 2) . '/conexion.php';
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $IdNomina = (int)($_GET['IdNomina'] ?? ($_POST['IdNomina'] ?? 0));
    if ($IdNomina <= 0) {
        echo json_encode(['ok'=>false,'msg'=>'IdNomina inválido']);
        exit;
    }

    $sql = "SELECT 
                n.IdNomina,
                n.Cedula,
                CONCAT(e.Nombre,' ',e.Apellido) AS NombreCompleto,
                ca.Nombre AS Cargo,
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
                d.ValorHorasExtra
            FROM Nomina n
            JOIN Empleado e  ON e.Cedula  = n.Cedula
            JOIN Cargo ca    ON ca.IdCargo = e.IdCargo
            JOIN DetalleNomina d ON d.IdNomina = n.IdNomina
            WHERE n.IdNomina = ?
            LIMIT 1";

    $st = $conexion->prepare($sql);
    $st->bind_param("i", $IdNomina);
    $st->execute();
    $res = $st->get_result();
    $row = $res->fetch_assoc();

    if (!$row) {
        echo json_encode(['ok'=>false,'msg'=>'Nómina no encontrada']);
        exit;
    }

    $cabecera = [
        'IdNomina'       => (int)$row['IdNomina'],
        'Cedula'         => $row['Cedula'],
        'NombreCompleto' => $row['NombreCompleto'],
        'Cargo'          => $row['Cargo'],                // ← nombre del cargo
        'SalarioBasico'  => (float)$row['SalarioBasico'],
        'SalarioBruto'   => (float)$row['SalarioBruto'],
        'INNS'           => (float)$row['INNS'],
        'IR'             => (float)$row['IR'],
        'DeduccionTotal' => (float)$row['DeduccionTotal'],
        'SalarioNeto'    => (float)$row['SalarioNeto'],
        'FechaRegistro'  => $row['FechaRegistro'],
    ];

    $detalle = [
        'HorasExtras'     => (float)$row['HorasExtras'],
        'Bonos'           => (float)$row['Bonos'],
        'Incentivos'      => (float)$row['Incentivos'],
        'Prestamos'       => (float)$row['Prestamos'],
        'ValorHorasExtra' => (float)$row['ValorHorasExtra'], // ← importante
    ];

    echo json_encode([
        'ok'       => true,
        'cabecera' => $cabecera,
        'detalle'  => $detalle,
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['ok'=>false,'msg'=>'Error al obtener nómina: '.$e->getMessage()]);
}
