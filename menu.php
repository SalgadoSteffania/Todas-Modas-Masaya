<?php
session_start();
require_once 'includes/permisos.php';

if (!isset($_SESSION['correo'])) {
  header("Location: index.php");
  exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <title>Toda Moda Masaya</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/menu.css" />
  <link rel="stylesheet" href="css/inicio.css?v=<?php echo time(); ?>" />
  <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
  <script defer src="js/menu.js?v=<?php echo time(); ?>"></script>
</head>

<body>
  <!-- Barra superior -->
  <header class="top-bar">
    <h1>TODA MODA MASAYA</h1>

    <div class="top-actions">
      <img src="img/ver_perfil.svg" alt="Ver perfil" class="top-icon" title="Ver perfil" onclick="verPerfil()" />
      <img src="img/cerrar_sesion.svg" alt="Cerrar sesión" class="top-icon" title="Cerrar sesión" onclick="cerrarSesion()" />
    </div>
  </header>

  <div class="container">

    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="profile">
        <img src="img/perfil.png" alt="Perfil" class="profile-img">
        <p class="nombre"><?= htmlspecialchars($_SESSION['nombre'] ?? ''); ?></p>
        <p class="correo"><?= htmlspecialchars($_SESSION['correo'] ?? ''); ?></p>
        <hr class="divider">
      </div>

      <div class="nav-section">
        <p class="titulo-menu">NAVEGACIÓN PRINCIPAL</p>

        <nav class="menu">
          <ul>

            <!-- INICIO -->
            <li class="item nivel1 activo" onclick="cargarModuloInicio()">
              <img src="img/inicio.svg" class="icon"> <span class="label">Inicio</span>
            </li>

            <!-- CLIENTES -->
            <?php if (tieneModulo('clientes') || tieneModulo('departamentos')): ?>
            <li class="submenu nivel1">
              <div class="submenu-header">
                <div class="left">
                  <img src="img/cliente.svg" class="icon"><span class="label">Clientes</span>
                </div>
                <img src="img/mas.svg" class="toggle-icon">
              </div>
              <ul class="submenu-list">
                <?php if (tieneModulo('clientes')): ?>
                  <li class="item" onclick="cargarModuloClientes()">Registrar Cliente</li>
                <?php endif; ?>

                <?php if (tieneModulo('departamentos')): ?>
                  <li class="item" onclick="cargarModuloDepartamentos()">Departamentos</li>
                <?php endif; ?>
              </ul>
            </li>
            <?php endif; ?>

            <!-- EMPLEADOS -->
            <?php if (tieneModulo('empleados') || tieneModulo('nomina') || tieneModulo('cargos')): ?>
            <li class="submenu nivel1">
              <div class="submenu-header">
                <div class="left">
                  <img src="img/empleado.svg" class="icon"><span class="label">Empleado</span>
                </div>
                <img src="img/mas.svg" class="toggle-icon">
              </div>
              <ul class="submenu-list">
                <?php if (tieneModulo('empleados')): ?>
                  <li class="item" onclick="cargarModuloEmpleados()">Registrar empleado</li>
                <?php endif; ?>
                
                <?php if (tieneModulo('nomina')): ?>
                  <li class="item" onclick="cargarModuloNomina()">Planilla de pago</li>
                <?php endif; ?>

                <?php if (tieneModulo('cargos')): ?>
                  <li class="item" onclick="cargarModuloCargos()">Cargos</li>
                <?php endif; ?>
              </ul>
            </li>
            <?php endif; ?>

            <!-- PRODUCTOS -->
            <?php if (tieneModulo('productos') || tieneModulo('categorias') || tieneModulo('proveedores')): ?>
            <li class="submenu nivel1">
              <div class="submenu-header">
                <div class="left">
                  <img src="img/inventario.svg" class="icon"><span class="label">Gestión de productos</span>
                </div>
                <img src="img/mas.svg" class="toggle-icon">
              </div>
              <ul class="submenu-list">
                <?php if (tieneModulo('productos')): ?>
                  <li class="item" onclick="cargarModuloProductos()">Productos</li>
                <?php endif; ?>

                <?php if (tieneModulo('categorias')): ?>
                  <li class="item" onclick="cargarModuloCategorias()">Categorías</li>
                <?php endif; ?>

                <?php if (tieneModulo('proveedores')): ?>
                  <li class="item" onclick="cargarModuloProveedores()">Ingresar proveedor</li>
                <?php endif; ?>
              </ul>
            </li>
            <?php endif; ?>

            <!-- MERCANCÍA -->
            <?php if (tieneModulo('compras') || tieneModulo('salidas')): ?>
            <li class="submenu nivel1">
              <div class="submenu-header">
                <div class="left">
                  <img src="img/mercancia.svg" class="icon"><span class="label">Gestión de mercancia</span>
                </div>
                <img src="img/mas.svg" class="toggle-icon">
              </div>
              <ul class="submenu-list">

                <?php if (tieneModulo('compras')): ?>
                  <li class="item" onclick="cargarModuloCompras()">Compras</li>
                <?php endif; ?>

                <?php if (tieneModulo('salidas')): ?>
                  <li class="item" onclick="cargarModuloSalidas()">Salida de inventario</li>
                <?php endif; ?>

              </ul>
            </li>
            <?php endif; ?>

            <!-- USUARIOS -->
            <?php if (tieneModulo('usuarios') || tieneModulo('roles')): ?>
            <li class="submenu nivel1">
              <div class="submenu-header">
                <div class="left">
                  <img src="img/usuario.svg" class="icon"><span class="label">Gestión de usuarios</span>
                </div>
                <img src="img/mas.svg" class="toggle-icon">
              </div>
              <ul class="submenu-list">

                <?php if (tieneModulo('usuarios')): ?>
                  <li class="item" onclick="cargarModuloUsuarios()">Usuarios</li>
                <?php endif; ?>

                <?php if (tieneModulo('roles')): ?>
                  <li class="item" onclick="cargarModuloRoles()">Roles</li>
                <?php endif; ?>

              </ul>
            </li>
            <?php endif; ?>

            <!-- REPORTES -->
            <?php if (tieneModulo('reportes')): ?>
            <li class="item nivel1" onclick="cargarModuloReportes()">
              <img src="img/reporte.svg" class="icon"><span class="label">Reportes</span>
            </li>
            <?php endif; ?>

          </ul>
        </nav>
      </div>
    </aside>

    <main id="contenido">
        <script>cargarModuloInicio()</script>
    </main>

  </div>
</body>
</html>
