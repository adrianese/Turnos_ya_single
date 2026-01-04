# 🔒 Guía de Implementación de Seguridad - Turnos-Ya

## 📋 **Resumen Ejecutivo**

Esta guía proporciona instrucciones paso a paso para implementar las mejoras críticas de seguridad identificadas en el análisis de vulnerabilidades. **El sistema actual NO es seguro para producción.**

**Tiempo estimado:** 2-3 semanas  
**Prioridad:** CRÍTICA

---

## 🚨 **Estado Actual de Seguridad**

### Vulnerabilidades Críticas Identificadas:
- ✅ **Validación de entrada insuficiente**
- ✅ **Sesiones sin configuración segura**
- ✅ **Sin rate limiting robusto**
- ✅ **Sin HTTPS forzado**
- ✅ **Sin sanitización completa**

### Nivel de Riesgo: **CRÍTICO** 🔴

---

## 🛠️ **FASE 1: Implementación Inmediata (1-2 días)**

### 1. **Configurar Variables de Entorno**

```bash
# Copiar archivo de ejemplo
cp .env.example .env

# Editar con valores reales
nano .env
```

**Variables críticas a configurar:**
```env
DB_PASS=tu_password_seguro_aqui
ENCRYPTION_KEY=tu_clave_de_32_caracteres_hex
BACKUP_ENCRYPTION_KEY=tu_clave_de_backup_de_32_caracteres_hex
SESSION_SECURE=true
```

### 2. **Configurar HTTPS**

#### Opción A: Let's Encrypt (Recomendado)
```bash
# Instalar Certbot
sudo apt install certbot python3-certbot-apache

# Obtener certificado
sudo certbot --apache -d tu-dominio.com
```

#### Opción B: Configuración Manual
```apache
# En /etc/apache2/sites-available/tu-sitio.conf
<VirtualHost *:443>
    ServerName tu-dominio.com
    DocumentRoot /var/www/html/Turnos-Ya-Single

    SSLEngine on
    SSLCertificateFile /ruta/a/certificado.crt
    SSLCertificateKeyFile /ruta/a/clave.key
    SSLCertificateChainFile /ruta/a/chain.crt

    # Headers de seguridad
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains; preload"
</VirtualHost>
```

### 3. **Habilitar Módulos de Apache**

```bash
# Habilitar módulos necesarios
sudo a2enmod headers
sudo a2enmod ssl
sudo a2enmod rewrite

# Reiniciar Apache
sudo systemctl restart apache2
```

### 4. **Configurar Permisos de Archivos**

```bash
# Permisos seguros
sudo chown -R www-data:www-data /var/www/html/Turnos-Ya-Single
sudo chmod -R 755 /var/www/html/Turnos-Ya-Single

# Archivos sensibles
sudo chmod 600 .env
sudo chmod 600 logs/*.log

# Directorios sensibles
sudo chmod 700 logs/
sudo chmod 700 database/
sudo chmod 700 scripts/
```

---

## 🛡️ **FASE 2: Hardening de Aplicación (3-5 días)**

### 1. **Actualizar Archivos Core**

Los siguientes archivos ya han sido actualizados con mejoras de seguridad:

- ✅ `inc/security_hardening.php` - Hardening central
- ✅ `inc/form_validator.php` - Validación robusta
- ✅ `.htaccess` - Configuración Apache segura
- ✅ `register.php` - Validación implementada
- ✅ `ANALISIS_SEGURIDAD.md` - Documentación completa

### 2. **Actualizar Archivos Restantes**

Aplicar el mismo patrón de validación a:

```php
// En todos los archivos que procesan formularios
require_once 'inc/form_validator.php';
require_once 'inc/security_hardening.php';

// Inicializar seguridad
initSecurityHardening();

// Validar entrada
$validator = new FormValidator($_POST);
if ($validator->validateReservation()) {
    $data = $validator->getSanitizedData();
    // Procesar datos seguros
} else {
    $errors = $validator->getErrors();
    // Mostrar errores
}
```

**Archivos a actualizar:**
- `reservar.php`
- `admin/horarios.php`
- `admin/servicios.php`
- `login.php` (si existe)
- Todas las APIs en `/api/`

### 3. **Mejorar Configuración de Base de Datos**

```php
// inc/db.php - Configuración segura
$pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci",
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    PDO::ATTR_STRINGIFY_FETCHES => false,
]);
```

### 4. **Configurar Logging de Seguridad**

```bash
# Crear directorios de logs
sudo mkdir -p /var/log/turnos_ya
sudo chown www-data:www-data /var/log/turnos_ya
sudo chmod 755 /var/log/turnos_ya

# Configurar logrotate
sudo nano /etc/logrotate.d/turnos_ya
```

**Contenido de `/etc/logrotate.d/turnos_ya`:**
```
/var/log/turnos_ya/*.log {
    daily
    missingok
    rotate 52
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload apache2
    endscript
}
```

---

## 🔐 **FASE 3: Configuración de Backup Seguro (1 semana)**

### 1. **Configurar Script de Backup**

```bash
# Hacer ejecutable el script
chmod +x scripts/backup_secure.sh

# Configurar variables de entorno para backup
echo "DB_PASSWORD=tu_password_db" >> .env
echo "BACKUP_ENCRYPTION_KEY=tu_clave_backup" >> .env
echo "NOTIFICATION_EMAIL=admin@tu-dominio.com" >> .env
```

### 2. **Crear Directorio de Backup Seguro**

```bash
# Crear directorio con permisos restrictivos
sudo mkdir -p /var/backups/turnos_ya
sudo chown root:www-data /var/backups/turnos_ya
sudo chmod 770 /var/backups/turnos_ya
```

### 3. **Configurar Cron para Backup Automático**

```bash
# Editar crontab
sudo crontab -e

# Agregar línea para backup diario a las 2 AM
0 2 * * * /var/www/html/Turnos-Ya-Single/scripts/backup_secure.sh
```

### 4. **Probar Backup**

```bash
# Ejecutar backup manual
./scripts/backup_secure.sh

# Verificar que se creó
ls -la /var/backups/turnos_ya/
```

---

## 📊 **FASE 4: Monitoreo y Alertas (2-3 semanas)**

### 1. **Configurar Monitoreo de Logs**

```bash
# Instalar herramientas de monitoreo
sudo apt install logwatch fail2ban

# Configurar fail2ban para protección adicional
sudo cp /etc/fail2ban/jail.conf /etc/fail2ban/jail.local
sudo nano /etc/fail2ban/jail.local
```

**Configuración de fail2ban:**
```ini
[turnos-ya]
enabled = true
port = http,https
filter = turnos-ya
logpath = /var/log/turnos_ya/security.log
maxretry = 3
bantime = 3600
```

### 2. **Configurar Alertas**

```bash
# Instalar mailutils para notificaciones
sudo apt install mailutils

# Configurar email para root
sudo nano /etc/aliases
# Agregar: root: admin@tu-dominio.com
sudo newaliases
```

### 3. **Implementar Health Checks**

Crear `health_check.php`:
```php
<?php
// Health check básico
header('Content-Type: application/json');

$checks = [
    'database' => checkDatabase(),
    'disk_space' => checkDiskSpace(),
    'security_logs' => checkSecurityLogs()
];

$status = 'healthy';
$statusCode = 200;

foreach ($checks as $check) {
    if (!$check['status']) {
        $status = 'unhealthy';
        $statusCode = 503;
        break;
    }
}

http_response_code($statusCode);
echo json_encode([
    'status' => $status,
    'timestamp' => date('c'),
    'checks' => $checks
]);

function checkDatabase() {
    try {
        // Verificar conexión a BD
        return ['status' => true, 'message' => 'OK'];
    } catch (Exception $e) {
        return ['status' => false, 'message' => $e->getMessage()];
    }
}

function checkDiskSpace() {
    $free = disk_free_space('/');
    $total = disk_total_space('/');
    $percentage = ($free / $total) * 100;

    return $percentage > 10 ?
        ['status' => true, 'message' => round($percentage, 1) . '% free'] :
        ['status' => false, 'message' => 'Low disk space'];
}

function checkSecurityLogs() {
    $logFile = '/var/log/turnos_ya/security.log';
    return file_exists($logFile) && is_writable($logFile) ?
        ['status' => true, 'message' => 'OK'] :
        ['status' => false, 'message' => 'Log file issues'];
}
```

---

## 🧪 **FASE 5: Testing y Validación (1 semana)**

### 1. **Pruebas de Seguridad**

```bash
# Instalar herramientas de testing
sudo apt install nikto sqlmap wapiti

# Escaneo básico
nikto -h https://tu-dominio.com

# Testing de inyección SQL (en entorno de desarrollo)
sqlmap -u "https://tu-dominio.com/api/endpoint" --batch

# OWASP ZAP para testing avanzado
# Descargar desde: https://www.zaproxy.org/
```

### 2. **Pruebas de Penetración**

**Herramientas recomendadas:**
- **OWASP ZAP** - Escaneo automatizado
- **Burp Suite** - Testing manual
- **Postman** - Testing de APIs
- **SQLMap** - Testing de SQL injection

### 3. **Validación de Checklist**

- [ ] HTTPS configurado y forzando redirección
- [ ] Headers de seguridad presentes
- [ ] Validación de entrada en todos los formularios
- [ ] Rate limiting implementado
- [ ] Sesiones configuradas de forma segura
- [ ] Logs de seguridad funcionando
- [ ] Backup automático configurado
- [ ] Permisos de archivos correctos
- [ ] Variables sensibles en .env
- [ ] Base de datos hardening aplicado

---

## 📈 **Métricas de Éxito**

### **Antes de las mejoras:**
- Vulnerabilidades críticas: 6+
- Nivel de riesgo: CRÍTICO
- Puntaje de seguridad: 4/10

### **Después de las mejoras:**
- Vulnerabilidades críticas: 0
- Nivel de riesgo: BAJO
- Puntaje de seguridad: 8.5/10

---

## 🚨 **Plan de Contingencia**

### **Si se detecta una brecha:**

1. **Aislar el sistema**
   ```bash
   sudo iptables -A INPUT -s IP_ATACANTE -j DROP
   ```

2. **Cambiar todas las credenciales**
   ```sql
   ALTER USER 'app_user'@'localhost' IDENTIFIED BY 'nueva_password_segura';
   ```

3. **Revisar logs de seguridad**
   ```bash
   tail -f /var/log/turnos_ya/security.log
   ```

4. **Restaurar desde backup limpio**
   ```bash
   ./scripts/backup_secure.sh --restore ULTIMO_BACKUP_SEGURO
   ```

---

## 📞 **Soporte y Contacto**

### **Recursos de Aprendizaje:**
- [OWASP Cheat Sheet](https://cheatsheetseries.owasp.org/)
- [PHP Security Best Practices](https://phpsecurity.readthedocs.io/)
- [Apache Security](https://httpd.apache.org/docs/2.4/misc/security_tips.html)

### **Auditoría Profesional:**
Para producción real, contratar auditoría de seguridad certificada:
- **Costo estimado:** $2,000 - $5,000 USD
- **Tiempo:** 1-2 semanas
- **Certificaciones:** OSCP, CEH, CISSP

---

## ✅ **Checklist Final de Producción**

- [ ] **Seguridad de Red**
  - [ ] HTTPS configurado y funcionando
  - [ ] Certificado SSL válido
  - [ ] Headers de seguridad implementados
  - [ ] Firewall configurado

- [ ] **Seguridad de Aplicación**
  - [ ] Validación de entrada en todos los formularios
  - [ ] Protección CSRF implementada
  - [ ] Sesiones seguras configuradas
  - [ ] Rate limiting activo

- [ ] **Seguridad de Datos**
  - [ ] Contraseñas hasheadas correctamente
  - [ ] Datos sensibles encriptados
  - [ ] Backup automático funcionando
  - [ ] Logs de seguridad activos

- [ ] **Monitoreo y Respuesta**
  - [ ] Sistema de alertas configurado
  - [ ] Logs siendo monitoreados
  - [ ] Plan de respuesta a incidentes documentado
  - [ ] Contactos de emergencia definidos

---

**🎯 Implementar TODAS las fases antes del despliegue en producción.**

**📅 Fecha límite recomendada: 2 semanas desde hoy.**

**⚠️ NO desplegar sin completar FASE 1 como mínimo.**