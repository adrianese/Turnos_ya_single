// Forzar zona horaria de Buenos Aires
date_default_timezone_set('America/Argentina/Buenos_Aires');
<?php
/**
 * Módulo de IA: Sistema de Recomendaciones de Horarios
 * Analiza patrones de comportamiento del usuario y disponibilidad
 * para sugerir los mejores horarios de reserva
 */

require_once 'db.php';

class IARecomendaciones {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Obtiene recomendaciones inteligentes de horarios para un usuario
     * @param int $usuario_id ID del usuario
     * @param string $fecha Fecha deseada (YYYY-MM-DD)
     * @param int $servicio_id ID del servicio (opcional)
     * @return array Lista de horarios recomendados con scores
     */
    public function obtenerRecomendaciones($usuario_id, $fecha, $servicio_id = null) {
        // 1. Analizar patrones históricos del usuario
        $patronesUsuario = $this->analizarPatronesUsuario($usuario_id);
        
        // 2. Obtener horarios disponibles
        $horariosDisponibles = $this->obtenerHorariosDisponibles($fecha, $servicio_id);
        
        // 3. Calcular scores de recomendación
        $recomendaciones = [];
        foreach ($horariosDisponibles as $horario) {
            $score = $this->calcularScore($horario, $patronesUsuario, $fecha);
            $recomendaciones[] = [
                'hora' => $horario['hora'],
                'duracion' => $horario['duracion'],
                'score' => $score,
                'razon' => $this->generarRazon($score, $patronesUsuario),
                'disponibilidad' => $horario['cupos_disponibles']
            ];
        }
        
        // 4. Ordenar por score (mayor a menor)
        usort($recomendaciones, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        // 5. Retornar top 5 recomendaciones
        return array_slice($recomendaciones, 0, 5);
    }
    
    /**
     * Analiza patrones históricos del usuario
     */
    private function analizarPatronesUsuario($usuario_id) {
        $sql = "SELECT 
                    HOUR(hora) as hora_preferida,
                    DAYOFWEEK(fecha) as dia_semana,
                    COUNT(*) as frecuencia,
                    AVG(CASE WHEN estado = 'asistido' THEN 1 ELSE 0 END) as tasa_asistencia
                FROM turnos 
                WHERE usuario_id = ? 
                  AND fecha < CURDATE()
                GROUP BY HOUR(hora), DAYOFWEEK(fecha)
                ORDER BY frecuencia DESC";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$usuario_id]);
        $historial = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular preferencias
        $horasPreferidas = [];
        $diasPreferidos = [];
        $tasaAsistenciaPromedio = 0;
        
        foreach ($historial as $registro) {
            $horasPreferidas[$registro['hora_preferida']] = $registro['frecuencia'];
            $diasPreferidos[$registro['dia_semana']] = $registro['frecuencia'];
            $tasaAsistenciaPromedio += $registro['tasa_asistencia'];
        }
        
        if (count($historial) > 0) {
            $tasaAsistenciaPromedio /= count($historial);
        }
        
        return [
            'horas_preferidas' => $horasPreferidas,
            'dias_preferidos' => $diasPreferidos,
            'tasa_asistencia' => $tasaAsistenciaPromedio,
            'total_turnos_pasados' => array_sum($horasPreferidas),
            'es_nuevo_usuario' => count($historial) < 3
        ];
    }
    
    /**
     * Obtiene horarios disponibles para una fecha
     */
    private function obtenerHorariosDisponibles($fecha, $servicio_id = null) {
        // Obtener día de la semana
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $numDia = date('w', strtotime($fecha));
        $diaSemana = $dias[$numDia];

        // Obtener configuración de horarios para este día
        $stmt = $this->pdo->prepare("SELECT * FROM horarios WHERE dia = ?");
        $stmt->execute([$diaSemana]);
        $horarioDia = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si no hay configuración o el día está cerrado, retornar vacío
        if (!$horarioDia || !$horarioDia['abierto'] || $horarioDia['es_feriado']) {
            return [];
        }

        $duracionTurno = $horarioDia['duracion'] ?? 30;

        // Obtener cupos máximos del día específico, con fallback al global
        $cuposMax = (int)($horarioDia['cupos_maximos'] ?? 0);
        if ($cuposMax <= 0) {
            // Fallback al cupo global
            $stmt = $this->pdo->query("SELECT valor FROM configuracion WHERE clave = 'cupos_simultaneos'");
            $cuposMax = (int)($stmt->fetchColumn() ?: 1);
        }

        $slots = [];

        // Procesar primer horario
        if ($horarioDia['hora_inicio'] && $horarioDia['hora_fin']) {
            $horaActual = new DateTime($fecha . ' ' . $horarioDia['hora_inicio']);
            $horaFinal = new DateTime($fecha . ' ' . $horarioDia['hora_fin']);

            while ($horaActual < $horaFinal) {
                $horaStr = $horaActual->format('H:i:s');

                // Verificar cupos ocupados en ese horario
                $sql = "SELECT COUNT(*) FROM turnos
                        WHERE fecha = ?
                          AND hora = ?
                          AND estado IN ('pendiente', 'confirmado')";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$fecha, $horaStr]);
                $ocupados = $stmt->fetchColumn();

                $cuposDisponibles = $cuposMax - $ocupados;

                if ($cuposDisponibles > 0) {
                    $slots[] = [
                        'hora' => $horaStr,
                        'duracion' => $duracionTurno,
                        'cupos_disponibles' => $cuposDisponibles
                    ];
                }

                $horaActual->modify('+' . $duracionTurno . ' minutes');
            }
        }

        // Procesar segundo horario si existe
        if ($horarioDia['turno_partido'] && $horarioDia['hora_inicio_2'] && $horarioDia['hora_fin_2']) {
            $horaActual = new DateTime($fecha . ' ' . $horarioDia['hora_inicio_2']);
            $horaFinal = new DateTime($fecha . ' ' . $horarioDia['hora_fin_2']);

            while ($horaActual < $horaFinal) {
                $horaStr = $horaActual->format('H:i:s');

                // Verificar cupos ocupados en ese horario
                $sql = "SELECT COUNT(*) FROM turnos
                        WHERE fecha = ?
                          AND hora = ?
                          AND estado IN ('pendiente', 'confirmado')";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$fecha, $horaStr]);
                $ocupados = $stmt->fetchColumn();

                $cuposDisponibles = $cuposMax - $ocupados;

                if ($cuposDisponibles > 0) {
                    $slots[] = [
                        'hora' => $horaStr,
                        'duracion' => $duracionTurno,
                        'cupos_disponibles' => $cuposDisponibles
                    ];
                }

                $horaActual->modify('+' . $duracionTurno . ' minutes');
            }
        }

        return $slots;
    }
    
    /**
     * Calcula score de recomendación para un horario
     */
    private function calcularScore($horario, $patrones, $fecha) {
        $score = 50; // Score base
        
        $hora = (int)substr($horario['hora'], 0, 2);
        $diaSemana = date('N', strtotime($fecha)); // 1 (lunes) a 7 (domingo)
        
        // Factor 1: Coincidencia con horas preferidas del usuario (peso 30%)
        if (!$patrones['es_nuevo_usuario'] && isset($patrones['horas_preferidas'][$hora])) {
            $frecuencia = $patrones['horas_preferidas'][$hora];
            $maxFrecuencia = max($patrones['horas_preferidas']) ?: 1;
            $score += ($frecuencia / $maxFrecuencia) * 30;
        }
        
        // Factor 2: Día de la semana preferido (peso 15%)
        if (!$patrones['es_nuevo_usuario'] && isset($patrones['dias_preferidos'][$diaSemana])) {
            $frecuenciaDia = $patrones['dias_preferidos'][$diaSemana];
            $maxFrecuenciaDia = max($patrones['dias_preferidos']) ?: 1;
            $score += ($frecuenciaDia / $maxFrecuenciaDia) * 15;
        }
        
        // Factor 3: Disponibilidad (peso 20%)
        $score += ($horario['cupos_disponibles'] / 10) * 20;
        
        // Factor 4: Horarios populares generales (peso 15%)
        // Mañanas (9-12) y tardes (16-19) son más populares
        if (($hora >= 9 && $hora <= 12) || ($hora >= 16 && $hora <= 19)) {
            $score += 15;
        }
        
        // Factor 5: Evitar horas extremas (peso 10%)
        if ($hora >= 8 && $hora <= 20) {
            $score += 10;
        }
        
        // Factor 6: Bonus para usuarios nuevos en horarios populares
        if ($patrones['es_nuevo_usuario']) {
            if ($hora >= 10 && $hora <= 11) {
                $score += 20; // Media mañana
            } elseif ($hora >= 17 && $hora <= 18) {
                $score += 15; // Media tarde
            }
        }
        
        return min(100, round($score)); // Limitar a 100
    }
    
    /**
     * Genera explicación de por qué se recomienda este horario
     */
    private function generarRazon($score, $patrones) {
        if ($score >= 85) {
            if ($patrones['es_nuevo_usuario']) {
                return "Horario muy popular y conveniente";
            }
            return "Coincide perfectamente con tus preferencias habituales";
        } elseif ($score >= 70) {
            return "Horario recomendado según tus reservas anteriores";
        } elseif ($score >= 60) {
            return "Buena disponibilidad en horario conveniente";
        } else {
            return "Horario disponible";
        }
    }
    
    /**
     * Obtiene estadísticas de ocupación para predicciones
     */
    public function obtenerEstadisticasOcupacion($fecha_inicio, $fecha_fin) {
        $sql = "SELECT 
                    fecha,
                    HOUR(hora) as hora_del_dia,
                    DAYOFWEEK(fecha) as dia_semana,
                    COUNT(*) as total_turnos,
                    SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as cancelados,
                    SUM(CASE WHEN estado = 'asistido' THEN 1 ELSE 0 END) as asistidos,
                    SUM(CASE WHEN estado IN ('pendiente', 'confirmado') THEN 1 ELSE 0 END) as activos
                FROM turnos 
                WHERE fecha BETWEEN ? AND ?
                GROUP BY fecha, HOUR(hora), DAYOFWEEK(fecha)
                ORDER BY fecha, hora_del_dia";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fecha_inicio, $fecha_fin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
