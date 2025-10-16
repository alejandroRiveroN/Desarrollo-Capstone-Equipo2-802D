<?php
// This is a view file. The logic is handled by the Flight routes.
// The variables $mensaje and $error are passed from the route.
?>
<div class="container mt-4">
    <div id="mensaje-container">
        <?php if (isset($mensaje) && $mensaje): ?>
            <div class="alert alert-success">
                <h4>Proceso Finalizado</h4>
                <p><?php echo $mensaje; ?></p>
                <a href="<?php echo Flight::get('base_url'); ?>/" class="btn btn-primary">Volver al Dashboard</a>
            </div>
        <?php endif; ?>
        <?php if (isset($error) && $error): ?>
            <div class="alert alert-danger">
                <h4>Error</h4>
                <p><?php echo $error; ?></p>
                <a href="<?php echo Flight::get('base_url'); ?>/" class="btn btn-secondary">Volver al Dashboard</a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Card para Limpieza de Tickets Antiguos -->
    <div class="card mb-4">
        <div class="card-header">
            <h2 class="mb-0">Limpieza de Tickets Antiguos</h2>
        </div>
        <div class="card-body">
            <p>Esta herramienta busca tickets cerrados o resueltos hace más de un año y te permite borrarlos para mantener la base de datos limpia.</p>
            <button id="btn-test-limpieza" class="btn btn-info">Verificar Tickets a Borrar (Simulación)</button>
            <div id="test-results-container" class="mt-3" style="display: none;">
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
        <div class="card-header bg-danger text-white">
            <h2 class="mb-0"><i class="bi bi-exclamation-triangle-fill"></i> Limpieza TOTAL de la Base de Datos</h2>
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
            
            <form action="<?php echo Flight::get('base_url'); ?>/admin/limpieza/total" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres borrar TODA la información? Esta acción no se puede deshacer.');">
                <button type="submit" name="confirmar_limpieza" class="btn btn-danger btn-lg">
                    <i class="bi bi-trash-fill"></i> Sí, entiendo y quiero borrar TODO
                </button>
                <a href="<?php echo Flight::get('base_url'); ?>/" class="btn btn-secondary btn-lg">No, cancelar y volver</a>
            </form>
        </div>
    </div>

    <!-- Card para Resetear Sistema -->
    <div class="card border-danger">
        <div class="card-header bg-danger text-white">
            <h2 class="mb-0"><i class="bi bi-exclamation-octagon-fill"></i> Resetear Sistema a Estado de Fábrica</h2>
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
            
            <form action="<?php echo Flight::get('base_url'); ?>/admin/limpieza/reset" method="POST" onsubmit="return confirm('¿Estás seguro de que quieres resetear el sistema? Los usuarios no se verán afectados, pero todo lo demás será borrado.');">
                <button type="submit" name="confirmar_reseteo" class="btn btn-danger btn-lg">
                    <i class="bi bi-trash-fill"></i> Sí, entiendo las consecuencias y quiero resetear el sistema
                </button>
                <a href="<?php echo Flight::get('base_url'); ?>/" class="btn btn-secondary btn-lg">No, cancelar y volver</a>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('btn-test-limpieza').addEventListener('click', function() {
    const resultsContainer = document.getElementById('test-results-container');
    const resultsCount = document.getElementById('test-results-count');
    const resultsList = document.getElementById('test-results-list');

    resultsContainer.style.display = 'block';
    resultsCount.textContent = 'Cargando...';
    resultsList.innerHTML = '';

    fetch('<?php echo Flight::get('base_url'); ?>/admin/limpieza/test', {
        method: 'POST',
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            resultsCount.textContent = 'Error: ' + data.error;
            return;
        }

        resultsCount.textContent = 'Se encontraron ' + data.length + ' tickets que serían eliminados:';
        if (data.length === 0) {
            resultsList.innerHTML = '<li>No se encontraron tickets para limpiar.</li>';
        } else {
            data.forEach(ticket => {
                const li = document.createElement('li');
                li.textContent = 'Ticket #' + ticket.id_ticket + ': ' + ticket.asunto + ' (Creado: ' + ticket.fecha_creacion + ')';
                resultsList.appendChild(li);
            });
        }
    })
    .catch(error => {
        resultsCount.textContent = 'Error en la solicitud: ' + error;
    });
});
</script>
