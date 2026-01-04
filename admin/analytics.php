<?php
require_once '../inc/auth.php';
require_once '../inc/db.php';
require_once '../inc/branding.php';
require_once '../inc/ia_predictor.php';

if (!isLoggedIn() || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

// Instanciar predictor
$predictor = new IAPredictor($pdo);

// Obtener datos para el dashboard
$reporte = $predictor->generarReporteCompleto();
$tendencias = $reporte['tendencias_historicas'];
$turnosRiesgo = $reporte['lista_riesgo'];
$prediccionesSemana = $reporte['predicciones_proxima_semana'];

$pageTitle = "Analytics & Predicciones IA";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?></title>
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        <?php if (PORTADA_URL): ?>
        body::before {
            background-image: url('../<?= PORTADA_URL ?>');
        }
        <?php endif; ?>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .header h1 {
            margin-bottom: 10px;
        }
        
        .header p {
            opacity: 0.9;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            font-size: 40px;
            margin-bottom: 15px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        
        .section h2 {
            margin-bottom: 20px;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .chart-container {
            margin: 20px 0;
        }
        
        .bar-chart {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .bar-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .bar-label {
            min-width: 120px;
            font-weight: 600;
            color: #555;
        }
        
        .bar-visual {
            flex: 1;
            height: 30px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 15px;
            position: relative;
            display: flex;
            align-items: center;
            padding-left: 15px;
            color: white;
            font-weight: bold;
        }
        
        .table-responsive {
            overflow-x: auto;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        
        thead {
            background: #f8f9fa;
        }
        
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        
        th {
            font-weight: 600;
            color: #555;
        }
        
        .badge {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .badge-danger {
            background: #ffebee;
            color: #c62828;
        }
        
        .badge-warning {
            background: #fff3e0;
            color: #ef6c00;
        }
        
        .badge-success {
            background: #e8f5e9;
            color: #2e7d32;
        }
        
        .badge-info {
            background: #e3f2fd;
            color: #1565c0;
        }
        
        .prediccion-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .prediccion-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        
        .prediccion-dia {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .prediccion-ocupacion {
            font-size: 24px;
            color: #667eea;
            font-weight: bold;
        }
        
        .tendencia-positiva {
            color: #4caf50;
        }
        
        .tendencia-negativa {
            color: #f44336;
        }
        
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .alert-warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }
        
        .alert-info {
            background: #d1ecf1;
            border-left: 4px solid #17a2b8;
            color: #0c5460;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .btn:hover {
            background: #5568d3;
        }
        
        .progress-ring {
            transform: rotate(-90deg);
        }
        
        .progress-ring-circle {
            transition: stroke-dashoffset 0.35s;
            stroke: #667eea;
            stroke-width: 8;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="container">
            <h1>🤖 <?= $pageTitle ?></h1>
            <p>Análisis inteligente y predicciones basadas en datos históricos</p>
        </div>
    </div>
    
    <div class="container">
        <!-- Estadísticas principales -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">📊</div>
                <div class="stat-value <?= $tendencias['crecimiento_porcentual'] > 0 ? 'tendencia-positiva' : 'tendencia-negativa' ?>">
                    <?= $tendencias['crecimiento_porcentual'] > 0 ? '+' : '' ?><?= $tendencias['crecimiento_porcentual'] ?>%
                </div>
                <div class="stat-label">Crecimiento (4 semanas)</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">⚠️</div>
                <div class="stat-value"><?= count($turnosRiesgo) ?></div>
                <div class="stat-label">Turnos en Riesgo</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">📅</div>
                <div class="stat-value"><?= count($tendencias['ocupacion_por_dia']) ?></div>
                <div class="stat-label">Días Activos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">🎯</div>
                <div class="stat-value">
                    <?php 
                    $promedioAsistencia = 0;
                    foreach ($tendencias['ocupacion_por_dia'] as $dia) {
                        $promedioAsistencia += $dia['tasa_asistencia'];
                    }
                    if (count($tendencias['ocupacion_por_dia']) > 0) {
                        $promedioAsistencia /= count($tendencias['ocupacion_por_dia']);
                    }
                    echo round($promedioAsistencia);
                    ?>%
                </div>
                <div class="stat-label">Tasa de Asistencia</div>
            </div>
        </div>
        
        <!-- Interpretación de tendencia -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
        Swal.fire({
            icon: 'info',
            title: 'Interpretación',
            html: '<?= addslashes($tendencias['interpretacion']) ?>',
            confirmButtonText: 'Cerrar',
            customClass: {
                popup: 'swal2-interpretacion',
            }
        });
        </script>
        
        <!-- Turnos en riesgo -->
        <?php if (count($turnosRiesgo) > 0): ?>
        <div class="section">
            <h2>⚠️ Turnos en Riesgo de Cancelación</h2>
            <p style="color: #666; margin-bottom: 15px;">
                Sistema de predicción de no-shows basado en patrones de comportamiento
            </p>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Riesgo</th>
                            <th>Probabilidad</th>
                            <th>Recomendación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($turnosRiesgo as $turno): ?>
                        <tr>
                            <td><?= htmlspecialchars($turno['nombre']) ?></td>
                            <td><?= date('d/m/Y', strtotime($turno['fecha'])) ?></td>
                            <td><?= date('H:i', strtotime($turno['hora'])) ?></td>
                            <td>
                                <span class="badge <?= 
                                    $turno['nivel_riesgo'] == 'Alto' ? 'badge-danger' : 
                                    ($turno['nivel_riesgo'] == 'Medio' ? 'badge-warning' : 'badge-success') 
                                ?>">
                                    <?= $turno['nivel_riesgo'] ?>
                                </span>
                            </td>
                            <td><?= $turno['probabilidad_cancelacion'] ?>%</td>
                            <td style="font-size: 12px;"><?= $turno['recomendacion'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Ocupación por día de la semana -->
        <div class="section">
            <h2>📊 Ocupación por Día de la Semana</h2>
            <div class="chart-container">
                <div class="bar-chart">
                    <?php 
                    $maxTurnos = 0;
                    foreach ($tendencias['ocupacion_por_dia'] as $dia) {
                        if ($dia['total_turnos'] > $maxTurnos) {
                            $maxTurnos = $dia['total_turnos'];
                        }
                    }
                    
                    foreach ($tendencias['ocupacion_por_dia'] as $dia): 
                        $porcentaje = $maxTurnos > 0 ? ($dia['total_turnos'] / $maxTurnos) * 100 : 0;
                    ?>
                    <div class="bar-item">
                        <div class="bar-label"><?= $dia['dia_semana'] ?></div>
                        <div class="bar-visual" style="width: <?= $porcentaje ?>%;">
                            <?= $dia['total_turnos'] ?> turnos
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Ocupación por franja horaria -->
        <div class="section">
            <h2>🕐 Ocupación por Franja Horaria</h2>
            <div class="chart-container">
                <div class="bar-chart">
                    <?php 
                    $maxFranja = 0;
                    foreach ($tendencias['ocupacion_por_franja'] as $franja) {
                        if ($franja['total_turnos'] > $maxFranja) {
                            $maxFranja = $franja['total_turnos'];
                        }
                    }
                    
                    foreach ($tendencias['ocupacion_por_franja'] as $franja): 
                        $porcentaje = $maxFranja > 0 ? ($franja['total_turnos'] / $maxFranja) * 100 : 0;
                    ?>
                    <div class="bar-item">
                        <div class="bar-label"><?= $franja['franja'] ?></div>
                        <div class="bar-visual" style="width: <?= $porcentaje ?>%;">
                            <?= $franja['total_turnos'] ?> turnos 
                            (<?= round($franja['tasa_cancelacion'], 1) ?>% cancelación)
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Predicciones próxima semana -->
        <div class="section">
            <h2>🔮 Predicción Próxima Semana</h2>
            <p style="color: #666; margin-bottom: 15px;">
                Ocupación esperada basada en patrones históricos y tendencias recientes
            </p>
            
            <div class="prediccion-grid">
                <?php foreach ($prediccionesSemana as $pred): ?>
                <div class="prediccion-card">
                    <div class="prediccion-dia">
                        <?= date('d/m', strtotime($pred['fecha'])) ?> - <?= $pred['dia'] ?>
                    </div>
                    <?php 
                    $totalOcupacion = 0;
                    foreach ($pred['prediccion'] as $horario) {
                        $totalOcupacion += $horario['ocupacion_esperada'];
                    }
                    ?>
                    <div class="prediccion-ocupacion"><?= $totalOcupacion ?> turnos</div>
                    <div style="font-size: 12px; color: #666; margin-top: 5px;">
                        <?= count($pred['prediccion']) ?> horarios disponibles
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Tendencia semanal -->
        <div class="section">
            <h2>📈 Tendencia de las Últimas Semanas</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Semana</th>
                            <th>Año</th>
                            <th>Total Turnos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tendencias['tendencia_semanal'] as $semana): ?>
                        <tr>
                            <td>Semana <?= $semana['semana'] ?></td>
                            <td><?= $semana['año'] ?></td>
                            <td><strong><?= $semana['total_turnos'] ?></strong></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Botones de acción -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="../dashboard.php" class="btn">← Volver al Dashboard Admin</a>
        </div>
    </div>
    
    <script>
        console.log('Dashboard de Analytics cargado');
        console.log('Reporte generado:', <?= json_encode($reporte) ?>);
    </script>
</body>
</html>
