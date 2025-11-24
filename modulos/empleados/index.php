<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit('No autorizado'); }
require_once dirname(__DIR__, 2) . '/conexion.php';


// Traer cargos para el combobox
$cargos = [];
$q = $conexion->query("SELECT IdCargo, Nombre FROM Cargo ORDER BY Nombre");
while ($row = $q->fetch_assoc()) $cargos[] = $row;
?>
<div class="vistas-wrapper">
  <h2 class="titulo-principal">Lista de Empleados</h2>
  <div class="tabla-card">
    <div class="card-top">
      <button id="btnNuevoEmpleado" class="btn-nuevo">Nuevo empleado</button>
    </div>
    <div class="card-actions">
      <input type="text" id="buscarEmpleado" class="input-buscar"
             placeholder="Buscar por cédula, nombre o apellido">
    </div>

    <table class="tabla-vistas" id="tablaEmpleados">
      <thead>
        <tr>
          <th>Cédula</th>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Dirección</th>
          <th>Teléfono</th>
          <th>Cargo</th>
          <th>Editar</th>
          <th>Eliminar</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>


<!-- Modal Crear y Editar -->
<div class="modal" id="modalEmpleado" aria-hidden="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTitulo">Información del empleado</h3>
    </div>
    <form id="formEmpleado" autocomplete="off">
      <input type="hidden" name="originalCedula" id="originalCedula">

      <div class="form-row">
        <div class="icon-slot"><!-- ícono futuro --></div>
        <div class="form-field">
          <label for="Cedula">Cédula</label>
          <input type="text" name="Cedula" id="Cedula" required placeholder="001-000000-0000A">
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"><!-- ícono futuro --></div>
        <div class="form-field">
          <label for="Nombre">Nombre</label>
          <input type="text" name="Nombre" id="Nombre" required>
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"><!-- ícono futuro --></div>
        <div class="form-field">
          <label for="Apellido">Apellido</label>
          <input type="text" name="Apellido" id="Apellido" required>
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"><!-- ícono futuro --></div>
        <div class="form-field">
          <label for="Direccion">Dirección</label>
          <input type="text" name="Direccion" id="Direccion" required>
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"><!-- ícono futuro --></div>
        <div class="form-field">
          <label for="Telefono">Teléfono</label>
          <input type="text" name="Telefono" id="Telefono" maxlength="8"
       oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"><!-- ícono futuro --></div>
        <div class="form-field">
          <label for="IdCargo">Cargo</label>
          <select name="IdCargo" id="IdCargo" required>
            <option value="" disabled selected>Seleccione un cargo</option>
            <?php foreach ($cargos as $c): ?>
              <option value="<?= htmlspecialchars($c['IdCargo']) ?>">
                <?= htmlspecialchars($c['Nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancelar" id="btnCancelar">Cancelar</button>
        <button type="submit" class="btn-guardar" id="btnGuardar">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Confirmación al Eliminar -->
<div class="modal" id="modalConfirm" aria-hidden="true">
  <div class="modal-content small">
    <div class="modal-header">
      <h3>Confirmar eliminación</h3>
    </div>
    <p id="confirmText">¿Está seguro de eliminar al empleado?</p>
    <div class="modal-actions">
      <button type="button" class="btn-cancelar" id="btnNo">No</button>
      <button type="button" class="btn-eliminar" id="btnSi">Sí, eliminar</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="toast" style="display:none;"></div>

<script>

  window.EMPLEADOS_CARGOS = <?= json_encode($cargos, JSON_UNESCAPED_UNICODE) ?>;
</script>
