<?php
require_once 'inc/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (registerUser($nombre, $email, $password)) {
        header('Location: index.php?registro=exitoso');
        exit;
    } else {
        $error = "Error al registrar el usuario.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro - MisTurnos</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <h2>Registro de Usuario</h2>
    <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="post">
        <input type="text" name="nombre" placeholder="Nombre" required>
        <input type="email" name="email" placeholder="Correo electrónico" required>
        <input type="password" name="password" placeholder="Contraseña" required>
        <button type="submit">Registrarme</button>
    </form>
    <p><a href="index.php">¿Ya tienes cuenta? Inicia sesión</a></p>
</body>
</html>
