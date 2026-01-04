# 🔒 ANÁLISIS DE VULNERABILIDADES - Turnos-Ya v2.1

## 📋 **RESUMEN EJECUTIVO**

Este análisis identifica vulnerabilidades de seguridad críticas y medias en el sistema Turnos-Ya. **El sistema NO está listo para producción** sin implementaciones adicionales de seguridad.

**Nivel de riesgo general: CRÍTICO** ⚠️

---

## 🚨 **VULNERABILIDADES CRÍTICAS**

### 1. **Validación de Entrada Insuficiente** 🔴

#### **Archivos afectados:**
- `register.php` - Sin validación de nombre, email, password
- `reservar.php` - Sin validación de servicios, fechas
- `admin/horarios.php` - Sin validación de horarios/cupos
- APIs - Sin validación de parámetros

#### **Problemas identificados:**
```php
// register.php - SIN validación
$nombre = $_POST['nombre'] ?? '';
$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
```

#### **Riesgos:**
- **XSS** vía campos de texto
- **SQL Injection** potencial (aunque usa PDO)
- **Buffer Overflow** en campos largos
- **Inyección de comandos** vía uploads

#### **Solución recomendada:**
```php
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePassword($password) {
    return strlen($password) >= 8 &&
           preg_match('/[A-Z]/', $password) &&
           preg_match('/[a-z]/', $password) &&
           preg_match('/[0-9]/', $password);
}
```

---

### 2. **Sesiones sin Configuración Segura** 🔴

#### **Problemas identificados:**
```php
// inc/auth.php - Configuración básica pero insuficiente
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}
```

#### **Vulnerabilidades:**
- **Sin límite de tiempo de sesión**
- **Sin regeneración periódica de ID**
- **Sin invalidación de sesiones antiguas**
- **Sin protección contra session fixation**
- **Sin configuración de session.save_path seguro**

#### **Riesgos:**
- **Session Hijacking**
- **Session Fixation**
- **Sesiones perpetuas**

#### **Solución recomendada:**
```php
// Configuración segura de sesiones
ini_set('session.cookie_lifetime', 3600); // 1 hora
ini_set('session.gc_maxlifetime', 3600);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.save_path', '/var/lib/php/sessions'); // Path seguro

// Regeneración periódica
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) { // 5 min
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}
```

---

### 3. **Contraseñas en Texto Plano Potencial** 🔴

#### **Problemas identificados:**
- **Logs de errores** pueden contener contraseñas
- **Sin política de contraseñas fuerte**
- **Sin rate limiting en login**
- **Sin bloqueo de cuenta tras intentos fallidos**

#### **Archivos riesgosos:**
```php
// chatbot_errors.log - Puede contener datos sensibles
error_log('Error: ' . $error_message);
```

#### **Riesgos:**
- **Credential Stuffing**
- **Brute Force attacks**
- **Password spraying**

#### **Solución recomendada:**
```php
// Política de contraseñas
function enforcePasswordPolicy($password) {
    $errors = [];
    if (strlen($password) < 12) $errors[] = "Mínimo 12 caracteres";
    if (!preg_match('/[A-Z]/', $password)) $errors[] = "Al menos 1 mayúscula";
    if (!preg_match('/[a-z]/', $password)) $errors[] = "Al menos 1 minúscula";
    if (!preg_match('/[0-9]/', $password)) $errors[] = "Al menos 1 número";
    if (!preg_match('/[^A-Za-z0-9]/', $password)) $errors[] = "Al menos 1 símbolo";
    return $errors;
}

// Rate limiting en login
function checkLoginAttempts($email) {
    $attempts = $_SESSION['login_attempts'][$email] ?? 0;
    if ($attempts >= 5) {
        // Bloquear por 15 minutos
        return false;
    }
    return true;
}
```

---

## ⚠️ **VULNERABILIDADES MEDIAS**

### 4. **Sin Rate Limiting Robusto** 🟡

#### **Implementación actual:**
```php
// api/chatbot.php - Rate limiting básico
$max_requests = 10; // por minuto
```

#### **Problemas:**
- **Solo en API de chatbot**
- **No en formularios críticos** (login, registro, reservas)
- **Almacenamiento en archivos** (no escalable)
- **Sin progressive delays**
- **Sin IP whitelisting/blacklisting**

#### **Riesgos:**
- **DoS attacks**
- **Brute force**
- **API abuse**

#### **Solución recomendada:**
```php
// Rate limiting con Redis/memcached
interface RateLimiter {
    public function isAllowed($key, $limit, $window);
}

class RedisRateLimiter implements RateLimiter {
    public function isAllowed($key, $limit, $window) {
        $redis = new Redis();
        $redis->connect('127.0.0.1', 6379);

        $current = $redis->get($key);
        if ($current >= $limit) {
            return false;
        }

        $redis->incr($key);
        $redis->expire($key, $window);
        return true;
    }
}
```

---

### 5. **Sin HTTPS Forzado** 🟡

#### **Problemas identificados:**
- **No hay redirección HTTP → HTTPS**
- **Sesiones no forzadas a secure**
- **CSP permite HTTP**

#### **Archivo .htaccess actual:**
```apache
# NO hay redirección HTTPS forzada
```

#### **Riesgos:**
- **Man-in-the-Middle attacks**
- **Session hijacking**
- **Data interception**

#### **Solución recomendada:**
```apache
# .htaccess - Forzar HTTPS
RewriteEngine On
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# Headers de seguridad mejorados
Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
```

---

### 6. **Sin Sanitización Completa de Datos** 🟡

#### **Problemas identificados:**
- **HTML entities** no siempre aplicados
- **Sin validación de tipos de datos**
- **Inputs de archivos no validados**
- **JSON inputs no sanitizados**

#### **Ejemplos riesgosos:**
```php
// Sin sanitización
$servicio_id = $_POST['servicio_id']; // Debería ser int
$fecha = $_POST['fecha']; // Sin validación de formato
```

#### **Riesgos:**
- **XSS stored/reflected**
- **CSRF bypass**
- **Data corruption**

#### **Solución recomendada:**
```php
// Sanitización completa
function sanitizeAllInputs($data) {
    if (is_array($data)) {
        return array_map('sanitizeAllInputs', $data);
    }

    $data = trim($data);
    $data = filter_var($data, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

// Validación de tipos
function validateReservationData($data) {
    $errors = [];

    if (!is_numeric($data['servicio_id'])) {
        $errors[] = "ID de servicio inválido";
    }

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['fecha'])) {
        $errors[] = "Formato de fecha inválido";
    }

    if (!preg_match('/^\d{2}:\d{2}$/', $data['hora'])) {
        $errors[] = "Formato de hora inválido";
    }

    return $errors;
}
```

---

### 7. **Posibles SQL Injection (PDO)** 🟡

#### **Estado actual:**
- **Usa PDO con prepared statements** ✅
- **Pero configuración incompleta**

#### **Problemas identificados:**
```php
// inc/db.php - Configuración básica
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
```

#### **Faltante:**
- `PDO::ATTR_EMULATE_PREPARES => false`
- `PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"`
- **Sin validación de tipos en bindings**

#### **Riesgos:**
- **SQL Injection** si hay errores de tipo
- **Charset issues**
- **Performance problems**

#### **Solución recomendada:**
```php
// Configuración segura de PDO
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    PDO::ATTR_STRINGIFY_FETCHES => false,
]);

// Prepared statements con tipos explícitos
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ? AND activo = ?");
$stmt->execute([$userId, $activo]); // Tipos inferidos
```

---

## 🛡️ **MEDIDAS DE SEGURIDAD EXISTENTES**

### **✅ Implementadas:**
- **CSRF protection** en login
- **Password hashing** con bcrypt
- **Session regeneration** en login
- **PDO prepared statements**
- **Security headers** básicos en .htaccess
- **Rate limiting** en API chatbot
- **Input sanitization** básica
- **File access protection** (.env, composer files)

### **✅ Parcialmente implementadas:**
- **Session security** (básica)
- **Error logging** (sin sanitización)
- **Input validation** (mínima)

---

## 🚀 **RECOMENDACIONES PARA PRODUCCIÓN**

### **INMEDIATAS (Esta semana):**

#### 1. **Auditoría de Seguridad Profesional**
```bash
# Herramientas recomendadas:
- OWASP ZAP (scanning dinámico)
- SonarQube (análisis estático)
- SQLMap (testing SQL injection)
- Nikto (scanning web server)
```

#### 2. **Hardening del Servidor**
```bash
# Apache hardening
sudo a2enmod headers security2
sudo a2dismod -f autoindex

# PHP hardening
expose_php = Off
display_errors = Off
log_errors = On
error_log = /var/log/php/error.log

# MySQL hardening
ALTER USER 'app_user'@'localhost' PASSWORD EXPIRE INTERVAL 90 DAY;
```

#### 3. **Encriptación de Datos Sensibles**
```php
// Encriptación de datos sensibles
function encryptSensitiveData($data) {
    $key = getenv('ENCRYPTION_KEY');
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($data, 'AES-256-CBC', $key, 0, $iv);
    return base64_encode($iv . $encrypted);
}

function decryptSensitiveData($encryptedData) {
    $key = getenv('ENCRYPTION_KEY');
    $data = base64_decode($encryptedData);
    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    return openssl_decrypt($encrypted, 'AES-256-CBC', $key, 0, $iv);
}
```

#### 4. **Validaciones Adicionales**
```php
// Validación completa de usuarios
function validateUserRegistration($data) {
    $errors = [];

    // Nombre
    if (empty($data['nombre']) || strlen($data['nombre']) < 2) {
        $errors[] = "Nombre debe tener al menos 2 caracteres";
    }

    // Email
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email inválido";
    }

    // Verificar email único
    if (emailExists($data['email'])) {
        $errors[] = "Email ya registrado";
    }

    // Contraseña
    $passwordErrors = enforcePasswordPolicy($data['password']);
    $errors = array_merge($errors, $passwordErrors);

    return $errors;
}
```

#### 5. **Logging de Seguridad**
```php
// Logging de seguridad estructurado
class SecurityLogger {
    private $logFile;

    public function __construct($logFile = '/var/log/app/security.log') {
        $this->logFile = $logFile;
    }

    public function logSecurityEvent($event, $data = []) {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'data' => $data
        ];

        error_log(json_encode($entry) . "\n", 3, $this->logFile);
    }
}

// Uso
$securityLogger = new SecurityLogger();
$securityLogger->logSecurityEvent('LOGIN_FAILED', ['email' => $email]);
$securityLogger->logSecurityEvent('SQL_INJECTION_ATTEMPT', ['query' => $suspiciousQuery]);
```

#### 6. **Backup Automático**
```bash
# Script de backup automático
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/app"

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/html/

# Rotate backups (keep last 7 days)
find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete

# Enviar notificación
echo "Backup completado: $DATE" | mail -s "Backup App" admin@example.com
```

---

## 📊 **PLAN DE IMPLEMENTACIÓN**

### **Fase 1: Crítico (1-2 días)**
- [ ] Implementar validación completa de inputs
- [ ] Configurar sesiones seguras
- [ ] Forzar HTTPS
- [ ] Mejorar rate limiting

### **Fase 2: Importante (3-5 días)**
- [ ] Hardening de servidor
- [ ] Encriptación de datos sensibles
- [ ] Logging de seguridad
- [ ] Backup automático

### **Fase 3: Optimización (1 semana)**
- [ ] Auditoría profesional
- [ ] Penetration testing
- [ ] Performance optimization
- [ ] Monitoring setup

---

## 🎯 **CONCLUSIÓN**

**El sistema Turnos-Ya tiene una base sólida pero requiere mejoras críticas de seguridad antes de cualquier despliegue en producción.**

**Tiempo estimado para producción-ready: 2-3 semanas con desarrollador senior.**

**Costo estimado de auditoría profesional: $2,000 - $5,000 USD.**

---

*Análisis realizado el 4 de enero de 2026 - Turnos-Ya v2.1*</content>
<parameter name="filePath">c:\apache\htdocs\Turnos-Ya-Single\ANALISIS_SEGURIDAD.md