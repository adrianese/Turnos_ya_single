<?php
require_once 'inc/auth.php';
require_once 'inc/db.php';
require_once 'inc/branding.php';

if (!isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$usuario = $_SESSION['usuario'];

// Obtener datos del negocio para admin/gerente
$negocio = [];
// Branding ya cargado por inc/branding.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Turnos Ya</title>
    <link rel="icon" type="image/x-icon" href="favicon.ico">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        body {
            margin: 0;
            padding: 0;
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
        
        .business-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .business-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .business-logo {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid white;
        }
        
        .business-details h2 {
            margin: 0;
            font-size: 20px;
        }
        
        .business-details p {
            margin: 5px 0 0 0;
            font-size: 13px;
            opacity: 0.9;
        }
        
        .header-actions {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        
        .user-badge {
            background: rgba(255,255,255,0.2);
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .logout-header-btn {
            padding: 8px 20px;
            background: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .logout-header-btn:hover {
            background: #d32f2f;
            transform: scale(1.05);
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }
        
        .welcome {
            background: white;
            color: #333;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .welcome h1 {
            margin: 0 0 10px 0;
            color: #667eea;
        }
        
        .menu-grid {
            display: flex;
            flex-direction: column;
            gap: 20px;
            margin-top: 20px;
        }
        
        @media (min-width: 481px) {
            .menu-grid {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
        
        .menu-card {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .menu-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .menu-card .icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .menu-card h3 {
            margin-bottom: 10px;
            color: #667eea;
        }
        
        .menu-card p {
            color: #666;
            font-size: 14px;
        }
        
        .logout-btn {
            display: inline-block;
            padding: 10px 20px;
            background: #f44336;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        
        .ia-badge {
            display: inline-block;
            background: #4CAF50;
            color: white;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 5px;
        }
    </style>
</head>
<body>
    <?php if ($usuario['rol'] === 'admin' || $usuario['rol'] === 'gerente'): ?>
    <div class="business-header">
        <div class="business-info">
            <img src="<?= LOGO_URL ?>" alt="Logo" class="business-logo">
            <div class="business-details">
                <h2><?= htmlspecialchars(NOMBRE_NEGOCIO) ?></h2>
                <p><?= htmlspecialchars(ESLOGAN_NEGOCIO) ?></p>
            </div>
        </div>
        <div class="header-actions">
            <div class="user-badge">
                <?= htmlspecialchars($usuario['nombre']) ?> | <?= strtoupper($usuario['rol']) ?>
            </div>
            <a href="logout.php" class="logout-header-btn">🚪 Cerrar Sesión</a>
        </div>
    </div>
    <?php endif; ?>
    
    <div class="dashboard-container">
        <div class="welcome">
            <h1>👋 Hola, <?= htmlspecialchars($usuario['nombre']) ?>!</h1>
            <?php if ($usuario['rol'] === 'cliente'): ?>
                <p>Bienvenido al sistema de gestión de turnos con IA</p>
            <?php else: ?>
                <p>Panel de administración</p>
            <?php endif; ?>
            
            <!-- Credenciales de prueba -->
            <div style="background: #f0f8ff; border-left: 4px solid #667eea; padding: 12px; margin-top: 15px; border-radius: 5px; font-size: 13px;">
                <strong>Admin:</strong> admin@demo.com / admin123<br>
                <strong>Cliente:</strong> cliente@test.com / test123
            </div>
        </div>
        
        <div class="menu-grid">
            <?php if ($usuario['rol'] === 'cliente'): ?>
                <a href="chatbot.php" class="menu-card">
                    <div class="icon">🤖</div>
                    <h3>Chatbot IA <span class="ia-badge">NUEVO</span></h3>
                    <p>Asistente inteligente para reservas conversacionales</p>
                </a>
                
                <a href="reservar.php" class="menu-card">
                    <div class="icon">🎯</div>
                    <h3>Reservar Turno <span class="ia-badge">IA</span></h3>
                    <p>Horarios recomendados inteligentes para ti</p>
                </a>
                
                <a href="mis-turnos.php" class="menu-card">
                    <div class="icon">📅</div>
                    <h3>Mis Turnos</h3>
                    <p>Ver y gestionar tus reservas</p>
                </a>
            <?php endif; ?>
            
            <?php if ($usuario['rol'] === 'gerente' || $usuario['rol'] === 'admin'): ?>
                <a href="admin/horarios.php" class="menu-card">
                    <div class="icon">🕐</div>
                    <h3>Configurar Horarios</h3>
                    <p>Gestionar disponibilidad y cupos</p>
                </a>
                
                <a href="admin/turnos.php" class="menu-card">
                    <div class="icon">📋</div>
                    <h3>Gestionar Turnos</h3>
                    <p>Ver y administrar todas las reservas</p>
                </a>
            <?php endif; ?>
            
            <?php if ($usuario['rol'] === 'admin'): ?>
                <a href="admin/configuracion.php" class="menu-card">
                    <div class="icon">⚙️</div>
                    <h3>Configuración</h3>
                    <p>Configurar branding y opciones del negocio</p>
                </a>
                
                    <a href="admin/servicios.php" class="menu-card">
                        <div class="icon">💼</div>
                        <h3>Servicios</h3>
                        <p>Gestionar servicios, precios y cupos</p>
                    </a>
                
                <a href="admin/analytics.php" class="menu-card">
                    <div class="icon">🤖</div>
                    <h3>Analytics IA <span class="ia-badge">NUEVO</span></h3>
                    <p>Predicciones y análisis inteligente</p>
                </a>
                
                <a href="admin/usuarios.php" class="menu-card">
                    <div class="icon">👥</div>
                    <h3>Usuarios</h3>
                    <p>Gestionar clientes y colaboradores</p>
                </a>
            <?php endif; ?>
        </div>
        
        <?php if ($usuario['rol'] === 'cliente'): ?>
        <div style="text-align: center; margin-top: 30px;">
            <a href="logout.php" class="logout-btn">🚪 Cerrar Sesión</a>
        </div>
        <?php endif; ?>
    </div>
    
    <?php
    // Incluir widget de chatbot flotante para todos los usuarios
    include 'inc/chatbot_widget.php';
    ?>
</body>
</html>
