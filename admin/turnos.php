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
    <div class="header-section" style="display: flex; align-items: center; gap: 18px;">

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
require_once '../inc/auth.php';
require_once '../inc/db.php';
require_once '../inc/branding.php';

if (!isLoggedIn() || ($_SESSION['usuario']['rol'] !== 'admin' && $_SESSION['usuario']['rol'] !== 'gerente')) {
    header('Location: ../index.php');
    exit;
}

$usuario = $_SESSION['usuario'];
$mensaje = '';
$error = '';

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion === 'cancelar') {
        $turno_id = $_POST['turno_id'] ?? 0;
        if ($turno_id) {
            $sql = "UPDATE turnos SET estado = 'cancelado' WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$turno_id])) {
                $mensaje = "Turno cancelado exitosamente.";
            } else {
                $error = "Error al cancelar el turno.";
            }
        }
    }
    
    if ($accion === 'completar') {
        $turno_id = $_POST['turno_id'] ?? 0;
        if ($turno_id) {
            $sql = "UPDATE turnos SET estado = 'completado' WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$turno_id])) {
                $mensaje = "Turno marcado como completado.";
            } else {
                $error = "Error al actualizar el turno.";
            }
        }
    }
    
    if ($accion === 'no_show') {
        $turno_id = $_POST['turno_id'] ?? 0;
        if ($turno_id) {
            $sql = "UPDATE turnos SET estado = 'no_show' WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            if ($stmt->execute([$turno_id])) {
                $mensaje = "Turno marcado como no-show.";
            } else {
                $error = "Error al actualizar el turno.";
            }
        }
    }
}

// Obtener filtros
$fecha_filtro = $_GET['fecha'] ?? date('Y-m-d');
$estado_filtro = $_GET['estado'] ?? 'todos';

// Construir query
$sql = "SELECT t.*, u.nombre as cliente_nombre, u.email as cliente_email, u.telefono,
               s.nombre as servicio_nombre, s.precio
        FROM turnos t
        JOIN usuarios u ON t.usuario_id = u.id
        LEFT JOIN servicios s ON t.servicio_id = s.id
        WHERE 1=1";

$params = [];

if ($fecha_filtro) {
    $sql .= " AND t.fecha = ?";
    $params[] = $fecha_filtro;
}

if ($estado_filtro !== 'todos') {
    $sql .= " AND t.estado = ?";
    $params[] = $estado_filtro;
}

$sql .= " ORDER BY t.fecha DESC, t.hora DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Estadísticas del día
$stmt = $pdo->prepare("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN estado = 'confirmado' THEN 1 ELSE 0 END) as confirmados,
        SUM(CASE WHEN estado = 'completado' THEN 1 ELSE 0 END) as completados,
        SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as cancelados,
        SUM(CASE WHEN estado = 'no_show' THEN 1 ELSE 0 END) as no_shows
    FROM turnos 
    WHERE fecha = ?
");
$stmt->execute([$fecha_filtro]);
$estadisticas = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Turnos - Turnos Ya</title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        <?php if (PORTADA_URL): ?>
        body::before {
            background-image: url('../<?= PORTADA_URL ?>');
        }
        <?php endif; ?>
        
        .container {
            max-width: 1400px;
            margin: 30px auto;
            padding: 20px;
        }
        
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .filtros {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            gap: 15px;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .estadisticas {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-card .numero {
            font-size: 36px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-card .label {
            color: #666;
            font-size: 14px;
        }
        
        .stat-card.total { border-left: 4px solid #2196F3; }
        .stat-card.confirmado { border-left: 4px solid #4CAF50; }
        .stat-card.completado { border-left: 4px solid #8BC34A; }
        .stat-card.cancelado { border-left: 4px solid #FF9800; }
        .stat-card.no-show { border-left: 4px solid #f44336; }
        
        table {
            width: 100%;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        th {
            background: #667eea;
            color: white;
            padding: 15px;
            text-align: left;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        tr:hover {
            background: #f5f5f5;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .badge-confirmado { background: #4CAF50; color: white; }
        .badge-completado { background: #8BC34A; color: white; }
        .badge-cancelado { background: #FF9800; color: white; }
        .badge-no_show { background: #f44336; color: white; }
        .badge-pendiente { background: #9E9E9E; color: white; }
        
        .acciones {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }
        
        .btn-sm {
            padding: 5px 10px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 12px;
            color: white;
        }
        
        .btn-success { background: #4CAF50; }
        .btn-warning { background: #FF9800; }
        .btn-danger { background: #f44336; }
        
        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: #667eea;
            text-decoration: none;
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
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-link">← Volver al Dashboard</a>
        
        <div class="header-section">
            <h1>📋 Gestión de Turnos</h1>
        </div>
        
        <?php if ($mensaje): ?>
            <div class="mensaje exito"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <!-- Filtros -->
        <form class="filtros" method="get">
            <label>
                <strong>Fecha:</strong>
                <input type="date" name="fecha" value="<?= htmlspecialchars($fecha_filtro) ?>" 
                       onchange="this.form.submit()">
            </label>
            
            <label>
                <strong>Estado:</strong>
                <select name="estado" onchange="this.form.submit()">
                    <option value="todos" <?= $estado_filtro === 'todos' ? 'selected' : '' ?>>Todos</option>
                    <option value="confirmado" <?= $estado_filtro === 'confirmado' ? 'selected' : '' ?>>Confirmado</option>
                    <option value="completado" <?= $estado_filtro === 'completado' ? 'selected' : '' ?>>Completado</option>
                    <option value="cancelado" <?= $estado_filtro === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    <option value="no_show" <?= $estado_filtro === 'no_show' ? 'selected' : '' ?>>No-Show</option>
                </select>
            </label>
            
            <button type="submit" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Filtrar
            </button>
        </form>
        
        <!-- Estadísticas -->
        <div class="estadisticas">
            <div class="stat-card total">
                <div class="numero"><?= $estadisticas['total'] ?></div>
                <div class="label">Total Turnos</div>
            </div>
            <div class="stat-card confirmado">
                <div class="numero"><?= $estadisticas['confirmados'] ?></div>
                <div class="label">Confirmados</div>
            </div>
            <div class="stat-card completado">
                <div class="numero"><?= $estadisticas['completados'] ?></div>
                <div class="label">Completados</div>
            </div>
            <div class="stat-card cancelado">
                <div class="numero"><?= $estadisticas['cancelados'] ?></div>
                <div class="label">Cancelados</div>
            </div>
            <div class="stat-card no-show">
                <div class="numero"><?= $estadisticas['no_shows'] ?></div>
                <div class="label">No-Shows</div>
            </div>
        </div>
        
        <!-- Tabla de turnos -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Cliente</th>
                    <th>Contacto</th>
                    <th>Servicio</th>
                    <th>Duración</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($turnos) > 0): ?>
                    <?php foreach ($turnos as $turno): ?>
                    <tr>
                        <td><?= $turno['id'] ?></td>
                        <td><?= date('d/m/Y', strtotime($turno['fecha'])) ?></td>
                        <td><?= date('H:i', strtotime($turno['hora'])) ?></td>
                        <td><?= htmlspecialchars($turno['cliente_nombre']) ?></td>
                        <td>
                            <?= htmlspecialchars($turno['cliente_email']) ?><br>
                            <small><?= htmlspecialchars($turno['telefono'] ?? '-') ?></small>
                        </td>
                        <td>
                            <?= htmlspecialchars($turno['servicio_nombre'] ?? 'Sin servicio') ?>
                            <?php if ($turno['precio']): ?>
                                <br><small>$<?= number_format($turno['precio'], 2) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><?= $turno['duracion'] ?> min</td>
                        <td>
                            <span class="badge badge-<?= $turno['estado'] ?>">
                                <?= strtoupper($turno['estado']) ?>
                            </span>
                        </td>
                        <td class="acciones">
                            <?php if ($turno['estado'] === 'confirmado'): ?>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="accion" value="completar">
                                    <input type="hidden" name="turno_id" value="<?= $turno['id'] ?>">
                                    <button type="submit" class="btn-sm btn-success">✓ Completar</button>
                                </form>
                                
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="accion" value="no_show">
                                    <input type="hidden" name="turno_id" value="<?= $turno['id'] ?>">
                                    <button type="submit" class="btn-sm btn-warning">✗ No-Show</button>
                                </form>
                                
                                <form method="post" style="display:inline;" onsubmit="return confirm('¿Cancelar este turno?')">
                                    <input type="hidden" name="accion" value="cancelar">
                                    <input type="hidden" name="turno_id" value="<?= $turno['id'] ?>">
                                    <button type="submit" class="btn-sm btn-danger">Cancelar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" style="text-align:center; padding: 40px; color: #999;">
                            No hay turnos para los filtros seleccionados
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
