# 🧪 Carpeta de Testing - Turnos-Ya

Esta carpeta contiene todos los scripts de prueba, verificación y debugging del sistema Turnos-Ya.

## 📁 Archivos Disponibles

### 🔧 Scripts de Configuración y Verificación
- **`setup_completo.php`** - Verificación completa del sistema y configuración inicial
- **`test_chatbot_api.php`** - Diagnóstico del API del chatbot
- **`test_notificaciones.php`** - Prueba del sistema de notificaciones por email
- **`debug_api.php`** - Debug detallado de respuestas del API

### 📊 Logs y Registros
- **`chatbot_errors.log`** - Registro de errores del chatbot

## 🚀 Cómo Usar

### 1. Verificación Completa del Sistema
```bash
php test/setup_completo.php
```
Verifica todas las tablas, archivos, configuraciones y funcionalidades.

### 2. Probar API del Chatbot
```bash
php test/test_chatbot_api.php
```
Requiere sesión activa. Verifica API Key, sesión y conectividad.

### 3. Probar Sistema de Notificaciones
```bash
php test/test_notificaciones.php
```
Prueba envío de emails de confirmación, recordatorio y cancelación.

### 4. Debug de API
```bash
# Desde navegador:
http://localhost/Turnos-Ya/test/debug_api.php

# O desde terminal:
php test/debug_api.php
```
Muestra respuesta raw del API del chatbot para debugging.

## 📋 Requisitos

- **PHP 7.4+** con extensiones PDO y cURL
- **MySQL 8.0+** con base de datos configurada
- **API Key de Gemini** configurada en tabla `configuracion`
- **Archivos del sistema** en el directorio padre (`../`)

## 🔍 Verificaciones Automáticas

Los scripts verifican automáticamente:

### ✅ Base de Datos
- Conexión PDO activa
- Todas las tablas requeridas existen
- API Key de Gemini configurada
- Datos de configuración válidos

### ✅ Archivos del Sistema
- Todos los archivos PHP críticos existen
- Permisos de lectura/escritura correctos
- Rutas de inclusión válidas

### ✅ Funcionalidades
- Sistema de notificaciones operativo
- API del chatbot responde
- Servicios de IA configurados
- Horarios y servicios activos

## 📊 Resultados de Verificación

### Configuración Completa (setup_completo.php)
```
✅ Conexión a base de datos: OK
✅ Tabla horarios inicializada correctamente
✅ Tabla notificaciones inicializada correctamente
✅ Todas las tablas existen
✅ API Key de Gemini configurada
✅ Favicon existe
✅ Todos los archivos críticos existen
✅ Horarios configurados: X días
✅ Servicios activos: X
✅ Sistema de notificaciones operativo
```

### API del Chatbot (test_chatbot_api.php)
```
✓ Sesión activa
✓ API Key configurada
✓ Respuesta de la API: [JSON válido]
```

### Notificaciones (test_notificaciones.php)
```
✅ Confirmación enviada
✅ Recordatorio enviado
✅ Cancelación enviada
✅ Registros en base de datos correctos
```

## 🛠️ Troubleshooting

### Error: "No hay sesión activa"
**Solución:** Inicia sesión primero en la aplicación principal
```bash
# Ve a: http://localhost/Turnos-Ya/
# Inicia sesión con: juan@cliente.com / admin123
```

### Error: "API Key NO encontrada"
**Solución:** Configura la API Key en la tabla configuracion
```sql
UPDATE configuracion SET valor = 'TU_API_KEY' WHERE clave = 'gemini_api_key';
```

### Error: "Tabla X NO existe"
**Solución:** Ejecuta el setup completo
```bash
php test/setup_completo.php
```

### Error: "Archivo X NO existe"
**Solución:** Verifica que todos los archivos del sistema estén en sus ubicaciones correctas

## 📈 Monitoreo Continuo

### Logs Automáticos
- Errores del chatbot se registran en `chatbot_errors.log`
- Eventos de IA se guardan en tabla `ia_eventos`
- Notificaciones enviadas se registran en tabla `notificaciones`

### Métricas Disponibles
- Número de conversaciones por usuario
- Tasa de éxito de reservas por chat
- Tiempo de respuesta promedio
- Errores por día

## 🔄 Actualizaciones

Cuando actualices el sistema:

1. **Ejecuta verificación completa:**
   ```bash
   php test/setup_completo.php
   ```

2. **Prueba funcionalidades críticas:**
   ```bash
   php test/test_chatbot_api.php
   php test/test_notificaciones.php
   ```

3. **Revisa logs de errores:**
   ```bash
   tail -f test/chatbot_errors.log
   ```

## 📞 Soporte

Si encuentras errores:
1. Revisa los logs en esta carpeta
2. Ejecuta los scripts de diagnóstico
3. Verifica la configuración de la base de datos
4. Confirma que todos los archivos del sistema existen

---

**🎯 Esta carpeta mantiene el sistema Turnos-Ya funcionando correctamente.**