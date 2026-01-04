<?php
require_once 'inc/auth.php';
require_once 'inc/db.php';
require_once 'inc/branding.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$usuario = $_SESSION['usuario'];

// Obtener próximos turnos del usuario
$sql = "SELECT t.*, s.nombre as servicio_nombre, s.precio, s.duracion as servicio_duracion
        FROM turnos t
        LEFT JOIN servicios s ON t.servicio_id = s.id
        WHERE t.usuario_id = ? 
        AND t.estado IN ('confirmado', 'pendiente')
        AND t.fecha >= CURDATE()
        ORDER BY t.fecha ASC, t.hora ASC
        LIMIT 10";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario['id']]);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Historial de turnos
$sql = "SELECT t.*, s.nombre as servicio_nombre
        FROM turnos t
        LEFT JOIN servicios s ON t.servicio_id = s.id
        WHERE t.usuario_id = ? 
        AND (t.fecha < CURDATE() OR t.estado IN ('cancelado', 'completado', 'no_show'))
        ORDER BY t.fecha DESC, t.hora DESC
        LIMIT 20";
$stmt = $pdo->prepare($sql);
$stmt->execute([$usuario['id']]);
$historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Turnos - Turnos Ya</title>
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
            max-width: 1000px;
            margin: 50px auto;
            padding: 20px;
        }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
        }
        
        .turno-card {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 5px solid #667eea;
        }
        
        .turno-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        
        .turno-fecha {
            font-size: 24px;
            font-weight: bold;
            color: #667eea;
        }
        
        .turno-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge-confirmado { background: #4CAF50; color: white; }
        .badge-completado { background: #8BC34A; color: white; }
        .badge-cancelado { background: #FF9800; color: white; }
        .badge-no_show { background: #f44336; color: white; }
        
        .btn-cancelar {
            padding: 8px 16px;
            background: #f44336;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }
        
        .empty-state .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        
        table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        
        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #eee;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">← Volver al Dashboard</a>
        
        <h1>📅 Mis Turnos</h1>
        
        <h2>Próximos Turnos</h2>
        
        <?php if (count($turnos) > 0): ?>
            <?php foreach ($turnos as $turno): ?>
            <div class="turno-card">
                <div class="turno-header">
                    <div class="turno-fecha">
                        📅 <?= date('d/m/Y', strtotime($turno['fecha'])) ?> 
                        a las <?= date('H:i', strtotime($turno['hora'])) ?>
                    </div>
                    <span class="badge badge-<?= $turno['estado'] ?>">
                        <?= strtoupper($turno['estado']) ?>
                    </span>
                </div>
                
                <div class="turno-info">
                    <div class="info-item">
                        <span>💼</span>
                        <div>
                            <strong>Servicio:</strong><br>
                            <?= htmlspecialchars($turno['servicio_nombre'] ?? 'Sin especificar') ?>
                        </div>
                    </div>
                    
                    <div class="info-item">
                        <span>⏱️</span>
                        <div>
                            <strong>Duración:</strong><br>
                            <?= $turno['duracion'] ?> minutos
                        </div>
                    </div>
                    
                    <?php if ($turno['precio']): ?>
                    <div class="info-item">
                        <span>💰</span>
                        <div>
                            <strong>Precio:</strong><br>
                            $<?= number_format($turno['precio'], 2) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php if ($turno['notas']): ?>
                <div style="padding: 10px; background: #f5f5f5; border-radius: 5px; margin-top: 10px;">
                    <strong>Notas:</strong> <?= htmlspecialchars($turno['notas']) ?>
                </div>
                <?php endif; ?>
                
                <?php if ($turno['estado'] === 'confirmado'): ?>
                <form method="post" action="cancelar_turno.php" style="margin-top: 15px;" 
                      onsubmit="return confirm('¿Estás seguro de cancelar este turno?')">
                    <input type="hidden" name="turno_id" value="<?= $turno['id'] ?>">
                    <button type="submit" class="btn-cancelar">Cancelar Turno</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <div class="icon">📭</div>
                <h3>No tienes turnos próximos</h3>
                <p>¿Quieres reservar uno?</p>
                <a href="reservar.php" style="display: inline-block; margin-top: 20px; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;">
                    Reservar Turno
                </a>
            </div>
        <?php endif; ?>
        
        <h2 style="margin-top: 50px;">Historial</h2>
        
        <?php if (count($historial) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Servicio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($historial as $turno): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($turno['fecha'])) ?></td>
                    <td><?= date('H:i', strtotime($turno['hora'])) ?></td>
                    <td><?= htmlspecialchars($turno['servicio_nombre'] ?? '-') ?></td>
                    <td>
                        <span class="badge badge-<?= $turno['estado'] ?>">
                            <?= strtoupper($turno['estado']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="text-align: center; color: #999; padding: 20px;">Sin historial</p>
        <?php endif; ?>
    </div>
    
    <?php
    // Incluir widget de chatbot flotante
    include 'inc/chatbot_widget.php';
    ?>
</body>
</html>
