-- Tabla para sistema de notificaciones
CREATE TABLE IF NOT EXISTS notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    destinatario VARCHAR(255) NOT NULL,
    tipo ENUM('confirmacion', 'recordatorio', 'cancelacion', 'general') NOT NULL,
    enviado BOOLEAN DEFAULT 0,
    turno_id INT,
    error_mensaje TEXT,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    enviado_en TIMESTAMP NULL,
    INDEX idx_tipo (tipo),
    INDEX idx_turno (turno_id),
    INDEX idx_destinatario (destinatario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;