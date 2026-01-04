<?php
// inc/auth.php
require_once 'db.php';

// Secure session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

session_start();

// Registrar usuario
function registerUser($nombre, $email, $password, $rol = 'cliente') {
    global $pdo;
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute([$nombre, $email, $hash, $rol]);
}

// Validar login
function loginUser($email, $password) {
    global $pdo;
    $sql = "SELECT * FROM usuarios WHERE email = ? AND activo = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['usuario'] = [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'rol' => $user['rol']
        ];
        session_regenerate_id(true); // Regenerate session ID on login
        return true;
    }

    return false;
}

function isLoggedIn() {
    return isset($_SESSION['usuario']);
}

function logout() {
    session_unset();
    session_destroy();
}
