-- Inicialización de la tabla horarios con configuración por defecto
-- Ejecutar después de crear la tabla horarios

INSERT INTO horarios (dia, abierto, hora_inicio, hora_fin, turno_partido, hora_inicio_2, hora_fin_2, duracion, es_feriado, cupos_maximos) VALUES
('Lunes', 1, '09:00', '18:00', 0, NULL, NULL, 30, 0, 3),
('Martes', 1, '09:00', '18:00', 0, NULL, NULL, 30, 0, 3),
('Miércoles', 1, '09:00', '18:00', 0, NULL, NULL, 30, 0, 3),
('Jueves', 1, '09:00', '18:00', 0, NULL, NULL, 30, 0, 3),
('Viernes', 1, '09:00', '18:00', 0, NULL, NULL, 30, 0, 3),
('Sábado', 1, '09:00', '14:00', 0, NULL, NULL, 30, 0, 2),
('Domingo', 0, NULL, NULL, 0, NULL, NULL, 30, 0, 0)
ON DUPLICATE KEY UPDATE
abierto = VALUES(abierto),
hora_inicio = VALUES(hora_inicio),
hora_fin = VALUES(hora_fin),
turno_partido = VALUES(turno_partido),
hora_inicio_2 = VALUES(hora_inicio_2),
hora_fin_2 = VALUES(hora_fin_2),
duracion = VALUES(duracion),
es_feriado = VALUES(es_feriado),
cupos_maximos = VALUES(cupos_maximos);