<?php
session_start();
require_once '../inc/db.php';

echo "=== Diagnóstico del Chatbot API ===\n\n";

// 1. Verificar sesión
if (isset($_SESSION['usuario'])) {
    echo "✓ Sesión activa\n";
    echo "  Usuario: " . $_SESSION['usuario']['nombre'] . "\n";
    echo "  ID: " . $_SESSION['usuario']['id'] . "\n";
    echo "  Rol: " . $_SESSION['usuario']['rol'] . "\n\n";
} else {
    echo "✗ No hay sesión activa\n\n";
}

// 2. Verificar API Key
try {
    $stmt = $pdo->prepare("SELECT valor FROM configuracion WHERE clave = 'gemini_api_key'");
    $stmt->execute();
    $apiKey = $stmt->fetchColumn();

    if ($apiKey) {
        echo "✓ API Key configurada: " . substr($apiKey, 0, 10) . "...\n\n";
    } else {
        echo "✗ API Key NO encontrada\n\n";
    }
} catch (Exception $e) {
    echo "✗ Error al verificar API Key: " . $e->getMessage() . "\n\n";
}

// 3. Probar API directamente
if (isset($_SESSION['usuario'])) {
    echo "=== Probando API del chatbot ===\n";

    $_POST['mensaje'] = 'Hola';

    ob_start();
    try {
        require '../api/chatbot.php';
        $output = ob_get_clean();
        echo "Respuesta de la API:\n";
        echo $output . "\n";
    } catch (Exception $e) {
        ob_end_clean();
        echo "✗ Error al ejecutar API: " . $e->getMessage() . "\n";
    }
} else {
    echo "⚠ Necesitas iniciar sesión para probar la API\n";
    echo "Ve a: http://localhost/Turnos-Ya/index.php\n";
}