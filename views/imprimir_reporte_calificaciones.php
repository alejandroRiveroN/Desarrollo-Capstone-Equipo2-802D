<?php
$titulo_reporte = "Reporte de Calificaciones de Tickets";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($titulo_reporte); ?></title>
    <link rel="stylesheet" href="<?php echo Flight::get('base_url'); ?>/css/print.css">
    <style>
        .summary { text-align: center; margin-bottom: 20px; }
        .summary h2 { margin: 0; }
        .summary p { margin: 5px 0; }
    </style>
</head>
<body onload="window.print()">

    <div class="summary">
        <h1><?php echo htmlspecialchars($titulo_reporte); ?></h1>
        <p>Fecha de Impresión: <?php echo date('d/m/Y H:i'); ?></p>
        <h2>Calificación Promedio: <?php echo number_format($average_rating, 2); ?> / 5.00</h2>
        <p>Total de evaluaciones: <?php echo count($evaluations); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID Ticket</th>
                <th>Asunto</th>
                <th>Cliente</th>
                <th>Agente</th>
                <th>Calificación</th>
                <th>Comentario</th>
                <th>Fecha</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($evaluations)): ?>
                <tr><td colspan="7" style="text-align: center;">No hay evaluaciones para mostrar.</td></tr>
            <?php else: ?>
                <?php foreach ($evaluations as $eval): ?>
                    <tr>
                        <td>#<?php echo htmlspecialchars($eval['id_ticket']); ?></td>
                        <td><?php echo htmlspecialchars($eval['asunto']); ?></td>
                        <td><?php echo htmlspecialchars($eval['nombre_cliente']); ?></td>
                        <td><?php echo htmlspecialchars($eval['nombre_agente'] ?? 'N/A'); ?></td>
                        <td style="text-align: center;"><?php echo htmlspecialchars($eval['calificacion']); ?>/5</td>
                        <td><?php echo htmlspecialchars($eval['comentario']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($eval['fecha_creacion'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>