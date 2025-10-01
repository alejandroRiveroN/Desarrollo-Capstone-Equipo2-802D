-- phpMyAdmin SQL Dump
-- Version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-08-2025
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de datos: `soporte_db`
--

-- --------------------------------------------------------
-- PARTE 1: ESTRUCTURA DE TABLAS
-- --------------------------------------------------------

CREATE TABLE `agentes` (
  `id_agente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `puesto` varchar(50) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `archivos_adjuntos` (
  `id_adjunto` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `id_comentario` int(11) DEFAULT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_guardado` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `tipo_mime` varchar(100) NOT NULL,
  `fecha_subida` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo_electronico` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `empresa` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `telegram` varchar(50) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `comentarios` (
  `id_comentario` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `id_autor` int(11) NOT NULL,
  `tipo_autor` enum('Agente','Cliente') NOT NULL,
  `comentario` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `es_privado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tickets` (
  `id_ticket` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `id_agente_asignado` int(11) DEFAULT NULL,
  `id_tipo_caso` int(11) DEFAULT NULL,
  `asunto` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('Abierto','En Progreso','En Espera','Resuelto','Cerrado','Anulado') DEFAULT 'Abierto',
  `prioridad` enum('Baja','Media','Alta','Urgente') DEFAULT 'Media',
  `fecha_vencimiento` datetime DEFAULT NULL,
  `costo` decimal(10,2) DEFAULT 0.00,
  `moneda` varchar(3) DEFAULT 'USD',
  `estado_facturacion` enum('Pendiente','Facturado','Pagado') NOT NULL DEFAULT 'Pendiente',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `medio_pago` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tiposdecaso` (
  `id_tipo_caso` int(11) NOT NULL,
  `nombre_tipo` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `telegram` varchar(50) DEFAULT NULL,
  `ruta_foto` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- PARTE 2: INSERCIÓN DE DATOS
-- --------------------------------------------------------

INSERT INTO `agentes` (`id_agente`, `id_usuario`, `puesto`, `fecha_contratacion`) VALUES
(1, 3, 'Agente de Soporte', '2025-08-29'),
(2, 4, 'Agente de Soporte', '2025-08-29'),
(3, 5, 'Supervisor', '2025-08-29'),
(4, 2, 'Administrador', '2025-08-29');

INSERT INTO `clientes` (`id_cliente`, `nombre`, `correo_electronico`, `telefono`, `empresa`, `pais`, `ciudad`, `whatsapp`, `telegram`, `activo`, `fecha_registro`) VALUES
(1, 'CLIENTE 1', 'cliente1@correo.com', '12121212', 'EMPRESA 1', NULL, NULL, NULL, NULL, 1, '2025-08-29 14:22:30');


INSERT INTO `roles` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
(1, 'Administrador', 'Acceso total al sistema, gestión de usuarios y configuraciones.'),
(2, 'Agente de Soporte', 'Puede gestionar tickets asignados y ver los de su equipo.'),
(3, 'Supervisor', 'Puede ver todos los tickets y generar reportes, pero no gestiona usuarios.');


INSERT INTO `tiposdecaso` (`id_tipo_caso`, `nombre_tipo`, `descripcion`, `activo`) VALUES
(1, 'SERVIDORES DE DATOS', '', 1);

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `nombre_completo`, `email`, `telefono`, `whatsapp`, `telegram`, `ruta_foto`, `password_hash`, `activo`, `fecha_creacion`) VALUES
(2, 1, 'Administrador del Negocio', 'admin@correo.com', '', '', '', 'uploads/avatars/688ae90605525_usuario1.jpg', '$2y$10$AVfKtFBS05wnc99PLQwSOeakMlCYmTZ5Jk1jkaMsruN2J77fOon8i', 1, '2025-07-27 05:53:49'),
(3, 2, 'Soporte de campo 1', 'soporte1@correo.com', '', '', '', 'uploads/avatars/688ae92d7e3c5_usuarios2.png', '$2y$10$u37WkUxy4AXeEDd9rMkc6eCGr3jCs53H2M0CTTOeDR8dP/xMAoJlW', 1, '2025-07-27 06:18:50'),
(4, 2, 'Soporte de campo 2', 'soporte2@correo.com', '', '', '', 'uploads/avatars/688ae937b55b0_usuarios2.png', '$2y$10$KuQA7by/s34bMW9TFqGFp.v0yHZhnHzyZPulqsGvWXnQzW5BFLJGC', 1, '2025-07-27 17:21:40'),
(5, 3, 'supervisor', 'supervisor@correo.com', '', '', '', 'uploads/avatars/68873f5fcdec2_usuarios2.png', '$2y$10$32OcKTF9skC3M7HjWxmI/u0zzkLs45IL5.PVe97rDw5xBZLr1ELru', 1, '2025-07-28 09:14:07');

-- --------------------------------------------------------
-- PARTE 3: ÍNDICES Y LLAVES PRIMARIAS
-- --------------------------------------------------------

ALTER TABLE `agentes` ADD PRIMARY KEY (`id_agente`), ADD UNIQUE KEY `id_usuario` (`id_usuario`);
ALTER TABLE `archivos_adjuntos` ADD PRIMARY KEY (`id_adjunto`), ADD KEY `id_ticket` (`id_ticket`), ADD KEY `id_comentario` (`id_comentario`);
ALTER TABLE `clientes` ADD PRIMARY KEY (`id_cliente`), ADD UNIQUE KEY `correo_electronico` (`correo_electronico`);
ALTER TABLE `comentarios` ADD PRIMARY KEY (`id_comentario`), ADD KEY `id_ticket` (`id_ticket`);
ALTER TABLE `roles` ADD PRIMARY KEY (`id_rol`), ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);
ALTER TABLE `tickets` ADD PRIMARY KEY (`id_ticket`), ADD KEY `id_cliente` (`id_cliente`), ADD KEY `id_agente_asignado` (`id_agente_asignado`), ADD KEY `id_tipo_caso` (`id_tipo_caso`);
ALTER TABLE `tiposdecaso` ADD PRIMARY KEY (`id_tipo_caso`), ADD UNIQUE KEY `nombre_tipo` (`nombre_tipo`);
ALTER TABLE `usuarios` ADD PRIMARY KEY (`id_usuario`), ADD UNIQUE KEY `email` (`email`), ADD KEY `id_rol` (`id_rol`);

-- --------------------------------------------------------
-- PARTE 4: AUTO_INCREMENTS
-- --------------------------------------------------------

ALTER TABLE `agentes` MODIFY `id_agente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `archivos_adjuntos` MODIFY `id_adjunto` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `clientes` MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `comentarios` MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `roles` MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
ALTER TABLE `tickets` MODIFY `id_ticket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `tiposdecaso` MODIFY `id_tipo_caso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
ALTER TABLE `usuarios` MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

-- --------------------------------------------------------
-- PARTE 5: RESTRICCIONES (FOREIGN KEYS)
-- --------------------------------------------------------

ALTER TABLE `agentes` ADD CONSTRAINT `agentes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;
ALTER TABLE `archivos_adjuntos` ADD CONSTRAINT `fk_adjunto_comentario` FOREIGN KEY (`id_comentario`) REFERENCES `comentarios` (`id_comentario`) ON DELETE CASCADE, ADD CONSTRAINT `fk_adjunto_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_ticket`) ON DELETE CASCADE;
ALTER TABLE `comentarios` ADD CONSTRAINT `fk_comentario_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_ticket`) ON DELETE CASCADE;
ALTER TABLE `tickets` ADD CONSTRAINT `fk_ticket_agente` FOREIGN KEY (`id_agente_asignado`) REFERENCES `agentes` (`id_agente`) ON DELETE SET NULL, ADD CONSTRAINT `fk_ticket_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE, ADD CONSTRAINT `fk_ticket_tipocaso` FOREIGN KEY (`id_tipo_caso`) REFERENCES `tiposdecaso` (`id_tipo_caso`) ON DELETE SET NULL;
ALTER TABLE `usuarios` ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`);

COMMIT;