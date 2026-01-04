<?php
/**
 * Sistema de Notificaciones - Turnos-Ya
 * Maneja envío de emails y recordatorios usando PHPMailer
 */

require_once 'db.php';
require_once 'email_config.php';

class NotificationService {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Envía notificación de confirmación de turno
     */
    public function enviarConfirmacionTurno($turno_id) {
        $turno = $this->obtenerDatosTurno($turno_id);
        if (!$turno) return false;

        $asunto = "✅ Turno Confirmado - Turnos Ya";
        $mensaje = $this->generarMensajeConfirmacion($turno);

        return $this->enviarEmail($turno['email'], $asunto, $mensaje, $turno_id, 'confirmacion');
    }

    /**
     * Envía recordatorio de turno
     */
    public function enviarRecordatorio($turno_id) {
        $turno = $this->obtenerDatosTurno($turno_id);
        if (!$turno) return false;

        $asunto = "🔔 Recordatorio de Turno - Turnos Ya";
        $mensaje = $this->generarMensajeRecordatorio($turno);

        return $this->enviarEmail($turno['email'], $asunto, $mensaje, $turno_id, 'recordatorio');
    }

    /**
     * Envía notificación de cancelación
     */
    public function enviarCancelacion($turno_id) {
        $turno = $this->obtenerDatosTurno($turno_id);
        if (!$turno) return false;

        $asunto = "❌ Turno Cancelado - Turnos Ya";
        $mensaje = $this->generarMensajeCancelacion($turno);

        return $this->enviarEmail($turno['email'], $asunto, $mensaje, $turno_id, 'cancelacion');
    }

    /**
     * Obtiene datos completos del turno
     */
    private function obtenerDatosTurno($turno_id) {
        $sql = "SELECT t.*, u.nombre, u.email, s.nombre as servicio_nombre, s.precio, s.duracion
                FROM turnos t
                JOIN usuarios u ON t.usuario_id = u.id
                LEFT JOIN servicios s ON t.servicio_id = s.id
                WHERE t.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$turno_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Genera mensaje de confirmación
     */
    private function generarMensajeConfirmacion($turno) {
        $fecha = date('d/m/Y', strtotime($turno['fecha']));
        $hora = date('H:i', strtotime($turno['hora']));

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #28a745;'>¡Turno Confirmado!</h2>
            <p>Hola <strong>{$turno['nombre']}</strong>,</p>
            <p>Tu turno ha sido confirmado exitosamente:</p>

            <div style='background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0;'>
                <p><strong>Servicio:</strong> {$turno['servicio_nombre']}</p>
                <p><strong>Fecha:</strong> {$fecha}</p>
                <p><strong>Hora:</strong> {$hora}</p>
                <p><strong>Duración:</strong> {$turno['duracion']} minutos</p>
                <p><strong>Precio:</strong> $" . number_format($turno['precio'], 2) . "</p>
            </div>

            <p><strong>Importante:</strong></p>
            <ul>
                <li>Llega 10 minutos antes de tu turno</li>
                <li>Si necesitas cancelar, hazlo con al menos 24 horas de anticipación</li>
                <li>Para cualquier cambio, contactanos</li>
            </ul>

            <p>¡Te esperamos!</p>
            <p><strong>Turnos Ya</strong></p>
        </div>
        ";
    }

    /**
     * Genera mensaje de recordatorio
     */
    private function generarMensajeRecordatorio($turno) {
        $fecha = date('d/m/Y', strtotime($turno['fecha']));
        $hora = date('H:i', strtotime($turno['hora']));

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #007bff;'>Recordatorio de Turno</h2>
            <p>Hola <strong>{$turno['nombre']}</strong>,</p>
            <p>Te recordamos que tienes un turno programado:</p>

            <div style='background: #e3f2fd; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #007bff;'>
                <p><strong>Servicio:</strong> {$turno['servicio_nombre']}</p>
                <p><strong>Fecha:</strong> {$fecha}</p>
                <p><strong>Hora:</strong> {$hora}</p>
                <p><strong>Duración:</strong> {$turno['duracion']} minutos</p>
            </div>

            <p>Si necesitas reagendar o cancelar tu turno, por favor avísanos con anticipación.</p>

            <p>¡Nos vemos pronto!</p>
            <p><strong>Turnos Ya</strong></p>
        </div>
        ";
    }

    /**
     * Genera mensaje de cancelación
     */
    private function generarMensajeCancelacion($turno) {
        $fecha = date('d/m/Y', strtotime($turno['fecha']));
        $hora = date('H:i', strtotime($turno['hora']));

        return "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #dc3545;'>Turno Cancelado</h2>
            <p>Hola <strong>{$turno['nombre']}</strong>,</p>
            <p>Tu turno ha sido cancelado:</p>

            <div style='background: #f8d7da; padding: 20px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545;'>
                <p><strong>Servicio:</strong> {$turno['servicio_nombre']}</p>
                <p><strong>Fecha:</strong> {$fecha}</p>
                <p><strong>Hora:</strong> {$hora}</p>
            </div>

            <p>Si deseas reagendar, puedes hacerlo desde nuestra plataforma.</p>

            <p>¡Esperamos verte pronto!</p>
            <p><strong>Turnos Ya</strong></p>
        </div>
        ";
    }

    /**
     * Envía email usando PHPMailer
     */
    private function enviarEmail($destinatario, $asunto, $mensaje, $turno_id = null, $tipo = 'general') {
        // Usar PHPMailer para envío
        $enviado = enviarEmailPHPMailer($destinatario, $asunto, $mensaje, $turno_id, $tipo);

        // Registrar en BD
        $this->registrarNotificacion($destinatario, $tipo, $enviado, $turno_id);

        return $enviado;
    }

    /**
     * Registra notificación en BD
     */
    private function registrarNotificacion($destinatario, $tipo, $enviado, $turno_id = null) {
        try {
            $sql = "INSERT INTO notificaciones (destinatario, tipo, enviado, turno_id, creado_en)
                    VALUES (?, ?, ?, ?, NOW())";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$destinatario, $tipo, $enviado ? 1 : 0, $turno_id]);
        } catch (Exception $e) {
            error_log('Error registrando notificación: ' . $e->getMessage());
        }
    }

    /**
     * Procesa recordatorios pendientes (para cron job)
     */
    public function procesarRecordatoriosPendientes() {
        // Recordatorios para turnos en 24 horas
        $sql = "SELECT t.id, t.fecha, t.hora, u.email
                FROM turnos t
                JOIN usuarios u ON t.usuario_id = u.id
                WHERE t.estado = 'confirmado'
                  AND t.recordatorio_enviado = 0
                  AND DATE_SUB(t.fecha, INTERVAL 1 DAY) = CURDATE()
                  AND t.hora > CURTIME()";

        $stmt = $this->pdo->query($sql);
        $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $enviados = 0;
        foreach ($turnos as $turno) {
            if ($this->enviarRecordatorio($turno['id'])) {
                // Marcar como enviado
                $update = $this->pdo->prepare("UPDATE turnos SET recordatorio_enviado = 1 WHERE id = ?");
                $update->execute([$turno['id']]);
                $enviados++;
            }
        }

        return $enviados;
    }
}
?>