<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit('No autorizado'); }
?>

<div class="vistas-wrapper">
  <h2 class="titulo-principal">Lista de Proveedores</h2>

  <div class="tabla-card">
    <div class="card-top">
      <button id="btnNuevoProveedor" class="btn-nuevo">Nuevo proveedor</button>
    </div>

    <div class="card-actions">
      <input type="text" id="buscarProveedor" class="input-buscar"
             placeholder="Buscar por nombre, email o teléfono">
    </div>

    <table class="tabla-vistas" id="tablaProveedores">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Teléfono</th>
          <th>Email</th>
          <th>Dirección</th>
          <th>Fecha registro</th>
          <th style="width:80px;">Editar</th>
          <th style="width:90px;">Eliminar</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- Modal Crear y Editar -->
<div class="modal" id="modalProveedor" aria-hidden="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTituloProv">Información del proveedor</h3>
    </div>

    <form id="formProveedor" autocomplete="off">
      <input type="hidden" name="IdProveedor" id="IdProveedor">

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="Nombre">Nombre</label>
          <input type="text" name="Nombre" id="Nombre" required>
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="Telefono">Teléfono</label>
           <input type="text" name="Telefono" id="Telefono" maxlength="8"
       oninput="this.value = this.value.replace(/[^0-9]/g, '');" required>
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="Email">Email</label>
          <input type="text" name="Email" id="Email" required placeholder="usuario@gmail.com">
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="Direccion">Dirección</label>
          <input type="text" name="Direccion" id="Direccion" required>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancelar" id="btnCancelarProv">Cancelar</button>
        <button type="submit" class="btn-guardar" id="btnGuardarProv">Guardar</button>
      </div>
    </form>
  </div>
</div>

<!-- Confirmación al eliminar -->
<div class="modal" id="modalConfirmProv" aria-hidden="true">
  <div class="modal-content small">
    <div class="modal-header"><h3>Confirmar eliminación</h3></div>
    <p>¿Está seguro de eliminar este proveedor?</p>
    <div class="modal-actions">
      <button type="button" class="btn-cancelar" id="btnNoProv">No</button>
      <button type="button" class="btn-eliminar" id="btnSiProv">Sí, eliminar</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="toast" class="toast" style="display:none;"></div>
