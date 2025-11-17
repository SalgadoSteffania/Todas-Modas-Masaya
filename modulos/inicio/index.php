<?php
session_start();
if (!isset($_SESSION['correo'])) {
  http_response_code(401);
  exit('No autorizado');
}

require_once dirname(__DIR__, 2) . '/conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

//CONTADORES
$clientes = $conexion->query("SELECT COUNT(*) AS total FROM Cliente")
->fetch_assoc()['total'] ?? 0;
$proveedores = $conexion->query("SELECT COUNT(*) AS total FROM Proveedor")
->fetch_assoc()['total'] ?? 0;
$unidadesInventario = $conexion->query("SELECT COALESCE(SUM(Cantidad),0) AS total FROM Producto")
->fetch_assoc()['total'] ?? 0;
$productosVendidos = $conexion->query("SELECT COALESCE(SUM(Cantidad),0) AS total FROM Detalle_de_salida")
->fetch_assoc()['total'] ?? 0;
$importeVendido = $conexion->query("SELECT COALESCE(SUM(Subtotal),0) AS total FROM Detalle_de_salida")
->fetch_assoc()['total'] ?? 0;
?>
<section class="inicio-container">
  <h2 class="panel-title">Panel General</h2>

  <div class="cards-grid">

    <!-- Clientes -->
    <article class="kpi-card kpi-azul">
      <div class="kpi-icon-space">
      </div>
      <div class="kpi-info">
        <h3 class="kpi-title">Clientes registrados</h3>
        <p class="kpi-value"><?= (int)$clientes; ?></p>
      </div>
    </article>

    <!-- Proveedores -->
    <article class="kpi-card kpi-naranja">
      <div class="kpi-icon-space"></div>
      <div class="kpi-info">
        <h3 class="kpi-title">Proveedores registrados</h3>
        <p class="kpi-value"><?= (int)$proveedores; ?></p>
      </div>
    </article>

    <!-- Unidades en inventario -->
    <article class="kpi-card kpi-morado">
      <div class="kpi-icon-space"></div>
      <div class="kpi-info">
        <h3 class="kpi-title">Unidades en inventario</h3>
        <p class="kpi-value"><?= (int)$unidadesInventario; ?></p>
      </div>
    </article>

    <!-- Productos vendidos -->
    <article class="kpi-card kpi-turquesa">
      <div class="kpi-icon-space"></div>
      <div class="kpi-info">
        <h3 class="kpi-title">Productos vendidos</h3>
        <p class="kpi-value"><?= (int)$productosVendidos; ?></p>
      </div>
    </article>

    <!-- Importe vendido -->
    <article class="kpi-card kpi-verde">
      <div class="kpi-icon-space"></div>
      <div class="kpi-info">
        <h3 class="kpi-title">Importe vendido</h3>
        <p class="kpi-value">C$ <?= number_format($importeVendido, 2); ?></p>
      </div>
    </article>

  </div>
</section>
