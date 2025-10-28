<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$base = Flight::get('base_url') ?? '';
$role = $_SESSION['id_rol'] ?? null;
$name = $_SESSION['nombre_completo'] ?? 'Usuario';
$uri  = $_SERVER['REQUEST_URI'] ?? '/';

$isActive = function(string $path) use ($uri) {
  // Marca activo si la ruta actual empieza con $path (resistente a querystrings)
  $current = parse_url($uri, PHP_URL_PATH) ?: '/';
  return (strpos(rtrim($current,'/'), rtrim($path,'/')) === 0) ? 'active' : '';
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sistema de Gestión de Soporte</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
  <link rel="stylesheet" href="<?php echo $base; ?>/css/sidebar.css?v=1.1" />
  <link rel="stylesheet" href="<?php echo $base; ?>/css/admin.css?v=1.0" />
</head>
<body data-base-url="<?php echo $base; ?>">

<div class="sidebar">
  <div class="sidebar-header">
    <a href="<?php echo $base; ?>/">
      <i class="bi bi-gear-fill"></i> Sistema de Soporte integral
    </a>
  </div>

  <nav class="nav flex-column">

    <a class="nav-link <?php echo $isActive($base.'/dashboard'); ?>" href="<?php echo $base; ?>/dashboard">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <!-- Cliente (rol 4): Cotizaciones -->
    <?php if ($role === 4): ?>
      <div class="nav-heading">Cotizaciones</div>
      <a class="nav-link <?php echo $isActive($base.'/cotizaciones/crear'); ?>" href="<?php echo $base; ?>/cotizaciones/crear">
        <i class="bi bi-clipboard-plus"></i> Solicitar Cotización
      </a>
      <a class="nav-link <?php echo $isActive($base.'/cotizaciones'); ?>" href="<?php echo $base; ?>/cotizaciones">
        <i class="bi bi-card-list"></i> Mis Cotizaciones
      </a>
    <?php endif; ?>

    <!-- Crear Ticket (1,3,4) -->
    <?php if (in_array($role, [1,3,4], true)): ?>
      <div class="nav-heading">Tickets</div>
      <a class="nav-link <?php echo $isActive($base.'/tickets/crear'); ?>" href="<?php echo $base; ?>/tickets/crear">
        <i class="bi bi-plus-circle"></i> Crear Ticket
      </a>
    <?php endif; ?>

    <!-- Admin / Supervisor: Cotizaciones -->
    <?php if (in_array($role, [1,3], true)): ?>
      <div class="nav-heading">Gestión</div>
      <a class="nav-link <?php echo $isActive($base.'/admin/cotizaciones'); ?>" href="<?php echo $base; ?>/admin/cotizaciones">
        <i class="bi bi-cash-coin"></i> Ver Cotizaciones
      </a>
    <?php endif; ?>

    <!-- Solo Admin -->
    <?php if ($role === 1): ?>
      <div class="nav-heading">Contacto</div>
      <a class="nav-link <?php echo $isActive($base.'/admin/mensajes'); ?>" href="<?php echo $base; ?>/admin/mensajes">
        <i class="bi bi-envelope-paper-fill"></i> Mensajes de Contacto
      </a>
      <div class="nav-heading">Administración</div>
      <a class="nav-link <?php echo $isActive($base.'/clientes'); ?>" href="<?php echo $base; ?>/clientes">
        <i class="bi bi-people-fill"></i> Clientes
      </a>
      <a class="nav-link <?php echo $isActive($base.'/usuarios'); ?>" href="<?php echo $base; ?>/usuarios">
        <i class="bi bi-person-badge-fill"></i> Usuarios
      </a>
      <a class="nav-link <?php echo $isActive($base.'/casos/tipos'); ?>" href="<?php echo $base; ?>/casos/tipos">
        <i class="bi bi-tags-fill"></i> Tipos de Caso
      </a>
      <a class="nav-link <?php echo $isActive($base.'/backup'); ?>" href="<?php echo $base; ?>/backup">
        <i class="bi bi-database-down"></i> Copia de Seguridad
      </a>
    <?php endif; ?>
  </nav>
</div>

<!-- User bar desktop -->
<div class="user-bar d-none d-lg-flex">
  <div class="dropdown">
    <a href="#" class="d-flex align-items-center text-dark text-decoration-none dropdown-toggle"
       data-bs-toggle="dropdown" aria-expanded="false">
      <i class="bi bi-person-circle fs-4 me-2"></i>
      <strong><?php echo htmlspecialchars($name); ?></strong>
    </a>
    <ul class="dropdown-menu dropdown-menu-end text-small shadow">
      <li>
        <a class="dropdown-item" href="<?php echo $base; ?>/password/cambiar">
          <i class="bi bi-key-fill me-2"></i> Cambiar Contraseña
        </a>
      </li>
      <li><hr class="dropdown-divider"></li>
      <li>
        <a class="dropdown-item" href="<?php echo $base; ?>/logout">
          <i class="bi bi-box-arrow-right me-2"></i> Cerrar Sesión
        </a>
      </li>
    </ul>
  </div>
</div>

