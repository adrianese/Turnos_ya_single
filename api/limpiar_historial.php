<?php
/**
 * API: Limpiar historial de conversación
 */

require_once __DIR__ . '/../inc/auth.php';

header('Content-Type: application/json; charset=utf-8');

// Verificar autenticación
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'error' => true,
        'mensaje' => 'Debes iniciar sesión'
    ]);
    exit;
}

try {
    // Limpiar historial de la sesión
    $_SESSION['chat_historial'] = [];
    
    echo json_encode([
        'success' => true,
        'mensaje' => 'Historial limpiado exitosamente'
    ], JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'mensaje' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
