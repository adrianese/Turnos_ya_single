<?php
require_once 'inc/auth.php';
require_once 'inc/db.php';
require_once 'inc/branding.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$isWidget = isset($_GET['widget']) && $_GET['widget'] == '1';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot IA - Turnos Ya</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            <?php if ($isWidget): ?>
            background: white;
            height: 100vh;
            display: flex;
            flex-direction: column;
            <?php else: ?>
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            <?php endif; ?>
        }
        
        <?php if (!$isWidget): ?>
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            <?php if (PORTADA_URL): ?>
            background-image: url('<?= PORTADA_URL ?>');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            <?php endif; ?>
            z-index: -2;
        }
        
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102, 239, 126, 0.1) 0%, rgba(118, 255, 162, 0.05) 100%);
            backdrop-filter: blur(1px);
            z-index: -1;
        }
        
        .back-link-top {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 20px;
            background: rgba(255,255,255,0.2);
            padding: 10px 20px;
            border-radius: 25px;
            transition: all 0.3s;
        }
        
        .back-link-top:hover {
            background: rgba(255,255,255,0.3);
            transform: scale(1.05);
        }
        <?php endif; ?>
        
        .chat-container {
            <?php if ($isWidget): ?>
            width: 100%;
            height: 100vh;
            border-radius: 0;
            box-shadow: none;
            <?php else: ?>
            width: 90%;
            max-width: 800px;
            height: 600px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.3);
            <?php endif; ?>
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        
        .chat-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        
        .chat-header h1 {
            margin-bottom: 5px;
            font-size: 24px;
        }
        
        .chat-header p {
            font-size: 14px;
            opacity: 0.9;
        }
        
        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            font-size: 24px;
        }
        
        .chat-messages {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
            background: #f5f5f5;
        }
        
        .message {
            margin-bottom: 15px;
            display: flex;
            animation: fadeIn 0.3s;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .message.user {
            justify-content: flex-end;
        }
        
        .message.assistant {
            justify-content: flex-start;
        }
        
        .message-content {
            max-width: 70%;
            padding: 12px 18px;
            border-radius: 18px;
            word-wrap: break-word;
        }
        
        .message.user .message-content {
            background: #667eea;
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .message.assistant .message-content {
            background: white;
            color: #333;
            border-bottom-left-radius: 4px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .message-icon {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 10px;
            font-size: 18px;
        }
        
        .message.user .message-icon {
            background: #667eea;
            order: 2;
        }
        
        .message.assistant .message-icon {
            background: #4CAF50;
        }
        
        .chat-input-container {
            padding: 20px;
            background: white;
            border-top: 1px solid #e0e0e0;
            display: flex;
            gap: 10px;
        }
        
        .chat-input {
            flex: 1;
            padding: 12px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 25px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.3s;
        }
        
        .chat-input:focus {
            border-color: #667eea;
        }
        
        .send-button {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 50%;
            color: white;
            font-size: 20px;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .send-button:hover {
            transform: scale(1.1);
        }
        
        .send-button:active {
            transform: scale(0.95);
        }
        
        .send-button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }
        
        .typing-indicator {
            display: none;
            padding: 10px 18px;
            background: white;
            border-radius: 18px;
            width: fit-content;
        }
        
        .typing-indicator.active {
            display: block;
        }
        
        .typing-indicator span {
            display: inline-block;
            width: 8px;
            height: 8px;
            background: #999;
            border-radius: 50%;
            margin: 0 2px;
            animation: bounce 1.4s infinite;
        }
        
        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }
        
        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }
        
        @keyframes bounce {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-10px);
            }
        }
        
        .suggestions {
            display: flex;
            gap: 10px;
            padding: 10px 20px;
            overflow-x: auto;
            background: white;
        }
        
        .suggestion-btn {
            padding: 8px 16px;
            background: #f5f5f5;
            border: 1px solid #e0e0e0;
            border-radius: 20px;
            white-space: nowrap;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }
        
        .suggestion-btn:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        
        .back-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            border-radius: 25px;
            transition: background 0.3s;
        }
        
        .back-link:hover {
            background: rgba(255,255,255,0.3);
        }
    </style>
</head>
<body>
    <?php if (!$isWidget): ?>
    <a href="dashboard.php" class="back-link-top">← Volver al Dashboard</a>
    <?php endif; ?>
    
    <div class="chat-container">
        <div class="chat-header">
            <h1>🤖 Asistente Inteligente</h1>
            <p>Hazme cualquier pregunta sobre reservas de turnos</p>
            <button onclick="limpiarHistorial()" style="position: absolute; top: 15px; right: 15px; padding: 8px 15px; background: rgba(255,255,255,0.2); border: none; border-radius: 20px; color: white; cursor: pointer; font-size: 13px; transition: all 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                🔄 Nueva conversación
            </button>
        </div>
        
        <div class="suggestions">
            <button class="suggestion-btn" onclick="enviarSugerencia('Quiero reservar un turno para mañana')">
                📅 Reservar turno
            </button>
            <button class="suggestion-btn" onclick="enviarSugerencia('¿Qué servicios ofrecen?')">
                💇 Ver servicios
            </button>
            <button class="suggestion-btn" onclick="enviarSugerencia('¿Tienen disponibilidad hoy?')">
                ⏰ Disponibilidad
            </button>
            <button class="suggestion-btn" onclick="enviarSugerencia('¿Cuáles son los precios?')">
                💰 Precios
            </button>
        </div>
        
        <div class="chat-messages" id="chatMessages">
            <div class="message assistant">
                <div class="message-icon">🤖</div>
                <div class="message-content">
                    ¡Hola! Soy tu asistente virtual de Turnos-Ya. Estoy aquí para ayudarte a reservar turnos, consultar disponibilidad y responder tus preguntas. ¿En qué puedo ayudarte hoy?
                </div>
            </div>
        </div>
        
        <div class="chat-input-container">
            <input type="text" class="chat-input" id="messageInput" 
                   placeholder="Escribe tu mensaje aquí..." 
                   onkeypress="handleKeyPress(event)">
            <button class="send-button" id="sendButton" onclick="enviarMensaje()">
                ➤
            </button>
        </div>
    </div>
    
    <script>
        const chatMessages = document.getElementById('chatMessages');
        const messageInput = document.getElementById('messageInput');
        const sendButton = document.getElementById('sendButton');
        
        function handleKeyPress(event) {
            if (event.key === 'Enter') {
                enviarMensaje();
            }
        }
        
        function enviarSugerencia(texto) {
            messageInput.value = texto;
            enviarMensaje();
        }
        
        async function enviarMensaje() {
            const mensaje = messageInput.value.trim();
            
            if (!mensaje) return;
            
            // Agregar mensaje del usuario
            agregarMensaje('user', mensaje);
            messageInput.value = '';
            
            // Deshabilitar input y botón
            messageInput.disabled = true;
            sendButton.disabled = true;
            
            // Mostrar indicador de escritura
            const typingId = mostrarTyping();
            
            try {
                // Enviar a la API con ruta absoluta
                const baseUrl = window.location.origin + window.location.pathname.replace('/chatbot.php', '');
                const apiUrl = baseUrl + '/api/chatbot.php';
                
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ mensaje: mensaje })
                });
                
                // Obtener el texto de respuesta primero
                const responseText = await response.text();
                
                // Ocultar typing
                ocultarTyping(typingId);
                
                // Intentar parsear como JSON
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (e) {
                    agregarMensaje('assistant', 'Error del servidor. Por favor, intenta de nuevo.');
                    return;
                }
                
                if (data.success) {
                    agregarMensaje('assistant', data.respuesta);
                } else {
                    agregarMensaje('assistant', 'Lo siento, hubo un error: ' + (data.mensaje || 'Error desconocido'));
                }
                
            } catch (error) {
                ocultarTyping(typingId);
                agregarMensaje('assistant', 'Lo siento, no pude conectar con el servidor.');
            } finally {
                // Rehabilitar input y botón
                messageInput.disabled = false;
                sendButton.disabled = false;
                messageInput.focus();
            }
        }
        
        function agregarMensaje(tipo, contenido) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${tipo}`;
            
            const icon = tipo === 'user' ? '👤' : '🤖';
            
            messageDiv.innerHTML = `
                <div class="message-icon">${icon}</div>
                <div class="message-content">${contenido}</div>
            `;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
        
        function mostrarTyping() {
            const typingDiv = document.createElement('div');
            typingDiv.className = 'message assistant';
            typingDiv.id = 'typing-' + Date.now();
            
            typingDiv.innerHTML = `
                <div class="message-icon">🤖</div>
                <div class="typing-indicator active">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            `;
            
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            return typingDiv.id;
        }
        
        function ocultarTyping(id) {
            const typingDiv = document.getElementById(id);
            if (typingDiv) {
                typingDiv.remove();
            }
        }
        
        async function limpiarHistorial() {
            if (!confirm('¿Deseas iniciar una nueva conversación? Se perderá el contexto actual.')) {
                return;
            }
            
            try {
                const response = await fetch('api/limpiar_historial.php', {
                    method: 'POST'
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Limpiar mensajes de la pantalla
                    chatMessages.innerHTML = '';
                    
                    // Agregar mensaje de bienvenida
                    agregarMensaje('assistant', '¡Hola! Soy tu asistente virtual de Turnos-Ya. Estoy aquí para ayudarte a reservar turnos, consultar disponibilidad y responder tus preguntas. ¿En qué puedo ayudarte hoy?');
                    
                    messageInput.focus();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al limpiar el historial',
                        confirmButtonText: 'Cerrar'
                    });
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al limpiar el historial',
                    confirmButtonText: 'Cerrar'
                });
            }
        }
        
        // Focus automático en el input
        messageInput.focus();
    </script>
</body>
</html>
