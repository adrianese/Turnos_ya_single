<?php
/**
 * API DEBUG: Chatbot IA con Gemini
 * Versión con errores visibles para debugging
 */

// MOSTRAR TODOS LOS ERRORES
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "=== DEBUG MODE ACTIVADO ===\n\n";

try {
    echo "1. Cargando auth.php...\n";
    require_once __DIR__ . '/../inc/auth.php';
    echo "✓ auth.php cargado\n\n";
    
    echo "2. Cargando gemini_service.php...\n";
    require_once __DIR__ . '/../inc/gemini_service.php';
    echo "✓ gemini_service.php cargado\n\n";
    
    echo "3. Verificando sesión...\n";
    if (!isLoggedIn()) {
        die("✗ ERROR: Usuario no autenticado\n");
    }
    echo "✓ Usuario autenticado: " . $_SESSION['usuario']['email'] . "\n\n";
    
    echo "4. Obteniendo mensaje...\n";
    $input = json_decode(file_get_contents('php://input'), true);
    $mensaje = $input['mensaje'] ?? $_POST['mensaje'] ?? 'hola';
    echo "✓ Mensaje: " . $mensaje . "\n\n";
    
    echo "5. Instanciando GeminiService...\n";
    global $pdo;
    $gemini = new GeminiService($pdo);
    echo "✓ GeminiService instanciado\n\n";
    
    echo "6. Procesando mensaje...\n";
    $usuario_id = $_SESSION['usuario']['id'];
    $respuesta = $gemini->procesarMensajeUsuario($mensaje, $usuario_id);
    echo "✓ Respuesta generada\n\n";
    
    echo "7. Generando JSON...\n";
    $json = json_encode([
        'success' => true,
        'mensaje' => $mensaje,
        'respuesta' => $respuesta,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
    echo "✓ JSON generado\n\n";
    echo "=== RESULTADO ===\n";
    echo $json;
    
} catch (Exception $e) {
    echo "\n\n✗✗✗ ERROR CAPTURADO ✗✗✗\n";
    echo "Mensaje: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n\n";
    echo "Stack Trace:\n";
    echo $e->getTraceAsString();
}
