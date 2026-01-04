<?php
/**
 * API: Detección y Gestión de No-Shows
 * Endpoint para obtener predicciones de cancelación y gestionar lista de espera
 */

require_once '../inc/auth.php';
require_once '../inc/ia_predictor.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

header('Content-Type: application/json; charset=utf-8');

try {
    global $pdo;
    $predictor = new IAPredictor($pdo);
    $accion = $_GET['accion'] ?? 'listar';
    
    switch ($accion) {
        case 'listar':
            // Listar todos los turnos en riesgo
            $turnosRiesgo = $predictor->identificarTurnosRiesgo();
            echo json_encode([
                'success' => true,
                'total' => count($turnosRiesgo),
                'turnos_riesgo' => $turnosRiesgo
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
            
        case 'predecir':
            // Predecir cancelación de un turno específico
            $turno_id = $_GET['turno_id'] ?? null;
            if (!$turno_id) {
                throw new Exception('Debe proporcionar turno_id');
            }
            
            $prediccion = $predictor->predecirCancelacion($turno_id);
            echo json_encode([
                'success' => true,
                'prediccion' => $prediccion
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            break;
            
        case 'marcar_confirmado':
            // Marcar turno como confirmado (reduce riesgo)
            if (!isset($_POST['turno_id'])) {
                throw new Exception('Debe proporcionar turno_id');
            }
            
            $turno_id = $_POST['turno_id'];
            $sql = "UPDATE turnos SET estado = 'confirmado' WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$turno_id]);
            
            echo json_encode([
                'success' => true,
                'mensaje' => 'Turno confirmado exitosamente'
            ]);
            break;
            
        case 'enviar_recordatorio':
            // Simular envío de recordatorio
            if (!isset($_POST['turno_id'])) {
                throw new Exception('Debe proporcionar turno_id');
            }
            
            $turno_id = $_POST['turno_id'];
            
            // Obtener datos del turno
            $sql = "SELECT t.*, u.email, u.nombre 
                    FROM turnos t 
                    JOIN usuarios u ON t.usuario_id = u.id 
                    WHERE t.id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$turno_id]);
            $turno = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$turno) {
                throw new Exception('Turno no encontrado');
            }
            
            // En producción, aquí se enviaría un email real
            // mail($turno['email'], 'Recordatorio de Turno', ...);
            
            echo json_encode([
                'success' => true,
                'mensaje' => "Recordatorio enviado a {$turno['nombre']} ({$turno['email']})",
                'turno' => $turno
            ], JSON_UNESCAPED_UNICODE);
            break;
            
        default:
            throw new Exception('Acción no válida');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'mensaje' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
