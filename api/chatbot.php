<?php
/**
 * API: Chatbot IA con Gemini
 * Endpoint para comunicación con el asistente inteligente
 */

// Capturar errores en un log
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../chatbot_errors.log');
error_reporting(E_ALL);

// Pero no mostrarlos en pantalla para no romper el JSON
ini_set('display_errors', 0);

require_once __DIR__ . '/../inc/auth.php';
require_once __DIR__ . '/../inc/gemini_service.php';

header('Content-Type: application/json; charset=utf-8');

// Rate limiting: 10 requests per minute per IP
$ip = $_SERVER['REMOTE_ADDR'];
$rate_file = __DIR__ . '/../logs/rate_limit_' . md5($ip) . '.txt';
$current_time = time();
$window = 60; // 1 minute
$max_requests = 10;

if (file_exists($rate_file)) {
    $data = json_decode(file_get_contents($rate_file), true);
    if ($data['window_start'] + $window < $current_time) {
        $data = ['count' => 1, 'window_start' => $current_time];
    } else {
        $data['count']++;
        if ($data['count'] > $max_requests) {
            http_response_code(429);
            echo json_encode(['error' => true, 'mensaje' => 'Demasiadas solicitudes. Inténtalo más tarde.']);
            exit;
        }
    }
} else {
    $data = ['count' => 1, 'window_start' => $current_time];
}
file_put_contents($rate_file, json_encode($data));

// Log de inicio
error_log('=== Chatbot API Called ===');
error_log('Time: ' . date('Y-m-d H:i:s'));

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    error_log('ERROR: Usuario no autenticado');
    echo json_encode([
        'error' => true,
        'mensaje' => 'Debes iniciar sesión para usar el asistente'
    ]);
    exit;
}

$usuario_email = $_SESSION['usuario']['email'] ?? $_SESSION['usuario']['nombre'] ?? 'Usuario';
error_log('Usuario autenticado: ' . $usuario_email);

try {
    // Obtener mensaje del usuario
    $input = json_decode(file_get_contents('php://input'), true);
    $mensaje = $input['mensaje'] ?? $_POST['mensaje'] ?? '';
    
    error_log('Mensaje recibido: ' . $mensaje);
    
    if (empty($mensaje)) {
        throw new Exception('Mensaje vacío');
    }
    
    // Instanciar servicio de IA
    global $pdo;
    error_log('Instanciando GeminiService...');
    $gemini = new GeminiService($pdo);
    
    // Procesar mensaje
    $usuario_id = $_SESSION['usuario']['id'];
    error_log('Procesando mensaje para usuario ID: ' . $usuario_id);
    $respuesta = $gemini->procesarMensajeUsuario($mensaje, $usuario_id);

    // Guardar conversación en historial
    try {
        $stmt = $pdo->prepare("INSERT INTO historial_chat (usuario_id, mensaje, respuesta, contexto) VALUES (?, ?, ?, ?)");
        $contexto = json_encode(['sesion_id' => session_id(), 'timestamp' => date('Y-m-d H:i:s')]);
        $stmt->execute([$usuario_id, $mensaje, $respuesta, $contexto]);
        error_log('Conversación guardada en historial');
    } catch (Exception $e) {
        error_log('Error guardando en historial: ' . $e->getMessage());
    }

    error_log('Respuesta generada OK');
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'mensaje' => $mensaje,
        'respuesta' => $respuesta,
        'timestamp' => date('Y-m-d H:i:s')
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    error_log('ERROR en API: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'mensaje' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
