<?php
session_start();

// Simular una sesión de usuario (ajusta según tu usuario real)
if (!isset($_SESSION['usuario'])) {
    // Intenta cargar un usuario de prueba
    require_once '../inc/db.php';
    $stmt = $pdo->query("SELECT * FROM usuarios WHERE email = 'cliente@test.com' LIMIT 1");
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($usuario) {
        $_SESSION['usuario'] = $usuario;
    }
}

// Capturar toda la salida
ob_start();

// Simular el POST
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['mensaje'] = 'Hola';

// Ejecutar la API
include '../api/chatbot.php';

// Obtener la salida
$output = ob_get_clean();

// Mostrar en texto plano para ver qué hay
header('Content-Type: text/plain; charset=utf-8');
echo "=== Salida Raw de la API ===\n\n";
echo $output;
echo "\n\n=== Longitud: " . strlen($output) . " bytes ===\n";

// Intentar decodificar como JSON
echo "\n=== Intento de decode JSON ===\n";
$json = json_decode($output, true);
if ($json === null) {
    echo "ERROR: No es JSON válido\n";
    echo "Error JSON: " . json_last_error_msg() . "\n";
} else {
    echo "✓ JSON válido:\n";
    print_r($json);
}