<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Ticket Asignado</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { width: 90%; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #ddd; border-radius: 8px; }
        .header { background-color: #1A2E44; color: #fff; padding: 10px; text-align: center; border-radius: 8px 8px 0 0; }
        .content { padding: 20px; }
        .button { display: inline-block; background-color: #38B2AC; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
        .footer { margin-top: 20px; font-size: 0.9em; color: #777; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Nuevo Ticket Asignado</h2>
        </div>
        <div class="content">
            <h3>Hola, <?php echo htmlspecialchars($nombre_agente); ?>.</h3>
            <p>Se te ha asignado un nuevo ticket en el sistema de soporte MCE.</p>
            
            <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

            <h4>Detalles del Ticket:</h4>
            <ul>
                <li><strong>ID del Ticket:</strong> #<?php echo htmlspecialchars($id_ticket); ?></li>
                <li><strong>Asunto:</strong> <?php echo htmlspecialchars($asunto_ticket); ?></li>
            </ul>

            <p>Puedes ver los detalles completos y responder al ticket haciendo clic en el siguiente botón:</p>
            
            <p style="text-align: center; margin: 30px 0;">
                <a href="<?php echo htmlspecialchars($ticket_url); ?>" class="button">Ver Ticket #<?php echo htmlspecialchars($id_ticket); ?></a>
            </p>

            <p>Gracias,<br>El equipo de Soporte MCE.</p>
        </div>
        <div class="footer">
            <p>&copy; <?php echo date('Y'); ?> MCE - Mantenimientos Computacionales Especializados.</p>
        </div>
    </div>
</body>
</html>