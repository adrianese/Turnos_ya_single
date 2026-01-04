<?php
/**
 * Script para procesar recordatorios de turnos pendientes
 * Debe ejecutarse periódicamente (ej: cada hora) vía cron job
 */

require_once 'inc/db.php';
require_once 'inc/notification_service.php';

try {
    $pdo = getDBConnection();
    $notificaciones = new NotificationService($pdo);

    // Procesar recordatorios para turnos en las próximas 24 horas
    $sql = "SELECT t.id, t.fecha, t.hora, u.nombre, u.email, s.nombre as servicio
            FROM turnos t
            JOIN usuarios u ON t.usuario_id = u.id
            JOIN servicios s ON t.servicio_id = s.id
            WHERE t.fecha = DATE_ADD(CURDATE(), INTERVAL 1 DAY)
            AND t.estado = 'confirmado'
            AND NOT EXISTS (
                SELECT 1 FROM notificaciones n
                WHERE n.turno_id = t.id AND n.tipo = 'recordatorio'
            )";

    $stmt = $pdo->query($sql);
    $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $enviados = 0;
    foreach ($turnos as $turno) {
        if ($notificaciones->enviarRecordatorio($turno['id'])) {
            $enviados++;
            echo "Recordatorio enviado para turno #{$turno['id']} - {$turno['servicio']}\n";
        }
    }

    echo "Procesamiento completado. Recordatorios enviados: $enviados\n";

} catch (Exception $e) {
    echo "Error procesando recordatorios: " . $e->getMessage() . "\n";
    exit(1);
}
?>