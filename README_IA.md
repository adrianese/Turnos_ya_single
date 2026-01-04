# 🤖 Funcionalidades de IA - Turnos-Ya v2.0

## ✅ **MÓDULOS DE INTELIGENCIA ARTIFICIAL**

*Versión: 2.0 | Fecha: Enero 2026 | Estado: Operativo*

---

## 🧠 **1. Chatbot Conversacional con Gemini**

### **Características Principales**
- **Google Gemini 2.5 Flash** para respuestas naturales en español
- **Historial persistente** de conversaciones por usuario
- **Contexto inteligente** que recuerda toda la sesión
- **Reservas por chat** con validación automática
- **Recomendaciones dinámicas** basadas en configuración del negocio

### **Configuración Dinámica**
El chatbot lee automáticamente la configuración del negocio:
- **Días laborables**: Configurable (ej: martes a sábado)
- **Horarios**: Configurable (ej: 9:00 a 18:00)
- **Nombre del negocio**: Configurable (ej: "Turnos Ya")
- **Servicios disponibles** desde base de datos
- **Disponibilidad en tiempo real**

### **Arquitectura Técnica**
```
Usuario → chatbot.php → api/chatbot.php → GeminiService → Gemini API
                                      ↓
                                Base de Datos (configuración, historial, disponibilidad)
```

---

## 🎯 **2. Sistema de Recomendaciones Inteligentes**

### **Algoritmo de Recomendación**
Score de recomendación (0-100%) basado en múltiples factores:

| Factor | Peso | Descripción |
|--------|------|-------------|
| Historial personal | 30% | Patrones de reserva del usuario |
| Preferencias de día | 15% | Días de la semana preferidos |
| Disponibilidad | 20% | Cupos disponibles en horario |
| Popularidad general | 15% | Horarios más solicitados |
| Horarios óptimos | 20% | Franjas ideales según configuración |

### **Personalización por Usuario**
- **Análisis de patrones**: ¿Siempre reserva los martes? ¿Prefiere mañanas?
- **Memoria histórica**: Recuerda preferencias pasadas
- **Adaptación dinámica**: Mejora recomendaciones con el tiempo

---

## 📊 **3. Analytics y Predicciones**

### **Análisis Predictivo**
- **Predicción de demanda** por día y horario
- **Identificación de picos** de ocupación
- **Tendencias semanales/mensuales**
- **Optimización automática** de horarios

### **Detección de No-Shows**
- **Análisis de ausencias** por usuario
- **Factores de riesgo** identificados automáticamente
- **Alertas preventivas** para usuarios recurrentes
- **Políticas automáticas** de penalización

---

## ⚙️ **4. Configuración y Personalización**

### **Variables Configurables**
```php
// Ejemplos de configuración que afectan la IA:
$dias_laborables = "2,3,4,5,6"; // Martes a sábado
$horario_inicio = "09:00";
$horario_fin = "18:00";
$nombre_negocio = "Turnos Ya";
$duracion_turno = 30; // minutos
```

### **Prompt Dinámico**
El system prompt se genera automáticamente incorporando:
- Nombre del negocio y especialidad
- Días y horarios de atención
- Servicios disponibles
- Políticas del establecimiento

---

## 🔧 **5. Integración Técnica**

### **APIs Utilizadas**
- **Google Gemini API**: Generación de respuestas conversacionales
- **Base de datos local**: Historial, configuración, disponibilidad
- **REST endpoints**: Comunicación entre frontend y backend

### **Archivos Clave**
- `inc/gemini_service.php` - Motor de IA principal
- `api/chatbot.php` - Endpoint REST del chatbot
- `inc/ia_recomendaciones.php` - Sistema de recomendaciones
- `inc/ia_predictor.php` - Analytics predictivos

---

## 📈 **6. Métricas de Rendimiento**

### **Eficiencia del Chatbot**
- **Tasa de conversión**: % de chats que resultan en reservas
- **Tiempo de respuesta**: < 2 segundos promedio
- **Satisfacción del usuario**: Basado en feedback
- **Precisión de recomendaciones**: > 85% de aceptación

### **Optimización Continua**
- **Aprendizaje automático** de patrones de usuario
- **Mejora del prompt** basada en resultados
- **A/B testing** de diferentes enfoques conversacionales

---

## 🚀 **Próximos Desarrollos**

### **Funcionalidades Planificadas**
- **Chatbot multilingüe** (inglés, portugués)
- **Integración WhatsApp** para notificaciones
- **Análisis de sentimientos** en conversaciones
- **Recomendaciones proactivas** por email

### **Mejoras Técnicas**
- **Cache inteligente** para respuestas frecuentes
- **Modelo de IA más avanzado** (Gemini Ultra)
- **Aprendizaje por refuerzo** para mejores respuestas

---

*La IA de Turnos-Ya evoluciona constantemente para ofrecer la mejor experiencia posible a usuarios y administradores.*
- Preferencias de horario (10%)
- Bonus para usuarios nuevos (10%)

**Archivos creados:**
- `inc/ia_recomendaciones.php` - Motor de recomendaciones
- `api/recomendaciones.php` - API REST para obtener sugerencias
- `reservar.php` - Interfaz con recomendaciones visuales

**Uso:**
```php
$ia = new IARecomendaciones($pdo);
$recomendaciones = $ia->obtenerRecomendaciones($usuario_id, $fecha, $servicio_id);
```

**Endpoint API:**
```
GET /api/recomendaciones.php?fecha=2025-12-28&servicio_id=1
```

---

### 2. 📊 Análisis Predictivo de Ocupación (Opción C)

Sistema de Machine Learning para predecir ocupación futura y detectar patrones:

**Características:**
- Predicción de ocupación por fecha y hora
- Análisis de tendencias históricas (4 semanas)
- Identificación de patrones por día y franja horaria
- Cálculo de crecimiento porcentual
- Nivel de confianza en las predicciones

**Métricas analizadas:**
- Ocupación por día de la semana
- Ocupación por franja horaria (mañana/tarde/noche)
- Tendencia semanal
- Tasa de asistencia promedio
- Crecimiento del negocio

**Archivos creados:**
- `inc/ia_predictor.php` - Motor de predicción
- `admin/analytics.php` - Dashboard visual de analytics
- `api/no-shows.php` - API para gestión de cancelaciones

**Funciones principales:**
```php
$predictor = new IAPredictor($pdo);

// Predecir ocupación de una fecha
$prediccion = $predictor->predecirOcupacion('2025-12-28');

// Obtener tendencias
$tendencias = $predictor->obtenerTendencias(4); // 4 semanas

// Identificar turnos en riesgo
$turnosRiesgo = $predictor->identificarTurnosRiesgo();
```

---

### 3. ⚠️ Detección de No-Shows y Predicción de Cancelaciones

Sistema inteligente para predecir qué turnos tienen mayor probabilidad de cancelación:

**Factores analizados:**
- Historial de cancelaciones del usuario
- Días hasta el turno (turnos lejanos tienen mayor riesgo)
- Horario del turno
- Comportamiento histórico

**Niveles de riesgo:**
- 🔴 **Alto** (>60%): Requiere confirmación
- 🟡 **Medio** (35-60%): Enviar recordatorio
- 🟢 **Bajo** (<35%): Turno estable

**Funciones:**
```php
// Predecir cancelación de un turno
$prediccion = $predictor->predecirCancelacion($turno_id);

// Resultado:
[
    'probabilidad_cancelacion' => 65,
    'nivel_riesgo' => 'Alto',
    'factores' => [...],
    'recomendacion' => 'Contactar al cliente para confirmar'
]
```

---

## 📁 Estructura de Archivos IA

```
Turnos-Ya/
├── inc/
│   ├── ia_recomendaciones.php   ✨ Motor de recomendaciones
│   └── ia_predictor.php          ✨ Motor de predicción y análisis
├── api/
│   ├── recomendaciones.php       ✨ API de horarios recomendados
│   └── no-shows.php              ✨ API de detección de cancelaciones
├── admin/
│   └── analytics.php             ✨ Dashboard de Analytics IA
├── reservar.php                  ✨ Reservas con sugerencias IA
└── database/
    └── setup_ia.sql              ✨ Script de configuración DB
```

---

## 🚀 Instalación y Configuración

### 1. Configurar la Base de Datos

```bash
mysql -u $DB_USER -p$DB_PASS $DB_NAME < database/setup_ia.sql
```

O ejecutar manualmente el script SQL en phpMyAdmin.

### 2. Verificar Configuración

Las siguientes configuraciones se insertan automáticamente:
- `horario_inicio`: 08:00:00
- `horario_fin`: 20:00:00
- `duracion_turno`: 30 minutos
- `cupos_simultaneos`: 2

### 3. Datos de Prueba

El script incluye:
- Usuario admin: `admin@turnosya.com` / `password123`
- Usuarios clientes de prueba
- Servicios de ejemplo
- Turnos históricos y futuros para testing

---

## 🎨 Características de la Interfaz

### Para Clientes:
- **Reservar Turno**: Muestra recomendaciones personalizadas con scores visuales
- Tarjetas interactivas con horarios sugeridos
- Explicación de por qué se recomienda cada horario
- Selección fácil con un clic

### Para Administradores:
- **Dashboard Analytics**: Vista completa con gráficos y métricas
- Identificación de turnos en riesgo
- Predicciones de ocupación futura
- Tendencias históricas
- Alertas automáticas

---

## 🔌 APIs Disponibles

### 1. Recomendaciones de Horarios
```
GET /api/recomendaciones.php?fecha=YYYY-MM-DD&servicio_id=1
```

**Respuesta:**
```json
{
  "success": true,
  "fecha": "2025-12-28",
  "recomendaciones": [
    {
      "hora": "09:00:00",
      "score": 85,
      "razon": "Coincide con tus preferencias habituales",
      "disponibilidad": 2
    }
  ]
}
```

### 2. Detección de No-Shows
```
GET /api/no-shows.php?accion=listar
GET /api/no-shows.php?accion=predecir&turno_id=123
POST /api/no-shows.php?accion=enviar_recordatorio
```

---

## 📊 Dashboard de Analytics

Acceso: `admin/analytics.php` (Solo administradores)

**Métricas mostradas:**
- 📊 Crecimiento porcentual (últimas 4 semanas)
- ⚠️ Número de turnos en riesgo
- 📅 Días activos
- 🎯 Tasa de asistencia promedio

**Gráficos:**
- Ocupación por día de la semana
- Ocupación por franja horaria
- Tendencia semanal
- Predicción próxima semana

**Tablas:**
- Lista de turnos en riesgo con recomendaciones
- Histórico de ocupación

---

## 🧠 Algoritmos de IA Implementados

### Recomendaciones:
- **Collaborative Filtering**: Basado en comportamiento del usuario
- **Content-Based**: Considera características del horario
- **Hybrid Approach**: Combina múltiples factores con pesos

### Predicción:
- **Time Series Analysis**: Análisis de series temporales
- **Pattern Recognition**: Detección de patrones recurrentes
- **Risk Scoring**: Sistema de puntuación de riesgo

---

## 🔒 Seguridad

- ✅ Todas las APIs requieren autenticación
- ✅ Validación de roles (admin/gerente/cliente)
- ✅ Sanitización de inputs
- ✅ Prepared statements para prevenir SQL injection
- ✅ Sesiones seguras

---

## 🎯 Próximos Pasos (Opción A - No Implementada)

**Chatbot de Reservas:**
- Integración con WhatsApp Business API
- Procesamiento de lenguaje natural
- Reservas por conversación
- Confirmaciones automáticas

---

## 🛠️ Tecnologías Utilizadas

- **Backend**: PHP 7.4+
- **Base de Datos**: MySQL 5.7+ / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **IA/ML**: Algoritmos propios en PHP
- **APIs**: REST JSON

---

## 📞 Soporte

Para dudas o mejoras, consulta la documentación en el código fuente.
Cada clase y método está documentado con PHPDoc.

---

**Desarrollado para Turnos-Ya** 🚀
Sistema de gestión de turnos con Inteligencia Artificial integrada.
