<?php
require_once 'inc/auth.php';
require_once 'inc/form_validator.php';

// Inicializar hardening de seguridad
initSecurityHardening();

// Rate limiting para registro
$clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!RateLimiter::checkLimit("register_{$clientIP}", 3, 3600)) { // 3 registros por hora por IP
    SecurityLogger::logEvent('REGISTER_RATE_LIMIT_EXCEEDED', ['ip' => $clientIP]);
    $error = "Demasiados intentos de registro. Intente más tarde.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($error)) {
    // Validar CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        SecurityLogger::logEvent('CSRF_TOKEN_INVALID', ['ip' => $clientIP]);
        $error = "Error de validación. Recargue la página e intente nuevamente.";
    } else {
        // Crear validador
        $validator = new FormValidator($_POST);

        if ($validator->validateRegistration()) {
            $data = $validator->getSanitizedData();

            // Verificar si email ya existe
            if (emailExists($data['email'])) {
                $error = "El email ya está registrado.";
                SecurityLogger::logEvent('DUPLICATE_EMAIL_ATTEMPT', ['email' => $data['email']]);
            } else {
                // Intentar registrar usuario
                if (registerUser($data['nombre'], $data['email'], $data['password'])) {
                    SecurityLogger::logEvent('USER_REGISTERED', [
                        'email' => $data['email'],
                        'ip' => $clientIP
                    ]);
                    header('Location: index.php?registro=exitoso');
                    exit;
                } else {
                    $error = "Error al registrar el usuario. Intente nuevamente.";
                    SecurityLogger::logEvent('USER_REGISTRATION_FAILED', [
                        'email' => $data['email'],
                        'ip' => $clientIP
                    ]);
                }
            }
        } else {
            $errors = $validator->getErrors();
            SecurityLogger::logEvent('VALIDATION_FAILED', [
                'errors' => $errors,
                'ip' => $clientIP
            ]);
        }
    }
}

// Generar nuevo token CSRF
$csrfToken = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Turnos-Ya</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <div class="container">
        <h2>Registro de Usuario</h2>

        <?php if (isset($error)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($errors) && is_array($errors)): ?>
            <div class="error-message">
                <ul>
                    <?php foreach ($errors as $field => $message): ?>
                        <li><?php echo htmlspecialchars($message); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

            <div class="form-group">
                <label for="nombre">Nombre completo:</label>
                <input type="text"
                       id="nombre"
                       name="nombre"
                       value="<?php echo htmlspecialchars($_POST['nombre'] ?? ''); ?>"
                       required
                       maxlength="50"
                       pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+"
                       title="Solo letras y espacios">
                <?php if (isset($errors['nombre'])): ?>
                    <span class="field-error"><?php echo htmlspecialchars($errors['nombre']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Correo electrónico:</label>
                <input type="email"
                       id="email"
                       name="email"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                       required
                       maxlength="100">
                <?php if (isset($errors['email'])): ?>
                    <span class="field-error"><?php echo htmlspecialchars($errors['email']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password"
                       id="password"
                       name="password"
                       required
                       minlength="8"
                       pattern="(?=.*[a-z])(?=.*[A-Z])(?=.*\d)"
                       title="Mínimo 8 caracteres, con mayúsculas, minúsculas y números">
                <?php if (isset($errors['password'])): ?>
                    <span class="field-error"><?php echo htmlspecialchars($errors['password']); ?></span>
                <?php endif; ?>
                <div class="password-requirements">
                    <small>La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números.</small>
                </div>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirmar contraseña:</label>
                <input type="password"
                       id="confirm_password"
                       name="confirm_password"
                       required>
                <?php if (isset($errors['confirm_password'])): ?>
                    <span class="field-error"><?php echo htmlspecialchars($errors['confirm_password']); ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="terminos" value="1" required>
                    Acepto los <a href="#" target="_blank">términos y condiciones</a> y la <a href="#" target="_blank">política de privacidad</a>
                </label>
                <?php if (isset($errors['terminos'])): ?>
                    <span class="field-error"><?php echo htmlspecialchars($errors['terminos']); ?></span>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn-primary">Registrarme</button>
        </form>

        <p class="text-center">
            <a href="index.php">¿Ya tienes cuenta? Inicia sesión</a>
        </p>
    </div>

    <script>
    // Validación frontend básica
    document.addEventListener('DOMContentLoaded', function() {
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        function validatePasswordMatch() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Las contraseñas no coinciden');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }

        password.addEventListener('change', validatePasswordMatch);
        confirmPassword.addEventListener('keyup', validatePasswordMatch);
    });
    </script>
</body>
</html>
