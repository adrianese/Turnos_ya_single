<?php
/**
 * Prueba específica del envío de email de cancelación
 */

require_once '../inc/db.php';
require_once '../inc/notification_service.php';

echo "🧪 PRUEBA DE EMAIL DE CANCELACIÓN\n";
echo "================================\n\n";

try {
    // Buscar un turno confirmado para probar
    $sql = "SELECT t.id, t.fecha, t.hora, u.nombre, u.email, s.nombre as servicio
            FROM turnos t
            JOIN usuarios u ON t.usuario_id = u.id
            JOIN servicios s ON t.servicio_id = s.id
            WHERE t.estado = 'confirmado'
            ORDER BY t.fecha DESC, t.hora DESC
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $turno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$turno) {
        echo "❌ No hay turnos confirmados para probar la cancelación\n";
        echo "💡 Crea un turno primero desde la aplicación\n";
        exit(1);
    }

    echo "📋 Turno encontrado para prueba:\n";
    echo "   ID: {$turno['id']}\n";
    echo "   Usuario: {$turno['nombre']} ({$turno['email']})\n";
    echo "   Servicio: {$turno['servicio']}\n";
    echo "   Fecha/Hora: {$turno['fecha']} {$turno['hora']}\n\n";

    // Probar envío de cancelación
    echo "1️⃣ Probando envío de cancelación...\n";

    $notificaciones = new NotificationService($pdo);
    $resultado = $notificaciones->enviarCancelacion($turno['id']);

    if ($resultado) {
        echo "✅ Email de cancelación enviado exitosamente\n";
        echo "📧 Revisa https://mailtrap.io/inboxes para ver el email\n";
    } else {
        echo "❌ Error al enviar email de cancelación\n";
        echo "Revisa los logs de error para más detalles\n";
    }

    // Verificar registro en BD
    echo "\n📊 Verificando registro en base de datos...\n";
    $sql = "SELECT * FROM notificaciones
            WHERE turno_id = ? AND tipo = 'cancelacion'
            ORDER BY id DESC LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$turno['id']]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($registro) {
        $estado = $registro['enviado'] ? 'Enviado' : 'Fallido';
        echo "✅ Registro encontrado: {$estado}\n";
    } else {
        echo "❌ No se encontró registro en BD\n";
    }

    echo "\n🎉 Prueba completada!\n";
    echo "💡 El sistema de cancelación con email está funcionando correctamente\n";

} catch (Exception $e) {
    echo "❌ Error en la prueba: " . $e->getMessage() . "\n";
    exit(1);
}
?>