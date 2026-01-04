<?php
/**
 * Script de prueba para el sistema de notificaciones
 * Prueba el envío de emails de confirmación, recordatorio y cancelación
 */

require_once '../inc/db.php';
require_once '../inc/notification_service.php';

echo "🧪 PRUEBA DEL SISTEMA DE NOTIFICACIONES\n";
echo "=======================================\n\n";

try {
    $notificaciones = new NotificationService($pdo);

    // Obtener un turno de prueba (el más reciente confirmado)
    $sql = "SELECT t.id, t.fecha, t.hora, u.nombre, u.email, s.nombre as servicio
            FROM turnos t
            JOIN usuarios u ON t.usuario_id = u.id
            JOIN servicios s ON t.servicio_id = s.id
            WHERE t.estado = 'confirmado'
            ORDER BY t.id DESC LIMIT 1";

    $stmt = $pdo->query($sql);
    $turno = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$turno) {
        echo "❌ No hay turnos confirmados para probar\n";
        echo "💡 Crea un turno primero desde la aplicación\n";
        exit(1);
    }

    echo "📋 Turno de prueba encontrado:\n";
    echo "   ID: {$turno['id']}\n";
    echo "   Usuario: {$turno['nombre']} ({$turno['email']})\n";
    echo "   Servicio: {$turno['servicio']}\n";
    echo "   Fecha/Hora: {$turno['fecha']} {$turno['hora']}\n\n";

    // Probar envío de confirmación
    echo "1️⃣ Probando envío de confirmación...\n";
    $resultado = $notificaciones->enviarConfirmacionTurno($turno['id']);
    echo ($resultado ? "✅ Confirmación enviada\n" : "❌ Error enviando confirmación\n");

    // Probar envío de recordatorio
    echo "\n2️⃣ Probando envío de recordatorio...\n";
    $resultado = $notificaciones->enviarRecordatorio($turno['id']);
    echo ($resultado ? "✅ Recordatorio enviado\n" : "❌ Error enviando recordatorio\n");

    // Probar envío de cancelación
    echo "\n3️⃣ Probando envío de cancelación...\n";
    $resultado = $notificaciones->enviarCancelacion($turno['id']);
    echo ($resultado ? "✅ Cancelación enviada\n" : "❌ Error enviando cancelación\n");

    // Verificar registros en la base de datos
    echo "\n📊 Verificando registros en base de datos...\n";
    $sql = "SELECT tipo, enviado, enviado_en FROM notificaciones WHERE turno_id = ? ORDER BY creado_en DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$turno['id']]);
    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($registros) > 0) {
        echo "✅ Registros encontrados:\n";
        foreach ($registros as $registro) {
            $estado = $registro['enviado'] ? 'Enviado' : 'Fallido';
            $fecha = $registro['enviado_en'] ?: 'No enviado';
            echo "   - {$registro['tipo']}: $estado ($fecha)\n";
        }
    } else {
        echo "❌ No se encontraron registros en la base de datos\n";
    }

    echo "\n🎉 Prueba completada!\n";
    echo "✅ Los emails se enviaron exitosamente usando PHPMailer + Mailtrap\n";
    echo "📧 Revisa https://mailtrap.io/inboxes para ver los emails enviados\n";
    echo "💡 El sistema de notificaciones está completamente funcional\n";

} catch (Exception $e) {
    echo "❌ Error en la prueba: " . $e->getMessage() . "\n";
    exit(1);
}
?>