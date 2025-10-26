<?php
$titulo_reporte = "Reporte de Tickets";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($titulo_reporte); ?></title>
    <link rel="stylesheet" href="<?php echo Flight::get('base_url'); ?>/css/print.css">
</head>
<body onload="window.print()">
    <h1 style="text-align: center;"><?php echo htmlspecialchars($titulo_reporte); ?></h1>
    <table>
        <thead>
            <tr>
                <th>ID</th><th>Cliente</th><th>Asunto</th><th>Tipo de Caso</th><th>Estado</th><th>Prioridad</th><th>Costo</th><th>Facturación</th><th>Agente</th><th>Fecha Creación</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><?php echo $ticket['id_ticket']; ?></td>
                    <td><?php echo htmlspecialchars($ticket['cliente']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['asunto']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['nombre_tipo'] ?? 'N/A'); ?></td>
                    <td><?php echo htmlspecialchars($ticket['estado']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['prioridad']); ?></td>
                    <td><?php echo number_format($ticket['costo'], 2) . ' ' . htmlspecialchars($ticket['moneda']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['estado_facturacion']); ?></td>
                    <td><?php echo htmlspecialchars($ticket['agente'] ?? 'Sin asignar'); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($ticket['fecha_creacion'])); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>