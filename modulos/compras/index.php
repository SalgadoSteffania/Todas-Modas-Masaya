<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit('No autorizado'); }

require_once dirname(__DIR__, 2) . '/conexion.php';

/* Proveedores para el cbx */
$proveedores = [];
$sqlProv = "SELECT IdProveedor, Nombre FROM Proveedor ORDER BY Nombre";
$resProv = $conexion->query($sqlProv);
if ($resProv) {
  while ($row = $resProv->fetch_assoc()) {
    $proveedores[] = $row;
  }
}

/* Productos para el cbx */
$productos = [];
$sqlProd = "SELECT IdProducto, Nombre, Talla, Marca FROM Producto ORDER BY Nombre";
$resProd = $conexion->query($sqlProd);
if ($resProd) {
  while ($row = $resProd->fetch_assoc()) {
    $productos[] = $row;
  }
}
?>
<div class="vistas-wrapper">
  <h2 class="titulo-principal">Lista de Compras</h2>

  <div class="tabla-card">
    <div class="card-top">
      <button id="btnNuevaCompra" class="btn-nuevo">Nueva compra</button>
    </div>

    <div class="card-actions">
      <!-- Filtro por rango de fechas -->
      <input type="date" id="fechaDesde" class="input-date" placeholder="Desde">
      <span class="rango-separador">a</span>
      <input type="date" id="fechaHasta" class="input-date" placeholder="Hasta">

     
      <input
        type="text"
        id="buscarCompra"
        class="input-buscar"
        placeholder="Buscar por comprador o proveedor"
      >
    </div>


    <table class="tabla-vistas" id="tablaCompras">
      <thead>
        <tr>
          <th style="width:80px;">ID Compra</th>
          <th>Comprador</th>
          <th>Proveedor</th>
          <th>Fecha de compra</th>
          <th style="width:110px;">Ver factura</th>
          <th style="width:80px;">Editar</th>
          <th style="width:90px;">Eliminar</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<!-- Modal Crear / Editar Compra -->
<div class="modal" id="modalCompra" aria-hidden="true">
  <div class="modal-content large">
    <div class="modal-header">
      <h3 id="modalTituloCompra">Nueva compra</h3>
    </div>

    <form id="formCompra" autocomplete="off">
      <input type="hidden" name="IdCompra" id="IdCompra">

      <!-- Datos generales -->
      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="IdProveedor">Proveedor</label>
          <select name="IdProveedor" id="IdProveedor" required>
            <option value="" disabled selected>Seleccione proveedor</option>
            <?php foreach ($proveedores as $p): ?>
              <option value="<?= htmlspecialchars($p['IdProveedor']) ?>">
                <?= htmlspecialchars($p['Nombre']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <div class="card-actions" class="input-date" >
          <label for="FechaCompra">Fecha de compra  </label>
          
          </div>
          <input type="date" name="Fecha" id="FechaCompra" value="<?= date('Y-m-d'); ?>">
        </div>
      </div>

      <!-- Detalle de compra -->
      <div class="form-row">
        <div class="form-field" style="width:100%;">
          <label>Detalle de productos</label>
          <table class="tabla-empleados tabla-detalle" id="tablaDetalleCompra">
            <thead>
              <tr>
                <th style="width:220px;">Producto</th>
                <th style="width:150px;">Marca</th>
                <th style="width:110px;">Talla</th>
                <th style="width:100px;">Cantidad</th>
                <th style="width:140px;">Precio Unitario</th>
                <th style="width:140px;">Subtotal</th>
                <th style="width:60px;">Quitar</th>
              </tr>
            </thead>
            <tbody id="detalleBody">
              <!-- Filas dinámicas -->
            </tbody>
          </table>
          <button type="button" class="btn-secundario" id="btnAgregarFila">
            Agregar producto
          </button>
        </div>
      </div>

      <div class="form-row" style="justify-content:flex-end;">
        <div class="form-field total-field">
          <label>Total de la compra</label>
          <input type="text" id="TotalCompra" readonly value="C$ 0.00">
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancelar" id="btnCancelarCompra">Cancelar</button>
        <button type="submit" class="btn-guardar" id="btnGuardarCompra">Guardar</button>
      </div>
    </form>
  </div>
</div>


<template id="filaDetalleTemplate">
  <tr>
    <!-- Producto -->
    <td>
      <select name="IdProducto[]" class="sel-producto" required>
        <option value="">Seleccione producto</option>
        <?php foreach ($productos as $pr): ?>
        <option
        value="<?= htmlspecialchars($pr['IdProducto']) ?>"
        data-talla="<?= htmlspecialchars($pr['Talla']) ?>"
        data-marca="<?= htmlspecialchars($pr['Marca']) ?>"
        >
    <?= htmlspecialchars($pr['Nombre'] . ' - ' . $pr['Marca']) ?>
  </option>
<?php endforeach; ?>

      </select>
    </td>

    <!-- Marca -->
    <td>
      <input type="text" class="inp-marca" readonly>
    </td>

    <!-- Talla-->
    <td>
      <input type="text" class="inp-talla" readonly>
    </td>

    <!-- Cantidad -->
    <td>
      <input type="number" name="Cantidad[]" class="inp-cantidad" min="1" value="1" required>
    </td>

    <!-- Precio unitario -->
    <td>
      <input type="number" name="PrecioUnitario[]" class="inp-precio" step="0.01" min="1" value="0.00" required>
    </td>

    <!-- Subtotal -->
    <td class="celda-subtotal">C$ 0.00</td>

    <!-- Quitar fila -->
    <td style="text-align:center;">
      <button type="button" class="btn-remove-row" title="Quitar fila">
        <img src="img/borrar.png" alt="Quitar">
      </button>
    </td>
  </tr>
</template>

<!-- Modal Ver factura -->
<div class="modal" id="modalFactura" aria-hidden="true">
  <div class="modal-content large">
    <div class="modal-header">
      <h3>Factura de compra</h3>
    </div>

    <div class="factura-header">
      <p><strong>ID Compra:</strong> <span id="factIdCompra"></span></p>
      <p><strong>Comprador:</strong> <span id="factComprador"></span></p>
      <p><strong>Proveedor:</strong> <span id="factProveedor"></span></p>
      <p><strong>Fecha:</strong> <span id="factFecha"></span></p>
    </div>

    <table class="tabla-vistas" id="tablaFactura">
  <thead>
    <tr>
      <th>Producto</th>
      <th>Marca</th>
      <th>Talla</th>
      <th>Cantidad</th>
      <th>Precio Unitario</th>
      <th>Subtotal</th>
    </tr>
  </thead>
  <tbody id="factDetalleBody"></tbody>
</table>


    <div class="factura-total">
      <p><strong>Total:</strong> <span id="factTotal"></span></p>
    </div>

    <div class="modal-actions">
      <a id="btnDescargarFactura" href="#" class="btn-guardar" target="_blank">
        Descargar PDF
      </a>
      <button type="button" class="btn-cancelar" id="btnCerrarFactura">Cerrar</button>
    </div>
  </div>
</div>


<div id="toast" class="toast" style="display:none;"></div>
