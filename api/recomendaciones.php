<?php
/**
 * API: Recomendaciones Inteligentes de Horarios
 * Endpoint para obtener sugerencias de horarios basadas en IA
 * Requiere autenticación (usuario logueado)
 */

require_once '../inc/auth.php';
require_once '../inc/ia_recomendaciones.php';

// Verificar que el usuario esté autenticado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'error' => true,
        'mensaje' => 'Debes iniciar sesión para acceder a las recomendaciones'
    ]);
    exit;
}

// Configurar respuesta JSON
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

try {
    // Obtener parámetros
    $fecha = $_GET['fecha'] ?? date('Y-m-d', strtotime('+1 day'));
    $servicio_id = isset($_GET['servicio_id']) ? (int)$_GET['servicio_id'] : null;
    $usuario_id = $_SESSION['usuario']['id'];
    
    // Validar fecha
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        throw new Exception('Formato de fecha inválido. Use YYYY-MM-DD');
    }
    
    // Verificar que la fecha no sea pasada
    if (strtotime($fecha) < strtotime(date('Y-m-d'))) {
        throw new Exception('No se pueden hacer reservas en fechas pasadas');
    }
    
    // Instanciar módulo de IA
    global $pdo;
    $ia = new IARecomendaciones($pdo);
    
    // Obtener recomendaciones
    $recomendaciones = $ia->obtenerRecomendaciones($usuario_id, $fecha, $servicio_id);
    
    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'fecha' => $fecha,
        'usuario' => $_SESSION['usuario']['nombre'],
        'total_recomendaciones' => count($recomendaciones),
        'recomendaciones' => $recomendaciones,
        'mensaje' => count($recomendaciones) > 0 
            ? 'Horarios recomendados para ti basados en tus preferencias' 
            : 'No hay horarios disponibles para esta fecha'
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'mensaje' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
