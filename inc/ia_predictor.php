<?php
/**
 * Módulo de IA: Análisis Predictivo de Ocupación
 * Sistema de Machine Learning para predecir ocupación futura,
 * tendencias, patrones y probabilidad de cancelaciones
 */

require_once 'db.php';

class IAPredictor {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Predice la ocupación para una fecha específica
     * @param string $fecha Fecha a predecir (YYYY-MM-DD)
     * @return array Predicción de ocupación por franja horaria
     */
    public function predecirOcupacion($fecha) {
        $diaSemana = date('N', strtotime($fecha)); // 1-7
        $mes = date('n', strtotime($fecha));
        
        // Obtener datos históricos de días similares
        $sql = "SELECT 
                    HOUR(hora) as hora,
                    COUNT(*) as total_turnos,
                    AVG(CASE WHEN estado IN ('confirmado', 'asistido') THEN 1 ELSE 0 END) as tasa_confirmacion
                FROM turnos 
                WHERE DAYOFWEEK(fecha) = ?
                  AND MONTH(fecha) = ?
                  AND fecha < CURDATE()
                GROUP BY HOUR(hora)";
        
        $stmt = $this->pdo->prepare($sql);
        // MySQL DAYOFWEEK: 1=domingo, 2=lunes... ajustar
        $diaSemanaMySQL = $diaSemana == 7 ? 1 : $diaSemana + 1;
        $stmt->execute([$diaSemanaMySQL, $mes]);
        $datosHistoricos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular predicción
        $predicciones = [];
        foreach ($datosHistoricos as $dato) {
            $promedioTurnos = $dato['total_turnos'];
            $tasaConfirmacion = $dato['tasa_confirmacion'];
            
            // Aplicar factores de ajuste
            $factor = $this->calcularFactorAjuste($fecha, $dato['hora']);
            
            $predicciones[] = [
                'hora' => sprintf("%02d:00:00", $dato['hora']),
                'ocupacion_esperada' => round($promedioTurnos * $factor),
                'probabilidad_alta' => ($promedioTurnos * $factor) > 3 ? 'Alta' : 
                                      (($promedioTurnos * $factor) > 1 ? 'Media' : 'Baja'),
                'tasa_confirmacion' => round($tasaConfirmacion * 100),
                'confianza' => $this->calcularConfianza($promedioTurnos)
            ];
        }
        
        return $predicciones;
    }
    
    /**
     * Calcula factor de ajuste según tendencias recientes
     */
    private function calcularFactorAjuste($fecha, $hora) {
        // Obtener tendencia de las últimas 4 semanas
        $sql = "SELECT COUNT(*) as total 
                FROM turnos 
                WHERE fecha >= DATE_SUB(CURDATE(), INTERVAL 4 WEEK)
                  AND HOUR(hora) = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$hora]);
        $tendenciaReciente = $stmt->fetchColumn();
        
        // Factor base
        $factor = 1.0;
        
        // Ajustar si hay crecimiento
        if ($tendenciaReciente > 10) {
            $factor *= 1.15; // 15% más si hay actividad reciente
        } elseif ($tendenciaReciente < 3) {
            $factor *= 0.85; // 15% menos si hay poca actividad
        }
        
        return $factor;
    }
    
    /**
     * Calcula nivel de confianza de la predicción
     */
    private function calcularConfianza($cantidadDatos) {
        if ($cantidadDatos >= 10) return 'Alta';
        if ($cantidadDatos >= 5) return 'Media';
        return 'Baja';
    }
    
    /**
     * Predice probabilidad de cancelación de un turno
     * @param int $turno_id ID del turno
     * @return array Predicción con probabilidad y factores
     */
    public function predecirCancelacion($turno_id) {
        // Obtener datos del turno
        $sql = "SELECT t.*, u.id as usuario_id, u.nombre, u.email 
                FROM turnos t 
                JOIN usuarios u ON t.usuario_id = u.id 
                WHERE t.id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$turno_id]);
        $turno = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$turno) {
            return ['error' => 'Turno no encontrado'];
        }
        
        // Analizar historial del usuario
        $sql = "SELECT 
                    COUNT(*) as total_turnos,
                    SUM(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) as total_cancelados,
                    SUM(CASE WHEN estado = 'asistido' THEN 1 ELSE 0 END) as total_asistidos
                FROM turnos 
                WHERE usuario_id = ? AND fecha < CURDATE()";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$turno['usuario_id']]);
        $historial = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $probabilidad = 20; // Base 20%
        $factores = [];
        
        // Factor 1: Historial de cancelaciones del usuario
        if ($historial['total_turnos'] > 0) {
            $tasaCancelacion = ($historial['total_cancelados'] / $historial['total_turnos']) * 100;
            
            if ($tasaCancelacion > 50) {
                $probabilidad += 40;
                $factores[] = "Usuario con alto historial de cancelaciones ({$tasaCancelacion}%)";
            } elseif ($tasaCancelacion > 30) {
                $probabilidad += 20;
                $factores[] = "Usuario con historial moderado de cancelaciones";
            } elseif ($tasaCancelacion < 10) {
                $probabilidad -= 15;
                $factores[] = "Usuario confiable con baja tasa de cancelación";
            }
        } else {
            $probabilidad += 10;
            $factores[] = "Usuario nuevo sin historial";
        }
        
        // Factor 2: Tiempo hasta el turno
        $diasHastaTurno = (strtotime($turno['fecha']) - time()) / (60 * 60 * 24);
        
        if ($diasHastaTurno > 7) {
            $probabilidad += 15;
            $factores[] = "Turno lejano (más de 7 días)";
        } elseif ($diasHastaTurno < 1) {
            $probabilidad -= 20;
            $factores[] = "Turno cercano (menos de 24 horas)";
        }
        
        // Factor 3: Horario del turno
        $hora = (int)substr($turno['hora'], 0, 2);
        if ($hora < 9 || $hora > 19) {
            $probabilidad += 10;
            $factores[] = "Horario menos conveniente";
        }
        
        // Limitar probabilidad entre 0-100
        $probabilidad = max(0, min(100, $probabilidad));
        
        return [
            'turno_id' => $turno_id,
            'probabilidad_cancelacion' => round($probabilidad),
            'nivel_riesgo' => $probabilidad > 60 ? 'Alto' : ($probabilidad > 35 ? 'Medio' : 'Bajo'),
            'factores' => $factores,
            'recomendacion' => $this->generarRecomendacionCancelacion($probabilidad)
        ];
    }
    
    /**
     * Genera recomendación basada en probabilidad de cancelación
     */
    private function generarRecomendacionCancelacion($probabilidad) {
        if ($probabilidad > 60) {
            return "Considerar contactar al cliente para confirmar o tener lista de espera";
        } elseif ($probabilidad > 35) {
            return "Enviar recordatorio automático 24hs antes";
        } else {
            return "Turno estable, no requiere acción especial";
        }
    }
    
    /**
     * Obtiene tendencias de ocupación general
     * @param int $semanas Número de semanas a analizar
     * @return array Tendencias y estadísticas
     */
    public function obtenerTendencias($semanas = 4) {
        $fechaInicio = date('Y-m-d', strtotime("-{$semanas} weeks"));
        
        // Ocupación por día de la semana
        $sql = "SELECT 
                    DAYNAME(fecha) as dia_semana,
                    DAYOFWEEK(fecha) as dia_num,
                    COUNT(*) as total_turnos,
                    AVG(CASE WHEN estado = 'asistido' THEN 1 ELSE 0 END) * 100 as tasa_asistencia
                FROM turnos 
                WHERE fecha >= ?
                GROUP BY DAYOFWEEK(fecha), DAYNAME(fecha)
                ORDER BY dia_num";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fechaInicio]);
        $porDia = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Ocupación por franja horaria
        $sql = "SELECT 
                    CASE 
                        WHEN HOUR(hora) BETWEEN 6 AND 11 THEN 'Mañana'
                        WHEN HOUR(hora) BETWEEN 12 AND 17 THEN 'Tarde'
                        WHEN HOUR(hora) BETWEEN 18 AND 23 THEN 'Noche'
                        ELSE 'Madrugada'
                    END as franja,
                    COUNT(*) as total_turnos,
                    AVG(CASE WHEN estado = 'cancelado' THEN 1 ELSE 0 END) * 100 as tasa_cancelacion
                FROM turnos 
                WHERE fecha >= ?
                GROUP BY franja
                ORDER BY 
                    CASE franja
                        WHEN 'Mañana' THEN 1
                        WHEN 'Tarde' THEN 2
                        WHEN 'Noche' THEN 3
                        ELSE 4
                    END";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fechaInicio]);
        $porFranja = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Tendencia semanal
        $sql = "SELECT 
                    WEEK(fecha) as semana,
                    YEAR(fecha) as año,
                    COUNT(*) as total_turnos
                FROM turnos 
                WHERE fecha >= ?
                GROUP BY YEAR(fecha), WEEK(fecha)
                ORDER BY año, semana";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$fechaInicio]);
        $tendenciaSemanal = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Calcular crecimiento
        $crecimiento = 0;
        if (count($tendenciaSemanal) >= 2) {
            $primera = $tendenciaSemanal[0]['total_turnos'];
            $ultima = $tendenciaSemanal[count($tendenciaSemanal) - 1]['total_turnos'];
            $crecimiento = $primera > 0 ? (($ultima - $primera) / $primera) * 100 : 0;
        }
        
        return [
            'periodo_analizado' => "{$semanas} semanas",
            'fecha_desde' => $fechaInicio,
            'ocupacion_por_dia' => $porDia,
            'ocupacion_por_franja' => $porFranja,
            'tendencia_semanal' => $tendenciaSemanal,
            'crecimiento_porcentual' => round($crecimiento, 1),
            'interpretacion' => $this->interpretarTendencia($crecimiento)
        ];
    }
    
    /**
     * Interpreta tendencia de crecimiento
     */
    private function interpretarTendencia($crecimiento) {
        if ($crecimiento > 20) {
            return "📈 Crecimiento fuerte - Considerar ampliar capacidad";
        } elseif ($crecimiento > 5) {
            return "📊 Crecimiento estable - Situación saludable";
        } elseif ($crecimiento > -5) {
            return "➡️ Estabilidad - Sin cambios significativos";
        } elseif ($crecimiento > -20) {
            return "📉 Decrecimiento leve - Monitorear situación";
        } else {
            return "⚠️ Decrecimiento significativo - Requiere atención";
        }
    }
    
    /**
     * Identifica turnos en riesgo de no-show
     * @return array Lista de turnos con alto riesgo
     */
    public function identificarTurnosRiesgo() {
        // Obtener turnos futuros confirmados
        $sql = "SELECT t.*, u.nombre, u.email 
                FROM turnos t 
                JOIN usuarios u ON t.usuario_id = u.id 
                WHERE t.fecha >= CURDATE() 
                  AND t.estado IN ('pendiente', 'confirmado')
                ORDER BY t.fecha, t.hora";
        $stmt = $this->pdo->query($sql);
        $turnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $turnosRiesgo = [];
        
        foreach ($turnos as $turno) {
            $prediccion = $this->predecirCancelacion($turno['id']);
            
            if ($prediccion['probabilidad_cancelacion'] > 50) {
                $turnosRiesgo[] = array_merge($turno, $prediccion);
            }
        }
        
        return $turnosRiesgo;
    }
    
    /**
     * Genera reporte completo de analytics
     */
    public function generarReporteCompleto() {
        $tendencias = $this->obtenerTendencias(4);
        $turnosRiesgo = $this->identificarTurnosRiesgo();
        
        // Próxima semana predicción
        $prediccionesSemana = [];
        for ($i = 0; $i < 7; $i++) {
            $fecha = date('Y-m-d', strtotime("+{$i} days"));
            $prediccionesSemana[] = [
                'fecha' => $fecha,
                'dia' => date('l', strtotime($fecha)),
                'prediccion' => $this->predecirOcupacion($fecha)
            ];
        }
        
        return [
            'tendencias_historicas' => $tendencias,
            'turnos_en_riesgo' => count($turnosRiesgo),
            'lista_riesgo' => $turnosRiesgo,
            'predicciones_proxima_semana' => $prediccionesSemana,
            'generado_en' => date('Y-m-d H:i:s')
        ];
    }
}
