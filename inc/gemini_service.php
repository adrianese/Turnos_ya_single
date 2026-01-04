<?php
/**
 * Servicio de IA con Google Gemini
 * Integración para asistente inteligente de Turnos-Ya
 * API Key configurada en la base de datos
 */

if (!class_exists('PDO')) {
    require_once __DIR__ . '/db.php';
}

class GeminiService {
    private $apiKey;
    private $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';
    private $pdo;
    
    public function __construct($pdo = null) {
        if ($pdo === null) {
            global $pdo;
        }
        $this->pdo = $pdo;
        $this->loadApiKey();
    }
    
    /**
     * Carga la API Key desde la configuración (BD primero, luego .env)
     */
    private function loadApiKey() {
        // Primero intentar desde BD
        try {
            $stmt = $this->pdo->query("SELECT valor FROM configuracion WHERE clave = 'gemini_api_key'");
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && !empty($row['valor'])) {
                $this->apiKey = $row['valor'];
                return;
            }
        } catch (Exception $e) {
            // Si falla BD, continuar con .env
        }

        // Fallback a .env
        $this->apiKey = $_ENV['GEMINI_API_KEY'] ?? getenv('GEMINI_API_KEY');
        if (!$this->apiKey || $this->apiKey === 'YOUR_NEW_API_KEY_HERE') {
            throw new Exception('API Key de Gemini no configurada. Configúrala en el panel de administración o en .env');
        }
    }
    
    /**
     * Genera contenido usando Gemini API
     * @param string $prompt Mensaje o pregunta del usuario
     * @param array $context Contexto adicional (datos de BD, historial, etc)
     * @return string Respuesta de la IA
     */
    public function generate($prompt, $context = []) {
        try {
            // Inicializar historial si no existe
            if (!isset($_SESSION['chat_historial'])) {
                $_SESSION['chat_historial'] = [];
            }
            
            // Construir el prompt completo con contexto
            $fullPrompt = $this->buildPromptWithContext($prompt, $context);
            
            // Preparar datos para la API
            $data = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $fullPrompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.5,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 2048,
                    'stopSequences' => []
                ]
            ];
            
            // Hacer la petición a la API
            $response = $this->makeRequest($data);
            
            // Registrar evento de IA
            $this->logIAEvent('generate', [
                'prompt_length' => strlen($prompt),
                'response_length' => strlen($response)
            ]);
            
            return $response;
            
        } catch (Exception $e) {
            error_log('Error en Gemini generate: ' . $e->getMessage());
            // Si es error de API key, mostrar mensaje específico
            if (strpos($e->getMessage(), 'API Key de Gemini no configurada') !== false) {
                return $e->getMessage();
            }
            return "Lo siento, hubo un error al procesar tu solicitud. Por favor, intenta de nuevo.";
        }
    }
    
    /**
     * Construye el prompt con contexto del sistema
     */
    private function buildPromptWithContext($prompt, $context) {
        $systemPrompt = $this->getSystemPrompt();
        
        // Agregar historial de conversación
        $historialText = '';
        if (isset($_SESSION['chat_historial']) && !empty($_SESSION['chat_historial'])) {
            $historialText = "\n\nHISTORIAL DE CONVERSACIÓN ACTUAL:\n";
            $ultimos = array_slice($_SESSION['chat_historial'], -10); // Últimos 10 mensajes
            foreach ($ultimos as $msg) {
                $historialText .= $msg['rol'] . ": " . $msg['contenido'] . "\n";
            }
        }

        // Agregar historial de conversaciones anteriores
        if (isset($context['historial_anterior']) && !empty($context['historial_anterior'])) {
            $historialText .= "\n\nCONVERSACIONES ANTERIORES (referencia):\n";
            $anteriores = array_slice($context['historial_anterior'], -5); // Últimas 5 conversaciones
            foreach ($anteriores as $conv) {
                $historialText .= "Usuario: " . $conv['usuario'] . "\n";
                $historialText .= "Asistente: " . $conv['asistente'] . "\n\n";
            }
        }
        
        // Agregar contexto si existe
        $contextText = '';
        if (!empty($context)) {
            $contextText = "\n\nCONTEXTO DISPONIBLE:\n";
            
            if (isset($context['servicios'])) {
                $contextText .= "SERVICIOS: " . json_encode($context['servicios'], JSON_UNESCAPED_UNICODE) . "\n";
            }
            
            if (isset($context['colaboradores'])) {
                $contextText .= "COLABORADORES: " . json_encode($context['colaboradores'], JSON_UNESCAPED_UNICODE) . "\n";
            }
            
            if (isset($context['disponibilidad']) && !empty($context['disponibilidad'])) {
                $contextText .= "\nDISPONIBILIDAD PRÓXIMOS DÍAS:\n";
                foreach ($context['disponibilidad'] as $disp) {
                    $contextText .= "- {$disp['dia']} [{$disp['fecha']}]: ";
                    
                    // Verificar si horarios es un array
                    if (is_array($disp['horarios']) && !empty($disp['horarios'])) {
                        // Si los elementos son arrays con clave 'hora'
                        if (isset($disp['horarios'][0]) && is_array($disp['horarios'][0])) {
                            $horariosStr = array_map(function($h) { 
                                return is_array($h) ? $h['hora'] : $h; 
                            }, $disp['horarios']);
                        } else {
                            // Si son strings directamente
                            $horariosStr = $disp['horarios'];
                        }
                        $contextText .= implode(', ', $horariosStr) . "\n";
                    } else {
                        $contextText .= "No disponible\n";
                    }
                }
            }
            
            if (isset($context['usuario'])) {
                $contextText .= "\nUSUARIO ACTUAL: " . $context['usuario']['nombre'] . "\n";
            }
        }
        
        return $systemPrompt . $contextText . $historialText . "\n\nUSUARIO: " . $prompt;
    }
    
    /**
     * Obtiene la configuración del negocio desde la base de datos
     */
    private function getConfiguracion() {
        try {
            $configKeys = [
                'nombre_negocio', 'nombre_unidad', 'dias_laborables', 
                'horario_inicio', 'horario_fin', 'duracion_turno'
            ];
            
            $config = [];
            foreach ($configKeys as $key) {
                $stmt = $this->pdo->prepare("SELECT valor FROM configuracion WHERE clave = ?");
                $stmt->execute([$key]);
                $config[$key] = $stmt->fetchColumn() ?: $this->getDefaultValue($key);
            }
            
            return $config;
        } catch (Exception $e) {
            // Valores por defecto si hay error
            return [
                'nombre_negocio' => 'Turnos Ya',
                'nombre_unidad' => 'Peluquería y Barbería',
                'dias_laborables' => '1,2,3,4,5,6,7', // Lunes a domingo por defecto
                'horario_inicio' => '09:00',
                'horario_fin' => '18:00',
                'duracion_turno' => '30'
            ];
        }
    }
    
    /**
     * Convierte números de días a nombres legibles
     */
    private function convertirDiasANombres($diasString) {
        $diasMap = [
            1 => 'Lunes',
            2 => 'Martes', 
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
            7 => 'Domingo'
        ];
        
        $dias = explode(',', $diasString);
        $nombres = [];
        
        foreach ($dias as $dia) {
            $diaNum = (int)trim($dia);
            if (isset($diasMap[$diaNum])) {
                $nombres[] = $diasMap[$diaNum];
            }
        }
        
        // Formatear como "lunes a viernes" o "lunes, miércoles, viernes"
        if (count($nombres) >= 3) {
            $ultimo = array_pop($nombres);
            return implode(', ', $nombres) . ' y ' . $ultimo;
        } elseif (count($nombres) == 2) {
            return implode(' y ', $nombres);
        } elseif (count($nombres) == 1) {
            return $nombres[0];
        }
        
        return 'lunes a viernes'; // fallback
    }
    
    /**
     * Valores por defecto para configuración
     */
    private function getDefaultValue($key) {
        $defaults = [
            'nombre_negocio' => 'Turnos Ya',
            'nombre_unidad' => 'Peluquería y Barbería', 
            'dias_laborables' => '2,3,4,5,6', // Martes a sábado
            'horario_inicio' => '09:00',
            'horario_fin' => '18:00',
            'duracion_turno' => '30'
        ];
        
        return $defaults[$key] ?? '';
    }
    
    /**
     * Hace la petición HTTP a la API de Gemini
     */
    private function makeRequest($data) {
        $url = $this->apiUrl . '?key=' . $this->apiKey;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json'
        ]);
        
        // Deshabilitar verificación SSL para desarrollo local
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('cURL Error: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('API Error: HTTP ' . $httpCode . ' - ' . $response);
        }
        
        $result = json_decode($response, true);
        
        if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            throw new Exception('Formato de respuesta inesperado');
        }
        
        return $result['candidates'][0]['content']['parts'][0]['text'];
    }
    
    /**
     * System Prompt para el asistente de Turnos-Ya
     */
    private function getSystemPrompt() {
        // Obtener configuración dinámica
        $config = $this->getConfiguracion();
        
        // Convertir días laborables a nombres
        $diasNombres = $this->convertirDiasANombres($config['dias_laborables']);
        
        return <<<PROMPT
    Eres el Asistente Virtual Inteligente de {$config['nombre_negocio']}, un sistema de gestión de turnos profesional para {$config['nombre_unidad']}.

    MISIÓN:
    Ayudar a los clientes a reservar, reprogramar o cancelar turnos de forma eficiente y amigable.

    INFORMACIÓN DEL NEGOCIO:
    - Nombre: {$config['nombre_negocio']}
    - Especialidad: {$config['nombre_unidad']}
    - Días laborables: {$diasNombres}
    - Horario de atención: {$config['horario_inicio']} a {$config['horario_fin']}
    - Duración estándar de turnos: {$config['duracion_turno']} minutos

    INSTRUCCIÓN ESPECIAL:
    Antes de mostrar horarios disponibles, pregunta siempre al usuario:
    "¿Deseas reservar para hoy a partir de la próxima hora disponible, o prefieres otro día?"
    Si el usuario responde que sí o lo da a entender, muestra solo los horarios futuros de hoy (a partir de la próxima hora disponible). Si responde que no, pregunta para qué día desea reservar y muestra los horarios de ese día.

    CONOCIMIENTOS:
    - Tienes acceso a la agenda de colaboradores (peluqueros, médicos, instructores, etc.)
    - Conoces los servicios disponibles, precios y duraciones
    - IMPORTANTE: Puedes consultar disponibilidad para HOY, MAÑANA y los próximos 7 días
    - Los horarios disponibles se te proporcionan día por día en el contexto
    - El negocio atiende de {$config['horario_inicio']} a {$config['horario_fin']} los {$diasNombres}

    COMPORTAMIENTO CRÍTICO:
    1. MEMORIA: Recuerda TODA la información que el usuario te ha proporcionado en la conversación. NO pidas información que ya te dieron.
    2. FECHAS FUTURAS: Cuando te pregunten por fechas futuras (mañana, 3 de enero, etc.), consulta la disponibilidad en el contexto. NUNCA digas que solo puedes ver "hoy".
    3. EFICIENCIA: Si ya tienes Servicio + Fecha + Hora + Colaborador, confirma INMEDIATAMENTE. No hagas más preguntas innecesarias.
    4. MENSAJES COMPLETOS: NUNCA termines un mensaje a la mitad. Si vas a decir algo, completa la idea SIEMPRE. Evita frases como "Sin embargo," o "Para poder..." sin terminar.
    5. BREVEDAD: Respuestas cortas y directas. Máximo 3-4 líneas por mensaje.
    6. NEGOCIACIÓN: Si un horario está ocupado, ofrece inmediatamente 2-3 alternativas cercanas del mismo día o días cercanos.
    7. CONTEXTO: Revisa el historial antes de responder para no repetir preguntas.

    FORMATO DE CONFIRMACIÓN:
    Cuando tengas los 4 datos (Servicio, Colaborador, Fecha, Hora), confirma inmediatamente con este formato exacto:
    [CONFIRMACION]: {Servicio} con {Colaborador} el {Fecha} a las {Hora}. Total: {Monto}

    RESTRICCIONES:
    - No reserves fuera del horario de atención ({$config['horario_inicio']} a {$config['horario_fin']})
    - No reserves en días no laborables ({$diasNombres})
    - No ofrezcas servicios que no existen
    - Completa SIEMPRE tus frases, nunca dejes mensajes a la mitad

    PROMPT;
    }
    
    /**
     * Procesa mensaje del usuario con contexto de la base de datos
     */
    public function procesarMensajeUsuario($mensaje, $usuario_id = null) {
        // Inicializar historial si no existe
        if (!isset($_SESSION['chat_historial'])) {
            $_SESSION['chat_historial'] = [];
        }
        
        // Agregar mensaje del usuario al historial
        $_SESSION['chat_historial'][] = [
            'rol' => 'Usuario',
            'contenido' => $mensaje,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        // Obtener contexto de la base de datos
        $context = $this->obtenerContexto($usuario_id);
        
        // Agregar historial de conversaciones anteriores
        if ($usuario_id) {
            $context['historial_anterior'] = $this->obtenerHistorialChat($usuario_id);
        }
        
        return $this->generate($mensaje, $context);
    }
    
    /**
     * Obtiene contexto relevante de la base de datos
     */
    private function obtenerContexto($usuario_id = null) {
        $context = [];
        
        // Obtener servicios activos
        $stmt = $this->pdo->query("SELECT nombre, descripcion, precio, duracion FROM servicios WHERE activo = 1");
        $context['servicios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener colaboradores visibles
        $stmt = $this->pdo->query("SELECT nombre, rol FROM usuarios WHERE rol IN ('gerente', 'admin') AND activo = 1");
        $context['colaboradores'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Obtener información del usuario si está disponible
        if ($usuario_id) {
            $stmt = $this->pdo->prepare("SELECT nombre, email FROM usuarios WHERE id = ?");
            $stmt->execute([$usuario_id]);
            $context['usuario'] = $stmt->fetch(PDO::FETCH_ASSOC);
        }
        
        // Obtener horarios disponibles para los próximos 7 días
        $context['disponibilidad'] = [];
        for ($i = 0; $i < 7; $i++) {
            $fecha = date('Y-m-d', strtotime("+$i days"));
            $nombreDia = $this->getNombreDia($fecha);
            $horarios = $this->obtenerHorariosDisponibles($fecha);
            if (!empty($horarios)) {
                $context['disponibilidad'][] = [
                    'fecha' => $fecha,
                    'dia' => $nombreDia,
                    'horarios' => $horarios
                ];
            }
        }
        
        return $context;
    }

    /**
     * Obtiene horarios disponibles para una fecha específica
     */
    private function obtenerHorariosDisponibles($fecha) {
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
                $horaStr = $horaActual->format('H:i');

                // Verificar cupos ocupados en ese horario
                $sql = "SELECT COUNT(*) FROM turnos
                        WHERE fecha = ?
                          AND hora = ?
                          AND estado IN ('pendiente', 'confirmado')";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$fecha, $horaStr . ':00']);
                $ocupados = $stmt->fetchColumn();

                $cuposDisponibles = $cuposMax - $ocupados;

                if ($cuposDisponibles > 0) {
                    $slots[] = $horaStr;
                }

                $horaActual->modify('+' . $duracionTurno . ' minutes');
            }
        }

        // Procesar segundo horario si existe
        if ($horarioDia['turno_partido'] && $horarioDia['hora_inicio_2'] && $horarioDia['hora_fin_2']) {
            $horaActual = new DateTime($fecha . ' ' . $horarioDia['hora_inicio_2']);
            $horaFinal = new DateTime($fecha . ' ' . $horarioDia['hora_fin_2']);

            while ($horaActual < $horaFinal) {
                $horaStr = $horaActual->format('H:i');

                // Verificar cupos ocupados en ese horario
                $sql = "SELECT COUNT(*) FROM turnos
                        WHERE fecha = ?
                          AND hora = ?
                          AND estado IN ('pendiente', 'confirmado')";
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([$fecha, $horaStr . ':00']);
                $ocupados = $stmt->fetchColumn();

                $cuposDisponibles = $cuposMax - $ocupados;

                if ($cuposDisponibles > 0) {
                    $slots[] = $horaStr;
                }

                $horaActual->modify('+' . $duracionTurno . ' minutes');
            }
        }

        return $slots;
    }

    /**
     * Obtiene historial de conversaciones anteriores del usuario
     */
    private function obtenerHistorialChat($usuario_id, $limite = 20) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT mensaje, respuesta, creado_en
                FROM historial_chat
                WHERE usuario_id = ?
                ORDER BY creado_en DESC
                LIMIT ?
            ");
            $stmt->execute([$usuario_id, $limite]);
            $conversaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Revertir orden para tener cronología correcta
            $conversaciones = array_reverse($conversaciones);

            $historial = [];
            foreach ($conversaciones as $conv) {
                $historial[] = [
                    'usuario' => $conv['mensaje'],
                    'asistente' => $conv['respuesta'],
                    'fecha' => $conv['creado_en']
                ];
            }

            return $historial;
        } catch (Exception $e) {
            error_log('Error obteniendo historial de chat: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Obtiene el nombre del día en español
     */
    private function getNombreDia($fecha) {
        $dias = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $timestamp = strtotime($fecha);
        $numDia = date('w', $timestamp);
        $dia = date('d/m', $timestamp);
        
        // Si es hoy o mañana, usar esos términos
        $hoy = date('Y-m-d');
        $manana = date('Y-m-d', strtotime('+1 day'));
        
        if ($fecha === $hoy) {
            return "HOY ($dia)";
        } elseif ($fecha === $manana) {
            return "MAÑANA ($dia)";
        } else {
            return $dias[$numDia] . " ($dia)";
        }
    }
    
    /**
     * Procesa una confirmación detectada por la IA
     */
    private function procesarConfirmacion($respuesta, $usuario_id) {
        // Extraer datos de la confirmación
        // [CONFIRMACION]: {Servicio} con {Colaborador} el {Fecha} a las {Hora}
        
        // Aquí puedes implementar la lógica para insertar el turno automáticamente
        // Por ahora solo lo registramos como evento
        
        $this->logIAEvent('confirmacion_detectada', [
            'usuario_id' => $usuario_id,
            'respuesta' => $respuesta
        ]);
    }
    
    /**
     * Registra eventos de IA para tracking
     */
    private function logIAEvent($tipo_evento, $datos, $usuario_id = null, $turno_id = null) {
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO ia_eventos (tipo_evento, usuario_id, turno_id, datos) 
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->execute([
                $tipo_evento,
                $usuario_id,
                $turno_id,
                json_encode($datos)
            ]);
        } catch (Exception $e) {
            error_log('Error logging IA event: ' . $e->getMessage());
        }
    }
    
    /**
     * Genera recordatorio personalizado para un turno
     */
    public function generarRecordatorio($turno_id) {
        try {
            // Obtener datos del turno
            $stmt = $this->pdo->prepare("
                SELECT t.*, u.nombre as cliente_nombre, s.nombre as servicio_nombre
                FROM turnos t
                JOIN usuarios u ON t.usuario_id = u.id
                LEFT JOIN servicios s ON t.servicio_id = s.id
                WHERE t.id = ?
            ");
            $stmt->execute([$turno_id]);
            $turno = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$turno) {
                throw new Exception('Turno no encontrado');
            }
            
            // Crear prompt para recordatorio
            $prompt = sprintf(
                "Genera un recordatorio breve y amable para un cliente. Cliente: %s. Servicio: %s. Fecha: %s a las %s. Evita sonar robótico, usa tono cercano. Máximo 2 líneas.",
                $turno['cliente_nombre'],
                $turno['servicio_nombre'] ?? 'Turno',
                date('d/m/Y', strtotime($turno['fecha'])),
                date('H:i', strtotime($turno['hora']))
            );
            
            $recordatorio = $this->generate($prompt);
            
            // Registrar evento
            $this->logIAEvent('recordatorio_generado', [
                'turno_id' => $turno_id,
                'mensaje' => $recordatorio
            ], $turno['usuario_id'], $turno_id);
            
            return $recordatorio;
            
        } catch (Exception $e) {
            error_log('Error generando recordatorio: ' . $e->getMessage());
            return "Recordatorio: Tienes un turno próximamente.";
        }
    }
}
