<?php
/**
 * API: Horarios Disponibles
 * Devuelve los horarios disponibles para una fecha específica
 */

require_once '../inc/auth.php';
require_once '../inc/db.php';

// Verificar que el usuario esté autenticado
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode([
        'error' => true,
        'mensaje' => 'Debes iniciar sesión'
    ]);
    exit;
}

// Configurar respuesta JSON
header('Content-Type: application/json; charset=utf-8');

try {
    // Obtener fecha
    $fecha = $_GET['fecha'] ?? date('Y-m-d', strtotime('+1 day'));
    $servicio_id = isset($_GET['servicio_id']) ? (int)$_GET['servicio_id'] : null;

    // Validar fecha
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        throw new Exception('Formato de fecha inválido');
    }

    // Obtener día de la semana
    $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
    $numDia = date('w', strtotime($fecha));
    $diaSemana = $dias[$numDia];

    // Obtener configuración de horarios para este día
    $stmt = $pdo->prepare("SELECT * FROM horarios WHERE dia = ?");
    $stmt->execute([$diaSemana]);
    $horarioDia = $stmt->fetch(PDO::FETCH_ASSOC);

    $horariosDisponibles = [];

    if ($horarioDia && $horarioDia['abierto'] && !$horarioDia['es_feriado']) {
        $duracionTurno = $horarioDia['duracion'] ?? 30;

        // Obtener cupos máximos del día específico, con fallback al global
        $cuposMax = (int)($horarioDia['cupos_maximos'] ?? 0);
        if ($cuposMax <= 0) {
            // Fallback al cupo global
            $stmt = $pdo->query("SELECT valor FROM configuracion WHERE clave = 'cupos_simultaneos'");
            $cuposMax = (int)($stmt->fetchColumn() ?: 1);
        }

        // Procesar primer horario
        if ($horarioDia['hora_inicio'] && $horarioDia['hora_fin']) {
            $horaActual = new DateTime($fecha . ' ' . $horarioDia['hora_inicio']);
            $horaFinal = new DateTime($fecha . ' ' . $horarioDia['hora_fin']);

            while ($horaActual < $horaFinal) {
                $horaStr = $horaActual->format('H:i:s');

                // Verificar ocupación
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM turnos WHERE fecha = ? AND hora = ? AND estado IN ('pendiente', 'confirmado')");
                $stmt->execute([$fecha, $horaStr]);
                $ocupados = $stmt->fetchColumn();

                if ($ocupados < $cuposMax) {
                    $horariosDisponibles[] = [
                        'hora' => $horaStr,
                        'hora_formateada' => $horaActual->format('H:i'),
                        'disponible' => true,
                        'cupos_disponibles' => $cuposMax - $ocupados
                    ];
                }

                $horaActual->modify('+' . $duracionTurno . ' minutes');
            }
        }

        // Procesar segundo horario si existe
        if ($horarioDia['turno_partido'] && $horarioDia['hora_inicio_2'] && $horarioDia['hora_fin_2']) {
            $horaActual = new DateTime($fecha . ' ' . $horarioDia['hora_inicio_2']);
            $horaFinal = new DateTime($fecha . ' ' . $horarioDia['hora_fin_2']);

            while ($horaActual < $horaFinal) {
                $horaStr = $horaActual->format('H:i:s');

                // Verificar ocupación
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM turnos WHERE fecha = ? AND hora = ? AND estado IN ('pendiente', 'confirmado')");
                $stmt->execute([$fecha, $horaStr]);
                $ocupados = $stmt->fetchColumn();

                if ($ocupados < $cuposMax) {
                    $horariosDisponibles[] = [
                        'hora' => $horaStr,
                        'hora_formateada' => $horaActual->format('H:i'),
                        'disponible' => true,
                        'cupos_disponibles' => $cuposMax - $ocupados
                    ];
                }

                $horaActual->modify('+' . $duracionTurno . ' minutes');
            }
        }
    }

    // Ordenar por hora
    usort($horariosDisponibles, function($a, $b) {
        return strcmp($a['hora'], $b['hora']);
    });

    // Respuesta exitosa
    echo json_encode([
        'success' => true,
        'fecha' => $fecha,
        'dia_semana' => $diaSemana,
        'horarios' => $horariosDisponibles,
        'total_disponibles' => count($horariosDisponibles),
        'mensaje' => count($horariosDisponibles) > 0
            ? "Horarios disponibles para {$diaSemana} " . date('d/m', strtotime($fecha))
            : "No hay horarios disponibles para esta fecha"
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'error' => true,
        'mensaje' => $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
?>