<?php
/**
 * Script de Hardening de Seguridad - Turnos-Ya
 * Implementa mejoras críticas de seguridad
 */

// Configuración de errores segura
ini_set('display_errors', 0);
ini_set('log_errors', 1);
error_reporting(E_ALL);

// Configuración de sesiones ultra-segura
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.gc_maxlifetime', 3600); // 1 hora
ini_set('session.cookie_lifetime', 3600);
ini_set('session.save_path', '/tmp/php_sessions'); // Cambiar a path seguro en producción
session_name('turnos_ya_secure_session');

// Forzar HTTPS si no está activo
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    if (!headers_sent()) {
        header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// Clase de validación de entrada
class InputValidator {
    public static function sanitizeString($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public static function validatePassword($password) {
        return strlen($password) >= 8 &&
               preg_match('/[A-Z]/', $password) &&
               preg_match('/[a-z]/', $password) &&
               preg_match('/[0-9]/', $password);
    }

    public static function validateDate($date) {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) &&
               strtotime($date) !== false;
    }

    public static function validateTime($time) {
        return preg_match('/^\d{2}:\d{2}$/', $time);
    }

    public static function validateNumeric($value, $min = null, $max = null) {
        if (!is_numeric($value)) return false;
        if ($min !== null && $value < $min) return false;
        if ($max !== null && $value > $max) return false;
        return true;
    }
}

// Clase de rate limiting mejorada
class RateLimiter {
    private static $storage = [];

    public static function checkLimit($key, $maxRequests = 10, $windowSeconds = 60) {
        $now = time();
        $windowStart = $now - $windowSeconds;

        // Limpiar entradas antiguas
        self::$storage = array_filter(self::$storage, function($entry) use ($windowStart) {
            return $entry['time'] > $windowStart;
        });

        // Contar requests en la ventana
        $requestsInWindow = array_filter(self::$storage, function($entry) use ($key, $windowStart) {
            return $entry['key'] === $key && $entry['time'] > $windowStart;
        });

        if (count($requestsInWindow) >= $maxRequests) {
            return false;
        }

        // Registrar nuevo request
        self::$storage[] = ['key' => $key, 'time' => $now];
        return true;
    }

    public static function getRemainingRequests($key, $maxRequests = 10, $windowSeconds = 60) {
        $now = time();
        $windowStart = $now - $windowSeconds;

        $requestsInWindow = array_filter(self::$storage, function($entry) use ($key, $windowStart) {
            return $entry['key'] === $key && $entry['time'] > $windowStart;
        });

        return max(0, $maxRequests - count($requestsInWindow));
    }
}

// Clase de logging de seguridad
class SecurityLogger {
    private static $logFile = __DIR__ . '/../logs/security.log';

    public static function logEvent($event, $data = []) {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'session_id' => session_id(),
            'data' => $data
        ];

        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0750, true);
        }

        file_put_contents(self::$logFile,
            json_encode($entry) . PHP_EOL,
            FILE_APPEND | LOCK_EX);
    }
}

// Función de sanitización global
function sanitizeInput($data) {
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return InputValidator::sanitizeString($data);
}

// Función para validar CSRF token
function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}

// Función para generar CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Función para regenerar sesión periódicamente
function regenerateSessionIfNeeded() {
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 minutos
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Función para validar permisos de admin
function requireAdmin() {
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
        SecurityLogger::logEvent('UNAUTHORIZED_ADMIN_ACCESS', [
            'page' => $_SERVER['REQUEST_URI']
        ]);
        header('Location: ../index.php');
        exit;
    }

    if ($_SESSION['role'] !== 'admin') {
        SecurityLogger::logEvent('INSUFFICIENT_PRIVILEGES', [
            'user_id' => $_SESSION['user_id'],
            'role' => $_SESSION['role'],
            'page' => $_SERVER['REQUEST_URI']
        ]);
        header('Location: ../index.php');
        exit;
    }
}

// Función para validar permisos de usuario
function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        SecurityLogger::logEvent('UNAUTHORIZED_ACCESS', [
            'page' => $_SERVER['REQUEST_URI']
        ]);
        header('Location: ../index.php');
        exit;
    }
}

// Headers de seguridad adicionales
function setSecurityHeaders() {
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self';");
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Inicializar hardening
function initSecurityHardening() {
    setSecurityHeaders();
    regenerateSessionIfNeeded();

    // Rate limiting básico para todas las páginas
    $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!RateLimiter::checkLimit($clientIP, 100, 60)) { // 100 requests por minuto
        SecurityLogger::logEvent('RATE_LIMIT_EXCEEDED', ['ip' => $clientIP]);
        http_response_code(429);
        die('Demasiadas solicitudes. Intente más tarde.');
    }
}

// Auto-inicializar si se incluye este archivo
if (basename(__FILE__) !== basename($_SERVER['SCRIPT_FILENAME'])) {
    initSecurityHardening();
}
?>