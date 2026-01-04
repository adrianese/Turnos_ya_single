-- Backup de Base de Datos: mis_turnos
-- Fecha: 2025-12-29 00:55:43
-- Generado por PHP

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


-- Estructura de tabla para la tabla `configuracion`
DROP TABLE IF EXISTS `configuracion`;
CREATE TABLE `configuracion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `clave` varchar(50) NOT NULL,
  `valor` text NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `clave` (`clave`)
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcado de datos para la tabla `configuracion`
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('1', 'nombre_unidad', 'Turnos-Ya', '2025-10-22 18:13:24', '2025-12-27 23:11:12');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('2', 'frase', 'Reservá tu turno fácil y rápido', '2025-10-22 18:13:24', '2025-10-22 18:13:24');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('3', 'timezone', 'America/Argentina/Buenos_Aires', '2025-10-22 18:13:24', '2025-10-22 18:13:24');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('4', 'horario_inicio', '09:00', '2025-12-27 22:19:18', '2025-12-27 22:43:28');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('5', 'horario_fin', '18:00', '2025-12-27 22:19:18', '2025-12-27 23:11:12');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('6', 'duracion_turno', '30', '2025-12-27 22:19:18', '2025-12-27 22:19:18');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('7', 'cupos_simultaneos', '3', '2025-12-27 22:19:18', '2025-12-27 22:43:28');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('8', 'gemini_api_key', 'AIzaSyDyaPPyETfhX7-LqOFakmmxCGthgUIHG7c', '2025-12-27 22:19:18', '2025-12-27 22:19:18');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('10', 'duracion_turno_default', '30', '2025-12-27 22:43:28', '2025-12-27 22:43:28');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('11', 'dias_laborables', '2,3,4,5,6', '2025-12-27 22:43:28', '2025-12-27 23:20:10');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('14', 'turnos_partidos', '0', '2025-12-27 22:43:29', '2025-12-27 23:11:12');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('16', 'frase_opcional', 'Reserva tu turno fácil y rápido', '2025-12-27 22:43:29', '2025-12-27 23:11:12');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('19', 'logo', 'img/logo-1766888054.png', '2025-12-27 23:06:05', '2025-12-27 23:14:14');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('20', 'foto_portada', 'img/portada-1766888054.jpg', '2025-12-27 23:06:06', '2025-12-27 23:14:14');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('24', 'atiende_feriados', '0', '2025-12-27 23:11:00', '2025-12-27 23:20:10');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('27', 'horario_fin_manana', '12:00', '2025-12-27 23:11:00', '2025-12-27 23:11:00');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('28', 'horario_inicio_tarde', '14:00', '2025-12-27 23:11:00', '2025-12-27 23:11:00');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('63', 'logo_url', 'img/logo-1766888054.png', '2025-12-28 00:37:20', '2025-12-28 00:37:20');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('64', 'portada_url', 'img/portada-1766888054.jpg', '2025-12-28 00:37:20', '2025-12-28 00:37:20');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('65', 'nombre_negocio', 'Turnos Ya', '2025-12-28 00:37:20', '2025-12-28 00:37:20');
INSERT INTO `configuracion` (`id`, `clave`, `valor`, `creado_en`, `actualizado_en`) VALUES ('66', 'eslogan', 'Sistema de gestión de turnos con IA', '2025-12-28 00:37:20', '2025-12-28 00:37:20');


-- Estructura de tabla para la tabla `historial_chat`
DROP TABLE IF EXISTS `historial_chat`;
CREATE TABLE `historial_chat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int NOT NULL,
  `mensaje` text NOT NULL,
  `respuesta` text NOT NULL,
  `contexto` json DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_creado_en` (`creado_en`),
  CONSTRAINT `historial_chat_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- Estructura de tabla para la tabla `horarios`
DROP TABLE IF EXISTS `horarios`;
CREATE TABLE `horarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dia` enum('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo') NOT NULL,
  `abierto` tinyint(1) NOT NULL DEFAULT '1',
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `turno_partido` tinyint(1) NOT NULL DEFAULT '0',
  `hora_inicio_2` time DEFAULT NULL,
  `hora_fin_2` time DEFAULT NULL,
  `duracion` int NOT NULL DEFAULT '30',
  `es_feriado` tinyint(1) DEFAULT '0',
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ux_horarios_dia` (`dia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- Estructura de tabla para la tabla `ia_eventos`
DROP TABLE IF EXISTS `ia_eventos`;
CREATE TABLE `ia_eventos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo_evento` varchar(50) NOT NULL,
  `usuario_id` int DEFAULT NULL,
  `turno_id` int DEFAULT NULL,
  `datos` json DEFAULT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_tipo_evento` (`tipo_evento`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_turno_id` (`turno_id`)
) ENGINE=InnoDB AUTO_INCREMENT=70 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcado de datos para la tabla `ia_eventos`
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('1', 'generate', NULL, NULL, '{\"prompt_length\": 25, \"response_length\": 510}', '2025-12-27 23:37:37');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('2', 'generate', NULL, NULL, '{\"prompt_length\": 37, \"response_length\": 121}', '2025-12-27 23:41:08');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('3', 'generate', NULL, NULL, '{\"prompt_length\": 40, \"response_length\": 168}', '2025-12-27 23:41:36');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('4', 'generate', NULL, NULL, '{\"prompt_length\": 70, \"response_length\": 175}', '2025-12-27 23:42:22');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('5', 'generate', NULL, NULL, '{\"prompt_length\": 19, \"response_length\": 153}', '2025-12-27 23:42:44');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('6', 'generate', NULL, NULL, '{\"prompt_length\": 26, \"response_length\": 191}', '2025-12-27 23:43:09');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('7', 'generate', NULL, NULL, '{\"prompt_length\": 4, \"response_length\": 175}', '2025-12-27 23:43:22');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('8', 'generate', NULL, NULL, '{\"prompt_length\": 37, \"response_length\": 142}', '2025-12-27 23:43:42');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('9', 'generate', NULL, NULL, '{\"prompt_length\": 37, \"response_length\": 126}', '2025-12-27 23:43:47');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('10', 'generate', NULL, NULL, '{\"prompt_length\": 7, \"response_length\": 223}', '2025-12-27 23:44:04');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('11', 'generate', NULL, NULL, '{\"prompt_length\": 2, \"response_length\": 134}', '2025-12-27 23:44:12');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('12', 'generate', NULL, NULL, '{\"prompt_length\": 7, \"response_length\": 90}', '2025-12-27 23:44:30');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('13', 'generate', NULL, NULL, '{\"prompt_length\": 26, \"response_length\": 123}', '2025-12-27 23:44:54');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('14', 'generate', NULL, NULL, '{\"prompt_length\": 35, \"response_length\": 106}', '2025-12-27 23:45:36');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('15', 'generate', NULL, NULL, '{\"prompt_length\": 25, \"response_length\": 307}', '2025-12-27 23:48:42');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('16', 'generate', NULL, NULL, '{\"prompt_length\": 28, \"response_length\": 141}', '2025-12-27 23:48:54');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('17', 'generate', NULL, NULL, '{\"prompt_length\": 26, \"response_length\": 211}', '2025-12-27 23:49:01');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('18', 'generate', NULL, NULL, '{\"prompt_length\": 37, \"response_length\": 102}', '2025-12-27 23:49:09');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('19', 'generate', NULL, NULL, '{\"prompt_length\": 25, \"response_length\": 238}', '2025-12-27 23:49:29');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('20', 'generate', NULL, NULL, '{\"prompt_length\": 28, \"response_length\": 192}', '2025-12-27 23:49:41');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('21', 'generate', NULL, NULL, '{\"prompt_length\": 29, \"response_length\": 313}', '2025-12-27 23:50:01');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('22', 'generate', NULL, NULL, '{\"prompt_length\": 13, \"response_length\": 303}', '2025-12-27 23:50:20');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('23', 'generate', NULL, NULL, '{\"prompt_length\": 32, \"response_length\": 128}', '2025-12-27 23:50:48');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('24', 'generate', NULL, NULL, '{\"prompt_length\": 37, \"response_length\": 266}', '2025-12-27 23:51:35');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('25', 'generate', NULL, NULL, '{\"prompt_length\": 37, \"response_length\": 259}', '2025-12-27 23:51:42');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('26', 'generate', NULL, NULL, '{\"prompt_length\": 33, \"response_length\": 127}', '2025-12-27 23:52:08');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('27', 'generate', NULL, NULL, '{\"prompt_length\": 6, \"response_length\": 134}', '2025-12-27 23:52:26');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('28', 'generate', NULL, NULL, '{\"prompt_length\": 10, \"response_length\": 179}', '2025-12-27 23:52:49');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('29', 'generate', NULL, NULL, '{\"prompt_length\": 10, \"response_length\": 141}', '2025-12-27 23:53:03');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('30', 'confirmacion_detectada', NULL, NULL, '{\"respuesta\": \"Perfecto, Juan. Ya tenemos toda la información.\\n\\n[CONFIRMACION]: Corte de Cabello con Gerente Quatro el 2 de enero a las 17:00. Total: 15.00\", \"usuario_id\": 6}', '2025-12-27 23:53:03');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('31', 'generate', NULL, NULL, '{\"prompt_length\": 8, \"response_length\": 94}', '2025-12-27 23:53:14');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('32', 'generate', NULL, NULL, '{\"prompt_length\": 70, \"response_length\": 123}', '2025-12-27 23:56:35');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('33', 'generate', NULL, NULL, '{\"prompt_length\": 22, \"response_length\": 164}', '2025-12-27 23:56:57');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('34', 'generate', NULL, NULL, '{\"prompt_length\": 11, \"response_length\": 198}', '2025-12-27 23:57:14');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('35', 'generate', NULL, NULL, '{\"prompt_length\": 10, \"response_length\": 91}', '2025-12-27 23:57:33');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('36', 'confirmacion_detectada', NULL, NULL, '{\"respuesta\": \"[CONFIRMACION]: Corte de Cabello con Gerente Quatro el 6 de enero a las 16:30. Total: 15.00\", \"usuario_id\": 6}', '2025-12-27 23:57:33');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('37', 'generate', NULL, NULL, '{\"prompt_length\": 27, \"response_length\": 65}', '2025-12-27 23:57:52');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('38', 'generate', NULL, NULL, '{\"prompt_length\": 7, \"response_length\": 37}', '2025-12-27 23:58:06');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('39', 'generate', NULL, NULL, '{\"prompt_length\": 114, \"response_length\": 91}', '2025-12-28 00:47:06');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('40', 'confirmacion_detectada', NULL, NULL, '{\"respuesta\": \"[CONFIRMACION]: Corte de Cabello con Gerente Quatro el 3 de enero a las 12:30. Total: 15.00\", \"usuario_id\": 6}', '2025-12-28 00:47:06');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('41', 'generate', NULL, NULL, '{\"prompt_length\": 10, \"response_length\": 61}', '2025-12-28 00:47:19');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('42', 'generate', NULL, NULL, '{\"prompt_length\": 37, \"response_length\": 61}', '2025-12-28 00:47:29');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('43', 'generate', NULL, NULL, '{\"prompt_length\": 25, \"response_length\": 117}', '2025-12-28 00:47:31');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('44', 'generate', NULL, NULL, '{\"prompt_length\": 28, \"response_length\": 116}', '2025-12-28 00:47:35');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('45', 'generate', NULL, NULL, '{\"prompt_length\": 26, \"response_length\": 176}', '2025-12-28 00:47:36');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('46', 'generate', NULL, NULL, '{\"prompt_length\": 68, \"response_length\": 194}', '2025-12-28 00:49:16');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('47', 'generate', NULL, NULL, '{\"prompt_length\": 2, \"response_length\": 126}', '2025-12-28 00:49:29');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('48', 'generate', NULL, NULL, '{\"prompt_length\": 3, \"response_length\": 111}', '2025-12-28 00:49:42');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('49', 'generate', NULL, NULL, '{\"prompt_length\": 33, \"response_length\": 128}', '2025-12-28 00:51:04');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('50', 'generate', NULL, NULL, '{\"prompt_length\": 23, \"response_length\": 145}', '2025-12-28 00:51:26');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('51', 'generate', NULL, NULL, '{\"prompt_length\": 37, \"response_length\": 200}', '2025-12-28 00:51:38');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('52', 'generate', NULL, NULL, '{\"prompt_length\": 25, \"response_length\": 116}', '2025-12-28 00:51:40');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('53', 'generate', NULL, NULL, '{\"prompt_length\": 28, \"response_length\": 122}', '2025-12-28 00:51:43');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('54', 'generate', NULL, NULL, '{\"prompt_length\": 4, \"response_length\": 76}', '2025-12-28 21:40:11');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('55', 'generate', NULL, NULL, '{\"prompt_length\": 35, \"response_length\": 122}', '2025-12-28 21:40:30');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('56', 'generate', NULL, NULL, '{\"prompt_length\": 27, \"response_length\": 249}', '2025-12-28 21:40:50');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('57', 'generate', NULL, NULL, '{\"prompt_length\": 20, \"response_length\": 199}', '2025-12-28 21:41:13');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('58', 'generate', NULL, NULL, '{\"prompt_length\": 56, \"response_length\": 237}', '2025-12-28 21:41:47');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('59', 'generate', NULL, NULL, '{\"prompt_length\": 37, \"response_length\": 215}', '2025-12-28 21:42:06');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('60', 'generate', NULL, NULL, '{\"prompt_length\": 31, \"response_length\": 133}', '2025-12-28 21:43:18');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('61', 'generate', NULL, NULL, '{\"prompt_length\": 56, \"response_length\": 280}', '2025-12-28 21:43:55');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('62', 'generate', NULL, NULL, '{\"prompt_length\": 37, \"response_length\": 200}', '2025-12-28 21:44:14');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('63', 'generate', NULL, NULL, '{\"prompt_length\": 25, \"response_length\": 374}', '2025-12-28 21:44:16');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('64', 'generate', NULL, NULL, '{\"prompt_length\": 28, \"response_length\": 156}', '2025-12-28 21:44:19');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('65', 'generate', NULL, NULL, '{\"prompt_length\": 26, \"response_length\": 250}', '2025-12-28 21:44:26');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('66', 'generate', NULL, NULL, '{\"prompt_length\": 62, \"response_length\": 152}', '2025-12-28 21:47:05');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('67', 'generate', NULL, NULL, '{\"prompt_length\": 52, \"response_length\": 169}', '2025-12-28 21:47:36');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('68', 'confirmacion_detectada', NULL, NULL, '{\"respuesta\": \"¡Perfecto! Si no tienes preferencia, podemos asignarte con el Administrador.\\n\\n[CONFIRMACION]: Corte de Cabello con Administrador el 1 de enero a las 10:00. Total: 15.00\", \"usuario_id\": 6}', '2025-12-28 21:47:36');
INSERT INTO `ia_eventos` (`id`, `tipo_evento`, `usuario_id`, `turno_id`, `datos`, `creado_en`) VALUES ('69', 'generate', NULL, NULL, '{\"prompt_length\": 20, \"response_length\": 75}', '2025-12-28 21:47:54');


-- Estructura de tabla para la tabla `predicciones_cache`
DROP TABLE IF EXISTS `predicciones_cache`;
CREATE TABLE `predicciones_cache` (
  `id` int NOT NULL AUTO_INCREMENT,
  `tipo` varchar(50) NOT NULL,
  `fecha_prediccion` date NOT NULL,
  `datos` json NOT NULL,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expira_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_tipo_fecha` (`tipo`,`fecha_prediccion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


-- Estructura de tabla para la tabla `servicios`
DROP TABLE IF EXISTS `servicios`;
CREATE TABLE `servicios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `precio` decimal(10,2) DEFAULT '0.00',
  `duracion` int NOT NULL DEFAULT '30',
  `max_cupos` int NOT NULL DEFAULT '1',
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcado de datos para la tabla `servicios`
INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `precio`, `duracion`, `max_cupos`, `activo`, `creado_en`, `actualizado_en`) VALUES ('1', 'Corte de Cabello', 'Corte clásico con lavado', '15.00', '30', '1', '1', '2025-12-27 22:19:19', NULL);
INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `precio`, `duracion`, `max_cupos`, `activo`, `creado_en`, `actualizado_en`) VALUES ('2', 'Corte + Barba', 'Corte de cabello y arreglo de barba', '25.00', '45', '1', '1', '2025-12-27 22:19:19', NULL);
INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `precio`, `duracion`, `max_cupos`, `activo`, `creado_en`, `actualizado_en`) VALUES ('3', 'Tratamiento Capilar', 'Tratamiento nutritivo para el cabello', '40.00', '60', '1', '1', '2025-12-27 22:19:19', NULL);
INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `precio`, `duracion`, `max_cupos`, `activo`, `creado_en`, `actualizado_en`) VALUES ('4', 'Peinado Especial', 'Para eventos y ocasiones especiales', '35.00', '45', '1', '1', '2025-12-27 22:19:19', NULL);
INSERT INTO `servicios` (`id`, `nombre`, `descripcion`, `precio`, `duracion`, `max_cupos`, `activo`, `creado_en`, `actualizado_en`) VALUES ('5', 'Coloración', 'Tinte completo de cabello', '50.00', '90', '1', '1', '2025-12-27 22:19:19', NULL);


-- Estructura de tabla para la tabla `turnos`
DROP TABLE IF EXISTS `turnos`;
CREATE TABLE `turnos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario_id` int DEFAULT NULL,
  `servicio_id` int DEFAULT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `duracion` int NOT NULL DEFAULT '30',
  `estado` enum('pendiente','confirmado','cancelado','asistido','no_asistio') NOT NULL DEFAULT 'pendiente',
  `notas` text,
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `recordatorio_enviado` tinyint(1) DEFAULT '0',
  `confirmado_por_usuario` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_turnos_fecha` (`fecha`),
  KEY `idx_turnos_estado` (`estado`),
  KEY `idx_turnos_usuario` (`usuario_id`),
  KEY `idx_turnos_servicio` (`servicio_id`),
  KEY `idx_turnos_hora` (`hora`),
  KEY `idx_turnos_fecha_hora` (`fecha`,`hora`),
  CONSTRAINT `fk_turnos_servicio` FOREIGN KEY (`servicio_id`) REFERENCES `servicios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_turnos_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcado de datos para la tabla `turnos`
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('1', '6', '1', '2025-12-28', '09:00:00', '30', 'confirmado', NULL, '2025-12-27 23:11:46', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('2', '5', '1', '2025-12-28', '10:00:00', '30', 'confirmado', NULL, '2025-12-27 23:11:46', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('3', '7', '3', '2025-12-28', '11:00:00', '30', 'confirmado', NULL, '2025-12-27 23:11:46', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('4', '2', '1', '2025-12-28', '14:00:00', '30', 'cancelado', NULL, '2025-12-27 23:11:46', '2025-12-27 23:20:26', '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('5', '2', '2', '2025-12-29', '09:00:00', '30', 'confirmado', NULL, '2025-12-27 23:11:46', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('6', '5', '2', '2025-12-29', '10:00:00', '30', 'confirmado', NULL, '2025-12-27 23:11:46', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('7', '7', '3', '2025-12-29', '11:00:00', '30', 'confirmado', NULL, '2025-12-27 23:11:46', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('8', '2', '1', '2025-12-29', '14:00:00', '30', 'confirmado', NULL, '2025-12-27 23:11:46', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('9', '7', '2', '2025-12-30', '09:00:00', '30', 'confirmado', NULL, '2025-12-27 23:11:46', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('10', '4', '2', '2025-12-30', '10:00:00', '30', 'confirmado', NULL, '2025-12-27 23:11:46', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('11', '5', '2', '2025-12-30', '11:00:00', '30', 'confirmado', NULL, '2025-12-27 23:11:46', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('12', '7', '2', '2025-12-30', '14:00:00', '30', 'cancelado', NULL, '2025-12-27 23:11:46', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('13', '7', '2', '2025-12-31', '09:00:00', '30', 'confirmado', NULL, '2025-12-27 23:11:46', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('14', '6', '1', '2025-12-31', '10:00:00', '30', 'confirmado', NULL, '2025-12-27 23:11:47', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('15', '2', '2', '2025-12-31', '11:00:00', '30', 'confirmado', NULL, '2025-12-27 23:11:47', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('16', '6', '1', '2025-12-31', '14:00:00', '30', 'cancelado', NULL, '2025-12-27 23:11:47', '2025-12-27 23:34:49', '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('17', '6', '2', '2025-12-30', '11:00:00', '45', 'cancelado', NULL, '2025-12-27 23:26:29', '2025-12-27 23:38:30', '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('18', '6', NULL, '2026-01-01', '17:00:00', '30', 'cancelado', NULL, '2025-12-27 23:34:25', '2025-12-27 23:53:58', '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('19', '6', '2', '2026-01-06', '15:30:00', '45', 'cancelado', NULL, '2025-12-27 23:55:04', '2025-12-27 23:55:28', '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('20', '6', '1', '2026-01-02', '10:00:00', '30', 'confirmado', NULL, '2025-12-28 21:18:23', NULL, '0', '0');
INSERT INTO `turnos` (`id`, `usuario_id`, `servicio_id`, `fecha`, `hora`, `duracion`, `estado`, `notas`, `creado_en`, `actualizado_en`, `recordatorio_enviado`, `confirmado_por_usuario`) VALUES ('21', '6', NULL, '2025-12-30', '16:00:00', '30', 'confirmado', NULL, '2025-12-28 21:42:43', NULL, '0', '0');


-- Estructura de tabla para la tabla `usuarios`
DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `rol` enum('admin','gerente','cliente') NOT NULL DEFAULT 'cliente',
  `activo` tinyint(1) DEFAULT '1',
  `visible` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `actualizado_en` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_usuarios_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcado de datos para la tabla `usuarios`
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `telefono`, `avatar`, `rol`, `activo`, `visible`, `creado_en`, `actualizado_en`) VALUES ('1', 'Administrador', 'admin@demo.com', '$2y$10$m5Omr2xjtS17HcrsO6.J7OrzjDr1Js1t7x/e7RCByWFo0TCN6S1ym', NULL, NULL, 'admin', '1', '1', '2025-10-22 18:13:24', '2025-12-27 22:52:12');
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `telefono`, `avatar`, `rol`, `activo`, `visible`, `creado_en`, `actualizado_en`) VALUES ('2', 'Adrian', 'correo@correo.com', '$2y$10$LMnkskEsKA9ouMzB1AZHBOSRkyHYI9jMwkbu/cLmQ1VnifhOSueFS', NULL, NULL, 'cliente', '1', '1', '2025-10-30 19:44:26', NULL);
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `telefono`, `avatar`, `rol`, `activo`, `visible`, `creado_en`, `actualizado_en`) VALUES ('3', 'Admin Turnos-Ya', 'admin@turnosya.com', '$2y$10$ENGFqeKSQh1WQvX6oFuqMeV6WMggbgOPnbmr0vz4eF0yBe03OOyGa', NULL, NULL, 'admin', '1', '1', '2025-12-27 22:19:19', '2025-12-27 22:52:12');
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `telefono`, `avatar`, `rol`, `activo`, `visible`, `creado_en`, `actualizado_en`) VALUES ('4', 'Juan Cliente', 'juan@cliente.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+54 11 1234-5678', NULL, 'cliente', '1', '1', '2025-12-27 22:19:19', NULL);
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `telefono`, `avatar`, `rol`, `activo`, `visible`, `creado_en`, `actualizado_en`) VALUES ('5', 'María López', 'maria@cliente.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+54 11 8765-4321', NULL, 'cliente', '1', '1', '2025-12-27 22:19:19', NULL);
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `telefono`, `avatar`, `rol`, `activo`, `visible`, `creado_en`, `actualizado_en`) VALUES ('6', 'Juan Pérez', 'cliente@test.com', '$2y$10$8frnLySMniqW4TNggvQDmOVdPytdZdCSQQObt5h3NkhZMe1BrsCDq', '+54 11 1234-5678', NULL, 'cliente', '1', '1', '2025-12-27 22:43:29', NULL);
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `telefono`, `avatar`, `rol`, `activo`, `visible`, `creado_en`, `actualizado_en`) VALUES ('7', 'Pepe Le Pew', 'pepe@correo.com', '$2y$10$aLkMe76S9PASApUpU8IKEeSXYkd77Kcg9MxrLc0iVN5jg0BZJjZK6', NULL, NULL, 'cliente', '1', '1', '2025-12-27 22:44:37', NULL);
INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `telefono`, `avatar`, `rol`, `activo`, `visible`, `creado_en`, `actualizado_en`) VALUES ('8', 'Gerente Quatro', 'gerente4@correo.com', '$2y$10$5SD83gbABiOX6bGO9tes7e3S5i2xwkj2GdI/aoOoOLJ6k2d5/CqyO', '54 911 45671290', NULL, 'gerente', '1', '1', '2025-12-27 23:22:20', NULL);

COMMIT;
