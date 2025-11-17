<?php
session_start();
if (!isset($_SESSION['correo'])) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['ok'=>false,'msg'=>'No autorizado']);
    exit;
}

require_once dirname(__DIR__, 2) . '/conexion.php';
header('Content-Type: application/json; charset=utf-8');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* Calculos */

function calcularIRMensual(float $salarioBruto, float $inssLaboral): float {
    $gravableMensual = $salarioBruto - $inssLaboral;
    if ($gravableMensual <= 0) {
        return 0.0;
    } 
    $tramos = [
        ['desde' => 0.01,       'hasta' => 100000.00, 'base' => 0.00,     'porcentaje' => 0.00, 'exceso' => 0.00],
        ['desde' => 100000.01,  'hasta' => 200000.00, 'base' => 0.00,     'porcentaje' => 0.15, 'exceso' => 100000.00],
        ['desde' => 200000.01,  'hasta' => 350000.00, 'base' => 15000.00, 'porcentaje' => 0.20, 'exceso' => 200000.00],
        ['desde' => 350000.01,  'hasta' => 500000.00, 'base' => 45000.00, 'porcentaje' => 0.25, 'exceso' => 350000.00],
        ['desde' => 500000.01,  'hasta' => INF,       'base' => 82500.00, 'porcentaje' => 0.30, 'exceso' => 500000.00],
    ];

    $irMensual = 0.0;

    foreach ($tramos as $t) {
        if ($gravableMensual >= $t['desde'] && $gravableMensual <= $t['hasta']) {
            if ($t['porcentaje'] <= 0) {
                $irMensual = 0.0;
            } else {
                $sobreExceso = $gravableMensual - $t['exceso'];
                if ($sobreExceso < 0) $sobreExceso = 0;
                $irMensual = $sobreExceso * $t['porcentaje'] + $t['base'];
            }
            break;
        }
    }

    return max(0.0, $irMensual);
}


function calcularNomina(
    float $salarioBasico,
    float $horasExtras,
    float $bonos,
    float $incentivos,
    float $prestamos
): array {
    if ($horasExtras < 0)  $horasExtras = 0;
    if ($horasExtras > 36) $horasExtras = 36;

    $valorDia   = $salarioBasico / 30.0;
    $valorHora  = $valorDia / 8.0;
    $valorHE = $valorHora * $horasExtras * 2;


    $salarioBruto = $salarioBasico + $valorHE + $bonos + $incentivos;

    $inss = $salarioBruto * 0.07;

    $irMensual = calcularIRMensual($salarioBruto, $inss);

    $deduccionTotal = $inss + $irMensual + $prestamos;

    $salarioNeto = $salarioBruto - $deduccionTotal;

    return [
        'SalarioBasico'   => $salarioBasico,
        'ValorHorasExtra' => $valorHE,
        'SalarioBruto'    => $salarioBruto,
        'INNS'            => $inss,
        'IR'              => $irMensual,
        'DeduccionTotal'  => $deduccionTotal,
        'SalarioNeto'     => $salarioNeto,
    ];
}

try {
    $IdNomina      = (int)($_POST['IdNomina'] ?? 0);
    $Cedula        = trim($_POST['Cedula'] ?? '');
    $SalarioBase   = (float)($_POST['SalarioBasico'] ?? 0);
    $HorasExtras   = (float)($_POST['HorasExtras'] ?? 0);
    $Bonos         = (float)($_POST['Bonos'] ?? 0);
    $Incentivos    = (float)($_POST['Incentivos'] ?? 0);
    $Prestamos     = (float)($_POST['Prestamos'] ?? 0);
    $FechaRegistro = $_POST['FechaRegistro'] ?? date('Y-m-d');

    if ($IdNomina <= 0 || $Cedula === '' || $SalarioBase <= 0) {
        echo json_encode(['ok'=>false,'msg'=>'Datos inválidos']);
        exit;
    }

    // Verificar que la nómina exista
    $stmtCheck = $conexion->prepare("SELECT IdNomina FROM Nomina WHERE IdNomina=?");
    $stmtCheck->bind_param("i", $IdNomina);
    $stmtCheck->execute();
    if (!$stmtCheck->get_result()->fetch_assoc()) {
        echo json_encode(['ok'=>false,'msg'=>'Nómina no encontrada']);
        exit;
    }

    // Verificar que exista el empleado
    $stmtEmp = $conexion->prepare("SELECT Cedula FROM Empleado WHERE Cedula=?");
    $stmtEmp->bind_param("s", $Cedula);
    $stmtEmp->execute();
    $resEmp = $stmtEmp->get_result();
    if (!$resEmp->fetch_assoc()) {
        echo json_encode(['ok'=>false,'msg'=>'Empleado no encontrado']);
        exit;
    }

    $cal = calcularNomina($SalarioBase, $HorasExtras, $Bonos, $Incentivos, $Prestamos);

    $conexion->begin_transaction();

    // Actualizar Nomina
    $stmt = $conexion->prepare(
      "UPDATE Nomina
       SET Cedula=?, SalarioBasico=?, SalarioBruto=?, INNS=?, IR=?, DeduccionTotal=?, SalarioNeto=?, FechaRegistro=?
       WHERE IdNomina=?"
    );
    $stmt->bind_param(
      "sddddddsi",
      $Cedula,
      $cal['SalarioBasico'],
      $cal['SalarioBruto'],
      $cal['INNS'],
      $cal['IR'],
      $cal['DeduccionTotal'],
      $cal['SalarioNeto'],
      $FechaRegistro,
      $IdNomina
    );
    $stmt->execute();

    // Actualizar DetalleNomina
    $stmtDet = $conexion->prepare(
      "UPDATE DetalleNomina
       SET HorasExtras=?, Bonos=?, Incentivos=?, Prestamos=?
       WHERE IdNomina=?"
    );
    $stmtDet->bind_param(
      "ddddi",
      $HorasExtras,
      $Bonos,
      $Incentivos,
      $Prestamos,
      $IdNomina,
    );
    $stmtDet->execute();

    $conexion->commit();
    echo json_encode(['ok'=>true,'msg'=>'Nómina actualizada']);

} catch (Exception $e) {
    if ($conexion && $conexion->errno) {
        $conexion->rollback();
    }
    echo json_encode(['ok'=>false,'msg'=>'Error: '.$e->getMessage()]);
}
