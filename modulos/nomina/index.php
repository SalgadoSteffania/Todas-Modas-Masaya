<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit('No autorizado'); }

require_once dirname(__DIR__, 2) . '/conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/*Cargar empleados para autocompletar cédula/nombre/cargo */
$empleados = [];
$sql = "SELECT e.Cedula,
               e.Nombre,
               e.Apellido,
               c.Nombre AS Cargo
        FROM Empleado e
        JOIN Cargo c ON c.IdCargo = e.IdCargo
        ORDER BY e.Nombre, e.Apellido";
$res = $conexion->query($sql);
while ($row = $res->fetch_assoc()) {
    $empleados[] = $row;
}

$jsonEmpleados = htmlspecialchars(
    json_encode($empleados, JSON_UNESCAPED_UNICODE),
    ENT_QUOTES,
    'UTF-8'
);
?>
<div class="vistas-wrapper">
  <h2 class="titulo-principal">Lista de Nómina</h2>

  <div class="tabla-card">
    <div class="card-top">
      <button id="btnNuevaNomina" class="btn-nuevo">Nueva nómina</button>
    </div>

    <!-- Filtros -->
    <div class="card-actions" style="flex-wrap: wrap;">
      <input
        type="text"
        id="filtroCedula"
        class="input-buscar"
        style="max-width: 320px;"
        placeholder="Buscar por cédula o nombre"
      >

      <div style="display:flex; align-items:center; gap:8px;">
        <label style="font-size:13px;">Desde:</label>
        <input type="date" id="nominaDesde" class="input-date" style="width:160px;">
      </div>

      <div style="display:flex; align-items:center; gap:8px;">
        <label style="font-size:13px;">Hasta:</label>
        <input type="date" id="nominaHasta" class="input-date" style="width:160px;">
      </div>
    </div>

    <!-- Tabla principal -->
    <table class="tabla-vistas" id="tablaNomina">
      <thead>
        <tr>
          <th style="width:80px;">ID Nómina</th>
          <th>Cédula</th>
          <th>Nombre y Apellidos</th>
          <th>Cargo</th>
          <th>Fecha registro</th>
          <th>Salario básico</th>
          <th>Salario bruto</th>
          <th>Deducción total</th>
          <th>Salario neto</th>
          <th style="width:90px;">Ver nómina</th>
          <th style="width:70px;">Editar</th>
          <th style="width:80px;">Eliminar</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- Datos de empleados para JS -->
<div id="empleadosDataNomina"
     data-empleados='<?= $jsonEmpleados ?>'
     style="display:none;"></div>

<!-- Modal Crear / Editar Nómina -->
<div class="modal" id="modalNomina" aria-hidden="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTituloNomina">Nueva nómina</h3>
    </div>

    <form id="formNomina" autocomplete="off">
      <input type="hidden" name="IdNomina" id="IdNomina">

      <!-- Cédula con datalist -->
      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="CedulaNomina">Cédula del empleado</label>
          <input
            type="text"
            id="CedulaNomina"
            name="Cedula"
            list="listaCedulasNomina"
            placeholder="Escriba la cédula"
            required
          >
          <datalist id="listaCedulasNomina">
            <?php foreach ($empleados as $e): ?>
              <option value="<?= htmlspecialchars($e['Cedula']) ?>">
                <?= htmlspecialchars($e['Cedula'] . ' - ' . $e['Nombre'] . ' ' . $e['Apellido']) ?>
              </option>
            <?php endforeach; ?>
          </datalist>
        </div>
      </div>

      <!-- Nombre y Cargo para autocompletar-->
      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="NombreEmpleadoNomina">Nombre y Apellidos</label>
          <input type="text" id="NombreEmpleadoNomina" readonly>
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="CargoEmpleadoNomina">Cargo</label>
          <input type="text" id="CargoEmpleadoNomina" readonly>
        </div>
      </div>

      <!-- Fecha registro -->
      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="FechaRegistroNomina">Fecha de registro</label>
          <input
            type="date"
            id="FechaRegistroNomina"
            name="FechaRegistro"
            value="<?= date('Y-m-d'); ?>"
          >
        </div>
      </div>

      <!-- Salario básico / horas extras -->
      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="SalarioBasicoNomina">Salario básico (C$)</label>
          <input
            type="number"
            id="SalarioBasicoNomina"
            name="SalarioBasico"
            min="0"
            step="0.01"
            required
          >
        </div>
        <div class="form-field">
          <label for="HorasExtrasNomina">Horas extras</label>
          <input
            type="number"
            id="HorasExtrasNomina"
            name="HorasExtras"
            min="0"
            max="36"
            step="0.5"
            value="0"
          >
        </div>
      </div>

      <!-- Bonos / Incentivos -->
      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="BonosNomina">Bonos (C$)</label>
          <input
            type="number"
            id="BonosNomina"
            name="Bonos"
            min="0"
            step="0.01"
            value="0"
          >
        </div>
        <div class="form-field">
          <label for="IncentivosNomina">Incentivos (C$)</label>
          <input
            type="number"
            id="IncentivosNomina"
            name="Incentivos"
            min="0"
            step="0.01"
            value="0"
          >
        </div>
      </div>

      <!-- Préstamos -->
      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="PrestamosNomina">Préstamos (C$)</label>
          <input
            type="number"
            id="PrestamosNomina"
            name="Prestamos"
            min="0"
            step="0.01"
            value="0"
          >
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancelar" id="btnCancelarNomina">Cancelar</button>
        <button type="submit" class="btn-guardar" id="btnGuardarNomina">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Modal Ver Nómina -->
<div class="modal" id="modalVerNomina" aria-hidden="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3>Detalle de Nómina</h3>
    </div>

    <div class="factura-header">
      <p><strong>ID Nómina:</strong> <span id="verIdNomina"></span></p>
      <p><strong>Cédula:</strong> <span id="verCedula"></span></p>
      <p><strong>Empleado:</strong> <span id="verNombre"></span></p>
      <p><strong>Cargo:</strong> <span id="verCargo"></span></p>
      <p><strong>Fecha:</strong> <span id="verFecha"></span></p>
    </div>

    <table class="tabla-empleados" id="tablaDetalleNomina">
      <thead>
        <tr>
          <th>Concepto</th>
          <th>Valor (C$)</th>
        </tr>
      </thead>
      <tbody id="verDetalleBody"></tbody>
    </table>

    <div class="factura-total">
      <p><strong>Salario Neto:</strong> <span id="verSalarioNeto"></span></p>
    </div>

    <div class="modal-actions">
      <a id="btnDescargarNomina" href="#" class="btn-guardar" target="_blank">
        Descargar PDF
      </a>
      <button type="button" class="btn-cancelar" id="btnCerrarVerNomina">Cerrar</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="toast" style="display:none;"></div>
