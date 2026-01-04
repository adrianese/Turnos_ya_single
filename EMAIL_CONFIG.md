# Configuración de Email - PHPMailer

## 📧 Sistema de Email Configurado

El sistema de notificaciones de Turnos Ya utiliza **PHPMailer** con configuración segura a través de **variables de entorno**.

### 🔒 Seguridad Implementada

- **Variables de entorno**: Todas las credenciales sensibles están en `.env`
- **Archivo protegido**: `.env` está incluido en `.gitignore`
- **Template disponible**: `.env.example` como guía para configuración

### 🔧 Configuración Actual

Las credenciales se cargan desde variables de entorno:

```php
$mail->Host = $_ENV['SMTP_HOST'];
$mail->Port = (int)$_ENV['SMTP_PORT'];
$mail->Username = $_ENV['SMTP_USERNAME'];
$mail->Password = $_ENV['SMTP_PASSWORD'];
```

### 📁 Archivos de Configuración

1. **`.env`** - Credenciales reales (NO subir al repositorio)
2. **`.env.example`** - Template con ejemplos
3. **`inc/email_config.php`** - Configuración de PHPMailer
4. **`inc/notification_service.php`** - Servicio de notificaciones

### 🚀 Para Producción

1. Copia `.env.example` a `.env`:
   ```bash
   cp .env.example .env
   ```

2. Configura tus credenciales reales en `.env`

3. Para diferentes proveedores SMTP, modifica las variables en `.env`

### 🧪 Probar el Sistema

```bash
# Ejecutar prueba de email
php test/test_email.php

# Ejecutar prueba completa de notificaciones
php test/test_notificaciones.php
```

### 📧 Tipos de Emails Enviados

- **Confirmación de turno** - Cuando se reserva un turno
- **Recordatorio** - 24 horas antes del turno
- **Cancelación** - Cuando se cancela un turno (✅ Configurado)
- **Pruebas** - Emails de testing

### 🔒 Seguridad

- ✅ Credenciales en variables de entorno
- ✅ Archivo `.env` excluido del repositorio
- ✅ Emails enviados de forma segura usando SMTP
- ✅ Logging de envíos sin exponer credenciales

### 📊 Monitoreo

Los emails enviados se registran en la tabla `notificaciones` con:
- Destinatario
- Tipo de email
- Estado de envío (éxito/error)
- ID del turno (si aplica)
- Fecha de envío

### 🌐 Proveedores SMTP Soportados

**Ejemplos de configuración en `.env`:**

**Gmail:**
```env
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=tu-email@gmail.com
SMTP_PASSWORD=tu-app-password
SMTP_ENCRYPTION=tls
```

**SendGrid:**
```env
SMTP_HOST=smtp.sendgrid.net
SMTP_PORT=587
SMTP_USERNAME=apikey
SMTP_PASSWORD=tu-sendgrid-api-key
SMTP_ENCRYPTION=tls
```

**Mailtrap (desarrollo):**
```env
SMTP_HOST=sandbox.smtp.mailtrap.io
SMTP_PORT=2525
SMTP_USERNAME=tu-username
SMTP_PASSWORD=tu-password
SMTP_ENCRYPTION=tls
```