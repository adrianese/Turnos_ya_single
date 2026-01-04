<?php
/**
 * Configuración de Email - PHPMailer
 * Configuración SMTP para envío de emails
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

/**
 * Configura y retorna una instancia de PHPMailer
 */
function configurarPHPMailer() {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP desde variables de entorno
        $mail->isSMTP();
        $mail->Host = $_ENV['SMTP_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = (int)$_ENV['SMTP_PORT'];
        $mail->Username = $_ENV['SMTP_USERNAME'];
        $mail->Password = $_ENV['SMTP_PASSWORD'];

        // Configuración de seguridad
        $encryption = $_ENV['SMTP_ENCRYPTION'] ?? 'tls';
        if ($encryption === 'tls') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        } elseif ($encryption === 'ssl') {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        }
        $mail->SMTPAutoTLS = true;

        // Configuración del remitente
        $mail->setFrom($_ENV['EMAIL_FROM'], $_ENV['EMAIL_FROM_NAME']);
        $mail->addReplyTo($_ENV['EMAIL_REPLY_TO'], $_ENV['EMAIL_FROM_NAME']);

        // Configuración general
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);

        return $mail;
    } catch (Exception $e) {
        error_log('Error configurando PHPMailer: ' . $e->getMessage());
        throw $e;
    }
}

/**
 * Envía un email usando PHPMailer
 */
function enviarEmailPHPMailer($destinatario, $asunto, $mensaje, $turno_id = null, $tipo = 'general') {
    try {
        $mail = configurarPHPMailer();

        // Configurar destinatario
        $mail->addAddress($destinatario);

        // Configurar contenido
        $mail->Subject = $asunto;
        $mail->Body = $mensaje;

        // Versión texto plano (opcional)
        $mail->AltBody = strip_tags($mensaje);

        // Enviar email
        $enviado = $mail->send();

        // Log de éxito
        error_log("Email enviado exitosamente a {$destinatario} - Tipo: {$tipo}" .
                 ($turno_id ? " - Turno ID: {$turno_id}" : ""));

        return $enviado;

    } catch (Exception $e) {
        // Log de error
        error_log("Error enviando email a {$destinatario}: " . $mail->ErrorInfo);
        error_log("Detalles del error: " . $e->getMessage());

        return false;
    }
}
?>