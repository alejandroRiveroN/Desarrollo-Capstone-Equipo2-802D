<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Imprimir Clientes</title>
    <link rel="stylesheet" href="<?php echo Flight::get('base_url'); ?>/css/print.css">
</head>
<body onload="window.print()">
    <div style="text-align: center; margin-bottom: 20px;">
        <h1>Listado de Clientes</h1>
        <p>Fecha de Impresión: <?php echo date('d/m/Y H:i:s'); ?></p>
        <p>Total de clientes: <?php echo count($clientes); ?></p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Email</th>
                <th>Teléfono</th>
                <th>País</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($clientes)): ?>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($cliente['id_cliente']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                        <td><?php echo htmlspecialchars($cliente['telefono'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($cliente['pais'] ?? 'N/A'); ?></td>
                        <td>
                            <?php echo ($cliente['activo']) ? 'Activo' : 'Inactivo'; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center">No se encontraron clientes para imprimir.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>