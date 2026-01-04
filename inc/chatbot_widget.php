<!-- Widget de Chatbot Flotante -->
<style>
    .chat-widget-container {
        position: fixed;
        bottom: 20px;
        right: 20px;
        z-index: 9999;
    }
    
    .chat-widget-button {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        transition: all 0.3s;
        position: relative;
    }
    
    .chat-widget-button:hover {
        transform: scale(1.1);
        box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
    }
    
    .chat-widget-button .pulse {
        position: absolute;
        width: 100%;
        height: 100%;
        border-radius: 50%;
        background: rgba(102, 126, 234, 0.4);
        animation: pulse-animation 2s infinite;
    }
    
    @keyframes pulse-animation {
        0% {
            transform: scale(1);
            opacity: 1;
        }
        100% {
            transform: scale(1.5);
            opacity: 0;
        }
    }
    
    .chat-widget-iframe {
        position: fixed;
        bottom: 90px;
        right: 20px;
        width: 400px;
        height: 600px;
        border: none;
        border-radius: 15px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        display: none;
        z-index: 9998;
        background: white;
    }
    
    .chat-widget-iframe.active {
        display: block;
        animation: slideIn 0.3s ease-out;
    }
    
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .chat-badge {
        position: absolute;
        top: -5px;
        right: -5px;
        background: #f44336;
        color: white;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        font-size: 11px;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        border: 2px solid white;
    }
    
    @media (max-width: 768px) {
        .chat-widget-iframe {
            width: calc(100vw - 40px);
            height: calc(100vh - 120px);
            right: 20px;
            bottom: 90px;
        }
    }
</style>

<div class="chat-widget-container">
    <button class="chat-widget-button" onclick="toggleChatWidget()" title="Abrir Asistente IA">
        <span class="pulse"></span>
        🤖
        <span class="chat-badge" id="chatBadge" style="display: none;">1</span>
    </button>
    <iframe 
        id="chatWidgetIframe" 
        class="chat-widget-iframe" 
        src="chatbot.php?widget=1"
        title="Chatbot IA">
    </iframe>
</div>

<script>
    function toggleChatWidget() {
        const iframe = document.getElementById('chatWidgetIframe');
        const badge = document.getElementById('chatBadge');
        
        if (iframe.classList.contains('active')) {
            iframe.classList.remove('active');
        } else {
            iframe.classList.add('active');
            badge.style.display = 'none';
            
            // Opcional: Cargar el iframe solo cuando se abre por primera vez
            if (!iframe.src) {
                iframe.src = 'chatbot.php?widget=1';
            }
        }
    }
    
    // Cerrar con tecla ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const iframe = document.getElementById('chatWidgetIframe');
            iframe.classList.remove('active');
        }
    });
    
    // Cerrar al hacer clic fuera
    document.addEventListener('click', function(e) {
        const container = document.querySelector('.chat-widget-container');
        const iframe = document.getElementById('chatWidgetIframe');
        
        if (!container.contains(e.target) && iframe.classList.contains('active')) {
            iframe.classList.remove('active');
        }
    });
    
    // Mostrar badge después de 5 segundos
    window.addEventListener('load', function() {
        setTimeout(function() {
            const iframe = document.getElementById('chatWidgetIframe');
            if (!iframe.classList.contains('active')) {
                document.getElementById('chatBadge').style.display = 'flex';
            }
        }, 5000);
    });
</script>
