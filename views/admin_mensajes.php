<?php require_once __DIR__ . '/partials/header.php'; ?>
<?php $base = rtrim(\Flight::get('base_url') ?? '/', '/'); ?>

<div class="container-fluid p-4">

    <h2 class="mb-4">
        <i class="bi bi-envelope-paper-fill"></i> Mensajes de Contacto
    </h2>

    <?php if (isset($_SESSION['mensaje_exito'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['mensaje_exito']); unset($_SESSION['mensaje_exito']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje_error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['mensaje_error']); unset($_SESSION['mensaje_error']); ?>
        </div>
    <?php endif; ?>


    <!-- =======================
         SECCIÓN 1: NUEVOS
    ======================== -->
    <div class="card mb-4">
        <div class="card-header fw-bold">
            <i class="bi bi-inbox"></i> Nuevos Mensajes
            <span class="badge bg-primary ms-2"><?= $new_total; ?></span>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Mensaje</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($nuevos)): ?>
                    <tr><td colspan="5" class="text-center text-muted">No hay mensajes nuevos.</td></tr>
                <?php else: foreach ($nuevos as $m): ?>
                    <tr>
                        <td><?= date('d/m/Y H:i', strtotime($m['fecha_creacion'])); ?></td>
                        <td><?= htmlspecialchars($m['nombre']); ?></td>
                        <td><?= htmlspecialchars($m['email']); ?></td>
                        <td><?= htmlspecialchars(substr($m['mensaje'],0,50)).'...'; ?></td>
                        <td>
                            <a href="<?= $base; ?>/admin/mensajes/ver/<?= $m['id']; ?>"
                               class="btn btn-sm btn-info">
                                <i class="bi bi-eye"></i> Ver / Responder
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>

            <?php
                $pages = ceil($new_total / $new_limit);
                if ($pages > 1):
                    $query = $_GET ?? [];
                    unset($query['page_n'], $query['page_r']);
                    $baseQuery = http_build_query($query);
                    $baseUrl = '?' . ($baseQuery ? $baseQuery . '&' : '');
                    $current = $new_page;
                    $max_links = 5;

                    $start = max(1, $current - intdiv($max_links, 2));
                    $end   = min($pages, $start + $max_links - 1);

                    if (($end - $start + 1) < $max_links)
                        $start = max(1, $end - $max_links + 1);
            ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">

                    <!-- Anterior -->
                    <li class="page-item <?= $current<=1?'disabled':'' ?>">
                        <a class="page-link" href="<?= $baseUrl.'page_n='.($current-1).'&page_r=1' ?>">Anterior</a>
                    </li>

                    <!-- 1 ... -->
                    <?php if ($start > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?= $baseUrl.'page_n=1&page_r=1' ?>">1</a></li>
                        <?php if ($start > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- Ventana -->
                    <?php for ($i=$start;$i<=$end;$i++): ?>
                        <li class="page-item <?= $i==$current?'active':'' ?>">
                            <a class="page-link" href="<?= $baseUrl.'page_n='.$i.'&page_r=1' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- ... fin -->
                    <?php if ($end < $pages): ?>
                        <?php if ($end < $pages-1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item"><a class="page-link" href="<?= $baseUrl.'page_n='.$pages.'&page_r=1' ?>"><?= $pages ?></a></li>
                    <?php endif; ?>

                    <!-- Siguiente -->
                    <li class="page-item <?= $current>=$pages?'disabled':'' ?>">
                        <a class="page-link" href="<?= $baseUrl.'page_n='.($current+1).'&page_r=1' ?>">Siguiente</a>
                    </li>
                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>


    <!-- =======================
         SECCIÓN 2: RESPONDIDOS
    ======================== -->
    <div class="card">
        <div class="card-header fw-bold">
            <i class="bi bi-check2-circle"></i> Respondidos
            <span class="badge bg-success ms-2"><?= $resp_total; ?></span>
        </div>

        <div class="card-body table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-dark">
                <tr>
                    <th>Respondida</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Mensaje</th>
                    <th>Acciones</th>
                </tr>
                </thead>

                <tbody>
                <?php if (empty($respondidos)): ?>
                    <tr><td colspan="5" class="text-center text-muted">Aún no hay mensajes respondidos.</td></tr>
                <?php else: foreach ($respondidos as $m): ?>
                    <tr>
                        <td><?= $m['fecha_respuesta'] ? date('d/m/Y H:i', strtotime($m['fecha_respuesta'])) : '-'; ?></td>
                        <td><?= htmlspecialchars($m['nombre']); ?></td>
                        <td><?= htmlspecialchars($m['email']); ?></td>
                        <td><?= htmlspecialchars(substr($m['mensaje'],0,50)).'...'; ?></td>
                        <td>
                            <a href="<?= $base; ?>/admin/mensajes/ver/<?= $m['id']; ?>"
                               class="btn btn-sm btn-secondary">
                                <i class="bi bi-eye"></i> Ver
                            </a>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
                </tbody>
            </table>

            <?php
                $pages = ceil($resp_total / $resp_limit);
                if ($pages > 1):
                    $query = $_GET ?? [];
                    unset($query['page_n'], $query['page_r']);
                    $baseQuery = http_build_query($query);
                    $baseUrl = '?' . ($baseQuery ? $baseQuery . '&' : '');
                    $current = $resp_page;
                    $max_links = 5;

                    $start = max(1, $current - intdiv($max_links, 2));
                    $end   = min($pages, $start + $max_links - 1);

                    if (($end - $start + 1) < $max_links)
                        $start = max(1, $end - $max_links + 1);
            ?>
            <nav class="mt-3">
                <ul class="pagination justify-content-center">

                    <li class="page-item <?= $current<=1?'disabled':'' ?>">
                        <a class="page-link" href="<?= $baseUrl.'page_r='.($current-1).'&page_n=1' ?>">Anterior</a>
                    </li>

                    <?php if ($start > 1): ?>
                        <li class="page-item"><a class="page-link" href="<?= $baseUrl.'page_r=1&page_n=1' ?>">1</a></li>
                        <?php if ($start > 2): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i=$start;$i<=$end;$i++): ?>
                        <li class="page-item <?= $i==$current?'active':'' ?>">
                            <a class="page-link" href="<?= $baseUrl.'page_r='.$i.'&page_n=1' ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>

                    <?php if ($end < $pages): ?>
                        <?php if ($end < $pages-1): ?>
                            <li class="page-item disabled"><span class="page-link">...</span></li>
                        <?php endif; ?>
                        <li class="page-item"><a class="page-link" href="<?= $baseUrl.'page_r='.$pages.'&page_n=1' ?>"><?= $pages ?></a></li>
                    <?php endif; ?>

                    <li class="page-item <?= $current>=$pages?'disabled':'' ?>">
                        <a class="page-link" href="<?= $baseUrl.'page_r='.($current+1).'&page_n=1' ?>">Siguiente</a>
                    </li>

                </ul>
            </nav>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
