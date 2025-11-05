<?php
session_start();
if (!isset($_SESSION['correo'])) { http_response_code(401); exit('No autorizado'); }

require_once dirname(__DIR__, 2) . '/conexion.php';


$categorias = [];
$sqlCat = "SELECT IdCategoria, Descripcion FROM Categoria ORDER BY Descripcion";
$resCat = $conexion->query($sqlCat);
if ($resCat) {
  while ($row = $resCat->fetch_assoc()) {
    $categorias[] = $row;
  }
}
?>
<div class="vistas-wrapper">
  <h2 class="titulo-principal">Lista de Productos</h2>

  <div class="tabla-card">
    <div class="card-top">
      <button id="btnNuevoProducto" class="btn-nuevo">Producto Nuevo</button>
    </div>

    <div class="card-actions">
      <!-- Filtro por categoría -->
      <select id="filtroCategoria" class="input-select">
        <option value="">Filtrar por categoría</option>
        <?php foreach ($categorias as $c): ?>
          <option value="<?= htmlspecialchars($c['IdCategoria']) ?>">
            <?= htmlspecialchars($c['Descripcion']) ?>
          </option>
        <?php endforeach; ?>
      </select>

  
      <input type="text" id="buscarProducto" class="input-buscar"
             placeholder="Buscar por nombre, marca, color o talla">
    </div>

    <table class="tabla-vistas" id="tablaProductos">
      <thead>
        <tr>
          <th style="width:80px;">ID</th>
          <th>Categoría</th>
          <th>Marca</th>
          <th>Nombre</th>
          <th>Descripción</th>
          <th>Talla</th>
          <th>Color</th>
          <th>Cantidad</th>
          <th>Precio</th>
          <th style="width:80px;">Editar</th>
          <th style="width:90px;">Eliminar</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>


<div class="modal" id="modalProducto" aria-hidden="true">
  <div class="modal-content">
    <div class="modal-header">
      <h3 id="modalTituloProd">Información del producto</h3>
    </div>

    <form id="formProducto" autocomplete="off">
      <input type="hidden" name="IdProducto" id="IdProducto">

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="IdCategoria">Categoría</label>
          <select name="IdCategoria" id="IdCategoria" required>
            <option value="" disabled selected>Seleccione categoría</option>
            <?php foreach ($categorias as $c): ?>
              <option value="<?= htmlspecialchars($c['IdCategoria']) ?>">
                <?= htmlspecialchars($c['Descripcion']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="Marca">Marca</label>
          <input type="text" name="Marca" id="Marca">
        </div>
      </div>

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
          <label for="Descripcion">Descripción</label>
          <input type="text" name="Descripcion" id="Descripcion">
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="Talla">Talla</label>
          <input type="text" name="Talla" id="Talla">
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="Color">Color</label>
          <input type="text" name="Color" id="Color">
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="Cantidad">Cantidad</label>
          <input type="number" name="Cantidad" id="Cantidad" min="0" required>
        </div>
      </div>

      <div class="form-row">
        <div class="icon-slot"></div>
        <div class="form-field">
          <label for="Precio_de_Venta">Precio de venta (C$)</label>
          <input type="number" step="0.01" name="Precio_de_Venta" id="Precio_de_Venta" required>
        </div>
      </div>

      <div class="modal-actions">
        <button type="button" class="btn-cancelar" id="btnCancelarProd">Cancelar</button>
        <button type="submit" class="btn-guardar" id="btnGuardarProd">Guardar</button>
      </div>
    </form>
  </div>
</div>


<div class="modal" id="modalConfirmProd" aria-hidden="true">
  <div class="modal-content small">
    <div class="modal-header"><h3>Confirmar eliminación</h3></div>
    <p>¿Está seguro de eliminar este producto?</p>
    <div class="modal-actions">
      <button type="button" class="btn-cancelar" id="btnNoProd">No</button>
      <button type="button" class="btn-eliminar" id="btnSiProd">Sí, eliminar</button>
    </div>
  </div>
</div>


<div id="toast" class="toast" style="display:none;"></div>
