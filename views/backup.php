<?php require_once __DIR__ . '/partials/header.php'; ?>

<!-- Contenedor principal con padding -->
<div class="container-fluid p-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="bi bi-database-down"></i> Copia de Seguridad</h2>
</div>

<?php
if (isset($_SESSION['mensaje_error'])) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['mensaje_error']) . '</div>';
    unset($_SESSION['mensaje_error']);
}
?>

<div class="card">
    <div class="card-body p-4">
        <h5 class="card-title">Generar Copia de Seguridad</h5>
        <p class="card-text">Haz clic en el botón para generar y descargar una copia de seguridad completa de la base de datos en formato SQL.</p>
        <form action="<?php echo Flight::get('base_url'); ?>/backup" method="POST">
            <button type="submit" class="btn btn-primary"><i class="bi bi-download"></i> Generar y Descargar Backup</button>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>

</div> <!-- Fin del contenedor principal -->