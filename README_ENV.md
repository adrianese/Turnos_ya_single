# 🔒 Configuración Segura - Variables de Entorno

## 📋 Requisitos Previos

Antes de ejecutar el proyecto, configura las variables de entorno:

### 1. Instalar Dependencias

```bash
composer install
```

### 2. Configurar Variables de Entorno

```bash
# Copiar el archivo de ejemplo
cp .env.example .env

# Editar .env con tus credenciales reales
nano .env
```

### 3. Variables Requeridas

#### Base de Datos
```env
DB_HOST=localhost
DB_NAME=mis_turnos
DB_USER=tu_usuario
DB_PASS=tu_password
```

#### API de Gemini
```env
GEMINI_API_KEY=tu_api_key_aqui
```

#### Configuración de Email (SMTP)
```env
SMTP_HOST=smtp.tu-proveedor.com
SMTP_PORT=587
SMTP_USERNAME=tu_usuario_smtp
SMTP_PASSWORD=tu_password_smtp
SMTP_ENCRYPTION=tls
EMAIL_FROM=noreply@tu-dominio.com
EMAIL_FROM_NAME="Tu App"
EMAIL_REPLY_TO=info@tu-dominio.com
```

### 4. Probar Configuración

```bash
# Probar conexión a base de datos
php -r "require 'inc/db.php'; echo '✅ DB OK\n';"

# Probar envío de email
php test/test_email.php

# Probar sistema completo
php test/test_notificaciones.php
```

## 🔐 Seguridad

- ✅ **Nunca subas `.env` al repositorio** (ya está en `.gitignore`)
- ✅ **Usa contraseñas fuertes** para todas las credenciales
- ✅ **Configura permisos restrictivos** en el servidor: `chmod 600 .env`
- ✅ **Usa HTTPS** en producción

## 🚨 Alertas de Seguridad

Si ves este error:
```
Dotenv\Exception\InvalidFileException: Failed to parse dotenv file
```

**Soluciones:**
1. Verifica que no haya espacios sin comillas en los valores
2. Usa comillas para valores con espacios: `EMAIL_FROM_NAME="Mi App"`
3. No uses caracteres especiales sin escapar

## 📞 Soporte

Para configurar diferentes proveedores SMTP, consulta `EMAIL_CONFIG.md`.