-- Crear tabla para feriados
CREATE TABLE IF NOT EXISTS feriados (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    tipo ENUM('nacional', 'provincial', 'municipal') DEFAULT 'nacional',
    UNIQUE KEY unique_fecha (fecha)
);