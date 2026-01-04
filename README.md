# ⚠️ Turnos-Ya - Sistema de Gestión de Turnos con IA

## **DISCLAIMER IMPORTANTE**

**Este proyecto es exclusivamente EDUCACIONAL y DEMOSTRATIVO.**

- **NO está diseñado para entornos de producción reales**
- **NO garantiza seguridad completa** (vulnerabilidades potenciales no auditadas)
- **NO está completamente testeado** (puede contener bugs no detectados)
- **NO incluye hardening de seguridad** (configuraciones básicas)
- **NO es responsable por uso indebido** o consecuencias de implementación

**Úselo bajo su propio riesgo. Recomendamos fuertemente NO desplegar en entornos públicos sin auditorías de seguridad profesionales.**

---

## ¿Qué es Turnos-Ya?

Sistema web completo para gestión inteligente de turnos con integración de Inteligencia Artificial (Google Gemini). Incluye chatbot conversacional, sistema de recomendaciones, notificaciones automáticas y panel administrativo.

### Características Principales

- **Chatbot con IA** - Reservas conversacionales en español
- **Sistema de horarios dinámicos** - Configuración por día con cupos variables
- **Notificaciones automáticas** - Emails HTML profesionales
- **Analytics y predicciones** - Dashboard administrativo completo
- **Responsive design** - Funciona en móviles y desktop
- **Sistema multi-usuario** - Clientes y administradores

### Tecnologías

- **Backend**: PHP 7.4+ con MySQL
- **Frontend**: HTML5/CSS3/JavaScript vanilla
- **IA**: Google Gemini API
- **Email**: SMTP con PHPMailer

---

## Instalación Rápida

### Prerrequisitos
- PHP 7.4+
- MySQL 8.0+
- Composer
- API Key de Google Gemini

### Pasos
1. **Clonar repositorio**
   ```bash
   git clone https://github.com/tuusuario/turnos-ya.git
   cd turnos-ya
   ```

2. **Instalar dependencias**
   ```bash
   composer install
   ```

3. **Configurar base de datos**
   ```bash
   # Crear base de datos
   mysql -u root -p < database/init.sql

   # Configurar variables de entorno
   cp .env.example .env
   # Editar .env con tus configuraciones
   ```

4. **Configurar permisos**
   ```bash
   chmod 755 logs/
   chmod 755 test/
   ```

5. **Acceder**
   - Abrir `index.php` en navegador
   - Usuario admin por defecto: admin/admin

---

## 📁 Estructura del Proyecto

```
turnos-ya/
├── admin/           # Panel administrativo
├── api/            # Endpoints REST
├── css/            # Estilos
├── database/       # Scripts SQL
├── inc/            # Librerías y utilidades
├── img/            # Imágenes y assets
├── logs/           # Logs del sistema
├── test/           # Scripts de testing
├── vendor/         # Dependencias Composer
├── index.php       # Página principal
├── dashboard.php   # Dashboard usuario
└── README.md       # Este archivo
```

---

## Documentación

- **[Documentación Completa](DOCUMENTACION_COMPLETA.md)** - Guía técnica detallada
- **[Guía del Chatbot](CHATBOT_GUIA.md)** - Configuración IA
- **[Configuración Email](EMAIL_CONFIG.md)** - Setup SMTP
- **[Testing](test/README.md)** - Scripts de verificación

---

## Configuración Avanzada

### Variables de Entorno (.env)
```env
# Base de datos
DB_HOST=localhost
DB_NAME=turnos_ya
DB_USER=tu_usuario
DB_PASS=tu_password

# Google Gemini
GEMINI_API_KEY=tu_api_key_aqui

# Email SMTP
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=tu_email@gmail.com
SMTP_PASS=tu_app_password
```

### Configuración de Horarios
- Acceder al panel admin → "Horarios y Cupos"
- Configurar horarios por día
- Establecer cupos máximos por día

---

## Testing

Ejecutar tests básicos:
```bash
cd test/
php run_all_tests.php
```

Tests disponibles:
- ✅ Conexión a BD
- ✅ Envío de emails
- ✅ API de horarios
- ✅ Funciones de IA

---

## Contribución

**Este proyecto es educativo.** Si encuentras mejoras:

1. Fork el proyecto
2. Crea una rama (`git checkout -b feature/mejora`)
3. Commit cambios (`git commit -am 'Agrega mejora'`)
4. Push (`git push origin feature/mejora`)
5. Abre Pull Request

---

## Licencia

Este proyecto es **100% educativo** y se distribuye bajo licencia MIT. No usar en producción sin modificaciones de seguridad.

---

## **RECORDATORIO FINAL**

**Este código NO está auditado para seguridad y NO debe usarse en entornos de producción reales.**

**Características de seguridad pendientes:**
- Validación de entrada insuficiente
- Posibles SQL injection (aunque usa PDO)
- Sesiones sin configuración segura
- Contraseñas en texto plano
- Sin rate limiting robusto
- Sin HTTPS forzado
- Sin sanitización completa de datos

**Para producción real, implemente:**
- Auditoría de seguridad profesional
- Hardening del servidor
- Encriptación de datos sensibles
- Validaciones adicionales
- Logging de seguridad
- Backup automático

---

*Desarrollado con fines educativos - Use responsablemente*</content>
<parameter name="filePath">c:\apache\htdocs\Turnos-Ya-Single\README.md