# 🤖 Chatbot IA - Guía de Uso

## ✅ Estado del Sistema

El chatbot está **completamente configurado** y listo para usar.

### Verificación completada:
- ✓ API Key de Google Gemini configurada
- ✓ Tabla `historial_chat` creada
- ✓ Tabla `ia_eventos` creada
- ✓ Servidor Apache corriendo
- ✓ Base de datos conectada
- ✓ Widget flotante integrado

## 🌐 Acceso

### Formas de acceder al chatbot:

1. **Widget Flotante** (NUEVO) 🎯
   - Botón flotante azul en la esquina inferior derecha
   - Disponible en: Dashboard, Reservar Turnos, Mis Turnos
   - Se abre automáticamente al iniciar sesión
   - Presiona ESC o clic fuera para cerrar

2. **Página Completa**
   - http://localhost/Turnos-Ya/chatbot.php
   - Acceso desde el dashboard

## 🚀 Características del Widget Flotante

- **Auto-apertura**: Se abre automáticamente después de iniciar sesión
- **Siempre disponible**: Botón flotante en todas las páginas principales
- **Badge de notificación**: Indicador visual después de 3 segundos
- **Responsive**: Se adapta a móviles y tablets
- **Fácil cierre**: ESC, clic fuera, o botón de cerrar

## 📋 Características del Chatbot

El chatbot utiliza **Google Gemini AI** y puede:

1. **Reservar turnos**: Ayuda a los usuarios a encontrar horarios disponibles
2. **Consultar disponibilidad**: Muestra horarios libres para los próximos 7 días
3. **Ver servicios**: Informa sobre servicios disponibles, precios y duraciones
4. **Información de colaboradores**: Muestra quiénes están disponibles
5. **Recordar contexto**: Mantiene el historial de la conversación

## 💬 Ejemplos de Uso

### Preguntas que puedes hacer:

```
- "Hola, necesito un turno para mañana"
- "¿Qué horarios hay disponibles el lunes?"
- "Quiero un corte de cabello, ¿cuándo hay lugar?"
- "¿Cuánto cuesta el servicio de coloración?"
- "¿Quiénes son los colaboradores disponibles?"
- "Necesito un turno para el 5 de enero"
```

## 🔧 Configuración Técnica

### Archivos principales:
- `chatbot.php` - Interfaz del chat
- `api/chatbot.php` - Endpoint API
- `inc/gemini_service.php` - Servicio de IA

### API Key:
La API Key de Gemini está almacenada en la tabla `configuracion`:
```sql
clave: gemini_api_key
valor: YOUR_NEW_API_KEY_HERE (configurar en admin/configuracion.php)
```

### Para cambiar la API Key:
```sql
UPDATE configuracion 
SET valor = 'TU_NUEVA_API_KEY' 
WHERE clave = 'gemini_api_key';
```

O desde PHP:
```php
// Actualizar en: admin/configuracion.php
```

## 🔍 Solución de Problemas

### Si el chat no responde:

1. **Verificar configuración**:
```bash
php check_config.php
```

2. **Ver logs de errores**:
- PHP: `c:\apache\logs\error.log`
- Apache: `c:\apache\logs\access.log`

3. **Verificar conexión a API**:
```php
php -r "
require 'inc/gemini_service.php';
\$gemini = new GeminiService();
echo \$gemini->generate('Hola');
"
```

### Si hay errores de base de datos:
```bash
php setup_chatbot.php
```

## 📊 Monitoreo

### Ver historial de conversaciones:
```sql
SELECT * FROM historial_chat 
ORDER BY creado_en DESC 
LIMIT 10;
```

### Ver eventos de IA:
```sql
SELECT * FROM ia_eventos 
ORDER BY creado_en DESC 
LIMIT 10;
```

## 🚀 Próximos Pasos

Para mejorar el chatbot puedes:

1. Ajustar el `systemPrompt` en `inc/gemini_service.php`
2. Agregar más contexto específico del negocio
3. Implementar comandos especiales
4. Mejorar el diseño de la interfaz
5. Agregar funcionalidad de confirmación automática de turnos

## ⚙️ Configuración del Widget

### Desactivar auto-apertura al iniciar sesión:

Edita `index.php` línea 11:
```php
// De:
header('Location: dashboard.php?openchat=1');

// A:
header('Location: dashboard.php');
```

### Desactivar badge de notificación:

Edita `inc/chatbot_widget.php`, comenta las líneas del badge:
```javascript
// setTimeout(function() {
//     const iframe = document.getElementById('chatWidgetIframe');
//     if (!iframe.classList.contains('active')) {
//         document.getElementById('chatBadge').style.display = 'flex';
//     }
// }, 3000);
```

### Cambiar posición del widget:

En `inc/chatbot_widget.php`, modifica:
```css
.chat-widget-container {
    bottom: 20px;    /* Cambiar para mover verticalmente */
    right: 20px;     /* Cambiar a 'left' para esquina izquierda */
}
```

## 🎯 **Widget Flotante y Auto-Inicio**

### **Características del Widget**
- **Botón flotante azul** en esquina inferior derecha
- **Disponible en todas las páginas principales**:
  - ✓ Dashboard (`dashboard.php`)
  - ✓ Reservar Turnos (`reservar.php`)
  - ✓ Mis Turnos (`mis-turnos.php`)
- **Auto-apertura** después del login (500ms delay)
- **Badge de notificación** después de 3 segundos
- **Responsive** para móviles y tablets

### **Experiencia de Usuario**
```
1. Usuario inicia sesión ✅
2. Redirige a dashboard con ?openchat=1
3. Widget aparece flotante
4. Chatbot se abre automáticamente
5. Usuario ve: "👋 Hola! ¿En qué puedo ayudarte?"
```

### **Personalización del Widget**

#### **Desactivar auto-apertura**:
```php
// En index.php línea 11:
header('Location: dashboard.php'); // Sin ?openchat=1
```

#### **Cambiar posición**:
```css
.chat-widget-container {
    bottom: 20px;  /* vertical */
    right: 20px;   /* horizontal */
}
```

#### **Desactivar badge de notificación**:
```javascript
// En inc/chatbot_widget.php, comentar el setTimeout
```

### **Archivos Involucrados**
- `inc/chatbot_widget.php` - Widget flotante
- `chatbot.php` - Interfaz adaptativa
- `index.php` - Redirección con parámetro
- `dashboard.php`, `reservar.php`, `mis-turnos.php` - Inclusión del widget

### **Ventajas**
- 📈 **Mayor uso del chatbot** (+50% esperado)
- 💬 **Conversaciones más iniciadas**
- ⚡ **Reservas más rápidas**
- 😊 **Mejor experiencia de usuario**

## 📝 Notas Importantes

- El chatbot usa sesiones PHP para mantener el historial
- Cada conversación se registra en la base de datos
- La API de Gemini tiene límites de uso gratuito
- El contexto incluye servicios, colaboradores y disponibilidad

---

**¿Necesitas ayuda?** Revisa los logs o ejecuta `php check_config.php` para diagnóstico.
