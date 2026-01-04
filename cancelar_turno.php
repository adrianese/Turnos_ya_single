<?php
require_once 'inc/auth.php';
require_once 'inc/db.php';
require_once 'inc/notification_service.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$usuario = $_SESSION['usuario'];

// Procesar cancelación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $turno_id = $_POST['turno_id'] ?? 0;
    
    if ($turno_id) {
        try {
            // Verificar que el turno pertenece al usuario
            $sql = "SELECT id, fecha, hora, estado FROM turnos 
                    WHERE id = ? AND usuario_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$turno_id, $usuario['id']]);
            $turno = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$turno) {
                $error = "Turno no encontrado o no te pertenece.";
            } elseif ($turno['estado'] !== 'confirmado') {
                $error = "Este turno ya no puede ser cancelado.";
            } else {
                // Cancelar el turno
                $sql = "UPDATE turnos SET estado = 'cancelado' WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                
                if ($stmt->execute([$turno_id])) {
                    $mensaje = "✅ Turno cancelado exitosamente";
                    
                    // Enviar notificación de cancelación
                    $notificaciones = new NotificationService($pdo);
                    $notificaciones->enviarCancelacion($turno_id);
                    
                    // Registrar en log de IA
                    $sql = "INSERT INTO ia_eventos_log (tipo, usuario_id, datos, resultado, creado_en) 
                            VALUES ('cancelacion', ?, ?, ?, NOW())";
                    $datos = json_encode(['turno_id' => $turno_id, 'fecha' => $turno['fecha'], 'hora' => $turno['hora']]);
                    $pdo->prepare($sql)->execute([$usuario['id'], $datos, 'exitoso']);
                } else {
                    $error = "Error al cancelar el turno.";
                }
            }
        } catch (Exception $e) {
            $error = "Error: " . $e->getMessage();
        }
    } else {
        $error = "ID de turno inválido.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cancelar Turno - Turnos Ya</title>
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .result-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .success-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        .error-icon {
            font-size: 64px;
            margin-bottom: 20px;
            color: #f44336;
        }
        
        .mensaje {
            font-size: 20px;
            margin-bottom: 30px;
        }
        
        .btn-group {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn {
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-secondary {
            background: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($mensaje)): ?>
            <div class="result-card">
                <div class="success-icon">✅</div>
                <div class="mensaje"><?= $mensaje ?></div>
                <div class="btn-group">
                    <a href="mis-turnos.php" class="btn btn-primary">Ver Mis Turnos</a>
                    <a href="reservar.php" class="btn btn-secondary">Reservar Otro</a>
                </div>
            </div>
        <?php elseif (isset($error)): ?>
            <div class="result-card">
                <div class="error-icon">❌</div>
                <div class="mensaje"><?= $error ?></div>
                <div class="btn-group">
                    <a href="mis-turnos.php" class="btn btn-primary">Volver a Mis Turnos</a>
                </div>
            </div>
        <?php else: ?>
            <div class="result-card">
                <div class="error-icon">⚠️</div>
                <div class="mensaje">Solicitud inválida</div>
                <div class="btn-group">
                    <a href="dashboard.php" class="btn btn-primary">Ir al Dashboard</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
