# 📚 DOCUMENTACIÓN COMPLETA - TURNOS-YA v2.0

**Sistema Inteligente de Gestión de Turnos con IA**  
**Fecha:** 27 de Diciembre de 2025  
**Estado:** ✅ OPERATIVO

---

## 📋 ÍNDICE

1. [Información del Proyecto](#información-del-proyecto)
2. [Arquitectura del Sistema](#arquitectura-del-sistema)
3. [Estructura de la Base de Datos](#estructura-de-la-base-de-datos)
4. [Funcionalidades](#funcionalidades)
5. [Instalación y Configuración](#instalación-y-configuración)
6. [API y Endpoints](#api-y-endpoints)
7. [Integración con IA (Google Gemini)](#integración-con-ia-google-gemini)
8. [Guía de Uso](#guía-de-uso)
9. [Roles y Permisos](#roles-y-permisos)
10. [Mantenimiento](#mantenimiento)

---

## 🎯 INFORMACIÓN DEL PROYECTO

### Descripción
**Turnos-Ya** es un sistema web completo para la gestión inteligente de turnos, diseñado para negocios que requieren agendamiento de citas. Integra Inteligencia Artificial mediante Google Gemini para ofrecer:
- Chatbot conversacional
- Recomendaciones personalizadas
- Análisis predictivo de ocupación
- Detección de no-shows

### Tecnologías
- **Backend:** PHP 7.4+
- **Base de Datos:** MySQL 8.0+
- **Frontend:** HTML5, CSS3, JavaScript
- **IA:** Google Gemini Pro API
- **Servidor:** Apache/Nginx

### Características Principales
✅ Sistema de autenticación multi-rol  
✅ Gestión completa de turnos  
✅ Calendario interactivo  
✅ Chatbot con IA conversacional  
✅ Recomendaciones inteligentes de horarios  
✅ Análisis predictivo de ocupación  
✅ Dashboard administrativo  
✅ Sistema de notificaciones  
✅ Responsive design

---

## 🏗️ ARQUITECTURA DEL SISTEMA

### Estructura de Carpetas

```
Turnos-Ya/
├── index.php                    # Página principal/login
├── dashboard.php                # Panel principal post-login
├── reservar.php                 # Sistema de reservas con calendario
├── register.php                 # Registro de usuarios
├── logout.php                   # Cierre de sesión
├── chatbot.php                  # Interfaz del chatbot IA
├── Dump20251227.sql            # Backup de base de datos
├── README_IA.md                # Documentación de IA
├── RESUMEN_COMPLETO.md         # Resumen de funcionalidades
│
├── inc/                        # Includes del sistema
│   ├── db.php                  # Conexión a base de datos
│   ├── auth.php                # Sistema de autenticación
│   ├── header.php              # Header común
│   ├── footer.php              # Footer común
│   ├── gemini_service.php      # Servicio de IA (Gemini)
│   ├── ia_recomendaciones.php  # Motor de recomendaciones
│   └── ia_predictor.php        # Análisis predictivo
│
├── api/                        # Endpoints REST
│   ├── chatbot.php             # API del chatbot
│   ├── recomendaciones.php     # API de recomendaciones
│   └── no-shows.php            # API de gestión de ausencias
│
├── admin/                      # Panel administrativo
│   ├── analytics.php           # Dashboard con análisis
│   ├── configuracion.php       # Configuración del sistema
│   ├── horarios.php            # Gestión de horarios
│   ├── plantilla.php           # Plantilla base
│   ├── css/
│   │   └── styles.css          # Estilos admin
│   └── inc/
│       ├── header.php          # Header admin
│       ├── footer.php          # Footer admin
│       └── sidebar.php         # Menú lateral
│
├── css/                        # Estilos globales
│   └── styles.css
│
└── database/                   # Scripts de base de datos
    ├── setup_ia.sql            # Script de instalación
    └── seeders/
        └── DatabaseSeeder.php  # Datos de prueba (Laravel)
```

---

## 💾 ESTRUCTURA DE LA BASE DE DATOS

### Tablas Principales

#### 1. **usuarios**
Gestiona todos los usuarios del sistema.

```sql
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(30),
    rol ENUM('admin', 'gerente', 'cliente') NOT NULL DEFAULT 'cliente',
    avatar VARCHAR(255),
    activo BOOLEAN DEFAULT 1,
    visible BOOLEAN DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Campos importantes:**
- `rol`: Define permisos (admin, gerente, cliente)
- `activo`: Usuario habilitado/deshabilitado
- `visible`: Para gerentes/colaboradores (si atienden público)

#### 2. **servicios**
Catálogo de servicios ofrecidos.

```sql
CREATE TABLE servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10, 2) DEFAULT 0.00,
    duracion INT DEFAULT 30,
    activo BOOLEAN DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

#### 3. **turnos**
Registro de todas las reservas.

```sql
CREATE TABLE turnos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    servicio_id INT,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    duracion INT DEFAULT 30,
    estado ENUM('pendiente', 'confirmado', 'cancelado', 'completado', 'no_show') 
           DEFAULT 'confirmado',
    notas TEXT,
    recordatorio_enviado BOOLEAN DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (servicio_id) REFERENCES servicios(id),
    INDEX idx_fecha_hora (fecha, hora),
    INDEX idx_usuario (usuario_id),
    INDEX idx_estado (estado)
);
```

**Estados de turno:**
- `pendiente`: Reserva iniciada pero no confirmada
- `confirmado`: Turno confirmado
- `cancelado`: Cancelado por usuario o admin
- `completado`: Turno finalizado exitosamente
- `no_show`: Cliente no asistió

#### 4. **configuracion**
Configuraciones del sistema y API keys.

```sql
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    descripcion TEXT,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Claves importantes:**
- `gemini_api_key`: API Key de Google Gemini
- `cupos_simultaneos`: Número de turnos simultáneos
- `duracion_turno_default`: Duración estándar (minutos)
- `dias_laborables`: Días de atención
- `horario_inicio`, `horario_fin`: Horarios de atención
- `turnos_partidos`: Si se trabaja en dos bloques horarios

#### 5. **ia_recomendaciones_log**
Log de recomendaciones generadas.

```sql
CREATE TABLE ia_recomendaciones_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha_recomendacion DATE,
    hora_recomendacion TIME,
    score DECIMAL(5,2),
    factores JSON,
    seleccionada BOOLEAN DEFAULT 0,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
);
```

#### 6. **ia_eventos_log**
Registro de eventos de IA (chatbot, análisis, etc).

```sql
CREATE TABLE ia_eventos_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo ENUM('chatbot', 'recomendacion', 'prediccion', 'no_show') NOT NULL,
    usuario_id INT,
    datos JSON,
    resultado TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    INDEX idx_tipo_fecha (tipo, creado_en)
);
```

---

## ⚙️ FUNCIONALIDADES

### 1. 🤖 Chatbot Conversacional con IA

**Archivo principal:** `chatbot.php`  
**API:** `api/chatbot.php`  
**Servicio:** `inc/gemini_service.php`

**Características:**
- Conversación natural en español
- Comprensión de contexto mediante Google Gemini
- Reservas mediante chat
- Consultas de disponibilidad en tiempo real
- Detección automática de confirmaciones
- Sugerencias inteligentes de horarios
- Historial de conversación

**System Prompt personalizado:**
El chatbot está configurado con información específica del negocio:
- Nombre del negocio
- Servicios disponibles con precios y duración
- Horarios de atención
- Políticas de cancelación
- Días laborables

**Ejemplo de uso:**
```javascript
// Enviar mensaje al chatbot
fetch('api/chatbot.php', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        mensaje: "Quiero reservar un turno para mañana",
        usuario_id: 123
    })
})
```

### 2. 🎯 Recomendaciones Inteligentes de Horarios

**Archivo principal:** `inc/ia_recomendaciones.php`  
**API:** `api/recomendaciones.php`  
**Interfaz:** `reservar.php`

**Algoritmo de recomendación:**

El sistema analiza múltiples factores para sugerir los mejores horarios:

1. **Historial personal (30%):** Analiza las reservas previas del usuario
2. **Días preferidos (15%):** Detecta patrones de días de la semana
3. **Disponibilidad (20%):** Considera cupos disponibles
4. **Popularidad (15%):** Horarios más solicitados en general
5. **Preferencias horarias (10%):** Mañana, tarde o noche
6. **Bonus nuevos usuarios (10%):** Incentivo para nuevos clientes

**Score de recomendación:**
- 80-100%: Altamente recomendado 🟢
- 60-79%: Recomendado 🟡
- 40-59%: Disponible 🟠
- 0-39%: Menos recomendado 🔴

**Ejemplo de respuesta:**
```json
{
  "fecha": "2025-12-28",
  "hora": "10:00:00",
  "score": 85.5,
  "explicacion": "Este horario coincide con tus preferencias habituales",
  "factores": {
    "historial": 30,
    "disponibilidad": 20,
    "popularidad": 12,
    "dia_preferido": 15,
    "hora_preferida": 8.5
  }
}
```

### 3. 📊 Análisis Predictivo de Ocupación

**Archivo principal:** `inc/ia_predictor.php`  
**Panel:** `admin/analytics.php`  
**API:** `api/no-shows.php`

**Métricas disponibles:**

1. **Predicción de ocupación por fecha**
   - Análisis de tendencias (4 semanas)
   - Considera día de la semana
   - Factores estacionales

2. **Análisis por día de semana**
   - Promedio de turnos por día
   - Crecimiento porcentual
   - Comparación semanal

3. **Análisis por franja horaria**
   - Mañana (6:00-12:00)
   - Tarde (12:00-18:00)
   - Noche (18:00-24:00)

4. **Detección de no-shows**
   - Identificación de usuarios con ausencias
   - Tasa de no-show por usuario
   - Patrones de comportamiento

**Dashboard visual:**
- 📈 Gráficos de tendencias
- 📊 Tablas de datos
- 🎯 Métricas clave (KPIs)
- 🔔 Alertas de riesgo

### 4. 🎯 Sistema de Cupos Dinámicos

**Archivo principal:** `admin/horarios.php`  
**API:** `api/horarios_disponibles.php`  
**Interfaz:** `reservar.php`  

**Características principales:**
- **Cupos variables por día** - Configuración individual de capacidad por día de la semana
- **Panel administrativo web** - Interfaz intuitiva para gestión de capacidad
- **Calendario visual inteligente** - Indicadores visuales para días cerrados
- **Campo de hora automático** - Autocompletado al seleccionar horario
- **Validación en tiempo real** - Prevención automática de sobre-reservas

**Configuración por defecto:**
- **Lunes-Viernes:** 3 cupos por horario (demanda laboral alta)
- **Sábado:** 2 cupos por horario (demanda reducida)
- **Domingo:** 0 cupos (cerrado completamente)

**Interfaz de administración:**
```php
// Panel admin/horarios.php
- Campo "Cupos máx" para cada día
- Validación 1-50 cupos
- Guardado inmediato en BD
- Actualización automática del sistema
```

**API Response con cupos:**
```json
{
  "horarios": [
    {
      "hora": "09:00:00",
      "hora_formateada": "09:00",
      "disponible": true,
      "cupos_disponibles": 2
    }
  ],
  "mensaje": "Horarios disponibles (3 cupos por turno)",
  "total_disponibles": 8
}
```

**Beneficios:**
- ✅ **Optimización de recursos** según demanda semanal
- ✅ **Prevención de sobrecarga** automática
- ✅ **Gestión flexible** desde interfaz web
- ✅ **UX mejorada** con indicadores visuales

### 5. 📅 Sistema de Reservas

**Archivo principal:** `reservar.php`

**Flujo de reserva:**
1. Usuario selecciona fecha en calendario
2. Sistema muestra horarios disponibles
3. Se presentan recomendaciones personalizadas
4. Usuario selecciona horario
5. Confirmación de reserva
6. Envío de notificación

**Validaciones:**
- Disponibilidad de cupos
- Horarios dentro del rango permitido
- Días laborables
- Prevención de duplicados

### 5. 👥 Sistema de Usuarios Multi-Rol

**Roles definidos:**

#### **Admin/Dueño**
- Configuración completa del sistema
- Gestión de usuarios (crear, editar, eliminar)
- Asignación de roles
- Configuración de horarios y servicios
- Acceso a analytics
- Cancelación masiva de turnos
- Personalización del negocio (logo, nombre, etc.)

#### **Gerente/Colaborador**
- Visualización de turnos asignados
- Gestión de sus propios horarios
- Consulta de clientes
- Reportes básicos
- Pueden ser visibles/invisibles según atiendan público

#### **Cliente/Usuario**
- Registro y login
- Reserva de turnos
- Cancelación de sus propios turnos
- Visualización de historial
- Acceso al chatbot
- Notificaciones de recordatorio

---

## 🚀 INSTALACIÓN Y CONFIGURACIÓN

### Requisitos Previos

- PHP 7.4 o superior
- MySQL 8.0 o superior
- Extensión PHP cURL habilitada
- Servidor web (Apache/Nginx)
- Acceso a Internet (para API de Gemini)

### Paso 1: Base de Datos

```bash
# Opción 1: Desde línea de comandos
mysql -u $DB_USER -p$DB_PASS $DB_NAME < database/setup_ia.sql

# Opción 2: Importar dump completo
mysql -u $DB_USER -p$DB_PASS $DB_NAME < Dump20251227.sql
```

**O usando phpMyAdmin:**
1. Crear base de datos `$DB_NAME`
2. Seleccionar la base de datos
3. Ir a pestaña "Importar"
4. Seleccionar archivo `Dump20251227.sql`
5. Click en "Continuar"

### Paso 2: Configurar Conexión a BD

Editar `inc/db.php`:

```php
<?php
$host = getenv('DB_HOST');
$dbname = getenv('DB_NAME');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');  // Tu contraseña

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", 
                   $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>
```

### Paso 3: Configurar API de Google Gemini

1. **Obtener API Key:**
   - Ir a [Google AI Studio](https://makersuite.google.com/app/apikey)
   - Crear nuevo proyecto
   - Generar API Key

2. **Configurar en BD:**
```sql
INSERT INTO configuracion (clave, valor, descripcion) 
VALUES ('gemini_api_key', 'TU_API_KEY_AQUI', 'API Key de Google Gemini')
ON DUPLICATE KEY UPDATE valor = 'TU_API_KEY_AQUI';
```

3. **Verificar configuración:**
```php
// En inc/gemini_service.php se carga automáticamente desde BD
$gemini = new GeminiService($pdo);
$gemini->verificarConexion(); // Devuelve true si funciona
```

### Paso 4: Configuración del Sistema

Editar valores en tabla `configuracion`:

```sql
-- Configuraciones básicas
UPDATE configuracion SET valor = '3' WHERE clave = 'cupos_simultaneos';
UPDATE configuracion SET valor = '30' WHERE clave = 'duracion_turno_default';
UPDATE configuracion SET valor = '1,2,3,4,5' WHERE clave = 'dias_laborables';
UPDATE configuracion SET valor = '09:00' WHERE clave = 'horario_inicio';
UPDATE configuracion SET valor = '18:00' WHERE clave = 'horario_fin';
UPDATE configuracion SET valor = '0' WHERE clave = 'turnos_partidos';
```

### Paso 5: Crear Usuario Admin

```sql
-- Contraseña: admin123 (cámbiala después)
INSERT INTO usuarios (nombre, email, password, rol) 
VALUES ('Administrador', 
        'admin@turnos-ya.com', 
        '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
        'admin');
```

### Paso 6: Permisos de Archivos

```bash
# En el servidor, dar permisos a carpetas necesarias
chmod 755 -R /var/www/html/Turnos-Ya
chmod 777 -R /var/www/html/Turnos-Ya/uploads  # Si usas uploads
```

### Paso 7: Verificar Instalación

Visitar: `http://localhost/Turnos-Ya/`

**Login de prueba:**
- Email: `admin@turnos-ya.com`
- Password: `admin123`

---

## 🔌 API Y ENDPOINTS

### Base URL
`http://tu-dominio.com/Turnos-Ya/api/`

### 1. Chatbot API

**Endpoint:** `POST /api/chatbot.php`

**Request:**
```json
{
  "mensaje": "Quiero reservar un turno",
  "usuario_id": 123,
  "historial": [
    {"role": "user", "content": "Hola"},
    {"role": "assistant", "content": "¡Hola! ¿En qué puedo ayudarte?"}
  ]
}
```

**Response:**
```json
{
  "success": true,
  "respuesta": "¡Claro! ¿Para qué fecha te gustaría reservar?",
  "confirmacion_detectada": false,
  "datos_reserva": null,
  "timestamp": "2025-12-27 15:30:00"
}
```

### 2. Recomendaciones API

**Endpoint:** `GET /api/recomendaciones.php`

**Parámetros:**
- `usuario_id` (requerido): ID del usuario
- `fecha_inicio` (opcional): Fecha inicial para recomendaciones
- `fecha_fin` (opcional): Fecha final
- `limite` (opcional, default: 5): Número de recomendaciones

**Response:**
```json
{
  "success": true,
  "recomendaciones": [
    {
      "fecha": "2025-12-28",
      "hora": "10:00:00",
      "score": 85.5,
      "disponible": true,
      "explicacion": "Horario altamente recomendado según tus preferencias",
      "factores": {
        "historial_score": 30,
        "disponibilidad_score": 20,
        "popularidad_score": 12,
        "dia_preferido_score": 15,
        "hora_preferida_score": 8.5
      }
    }
  ]
}
```

### 3. No-Shows API

**Endpoint:** `GET /api/no-shows.php`

**Parámetros:**
- `action`: `detectar` | `estadisticas` | `usuario`
- `usuario_id` (opcional): Para estadísticas por usuario
- `fecha_inicio`, `fecha_fin` (opcionales): Rango de fechas

**Response (detectar):**
```json
{
  "success": true,
  "usuarios_riesgo": [
    {
      "usuario_id": 45,
      "nombre": "Juan Pérez",
      "email": "juan@email.com",
      "total_turnos": 10,
      "no_shows": 4,
      "tasa_no_show": 40,
      "nivel_riesgo": "alto"
    }
  ]
}
```

---

## 🤖 INTEGRACIÓN CON IA (GOOGLE GEMINI)

### Clase GeminiService

**Ubicación:** `inc/gemini_service.php`

**Inicialización:**
```php
require_once 'inc/gemini_service.php';
$gemini = new GeminiService($pdo);
```

**Métodos principales:**

#### 1. `chat($mensaje, $historial = [], $usuario_id = null)`
Envía mensaje al chatbot y obtiene respuesta.

```php
$respuesta = $gemini->chat(
    "Quiero reservar un turno para mañana",
    $historial_chat,
    $usuario_id
);
```

#### 2. `detectarConfirmacion($texto)`
Detecta si el usuario está confirmando una acción.

```php
$es_confirmacion = $gemini->detectarConfirmacion("sí, confirmo");
// Devuelve: true
```

#### 3. `generarRecordatorio($turno_data)`
Genera mensaje personalizado de recordatorio.

```php
$recordatorio = $gemini->generarRecordatorio([
    'fecha' => '2025-12-28',
    'hora' => '10:00',
    'servicio' => 'Corte de cabello',
    'cliente' => 'Juan Pérez'
]);
```

#### 4. `verificarConexion()`
Verifica que la API Key funciona correctamente.

```php
if ($gemini->verificarConexion()) {
    echo "✅ Conexión exitosa con Gemini";
} else {
    echo "❌ Error en la conexión";
}
```

### System Prompt

El chatbot está configurado con un prompt especializado que incluye:

```php
$systemPrompt = "
Eres un asistente virtual para 'Turnos-Ya', un sistema de gestión de turnos.

INFORMACIÓN DEL NEGOCIO:
- Nombre: [Cargado desde BD]
- Servicios: [Lista dinámica desde BD]
- Horarios: [Configuración desde BD]
- Políticas: [Desde configuración]

INSTRUCCIONES:
1. Sé amable y profesional
2. Responde en español
3. Ayuda a reservar, consultar y cancelar turnos
4. Proporciona información clara sobre servicios y precios
5. Si detectas una confirmación, usa palabras clave como 'confirmar', 'sí', 'ok'
6. No inventes información que no tengas

CAPACIDADES:
- Consultar disponibilidad
- Ayudar a reservar turnos
- Responder preguntas sobre servicios
- Cancelar turnos (con confirmación)
- Proporcionar información de contacto
";
```

---

## 📖 GUÍA DE USO

### Para Administradores

#### Configurar el Sistema

1. **Acceder al panel admin:**
   - Login con cuenta de admin
   - Ir a "Configuración" en el menú

2. **Configurar horarios:**
   ```
   - Días laborables: Lun-Vie
   - Horario: 9:00 - 18:00
   - Turnos simultáneos: 3
   - Duración: 30 minutos
   ```

3. **Gestionar servicios:**
   - Agregar nuevo servicio
   - Definir precio y duración
   - Activar/desactivar según necesidad

4. **Crear gerentes/colaboradores:**
   - Nuevo usuario
   - Rol: Gerente
   - Marcar "Visible" si atiende público

#### Ver Analytics

1. **Acceder a `admin/analytics.php`**
2. **Visualizar métricas:**
   - Ocupación actual
   - Tendencias de reserva
   - Horarios más populares
   - Usuarios en riesgo de no-show

#### Cancelar Turnos Masivamente

```sql
-- Cancelar todos los turnos de un día
UPDATE turnos 
SET estado = 'cancelado' 
WHERE fecha = '2025-12-28' 
  AND estado = 'confirmado';
```

### Para Clientes

#### Reservar un Turno

1. **Login en el sistema**
2. **Ir a "Reservar Turno"**
3. **Seleccionar fecha en calendario**
4. **Ver recomendaciones** (si disponibles)
5. **Elegir horario**
6. **Confirmar reserva**
7. **Recibir confirmación**

#### Usar el Chatbot

1. **Acceder a "Chatbot"** desde el menú
2. **Escribir consulta:**
   - "¿Qué horarios hay disponibles mañana?"
   - "Quiero reservar un corte de cabello"
   - "¿Cuánto cuesta un tratamiento?"
3. **Seguir las indicaciones del bot**
4. **Confirmar cuando se solicite**

#### Cancelar un Turno

1. **Ir a "Mis Turnos"**
2. **Seleccionar turno a cancelar**
3. **Click en "Cancelar"**
4. **Confirmar cancelación**

---

## 🔐 ROLES Y PERMISOS

### Matriz de Permisos

| Acción | Admin | Gerente | Cliente |
|--------|-------|---------|---------|
| Ver propios turnos | ✅ | ✅ | ✅ |
| Reservar turno | ✅ | ✅ | ✅ |
| Cancelar propio turno | ✅ | ✅ | ✅ |
| Ver todos los turnos | ✅ | ✅ | ❌ |
| Cancelar cualquier turno | ✅ | ✅ | ❌ |
| Gestionar usuarios | ✅ | ❌ | ❌ |
| Configurar sistema | ✅ | ❌ | ❌ |
| Ver analytics | ✅ | ✅ | ❌ |
| Gestionar servicios | ✅ | ⚠️ | ❌ |
| Usar chatbot | ✅ | ✅ | ✅ |
| Asignar roles | ✅ | ❌ | ❌ |

⚠️ = Permisos limitados

### Implementación de Permisos

```php
// En inc/auth.php
function verificarPermiso($rol_requerido) {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: index.php');
        exit();
    }
    
    $roles_permitidos = [
        'admin' => ['admin'],
        'gerente' => ['admin', 'gerente'],
        'cliente' => ['admin', 'gerente', 'cliente']
    ];
    
    if (!in_array($_SESSION['rol'], $roles_permitidos[$rol_requerido])) {
        die('Acceso denegado');
    }
}

// Uso en páginas protegidas
require_once 'inc/auth.php';
verificarPermiso('admin'); // Solo admins
```

---

## 🔧 MANTENIMIENTO

### Backups Automáticos

**Script de backup diario:**

```bash
#!/bin/bash
# backup_turnos.sh

FECHA=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backups/turnos-ya"
DB_NAME="$DB_NAME"
DB_USER="$DB_USER"
DB_PASS="$DB_PASS"

# Crear backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/backup_$FECHA.sql

# Comprimir
gzip $BACKUP_DIR/backup_$FECHA.sql

# Eliminar backups antiguos (más de 30 días)
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +30 -delete

echo "Backup completado: backup_$FECHA.sql.gz"
```

**Configurar en crontab:**
```bash
# Ejecutar diariamente a las 2 AM
0 2 * * * /usr/local/bin/backup_turnos.sh
```

### Limpieza de Datos

**Limpiar logs antiguos:**

```sql
-- Eliminar logs de IA de más de 90 días
DELETE FROM ia_eventos_log 
WHERE creado_en < DATE_SUB(NOW(), INTERVAL 90 DAY);

-- Eliminar logs de recomendaciones de más de 60 días
DELETE FROM ia_recomendaciones_log 
WHERE creado_en < DATE_SUB(NOW(), INTERVAL 60 DAY);
```

**Script de limpieza automática (PHP):**

```php
<?php
// maintenance/cleanup.php
require_once '../inc/db.php';

// Limpiar turnos cancelados antiguos (más de 1 año)
$stmt = $pdo->prepare("
    DELETE FROM turnos 
    WHERE estado = 'cancelado' 
      AND fecha < DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
");
$stmt->execute();

// Limpiar logs antiguos
$stmt = $pdo->prepare("
    DELETE FROM ia_eventos_log 
    WHERE creado_en < DATE_SUB(NOW(), INTERVAL 90 DAY)
");
$stmt->execute();

echo "Limpieza completada\n";
?>
```

### Monitoreo de API Gemini

**Verificar uso de API:**

```php
<?php
// maintenance/check_api_usage.php
require_once '../inc/gemini_service.php';
require_once '../inc/db.php';

$gemini = new GeminiService($pdo);

// Verificar conexión
if ($gemini->verificarConexion()) {
    echo "✅ API Gemini funcionando correctamente\n";
    
    // Contar uso en las últimas 24 horas
    $stmt = $pdo->query("
        SELECT COUNT(*) as total 
        FROM ia_eventos_log 
        WHERE tipo = 'chatbot' 
          AND creado_en > DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $uso = $stmt->fetch();
    
    echo "📊 Llamadas a la API (24h): " . $uso['total'] . "\n";
} else {
    echo "❌ Error en API Gemini - Verificar API Key\n";
}
?>
```

### Optimización de Base de Datos

```sql
-- Optimizar tablas
OPTIMIZE TABLE usuarios, turnos, servicios, configuracion;

-- Analizar tablas para mejorar consultas
ANALYZE TABLE turnos, ia_eventos_log;

-- Verificar integridad
CHECK TABLE turnos, usuarios;

-- Reparar si es necesario
REPAIR TABLE turnos;
```

### Actualización de Sistema

**Procedimiento recomendado:**

1. **Hacer backup completo**
2. **Modo mantenimiento** (crear archivo `maintenance.php`)
3. **Actualizar archivos PHP**
4. **Ejecutar scripts de migración** si los hay
5. **Verificar funcionalidad**
6. **Desactivar modo mantenimiento**

---

## 📊 MÉTRICAS Y KPIs

### Métricas de Negocio

```sql
-- Turnos del mes actual
SELECT COUNT(*) as total_turnos
FROM turnos 
WHERE MONTH(fecha) = MONTH(CURDATE()) 
  AND YEAR(fecha) = YEAR(CURDATE())
  AND estado IN ('confirmado', 'completado');

-- Tasa de ocupación semanal
SELECT 
    WEEK(fecha) as semana,
    COUNT(*) as turnos,
    (COUNT(*) / (SELECT valor FROM configuracion WHERE clave = 'cupos_simultaneos') * 100) as ocupacion
FROM turnos 
WHERE estado = 'confirmado'
GROUP BY WEEK(fecha);

-- Servicios más solicitados
SELECT 
    s.nombre,
    COUNT(t.id) as total_reservas
FROM turnos t
JOIN servicios s ON t.servicio_id = s.id
WHERE t.estado IN ('confirmado', 'completado')
GROUP BY s.id
ORDER BY total_reservas DESC
LIMIT 10;

-- Tasa de no-show
SELECT 
    COUNT(CASE WHEN estado = 'no_show' THEN 1 END) / COUNT(*) * 100 as tasa_no_show
FROM turnos 
WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 30 DAY);
```

### Métricas de IA

```sql
-- Uso del chatbot (último mes)
SELECT 
    DATE(creado_en) as fecha,
    COUNT(*) as interacciones
FROM ia_eventos_log
WHERE tipo = 'chatbot'
  AND creado_en >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY DATE(creado_en);

-- Efectividad de recomendaciones
SELECT 
    AVG(score) as score_promedio,
    COUNT(CASE WHEN seleccionada = 1 THEN 1 END) as aceptadas,
    COUNT(*) as total,
    (COUNT(CASE WHEN seleccionada = 1 THEN 1 END) / COUNT(*) * 100) as tasa_aceptacion
FROM ia_recomendaciones_log
WHERE creado_en >= DATE_SUB(NOW(), INTERVAL 30 DAY);
```

---

## 🆘 SOLUCIÓN DE PROBLEMAS

### Error: "No se puede conectar a la base de datos"

**Causa:** Credenciales incorrectas o MySQL no está ejecutándose

**Solución:**
```bash
# Verificar que MySQL esté corriendo
sudo systemctl status mysql

# Iniciar MySQL si está detenido
sudo systemctl start mysql

# Verificar credenciales en inc/db.php
```

### Error: "API Key de Gemini inválida"

**Causa:** API Key incorrecta o sin permisos

**Solución:**
1. Verificar API Key en Google AI Studio
2. Actualizar en base de datos:
```sql
UPDATE configuracion 
SET valor = 'NUEVA_API_KEY' 
WHERE clave = 'gemini_api_key';
```
3. Verificar límites de uso de la API

### Chatbot no responde

**Causa:** Problemas con cURL o API Gemini

**Solución:**
```php
// Verificar extensión cURL
<?php
if (function_exists('curl_version')) {
    echo "✅ cURL está habilitado";
} else {
    echo "❌ cURL no está habilitado";
}
?>

// Habilitar cURL en php.ini
extension=curl
```

### Recomendaciones no se generan

**Causa:** Sin datos históricos suficientes

**Solución:**
- El usuario necesita al menos 3 turnos previos para recomendaciones personalizadas
- Para usuarios nuevos, se muestran horarios populares generales

### Error 500 en scripts PHP

**Causa:** Error de sintaxis o configuración

**Solución:**
```php
// Activar display de errores (solo desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Revisar logs de Apache/PHP
tail -f /var/log/apache2/error.log
```

---

## 📝 NOTAS FINALES

### Seguridad

- ✅ Contraseñas hasheadas con `password_hash()`
- ✅ Prepared statements para prevenir SQL injection
- ✅ Validación de entrada en todos los formularios
- ✅ Sesiones seguras
- ✅ API Key protegida en base de datos
- ⚠️ **IMPORTANTE:** Cambiar credenciales por defecto en producción

### Mejoras Futuras

1. **Sistema de notificaciones por email/SMS**
2. **Aplicación móvil nativa**
3. **Integración con Google Calendar**
4. **Pasarela de pagos online**
5. **Sistema de valoraciones/reseñas**
6. **Multi-negocio (franquicias)**
7. **Reportes avanzados en PDF**
8. **Integración con WhatsApp Business**

### Contacto y Soporte

Para soporte técnico o consultas:
- **Documentación:** Este archivo
- **Repositorio:** [Tu repositorio]
- **Issues:** [Tu sistema de tickets]

---

## 📄 LICENCIA

[Especificar licencia del proyecto]

---

**Última actualización:** 27 de Diciembre de 2025  
**Versión:** 2.0  
**Estado:** ✅ Producción

---

## 🎯 CHECKLIST DE IMPLEMENTACIÓN

- [x] Base de datos creada e inicializada
- [x] Configuración de conexión a BD
- [x] API Key de Gemini configurada
- [x] Usuario administrador creado
- [x] Servicios básicos configurados
- [x] Horarios de atención definidos
- [x] Sistema de autenticación funcionando
- [x] Reservas de turnos operativas
- [x] Chatbot con IA funcionando
- [x] Recomendaciones inteligentes activas
- [x] Analytics y predicciones disponibles
- [x] Backups automáticos configurados
- [ ] Logo personalizado del negocio
- [ ] Dominio y hosting configurados
- [ ] Certificado SSL instalado
- [ ] Sistema de emails configurado
- [ ] Testing completo realizado
- [ ] Capacitación de usuarios finales

---

**¡Sistema Turnos-Ya v2.0 listo para usar!** 🚀
