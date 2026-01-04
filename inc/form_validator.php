<?php
/**
 * Validador de Formularios - Turnos-Ya
 * Implementa validaciones robustas para todos los formularios
 */

require_once 'security_hardening.php';

class FormValidator {
    private $errors = [];
    private $data = [];

    public function __construct($postData = null) {
        $this->data = $postData ?: $_POST;
        $this->data = sanitizeInput($this->data);
    }

    public function validateRegistration() {
        // Validar nombre
        if (empty($this->data['nombre'])) {
            $this->errors['nombre'] = 'El nombre es obligatorio';
        } elseif (strlen($this->data['nombre']) < 2) {
            $this->errors['nombre'] = 'El nombre debe tener al menos 2 caracteres';
        } elseif (strlen($this->data['nombre']) > 50) {
            $this->errors['nombre'] = 'El nombre no puede exceder 50 caracteres';
        } elseif (!preg_match('/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/', $this->data['nombre'])) {
            $this->errors['nombre'] = 'El nombre solo puede contener letras y espacios';
        }

        // Validar email
        if (empty($this->data['email'])) {
            $this->errors['email'] = 'El email es obligatorio';
        } elseif (!InputValidator::validateEmail($this->data['email'])) {
            $this->errors['email'] = 'El formato del email es inválido';
        } elseif (strlen($this->data['email']) > 100) {
            $this->errors['email'] = 'El email no puede exceder 100 caracteres';
        }

        // Validar contraseña
        if (empty($this->data['password'])) {
            $this->errors['password'] = 'La contraseña es obligatoria';
        } elseif (!InputValidator::validatePassword($this->data['password'])) {
            $this->errors['password'] = 'La contraseña debe tener al menos 8 caracteres, incluir mayúsculas, minúsculas y números';
        }

        // Validar confirmación de contraseña
        if (empty($this->data['confirm_password'])) {
            $this->errors['confirm_password'] = 'La confirmación de contraseña es obligatoria';
        } elseif ($this->data['password'] !== $this->data['confirm_password']) {
            $this->errors['confirm_password'] = 'Las contraseñas no coinciden';
        }

        // Validar términos y condiciones
        if (empty($this->data['terminos'])) {
            $this->errors['terminos'] = 'Debe aceptar los términos y condiciones';
        }

        return empty($this->errors);
    }

    public function validateReservation() {
        // Validar servicio
        if (empty($this->data['servicio_id'])) {
            $this->errors['servicio_id'] = 'Debe seleccionar un servicio';
        } elseif (!InputValidator::validateNumeric($this->data['servicio_id'], 1, 1000)) {
            $this->errors['servicio_id'] = 'Servicio inválido';
        }

        // Validar fecha
        if (empty($this->data['fecha'])) {
            $this->errors['fecha'] = 'La fecha es obligatoria';
        } elseif (!InputValidator::validateDate($this->data['fecha'])) {
            $this->errors['fecha'] = 'Formato de fecha inválido';
        } else {
            $fechaSeleccionada = strtotime($this->data['fecha']);
            $hoy = strtotime(date('Y-m-d'));
            $maxFecha = strtotime('+30 days', $hoy);

            if ($fechaSeleccionada < $hoy) {
                $this->errors['fecha'] = 'No puede reservar en fechas pasadas';
            } elseif ($fechaSeleccionada > $maxFecha) {
                $this->errors['fecha'] = 'Solo puede reservar hasta 30 días en el futuro';
            }
        }

        // Validar hora
        if (empty($this->data['hora'])) {
            $this->errors['hora'] = 'La hora es obligatoria';
        } elseif (!InputValidator::validateTime($this->data['hora'])) {
            $this->errors['hora'] = 'Formato de hora inválido';
        } else {
            list($hora, $minuto) = explode(':', $this->data['hora']);
            $horaInt = (int)$hora;
            $minutoInt = (int)$minuto;

            if ($horaInt < 8 || $horaInt > 18) {
                $this->errors['hora'] = 'El horario debe estar entre 08:00 y 18:00';
            } elseif ($minutoInt % 30 !== 0) {
                $this->errors['hora'] = 'Los turnos deben ser cada 30 minutos';
            }
        }

        // Validar notas (opcional)
        if (!empty($this->data['notas']) && strlen($this->data['notas']) > 500) {
            $this->errors['notas'] = 'Las notas no pueden exceder 500 caracteres';
        }

        return empty($this->errors);
    }

    public function validateLogin() {
        // Rate limiting para login
        $clientIP = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        if (!RateLimiter::checkLimit("login_{$clientIP}", 5, 900)) { // 5 intentos por 15 min
            $this->errors['general'] = 'Demasiados intentos de login. Intente más tarde.';
            SecurityLogger::logEvent('LOGIN_RATE_LIMIT_EXCEEDED', ['ip' => $clientIP]);
            return false;
        }

        // Validar email
        if (empty($this->data['email'])) {
            $this->errors['email'] = 'El email es obligatorio';
        } elseif (!InputValidator::validateEmail($this->data['email'])) {
            $this->errors['email'] = 'Formato de email inválido';
        }

        // Validar contraseña
        if (empty($this->data['password'])) {
            $this->errors['password'] = 'La contraseña es obligatoria';
        }

        return empty($this->errors);
    }

    public function validateAdminHorarios() {
        // Validar día de la semana
        $diasValidos = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        if (empty($this->data['dia'])) {
            $this->errors['dia'] = 'El día es obligatorio';
        } elseif (!in_array(strtolower($this->data['dia']), $diasValidos)) {
            $this->errors['dia'] = 'Día inválido';
        }

        // Validar hora inicio
        if (empty($this->data['hora_inicio'])) {
            $this->errors['hora_inicio'] = 'La hora de inicio es obligatoria';
        } elseif (!InputValidator::validateTime($this->data['hora_inicio'])) {
            $this->errors['hora_inicio'] = 'Formato de hora de inicio inválido';
        }

        // Validar hora fin
        if (empty($this->data['hora_fin'])) {
            $this->errors['hora_fin'] = 'La hora de fin es obligatoria';
        } elseif (!InputValidator::validateTime($this->data['hora_fin'])) {
            $this->errors['hora_fin'] = 'Formato de hora de fin inválido';
        }

        // Validar que hora fin > hora inicio
        if (!empty($this->data['hora_inicio']) && !empty($this->data['hora_fin'])) {
            if (strtotime($this->data['hora_fin']) <= strtotime($this->data['hora_inicio'])) {
                $this->errors['hora_fin'] = 'La hora de fin debe ser posterior a la hora de inicio';
            }
        }

        // Validar cupos
        if (!empty($this->data['cupos']) && !InputValidator::validateNumeric($this->data['cupos'], 1, 100)) {
            $this->errors['cupos'] = 'Los cupos deben estar entre 1 y 100';
        }

        return empty($this->errors);
    }

    public function getErrors() {
        return $this->errors;
    }

    public function getFirstError() {
        return reset($this->errors);
    }

    public function hasErrors() {
        return !empty($this->errors);
    }

    public function getSanitizedData() {
        return $this->data;
    }
}

// Funciones helper para validación rápida
function validateAndSanitizeInput($data, $rules) {
    $errors = [];
    $sanitized = [];

    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? '';

        // Sanitizar
        $sanitized[$field] = sanitizeInput($value);

        // Aplicar reglas de validación
        if (isset($rule['required']) && $rule['required'] && empty($value)) {
            $errors[$field] = "El campo {$field} es obligatorio";
            continue;
        }

        if (!empty($value)) {
            if (isset($rule['type'])) {
                switch ($rule['type']) {
                    case 'email':
                        if (!InputValidator::validateEmail($value)) {
                            $errors[$field] = "Formato de email inválido";
                        }
                        break;
                    case 'numeric':
                        $min = $rule['min'] ?? null;
                        $max = $rule['max'] ?? null;
                        if (!InputValidator::validateNumeric($value, $min, $max)) {
                            $errors[$field] = "Valor numérico inválido para {$field}";
                        }
                        break;
                    case 'date':
                        if (!InputValidator::validateDate($value)) {
                            $errors[$field] = "Formato de fecha inválido";
                        }
                        break;
                    case 'time':
                        if (!InputValidator::validateTime($value)) {
                            $errors[$field] = "Formato de hora inválido";
                        }
                        break;
                }
            }

            if (isset($rule['min_length']) && strlen($value) < $rule['min_length']) {
                $errors[$field] = "El campo {$field} debe tener al menos {$rule['min_length']} caracteres";
            }

            if (isset($rule['max_length']) && strlen($value) > $rule['max_length']) {
                $errors[$field] = "El campo {$field} no puede exceder {$rule['max_length']} caracteres";
            }

            if (isset($rule['pattern']) && !preg_match($rule['pattern'], $value)) {
                $errors[$field] = "Formato inválido para {$field}";
            }
        }
    }

    return ['errors' => $errors, 'sanitized' => $sanitized, 'valid' => empty($errors)];
}
?>