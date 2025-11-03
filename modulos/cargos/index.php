<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit('No autorizado'); }
?>
<div class="vistas-wrapper">
  <h2 class="titulo-principal">Lista de Cargos</h2>

  <div class="tabla-card">
    <div class="card-top">
      <button id="btnNuevoCargo" class="btn-nuevo">Nuevo cargo</button>
    </div>

    <div class="card-actions">
      <input type="text" id="buscarCargo" class="input-buscar"
             placeholder="Buscar por nombre">
    </div>

    <table class="tabla-vistas" id="tablaCargos">
      <thead>
        <tr>
          <th style="width:110px;">ID</th>
          <th>Nombre</th>
          <th style="width:80px;">Editar</th>
          <th style="width:90px;">Eliminar</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>


<div class="modal" id="modalCargo" aria-hidden="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTituloCargo">Información del cargo</h3>
    </div>

    <form id="formCargo" autocomplete="off">
      <input type="hidden" name="IdCargo" id="IdCargo">

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="NombreCargo">Nombre</label>
          <input type="text" name="Nombre" id="NombreCargo" required>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancelar" id="btnCancelarCargo">Cancelar</button>
        <button type="submit" class="btn-guardar" id="btnGuardarCargo">Guardar</button>
      </div>
    </form>
  </div>
</div>


<div class="modal" id="modalConfirmCargo" aria-hidden="true">
  <div class="modal-content small">
    <div class="modal-header"><h3>Confirmar eliminación</h3></div>
    <p>¿Está seguro de eliminar este cargo?</p>
    <div class="modal-actions">
      <button type="button" class="btn-cancelar" id="btnNoCargo">No</button>
      <button type="button" class="btn-eliminar" id="btnSiCargo">Sí, eliminar</button>
    </div>
  </div>
</div>


<div id="toast" class="toast" style="display:none;"></div>
