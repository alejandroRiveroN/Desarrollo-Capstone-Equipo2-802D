<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCE - Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f2f4f7;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            font-family: "Segoe UI", sans-serif;
        }

        .login-card {
            width: 100%;
            max-width: 380px;
            border-radius: 12px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            background: #fff;
        }

        .login-header {
            background-color: #133C55;
            color: #fff;
            text-align: center;
            padding: 1.5rem;
        }

        .login-header h2 {
            font-weight: 700;
            margin: 0;
        }

        .login-header p {
            font-size: 0.9rem;
            margin: 0;
            opacity: 0.9;
        }

        .login-body {
            padding: 2rem;
        }

        .form-label {
            font-weight: 500;
        }

        .btn-acceder {
            background-color: #00b894;
            color: white;
            border: none;
            border-radius: 6px;
            padding: 0.6rem;
            font-weight: 600;
            transition: background 0.3s;
        }

        .btn-acceder:hover {
            background-color: #019875;
        }

        .forgot-password {
            text-align: center;
            margin-top: 1rem;
        }

        .forgot-password a {
            color: #555;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .forgot-password a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="login-header">
            <h2>MCE</h2>
            <p>Sistema de Soporte TI</p>
        </div>
        <div class="login-body">
            <h4 class="text-center mb-4">Iniciar Sesión</h4>

            <?php if (isset($error_message) && $error_message): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <form action="/login" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Usuario o Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="ejemplo@empresa.com" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn-acceder">Acceder</button>
                </div>
            </form>

            <div class="forgot-password">
                <a href="/contraseña_olvidada">¿Olvidaste tu contraseña?</a>
            </div>
        </div>
    </div>

</body>
</html>