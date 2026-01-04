<?php
// Inicia sesión y verifica si es admin
require_once '../inc/auth.php';
require_once '../inc/db.php';
require_once '../inc/branding.php';

if (!isLoggedIn() || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

$mensaje = '';
$error = '';

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Procesar horarios por día
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

        foreach ($dias as $dia) {
            $abierto = isset($_POST['abierto_' . $dia]) ? 1 : 0;
            $horaInicio = $_POST['hora_inicio_' . $dia] ?? null;
            $horaFin = $_POST['hora_fin_' . $dia] ?? null;
            $turnoPartido = isset($_POST['turno_partido_' . $dia]) ? 1 : 0;
            $horaInicio2 = $_POST['hora_inicio_2_' . $dia] ?? null;
            $horaFin2 = $_POST['hora_fin_2_' . $dia] ?? null;
            $duracion = (int)($_POST['duracion_' . $dia] ?? 30);
            $cuposMaximos = (int)($_POST['cupos_maximos_' . $dia] ?? 1);
            
            // Validar cupos
            if ($cuposMaximos < 1 || $cuposMaximos > 50) {
                $cuposMaximos = 1; // Valor por defecto si es inválido
            }

            // Solo guardar si está abierto
            if ($abierto && $horaInicio && $horaFin) {
                $sql = "INSERT INTO horarios (dia, abierto, hora_inicio, hora_fin, turno_partido, hora_inicio_2, hora_fin_2, duracion, cupos_maximos)
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                        ON DUPLICATE KEY UPDATE
                        abierto = VALUES(abierto),
                        hora_inicio = VALUES(hora_inicio),
                        hora_fin = VALUES(hora_fin),
                        turno_partido = VALUES(turno_partido),
                        hora_inicio_2 = VALUES(hora_inicio_2),
                        hora_fin_2 = VALUES(hora_fin_2),
                        duracion = VALUES(duracion),
                        cupos_maximos = VALUES(cupos_maximos)";
                $pdo->prepare($sql)->execute([$dia, $abierto, $horaInicio, $horaFin, $turnoPartido, $horaInicio2, $horaFin2, $duracion, $cuposMaximos]);
            } else {
                // Si no está abierto, marcar como cerrado
                $sql = "INSERT INTO horarios (dia, abierto, hora_inicio, hora_fin, turno_partido, hora_inicio_2, hora_fin_2, duracion, cupos_maximos)
                        VALUES (?, 0, NULL, NULL, 0, NULL, NULL, 30, 0)
                        ON DUPLICATE KEY UPDATE abierto = 0, hora_inicio = NULL, hora_fin = NULL, turno_partido = 0, hora_inicio_2 = NULL, hora_fin_2 = NULL, cupos_maximos = 0";
                $pdo->prepare($sql)->execute([$dia]);
            }
        }

        // Procesar feriados
        if (isset($_POST['feriados']) && is_array($_POST['feriados'])) {
            // Primero marcar todos como no feriados
            $pdo->exec("UPDATE horarios SET es_feriado = 0");

            // Luego marcar los seleccionados como feriados
            foreach ($_POST['feriados'] as $dia) {
                $sql = "UPDATE horarios SET es_feriado = 1 WHERE dia = ?";
                $pdo->prepare($sql)->execute([$dia]);
            }
        }

        $mensaje = "✅ Configuración de horarios guardada exitosamente";
    } catch (Exception $e) {
        $error = "Error al guardar: " . $e->getMessage();
    }
}

// Obtener configuración actual de horarios
$sql = "SELECT * FROM horarios ORDER BY FIELD(dia, 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo')";
$stmt = $pdo->query($sql);
$horarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organizar por día
$horariosPorDia = [];
foreach ($horarios as $h) {
    $horariosPorDia[$h['dia']] = $h;
}

$pageTitle = "Configuración de Horarios y Cupos";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= $pageTitle ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        <?php if (PORTADA_URL): ?>
        body::before {
            background-image: url('../img/portada-1767467044.jpg');
        }
        <?php endif; ?>
        
        .container {
            max-width: 900px;
            margin: 30px auto;
            padding: 20px;
        }
        
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
        
        .form-section {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }

        input[type="time"],
        select {
            padding: 10px;
            font-size: 1em;
            width: 100%;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .day-checkboxes label {
            display: inline-block;
            margin-right: 15px;
            font-weight: normal;
        }

        .inline-group {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .inline-group input[type="radio"] {
            margin-right: 8px;
        }

        .btn-save {
            background: #667eea;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        .btn-save:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../dashboard.php" class="back-link">← Volver al Dashboard</a>
        
        <h1>🕐 <?= $pageTitle ?></h1>
        
        <?php if ($mensaje): ?>
            <div class="mensaje exito"><?= $mensaje ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="mensaje error"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="form-section">
            <h3>📅 Configuración de Horarios y Cupos por Día</h3>
            <p>Configure horarios específicos y cupos máximos para cada día de la semana. Puede tener diferentes horarios y capacidad para cada día.</p>

            <?php
            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
            foreach ($dias as $dia):
                $h = $horariosPorDia[$dia] ?? ['abierto' => 0, 'hora_inicio' => '09:00', 'hora_fin' => '18:00', 'turno_partido' => 0, 'hora_inicio_2' => null, 'hora_fin_2' => null, 'duracion' => 30, 'es_feriado' => 0, 'cupos_maximos' => 1];
            ?>
            <div class="dia-config" style="border: 1px solid #ddd; padding: 15px; margin: 10px 0; border-radius: 5px;">
                <div style="display: flex; align-items: center; margin-bottom: 10px;">
                    <input type="checkbox" name="abierto_<?= $dia ?>" value="1" id="abierto_<?= $dia ?>" <?= $h['abierto'] ? 'checked' : '' ?>>
                    <label for="abierto_<?= $dia ?>" style="margin-left: 8px; font-weight: bold; font-size: 1.1em;"><?= $dia ?></label>
                    <input type="checkbox" name="feriados[]" value="<?= $dia ?>" id="feriado_<?= $dia ?>" style="margin-left: 20px;" <?= $h['es_feriado'] ? 'checked' : '' ?>>
                    <label for="feriado_<?= $dia ?>" style="margin-left: 5px; color: #d9534f;">Feriado</label>
                </div>

                <div class="horarios-dia" id="horarios_<?= $dia ?>" style="display: <?= $h['abierto'] ? 'block' : 'none' ?>;">
                    <div style="display: flex; gap: 20px; align-items: center;">
                        <div>
                            <label>Horario 1:</label>
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <input type="time" name="hora_inicio_<?= $dia ?>" value="<?= $h['hora_inicio'] ?? '09:00' ?>">
                                <span>a</span>
                                <input type="time" name="hora_fin_<?= $dia ?>" value="<?= $h['hora_fin'] ?? '18:00' ?>">
                            </div>
                        </div>

                        <div>
                            <input type="checkbox" name="turno_partido_<?= $dia ?>" value="1" id="partido_<?= $dia ?>" <?= $h['turno_partido'] ? 'checked' : '' ?>>
                            <label for="partido_<?= $dia ?>">Turno partido</label>
                        </div>

                        <div>
                            <label>Duración (min):</label>
                            <input type="number" name="duracion_<?= $dia ?>" value="<?= $h['duracion'] ?? 30 ?>" min="15" max="180" style="width: 80px;">
                        </div>

                        <div>
                            <label>Cupos máx:</label>
                            <input type="number" name="cupos_maximos_<?= $dia ?>" value="<?= $h['cupos_maximos'] ?? 1 ?>" min="1" max="20" style="width: 70px;">
                        </div>
                    </div>

                    <div class="horario-partido" id="horario2_<?= $dia ?>" style="display: <?= $h['turno_partido'] ? 'block' : 'none' ?>; margin-top: 10px;">
                        <label>Horario 2:</label>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <input type="time" name="hora_inicio_2_<?= $dia ?>" value="<?= $h['hora_inicio_2'] ?? '16:00' ?>">
                            <span>a</span>
                            <input type="time" name="hora_fin_2_<?= $dia ?>" value="<?= $h['hora_fin_2'] ?? '20:00' ?>">
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- Botón guardar -->
            <div class="form-group">
                <button type="submit" class="btn-save">💾 Guardar configuración</button>
            </div>
        </form>
    </div>

    <script>
    // Función para mostrar/ocultar horarios cuando se marca/desmarcar día
    document.querySelectorAll('input[type="checkbox"][id^="abierto_"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const dia = this.id.replace('abierto_', '');
            const horariosDiv = document.getElementById('horarios_' + dia);
            horariosDiv.style.display = this.checked ? 'block' : 'none';
        });
    });

    // Función para mostrar/ocultar segundo horario cuando se marca turno partido
    document.querySelectorAll('input[type="checkbox"][id^="partido_"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const dia = this.id.replace('partido_', '');
            const horario2Div = document.getElementById('horario2_' + dia);
            horario2Div.style.display = this.checked ? 'block' : 'none';
        });
    });
    </script>
</body>
</html>
