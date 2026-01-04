<?php
/**
 * Prueba de envío de email con PHPMailer
 */

require_once 'inc/email_config.php';

echo "🧪 Probando envío de email con PHPMailer...\n\n";

try {
    // Configurar email de prueba
    $destinatario = 'test@example.com'; // Este email irá a Mailtrap
    $asunto = '🧪 Prueba de Email - Turnos Ya';
    $mensaje = '
    <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
        <h2 style="color: #333;">¡Hola!</h2>
        <p>Este es un email de prueba enviado desde el sistema Turnos Ya.</p>
        <p>Si estás viendo este mensaje, la configuración de PHPMailer está funcionando correctamente.</p>
        <p><strong>Detalles de la configuración:</strong></p>
        <ul>
            <li>Servidor SMTP: sandbox.smtp.mailtrap.io</li>
            <li>Puerto: 2525</li>
            <li>Autenticación: Sí</li>
            <li>Encriptación: STARTTLS</li>
        </ul>
        <p>Este email se envió usando PHPMailer con credenciales de Mailtrap.</p>
        <br>
        <p style="color: #666; font-size: 12px;">
            Sistema de Turnos Ya - Email de prueba
        </p>
    </div>
    ';

    // Enviar email
    $enviado = enviarEmailPHPMailer($destinatario, $asunto, $mensaje, null, 'prueba');

    if ($enviado) {
        echo "✅ Email enviado exitosamente!\n";
        echo "📧 Revisa tu bandeja de entrada en Mailtrap para ver el email.\n";
        echo "🔗 https://mailtrap.io/inboxes\n";
    } else {
        echo "❌ Error al enviar el email.\n";
        echo "Revisa los logs de error para más detalles.\n";
    }

} catch (Exception $e) {
    echo "❌ Error en la configuración: " . $e->getMessage() . "\n";
}
?>