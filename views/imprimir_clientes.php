<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprimir Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20mm; /* Márgenes para impresión */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .header-print {
            text-align: center;
            margin-bottom: 30px;
        }
        @media print {
            /* Ocultar elementos no deseados en la impresión si los hubiera */
            button, .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header-print">
        <h1>Listado de Clientes</h1>
        <p>Fecha de Impresión: <?php echo date('d/m/Y H:i:s'); ?></p>
        <p>Total de clientes: <?php echo count($clientes); ?></p>
    </div>

    <table class="table table-striped table-bordered">
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
                        <td><?php echo htmlspecialchars($cliente['correo_electronico']); ?></td>
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

    <script>
        // Imprimir automáticamente al cargar la página
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>