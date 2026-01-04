<?php
require_once 'inc/auth.php';
require_once 'inc/db.php';
require_once 'inc/branding.php';

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Token CSRF inválido.";
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        if (loginUser($email, $password)) {
            // Regenerate token after successful login
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Credenciales incorrectas.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de turnos online para <?= htmlspecialchars(NOMBRE_NEGOCIO) ?>. Reserva tus turnos de manera fácil y rápida.">
    <meta name="keywords" content="turnos, reservas, <?= htmlspecialchars(NOMBRE_NEGOCIO) ?>, citas, online">
    <meta name="author" content="<?= htmlspecialchars(NOMBRE_NEGOCIO) ?>">
    <title>Login - <?= htmlspecialchars(NOMBRE_NEGOCIO) ?> - Turnos Ya</title>
    <link rel="icon" type="image/x-icon" href="/img/logo-1767467044.jpg">
    <link rel="stylesheet" href="css/styles.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            <?php if (PORTADA_URL): ?>
            background-image: url('<?= PORTADA_URL ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            <?php endif; ?>
            z-index: -2;
        }
        
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102, 239, 126, 0.15) 0%, rgba(118, 255, 162, 0.1) 100%);
            backdrop-filter: blur(1px);
            z-index: -1;
        }
        
        .login-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 420px;
            width: 90%;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h2 {
            margin: 0;
            color: #667eea;
            font-size: 28px;
        }
        
        .login-header p {
            color: #666;
            margin-top: 10px;
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .credentials-box {
            background: #e3f2fd;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            border-left: 4px solid #2196F3;
        }
        
        .credentials-box strong {
            color: #1976d2;
            display: block;
            margin-bottom: 10px;
        }
        
        .credentials-box .cred-item {
            font-family: monospace;
            font-size: 13px;
            padding: 5px 0;
            color: #333;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-primary {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <?php if (isset($_SESSION['usuario'])): ?>
        <div style="margin-bottom: 20px; text-align: right;">
            <a href="dashboard.php" style="background: #667eea; color: #fff; padding: 8px 18px; border-radius: 7px; text-decoration: none; font-weight: 500; box-shadow: 0 2px 8px rgba(102,126,234,0.12); transition: background 0.2s;">← Volver al Dashboard</a>
        </div>
        <?php endif; ?>
        <div class="login-header">
            <div style="width: 80px; height: 80px; margin: 0 auto 15px;">
                <img src="/img/logo-1767467044.jpg" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
            </div>
            <h2><?= htmlspecialchars(NOMBRE_NEGOCIO) ?></h2>
            <p><?= htmlspecialchars(ESLOGAN_NEGOCIO) ?></p>
        </div>
        <?php if (isset($_GET['registro'])): ?>
            <script>
            Swal.fire({
                icon: 'success',
                title: 'Registro exitoso',
                text: '✅ Ya puedes iniciar sesión.',
                confirmButtonText: 'Cerrar'
            });
            </script>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?= addslashes($error) ?>',
                confirmButtonText: 'Cerrar'
            });
            </script>
        <?php endif; ?>
        
        <div class="credentials-box">
            <strong>🔑 Credenciales de Prueba</strong>
            <div class="cred-item"> <p>
                <strong>Admin:</strong> admin@demo.com / admin123 </p></div>
            <div class="cred-item"><strong>Cliente:</strong> cliente@test.com / test123</div>
        </div>
        
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-group">
                <input type="email" name="email" placeholder="Correo electrónico" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>
            <button type="submit" class="btn-primary">Entrar</button>
        </form>
        
        <div class="login-footer">
            <a href="register.php">¿No tienes cuenta? Regístrate aquí</a>
        </div>
    </div>
</body>
</html>
