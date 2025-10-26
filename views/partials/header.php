<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Soporte</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo Flight::get('base_url'); ?>/css/sidebar.css?v=1.1">
    <link rel="stylesheet" href="<?php echo Flight::get('base_url'); ?>/css/admin.css?v=1.0">
</head>
<body>

<div class="sidebar">
    <div class="sidebar-header">
        <a href="<?php echo Flight::get('base_url'); ?>/"><i class="bi bi-gear-fill"></i> Sistema de Soporte integral</a>
    </div>
    <nav class="nav flex-column">
        <a class="nav-link" href="<?php echo Flight::get('base_url'); ?>/dashboard"><i class="bi bi-speedometer2"></i> Dashboard</a>
        
        <?php if (isset($_SESSION['id_rol']) && in_array($_SESSION['id_rol'], [1, 3, 4])): // 1=Admin, 3=Supervisor ?>
            <a class="nav-link" href="<?php echo Flight::get('base_url'); ?>/tickets/crear"><i class="bi bi-plus-circle"></i> Crear Ticket</a>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1): ?>
            <div class="nav-heading">Administración</div>
            <a class="nav-link" href="<?php echo Flight::get('base_url'); ?>/clientes"><i class="bi bi-people-fill"></i> Clientes</a>
            <a class="nav-link" href="<?php echo Flight::get('base_url'); ?>/usuarios"><i class="bi bi-person-badge-fill"></i> Usuarios</a>
            <a class="nav-link" href="<?php echo Flight::get('base_url'); ?>/casos/tipos"><i class="bi bi-tags-fill"></i> Tipos de Caso</a>
            <!-- Enlace a Mensajes de Contacto -->
            <a class="nav-link" href="<?php echo Flight::get('base_url'); ?>/admin/mensajes">
                <div class="sb-nav-link-icon"><i class="bi bi-envelope-paper-fill"></i></div> Mensajes de Contacto
            </a>
            <a class="nav-link" href="<?php echo Flight::get('base_url'); ?>/backup"><i class="bi bi-database-down"></i> Copia de Seguridad</a>
        <?php endif; ?>
    </nav>
</div>

<nav class="navbar navbar-dark bg-dark d-lg-none">
  <div class="container-fluid">
    <button class="navbar-toggler" type="button" id="sidebarToggleBtn">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="dropdown ms-auto">
      <button
        class="btn btn-dark d-flex align-items-center dropdown-toggle"
        id="userMenuMobile"
        type="button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
      >
        <i class="bi bi-person-circle fs-4 me-2"></i>
        <strong><?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></strong>
      </button>

      <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end text-small shadow"
          aria-labelledby="userMenuMobile">
        <li>
          <a class="dropdown-item" href="<?php echo Flight::get('base_url'); ?>/password/cambiar">
            <i class="bi bi-key-fill me-2"></i> Cambiar Contraseña
          </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
          <a class="dropdown-item" href="<?php echo Flight::get('base_url'); ?>/logout">
            <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

<div class="user-bar d-none d-lg-flex">
    <div class="dropdown">
        <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle fs-4 me-2"></i>
            <strong><?php echo htmlspecialchars($_SESSION['nombre_completo']); ?></strong>
        </a>
        <ul class="dropdown-menu dropdown-menu-end text-small shadow">
            <li><a class="dropdown-item" href="<?php echo Flight::get('base_url'); ?>/password/cambiar"><i class="bi bi-key-fill me-2"></i> Cambiar Contraseña</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="<?php echo Flight::get('base_url'); ?>/logout"><i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión</a></li>
        </ul>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>