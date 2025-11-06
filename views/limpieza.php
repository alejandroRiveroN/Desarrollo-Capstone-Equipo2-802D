<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
$base = Flight::get('base_url') ?? '';
include __DIR__ . '/partials/header.php';
?>
<main class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h2 class="mb-0"><i class="bi bi-eraser-fill"></i> Herramientas de Limpieza</h2>
    </div>

    <?php if (isset($mensaje) && $mensaje): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <strong>Proceso Finalizado:</strong> <?php echo htmlspecialchars($mensaje); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (isset($error) && $error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Card para Limpieza de Tickets Antiguos -->
    <div class="card mb-4">
        <div class="card-header fw-bold">
            <i class="bi bi-archive-fill"></i> Limpieza de Tickets Antiguos
        </div>
        <div class="card-body">
            <p>Esta herramienta busca tickets cerrados o resueltos hace más de un año y te permite borrarlos para mantener la base de datos limpia.</p>
            <button id="btn-test-limpieza" class="btn btn-info"><i class="bi bi-search"></i> Verificar Tickets a Borrar (Simulación)</button>
            <div id="test-results-container" class="mt-3" style="display:none;">
                <h4>Resultados de la Simulación</h4>
                <p><strong id="test-results-count"></strong></p>
                <ul id="test-results-list"></ul>
                <hr>
                <p>Para borrarlos permanentemente, usa la opción de "Limpieza Total" o "Resetear Sistema".</p>
            </div>
        </div>
    </div>

    <!-- Card para Limpieza Total -->
    <div class="card border-danger mb-4">
        <div class="card-header bg-danger text-white fw-bold">
            <i class="bi bi-exclamation-triangle-fill"></i> Limpieza TOTAL de la Base de Datos
        </div>
        <div class="card-body">
            <h4 class="card-title text-danger">¡Estás a punto de borrar TODA la información del sistema!</h4>
            <p>Este proceso eliminará permanentemente:</p>
            <ul>
                <li><strong>TODOS</strong> los tickets.</li>
                <li><strong>TODOS</strong> los comentarios.</li>
                <li><strong>TODOS</strong> los archivos adjuntos.</li>
                <li class="fw-bold text-danger"><strong>TODOS LOS CLIENTES.</strong></li>
            </ul>
            <p>La base de datos quedará como nueva, lista para registrar datos reales. Tendrás que crear a tus clientes de nuevo.</p>
            <hr>
            <p><strong>¿Estás absolutamente seguro de que quieres continuar?</strong></p>
            
            <form action="<?php echo Flight::get('base_url'); ?>/admin/limpieza/total" method="POST">
                <button type="submit" name="confirmar_limpieza" class="btn btn-danger btn-lg">
                    <i class="bi bi-trash-fill"></i> Sí, quiero borrar TODO
                </button>
                <a href="<?php echo Flight::get('base_url'); ?>/dashboard" class="btn btn-secondary btn-lg"><i class="bi bi-x-circle"></i> Cancelar</a>
            </form>
        </div>
    </div>

    <!-- Card para Resetear Sistema -->
    <div class="card border-danger">
        <div class="card-header bg-danger text-white fw-bold">
            <i class="bi bi-exclamation-octagon-fill"></i> Resetear Sistema a Estado de Fábrica
        </div>
        <div class="card-body">
            <h4 class="card-title text-danger">¡Estás a punto de borrar casi toda la base de datos!</h4>
            <p>Este proceso eliminará permanentemente todos los datos de las siguientes tablas:</p>
            <ul>
                <li>Clientes</li>
                <li>Tipos de Caso</li>
                <li>Agentes</li>
                <li>Tickets</li>
                <li>Comentarios</li>
                <li>Archivos Adjuntos</li>
            </ul>
            <p class="fw-bold">La única tabla que se conservará intacta es la tabla de `Usuarios`.</p>
            <hr>
            <p><strong>Esta acción no se puede deshacer. ¿Estás absolutamente seguro de que quieres resetear el sistema?</strong></p>
            
            <form action="<?php echo Flight::get('base_url'); ?>/admin/limpieza/reset" method="POST">
                <button type="submit" name="confirmar_reseteo" class="btn btn-danger btn-lg">
                    <i class="bi bi-trash-fill"></i> Sí, quiero resetear el sistema
                </button>
                <a href="<?php echo Flight::get('base_url'); ?>/dashboard" class="btn btn-secondary btn-lg"><i class="bi bi-x-circle"></i> Cancelar</a>
            </form>
        </div>
    </div>
</main>

<script src="<?php echo Flight::get('base_url'); ?>/js/limpieza.js"></script>

<?php include __DIR__ . '/partials/footer.php'; ?>
