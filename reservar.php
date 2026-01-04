<style>
.clock-minimal {
    position: absolute;
    top: 20px;
    right: 30px;
    font-family: 'Segoe UI', Arial, sans-serif;
    font-size: 1.2rem;
    color: #444;
    background: rgba(255,255,255,0.7);
    border-radius: 8px;
    padding: 6px 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
    letter-spacing: 1px;
    z-index: 1000;
}
</style>
<div class="container">
    <a href="dashboard.php" class="back-link">← Volver al Dashboard</a>

    <div style="display: flex; align-items: center; gap: 18px; margin-bottom: 10px;">

        <div class="clock-minimal" id="clockMinimal"></div>
    </div>
    <script>
    function updateClockMinimal() {
        const now = new Date();
        const options = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
        document.getElementById('clockMinimal').textContent = now.toLocaleTimeString('es-AR', options);
    }
    setInterval(updateClockMinimal, 1000);
    updateClockMinimal();
    </script>
    <!-- ...el resto del contenido de container... -->
<?php
require_once 'inc/auth.php';
require_once 'inc/db.php';
require_once 'inc/branding.php';
require_once 'inc/notification_service.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$mensaje = '';
$error = '';

// Procesar reserva
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reservar'])) {
    $fecha = trim($_POST['fecha'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $servicio_id = !empty($_POST['servicio_id']) ? (int)$_POST['servicio_id'] : null;
    
    // Validar que fecha y hora no estén vacíos
    if (empty($fecha) || empty($hora)) {
        $error = "Debe seleccionar una fecha y una hora válidas.";
    } else {
        // Verificar si es feriado
        $sql = "SELECT COUNT(*) FROM feriados WHERE fecha = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$fecha]);
        if ($stmt->fetchColumn() > 0) {
            $error = "No se pueden reservar turnos en días feriados.";
        } else {
            try {
                // Obtener duración del servicio
                $duracion = 30;
                if ($servicio_id) {
                    $stmt = $pdo->prepare("SELECT duracion FROM servicios WHERE id = ?");
                    $stmt->execute([$servicio_id]);
                    $servicio = $stmt->fetch(PDO::FETCH_ASSOC);
                    if ($servicio) {
                        $duracion = $servicio['duracion'];
                    }
                }
                
                // Verificar disponibilidad
                $sql = "SELECT COUNT(*) FROM turnos 
                        WHERE fecha = ? AND hora = ? 
                        AND estado IN ('pendiente', 'confirmado')";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$fecha, $hora]);
                $ocupados = $stmt->fetchColumn();
                
                // Obtener cupos máximos del día específico
                $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                $numDia = date('w', strtotime($fecha));
                $diaSemana = $dias[$numDia];
                
                $stmt = $pdo->prepare("SELECT cupos_maximos FROM horarios WHERE dia = ?");
                $stmt->execute([$diaSemana]);
                $cuposDia = $stmt->fetchColumn();
                
                $cuposMax = (int)($cuposDia ?: 0);
                if ($cuposMax <= 0) {
                    // Fallback al cupo global
                    $sql = "SELECT valor FROM configuracion WHERE clave = 'cupos_simultaneos'";
                    $resultCupos = $pdo->query($sql);
                    $cuposMax = $resultCupos ? (int)$resultCupos->fetchColumn() : 1;
                }
                if ($cuposMax < 1) $cuposMax = 1;
                
                if ($ocupados >= $cuposMax) {
                    $error = "Este horario ya no está disponible. Hay $ocupados de $cuposMax cupos ocupados.";
                } else {
                    // Insertar turno
                    $sql = "INSERT INTO turnos (usuario_id, fecha, hora, duracion, servicio_id, estado, creado_en) 
                            VALUES (?, ?, ?, ?, ?, 'confirmado', NOW())";
                    $stmt = $pdo->prepare($sql);
                    
                    if ($stmt->execute([$usuario['id'], $fecha, $hora, $duracion, $servicio_id])) {
                        $turno_id = $pdo->lastInsertId();
                        
                        // Enviar notificación de confirmación
                        $notificaciones = new NotificationService($pdo);
                        $notificaciones->enviarConfirmacionTurno($turno_id);
                        
                        $mensaje = "✅ ¡Turno #$turno_id reservado exitosamente!<br>
                                   📅 Fecha: $fecha<br>
                                   🕐 Hora: $hora<br>
                                   ⏱️ Duración: $duracion minutos<br>
                                   📧 Se ha enviado una confirmación a tu email.";
                    } else {
                        $error = "Error al guardar el turno en la base de datos.";
                    }
                }
            } catch (PDOException $e) {
                $error = "Error en la base de datos: " . $e->getMessage();
            } catch (Exception $e) {
                $error = "Error al reservar: " . $e->getMessage();
            }
        }
    }
}

// Obtener servicios disponibles
$servicios = $pdo->query("SELECT * FROM servicios ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

// Obtener configuración de horarios para el calendario
$horariosConfig = $pdo->query("SELECT dia, abierto FROM horarios ORDER BY FIELD(dia, 'Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado')")->fetchAll(PDO::FETCH_KEY_PAIR);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Turno - Turnos Ya</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            background: #f5f7fa;
            position: relative;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('img/portada-1767467044.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            z-index: -2;
        }
        
        body::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(245, 255, 250, 0.3);
            backdrop-filter: blur(1px);
            z-index: -1;
        }
        
        .container {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .mensaje {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .mensaje.exito {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .mensaje.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .recomendaciones-ia {
            background: #f0f7ff;
            border: 2px solid #4CAF50;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .recomendaciones-ia h3 {
            margin-top: 0;
            color: #2196F3;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .recomendaciones-ia h3::before {
            content: "🤖";
            font-size: 24px;
        }
        
        .horario-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .horario-card:hover {
            border-color: #4CAF50;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .horario-card.selected {
            border-color: #4CAF50;
            background: #e8f5e9;
        }
        
        .horario-info {
            flex: 1;
        }
        
        .horario-hora {
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        
        .horario-razon {
            color: #666;
            font-size: 14px;
            margin-top: 5px;
        }
        
        .horario-score {
            background: #4CAF50;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: bold;
        }
        
        .loading {
            text-align: center;
            padding: 20px;
            color: #666;
        }
        
        .btn-primary {
            background: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
        }
        
        .btn-primary:hover {
            background: #45a049;
        }
        
        /* Estilos para el selector de servicios */
        .servicio-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .servicio-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 10px;
        }
        
        .servicio-icon {
            font-size: 20px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .servicio-select {
            width: 100%;
            padding: 15px 20px;
            font-size: 16px;
            border: 3px solid #e0e0e0;
            border-radius: 12px;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            color: #333;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            appearance: none;
            background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23666' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3e%3cpolyline points='6,9 12,15 18,9'%3e%3c/polyline%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: right 15px center;
            background-size: 16px;
            padding-right: 50px;
        }
        
        .servicio-select:focus {
            outline: none;
            border-color: #4CAF50;
            box-shadow: 0 0 0 4px rgba(76, 175, 80, 0.2);
            transform: translateY(-2px);
        }
        
        .servicio-select:hover {
            border-color: #4CAF50;
            background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        }
        
        .servicio-select option {
            padding: 10px;
            background: white;
            color: #333;
        }
        
        /* Animaciones para confirmaciones */
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }
            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }
        
        .horarios-grid {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        
        .horarios-row {
            display: flex;
            gap: 10px;
            justify-content: flex-start;
        }
        
        .horario-slot {
            background: white;
            border: 2px solid #4CAF50;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            min-width: 120px;
            flex: 1;
        }
        
        .horario-slot:hover {
            border-color: #2196F3;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        
        .horario-slot.selected {
            border-color: #2196F3;
            background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
            transform: scale(1.05);
            box-shadow: 0 6px 20px rgba(33, 150, 243, 0.3);
            position: relative;
        }
        
        .horario-slot.selected::after {
            content: "✓";
            position: absolute;
            top: -8px;
            right: -8px;
            background: #2196F3;
            color: white;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }
        
        .horario-slot.available {
            border-color: #4CAF50;
        }
        
        .horario-slot .hora-principal {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .horario-slot .cupos-info {
            font-size: 12px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .horario-slot .disponibilidad {
            font-size: 12px;
            color: #4CAF50;
            font-weight: bold;
        }
        
        .horarios-leyenda {
            margin-top: 20px;
            padding: 15px;
            background: #fff3cd;
            border-radius: 6px;
            border-left: 4px solid #ffc107;
        }
        
        .horarios-leyenda p {
            margin: 5px 0;
            color: #856404;
        }
        
        .no-horarios {
            text-align: center;
            padding: 30px;
            color: #666;
        }
        
        .no-horarios p {
            margin: 10px 0;
            font-size: 16px;
        }
        
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
            padding: 10px 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        
        /* Estilos del calendario */
        .calendar-container {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        
        .calendar-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .calendar-title {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .month-selector,
        .year-selector {
            font-size: 16px;
            font-weight: bold;
            padding: 8px 12px;
            border: 2px solid #667eea;
            border-radius: 6px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .month-selector:hover,
        .year-selector:hover {
            background: #f5f7ff;
            border-color: #764ba2;
        }
        
        .calendar-nav {
            background: #667eea;
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            font-size: 18px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .calendar-nav:hover {
            background: #764ba2;
            transform: scale(1.1);
        }
        
        .calendar-days-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-bottom: 10px;
        }
        
        .calendar-days-header > div {
            text-align: center;
            font-weight: bold;
            color: #667eea;
            padding: 10px 0;
            font-size: 14px;
        }
        
        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }
        
        .calendar-day {
            aspect-ratio: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s;
            border: 2px solid transparent;
        }
        
        .calendar-day:hover:not(.disabled):not(.other-month):not(.closed) {
            background: #f5f7ff;
            border-color: #667eea;
            transform: scale(1.05);
        }
        
        .calendar-day.today {
            background: #fff3cd;
            border-color: #ffc107;
        }
        
        .calendar-day.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: bold;
            border-color: #667eea;
            position: relative;
        }
        
        .calendar-day.selected::after {
            content: "✓";
            position: absolute;
            top: -5px;
            right: -5px;
            background: #4CAF50;
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 12px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        
        .calendar-day.disabled {
            color: #ccc;
            cursor: not-allowed;
            background: #f9f9f9;
        }
        
        .calendar-day.closed {
            color: #ff6b6b;
            cursor: not-allowed;
            background: #ffeaea;
            position: relative;
        }
        
        .calendar-day.closed::before {
            content: "❌";
            position: absolute;
            top: -5px;
            right: -5px;
            font-size: 10px;
            color: #ff6b6b;
        }
        
        .calendar-day.other-month {
            color: #ccc;
        }
        
        .fecha-seleccionada {
            margin-top: 15px;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🎯 Reservar Turno</h1>
        <p>Bienvenido/a, <strong><?= htmlspecialchars($usuario['nombre']) ?></strong></p>
        
        <?php if ($mensaje): ?>
            <div class="mensaje exito"><?= $mensaje ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST" id="formReserva">
            
            <div class="form-group servicio-group">
                <label for="servicio_id" class="servicio-label">
                    <span class="servicio-icon">💼</span>
                    Servicio (opcional)
                </label>
                <select name="servicio_id" id="servicio_id" class="servicio-select">
                    <option value="">-- Seleccionar servicio --</option>
                    <?php foreach ($servicios as $servicio): ?>
                        <option value="<?= $servicio['id'] ?>">
                            <?= htmlspecialchars($servicio['nombre']) ?> 
                            (<?= $servicio['duracion'] ?> min - $<?= number_format($servicio['precio'], 2) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Selecciona una fecha:</label>
                <div class="calendar-container">
                    <div class="calendar-header">
                        <button type="button" id="prevMonth" class="calendar-nav">◄</button>
                        <div class="calendar-title">
                            <select id="monthSelector" class="month-selector"></select>
                            <select id="yearSelector" class="year-selector"></select>
                        </div>
                        <button type="button" id="nextMonth" class="calendar-nav">►</button>
                    </div>
                    <div class="calendar-days-header">
                        <div>Dom</div>
                        <div>Lun</div>
                        <div>Mar</div>
                        <div>Mié</div>
                        <div>Jue</div>
                        <div>Vie</div>
                        <div>Sáb</div>
                    </div>
                    <div id="calendarGrid" class="calendar-grid"></div>
                </div>
                <input type="hidden" name="fecha" id="fecha" required>
                <div id="fechaSeleccionada" class="fecha-seleccionada"></div>
            </div>
            
            <div class="recomendaciones-ia" id="recomendacionesContainer">
                <h3>Recomendaciones Inteligentes</h3>
                <p>Selecciona una fecha para ver los horarios recomendados para ti</p>
            </div>
            
            <div class="form-group">
                <label for="hora">Hora seleccionada:</label>
                <input type="time" name="hora" id="hora" readonly style="background: #f5f5f5; cursor: not-allowed;">
                <small style="color: #666; display: block; margin-top: 5px;">
                    Selecciona un horario de la cuadrícula abajo para completar este campo
                </small>
            </div>
            
            <button type="submit" name="reservar" class="btn-primary" style="margin-top: 20px; padding: 12px 24px; font-size: 16px;">
                ✅ Confirmar Reserva
            </button>
        </form>
        
        <a href="dashboard.php" class="btn-secondary">← Volver al Dashboard</a>
    </div>
    
    <script>
        // Variables globales
        let horaInput;
        
        // Función global para seleccionar horario
        function seleccionarHorario(hora, element) {
            console.log('seleccionarHorario called with hora:', hora, 'element:', element);
            
            // Asegurarse de que horaInput esté disponible
            if (!horaInput) {
                horaInput = document.getElementById('hora');
            }
            
            // Remover selección anterior de todos los tipos de elementos
            document.querySelectorAll('.horario-card, .horario-slot').forEach(card => {
                card.classList.remove('selected');
            });
            
            // Marcar como seleccionado
            if (element) {
                element.classList.add('selected');
                console.log('Elemento marcado como seleccionado:', element);
            }
            
            // Convertir formato de hora para input time (HH:MM:SS -> HH:MM)
            const horaFormateada = hora.substring(0, 5);
            
            // Establecer valor en el input
            if (horaInput) {
                horaInput.value = horaFormateada;
                console.log('Hora establecida en input:', horaFormateada);
                
                // Agregar feedback visual adicional
                horaInput.style.borderColor = '#2196F3';
                horaInput.style.boxShadow = '0 0 0 2px rgba(33, 150, 243, 0.2)';
                horaInput.style.background = '#e3f2fd';
                
                // Remover el feedback después de 2 segundos
                setTimeout(() => {
                    horaInput.style.borderColor = '';
                    horaInput.style.boxShadow = '';
                    horaInput.style.background = '#f5f5f5';
                }, 2000);
            } else {
                console.error('horaInput no encontrado');
                alert('Error: campo de hora no encontrado. Recarga la página.');
            }
            
            // Mostrar confirmación visual
            mostrarConfirmacionSeleccion(hora);
        }
        
        function mostrarConfirmacionSeleccion(hora) {
            // Convertir formato de hora para mostrar (HH:MM:SS -> HH:MM)
            const horaFormateada = hora.substring(0, 5);
            
            // Crear elemento de confirmación temporal
            const confirmacion = document.createElement('div');
            confirmacion.textContent = `🕐 Horario ${horaFormateada} seleccionado`;
            confirmacion.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: #4CAF50;
                color: white;
                padding: 12px 20px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
                z-index: 10000;
                font-weight: bold;
                animation: slideIn 0.3s ease-out;
            `;
            
            document.body.appendChild(confirmacion);
            
            // Remover después de 3 segundos
            setTimeout(() => {
                confirmacion.style.animation = 'slideOut 0.3s ease-in';
                setTimeout(() => {
                    if (confirmacion.parentNode) {
                        confirmacion.parentNode.removeChild(confirmacion.parentNode);
                    }
                }, 300);
            }, 3000);
        }
        
        // Configuración de horarios desde PHP
        const horariosConfig = <?php echo json_encode($horariosConfig); ?>;
        
        // Esperar a que el DOM esté completamente cargado
        document.addEventListener('DOMContentLoaded', function() {
            const fechaInput = document.getElementById('fecha');
            horaInput = document.getElementById('hora');
            const recomendacionesContainer = document.getElementById('recomendacionesContainer');
            const servicioInput = document.getElementById('servicio_id');
            
            if (!fechaInput || !recomendacionesContainer) {
                console.error('Elementos del DOM no encontrados');
                return;
            }
            
            // Variables del calendario
            let currentDate = new Date();
            let selectedDate = null;
            let selectedDateObj = null; // Nueva variable para almacenar el objeto Date
            const monthNames = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                               'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
            
            function initCalendar() {
                document.getElementById('prevMonth').addEventListener('click', () => {
                    currentDate.setMonth(currentDate.getMonth() - 1);
                    renderCalendar();
                    updateSelectors();
                    // Mantener la fecha seleccionada visible si existe
                    if (selectedDate) {
                        actualizarFechaSeleccionada();
                    }
                });
                
                document.getElementById('nextMonth').addEventListener('click', () => {
                    currentDate.setMonth(currentDate.getMonth() + 1);
                    renderCalendar();
                    updateSelectors();
                    // Mantener la fecha seleccionada visible si existe
                    if (selectedDate) {
                        actualizarFechaSeleccionada();
                    }
                });
                
                document.getElementById('monthSelector').addEventListener('change', (e) => {
                    currentDate.setMonth(parseInt(e.target.value));
                    renderCalendar();
                    updateSelectors();
                    // Mantener la fecha seleccionada visible si existe
                    if (selectedDate) {
                        actualizarFechaSeleccionada();
                    }
                });
                
                document.getElementById('yearSelector').addEventListener('change', (e) => {
                    currentDate.setFullYear(parseInt(e.target.value));
                    renderCalendar();
                    updateSelectors();
                    // Mantener la fecha seleccionada visible si existe
                    if (selectedDate) {
                        actualizarFechaSeleccionada();
                    }
                });
            }
            
            function populateSelectors() {
                const monthSelector = document.getElementById('monthSelector');
                const yearSelector = document.getElementById('yearSelector');
                
                // Poblar selector de meses
                monthNames.forEach((name, index) => {
                    const option = document.createElement('option');
                    option.value = index;
                    option.textContent = name;
                    monthSelector.appendChild(option);
                });
                
                // Poblar selector de años (actual ± 2 años)
                const currentYear = new Date().getFullYear();
                for (let year = currentYear - 1; year <= currentYear + 2; year++) {
                    const option = document.createElement('option');
                    option.value = year;
                    option.textContent = year;
                    yearSelector.appendChild(option);
                }
                
                updateSelectors();
            }
            
            function updateSelectors() {
                document.getElementById('monthSelector').value = currentDate.getMonth();
                document.getElementById('yearSelector').value = currentDate.getFullYear();
            }
            
            function renderCalendar() {
                const calendarGrid = document.getElementById('calendarGrid');
                calendarGrid.innerHTML = '';
                
                const year = currentDate.getFullYear();
                const month = currentDate.getMonth();
                
                console.log('Renderizando calendario para:', year, month, 'selectedDate:', selectedDate);
                
                // Primer día del mes
                const firstDay = new Date(year, month, 1);
                const lastDay = new Date(year, month + 1, 0);
                
                // Días del mes anterior para completar la primera semana
                const prevMonthDays = firstDay.getDay();
                const prevMonth = new Date(year, month, 0);
                
                // Agregar días del mes anterior
                for (let i = prevMonthDays - 1; i >= 0; i--) {
                    const day = prevMonth.getDate() - i;
                    const dayElement = createDayElement(day, true, false);
                    calendarGrid.appendChild(dayElement);
                }
                
                // Agregar días del mes actual
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                for (let day = 1; day <= lastDay.getDate(); day++) {
                    const date = new Date(year, month, day);
                    date.setHours(0, 0, 0, 0);
                    
                    const isToday = date.getTime() === today.getTime();
                    const isPast = date < today;
                    const isSelected = selectedDateObj && 
                                      date.getTime() === selectedDateObj.getTime();
                    
                    console.log('Día', day, 'isSelected:', isSelected, 'selectedDate:', selectedDate, 'selectedDateObj:', selectedDateObj, 'date.getTime():', date.getTime(), 'selectedDateObj.getTime():', selectedDateObj ? selectedDateObj.getTime() : 'null');
                    
                    const dayElement = createDayElement(day, false, isPast, isToday, isSelected, date);
                    calendarGrid.appendChild(dayElement);
                }
                
                // Completar última semana con días del siguiente mes
                const remainingDays = 42 - calendarGrid.children.length;
                for (let day = 1; day <= remainingDays; day++) {
                    const dayElement = createDayElement(day, true, false);
                    calendarGrid.appendChild(dayElement);
                }
                
                // Asegurar que la fecha seleccionada se mantenga visible
                if (selectedDate) {
                    const fechaSeleccionadaDiv = document.getElementById('fechaSeleccionada');
                    const selectedDateObj = new Date(selectedDate);
                    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    fechaSeleccionadaDiv.textContent = '📅 ' + selectedDateObj.toLocaleDateString('es-ES', options);
                }
            }
            
            function createDayElement(day, isOtherMonth, isPast, isToday = false, isSelected = false, date = null) {
                const dayDiv = document.createElement('div');
                dayDiv.className = 'calendar-day';
                dayDiv.textContent = day;
                
                if (isOtherMonth) {
                    dayDiv.classList.add('other-month');
                } else if (isPast) {
                    dayDiv.classList.add('disabled');
                } else {
                    // Verificar si el día está cerrado
                    const dayOfWeek = date.getDay(); // 0=Domingo, 1=Lunes, etc.
                    const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                    const dayName = dayNames[dayOfWeek];
                    const isClosed = !horariosConfig[dayName] || horariosConfig[dayName] == 0;
                    
                    if (isClosed) {
                        dayDiv.classList.add('closed');
                        dayDiv.title = `${dayName} cerrado - No se atiende`;
                    } else {
                        if (isToday) {
                            dayDiv.classList.add('today');
                        }
                        if (isSelected) {
                            dayDiv.classList.add('selected');
                        }
                        
                        dayDiv.addEventListener('click', () => selectDate(date, dayDiv));
                    }
                }
                
                return dayDiv;
            }
            
            function actualizarFechaSeleccionada() {
                if (selectedDateObj) {
                    const fechaSeleccionadaDiv = document.getElementById('fechaSeleccionada');
                    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    fechaSeleccionadaDiv.textContent = '📅 ' + selectedDateObj.toLocaleDateString('es-ES', options);
                }
            }
            
            function selectDate(date, element) {
                // Verificar si el día está cerrado
                const dayOfWeek = date.getDay();
                const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                const dayName = dayNames[dayOfWeek];
                const isClosed = !horariosConfig[dayName] || horariosConfig[dayName] == 0;
                
                if (isClosed) {
                    alert(`Lo sentimos, no atendemos los ${dayName.toLowerCase()}s.`);
                    return;
                }
                
                // Formatear fecha como YYYY-MM-DD
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                const dateString = `${year}-${month}-${day}`;
                
                selectedDate = dateString;
                selectedDateObj = new Date(date); // Almacenar el objeto Date original
                fechaInput.value = dateString;
                
                console.log('Fecha seleccionada:', dateString, 'Objeto Date:', selectedDateObj);
                
                // Actualizar visualización del calendario
                renderCalendar();
                
                // Mostrar fecha seleccionada
                actualizarFechaSeleccionada();
                
                // Cargar recomendaciones
                cargarRecomendaciones();
            }
            
            function formatearHora(hora) {
                const [h, m] = hora.split(':');
                return `${h}:${m}`;
            }
            
            // Agregar validación al formulario
            const formReserva = document.getElementById('formReserva');
            formReserva.addEventListener('submit', function(e) {
                const fechaSeleccionada = fechaInput.value;
                const horaSeleccionada = horaInput.value;
                
                console.log('Validación de formulario:');
                console.log('- Fecha seleccionada:', fechaSeleccionada);
                console.log('- Hora seleccionada:', horaSeleccionada);
                
                if (!fechaSeleccionada) {
                    e.preventDefault();
                    alert('Por favor selecciona una fecha en el calendario.');
                    return false;
                }
                
                if (!horaSeleccionada) {
                    e.preventDefault();
                    alert('Por favor selecciona un horario disponible de la cuadrícula.');
                    return false;
                }
                
                console.log('Validación exitosa, enviando formulario con hora:', horaSeleccionada);
                
                return true;
            });
            
            async function cargarRecomendaciones() {
                const fecha = fechaInput.value;
                const servicio_id = servicioInput.value;
                
                console.log('cargarRecomendaciones called with fecha:', fecha, 'servicio_id:', servicio_id);
                
                if (!fecha) {
                    console.log('No hay fecha seleccionada, saliendo');
                    return;
                }
                
                recomendacionesContainer.innerHTML = `
                    <h3>Recomendaciones Inteligentes</h3>
                    <div class="loading">⏳ Analizando tus preferencias...</div>
                `;
                
                try {
                    let url = `api/recomendaciones.php?fecha=${fecha}`;
                    if (servicio_id) {
                        url += `&servicio_id=${servicio_id}`;
                    }
                    
                    console.log('Cargando recomendaciones desde:', url);
                    const response = await fetch(url);
                    console.log('Respuesta del servidor:', response.status);
                    
                    if (!response.ok) {
                        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                    }
                    
                    const data = await response.json();
                    console.log('Datos recibidos:', data);
                    
                    if (data.success && data.recomendaciones.length > 0) {
                        let html = `
                            <h3>Recomendaciones Inteligentes</h3>
                            <p style="color: #666; margin-bottom: 15px;">
                                ${data.mensaje}
                            </p>
                        `;
                        
                        data.recomendaciones.forEach((rec, index) => {
                            const scoreColor = rec.score >= 80 ? '#4CAF50' : 
                                             rec.score >= 60 ? '#FF9800' : '#9E9E9E';
                            
                            html += `
                                <div class="horario-card" onclick="seleccionarHorario('${rec.hora}', this)">
                                    <div class="horario-info">
                                        <div class="horario-hora">${formatearHora(rec.hora)}</div>
                                        <div class="horario-razon">${rec.razon}</div>
                                    </div>
                                    <div class="horario-score" style="background: ${scoreColor}">
                                        ${rec.score}%
                                    </div>
                                </div>
                            `;
                        });
                        
                        recomendacionesContainer.innerHTML = html;
                    } else {
                        // Si no hay recomendaciones IA, mostrar horarios disponibles directamente
                        console.log('No hay recomendaciones IA, cargando horarios disponibles...');
                        await cargarHorariosDisponibles(fecha, servicio_id);
                    }
                } catch (error) {
                    console.error('Error cargando recomendaciones IA:', error);
                    // En caso de error, mostrar horarios disponibles
                    console.log('Error en recomendaciones IA, cargando horarios disponibles como fallback...');
                    await cargarHorariosDisponibles(fecha, servicio_id);
                }
            }
            
            async function cargarHorariosDisponibles(fecha, servicio_id) {
                try {
                    let url = `api/horarios_disponibles.php?fecha=${fecha}`;
                    if (servicio_id) {
                        url += `&servicio_id=${servicio_id}`;
                    }
                    
                    console.log('Cargando horarios disponibles desde:', url);
                    const response = await fetch(url);
                    const data = await response.json();
                    
                    if (data.success && data.horarios.length > 0) {
                        let html = `
                            <h3>🕐 Horarios Disponibles</h3>
                            <p style="color: #666; margin-bottom: 15px;">
                                ${data.mensaje} - ${data.total_disponibles} horarios libres
                            </p>
                            
                            <div class="horarios-grid">
                        `;
                        
                        // Mostrar horarios en grupos de 4 columnas
                        for (let i = 0; i < data.horarios.length; i += 4) {
                            html += '<div class="horarios-row">';
                            
                            for (let j = 0; j < 4 && (i + j) < data.horarios.length; j++) {
                                const horario = data.horarios[i + j];
                                const cuposText = horario.cupos_disponibles > 1 ? 
                                    `(${horario.cupos_disponibles} cupos)` : '';
                                
                                html += `
                                    <div class="horario-slot available" onclick="seleccionarHorario('${horario.hora}', this)">
                                        <div class="hora-principal">${horario.hora_formateada}</div>
                                        <div class="cupos-info">${cuposText}</div>
                                        <div class="disponibilidad">✅ Disponible</div>
                                    </div>
                                `;
                            }
                            
                            html += '</div>';
                        }
                        
                        html += `
                            </div>
                            
                            <div class="horarios-leyenda">
                                <p><strong>💡 Instrucciones:</strong> Haz click en cualquier horario disponible para seleccionarlo</p>
                                <p><small>El campo de hora se completará automáticamente al seleccionar un horario</small></p>
                            </div>
                        `;
                        
                        recomendacionesContainer.innerHTML = html;
                    } else {
                        recomendacionesContainer.innerHTML = `
                            <h3>🕐 Horarios Disponibles</h3>
                            <div class="no-horarios">
                                <p>😔 No hay horarios disponibles para esta fecha</p>
                                <p>Intenta seleccionar otra fecha en el calendario</p>
                            </div>
                        `;
                    }
                } catch (error) {
                    console.error('Error cargando horarios disponibles:', error);
                    recomendacionesContainer.innerHTML = `
                        <h3>🕐 Horarios Disponibles</h3>
                        <p style="color: #d32f2f;">
                            Error al cargar horarios. Por favor, intenta de nuevo.
                        </p>
                    `;
                }
            }
            
            // Inicializar todo
            initCalendar();
            populateSelectors();
            renderCalendar();
            
            // Función para encontrar el siguiente día disponible
            function encontrarSiguienteDiaDisponible(startDate) {
                let date = new Date(startDate);
                const maxDays = 30; // Máximo 30 días hacia adelante
                
                for (let i = 0; i < maxDays; i++) {
                    const dayOfWeek = date.getDay();
                    const dayNames = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
                    const dayName = dayNames[dayOfWeek];
                    const isOpen = horariosConfig[dayName] && horariosConfig[dayName] == 1;
                    
                    if (isOpen) {
                        return date;
                    }
                    
                    date.setDate(date.getDate() + 1);
                }
                
                return null; // No se encontró día disponible
            }
            
            // Seleccionar automáticamente el día siguiente disponible (mañana)
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            tomorrow.setHours(0, 0, 0, 0);
            
            const siguienteDiaDisponible = encontrarSiguienteDiaDisponible(tomorrow);
            if (siguienteDiaDisponible) {
                selectDate(siguienteDiaDisponible, null);
            }
        });
        
        function formatearHora(hora) {
            const [h, m] = hora.split(':');
            const hour = parseInt(h);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const hour12 = hour % 12 || 12;
            return `${hour12}:${m} ${ampm}`;
        }
    </script>
    
    <?php
    // Incluir widget de chatbot flotante
    include 'inc/chatbot_widget.php';
    ?>
</body>
</html>
