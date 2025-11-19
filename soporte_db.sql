-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-11-2025 a las 17:19:23
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `soporte_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `agente`
--

CREATE TABLE `agente` (
  `id_agente` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `puesto` varchar(50) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `agente`
--

INSERT INTO `agente` (`id_agente`, `id_usuario`, `puesto`, `fecha_contratacion`) VALUES
(4, 2, 'Administrador', '2025-08-29'),
(11, 12, 'Supervisor en practica', '2025-10-17'),
(12, 1, NULL, '2025-11-04'),
(13, 10, NULL, '2025-11-04'),
(14, 54, 'Soporte Nivel 2', '2025-11-07'),
(15, 55, 'Soporte Nivel 3', '2025-11-07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivo_adjunto`
--

CREATE TABLE `archivo_adjunto` (
  `id_adjunto` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `id_comentario` int(11) DEFAULT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_guardado` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `tipo_mime` varchar(100) NOT NULL,
  `fecha_subida` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `empresa` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `nombre`, `email`, `telefono`, `empresa`, `pais`, `ciudad`, `activo`, `fecha_registro`) VALUES
(45, 'Alejandro Rivero', 'alej.rivero@duocuc.cl', NULL, 'MCE', 'Chile', 'San Antonio', 1, '2025-11-04 00:05:59'),
(46, 'Esteban Peña', 'esteban@cliente.com', NULL, NULL, NULL, NULL, 1, '2025-11-04 22:35:24'),
(47, 'Alejandro Nilo', 'alejandro@cliente.com', NULL, NULL, NULL, NULL, 1, '2025-11-06 00:34:43'),
(48, 'Jesus Sandoval', 'jesussgutierrez@gmail.com', NULL, 'Moon', 'Chile', 'San Antonio', 1, '2025-11-07 18:14:46'),
(49, 'Samuel Nilo', 'SamuelN@gmail.com', NULL, 'MCE', 'Chile', 'San Antonio', 1, '2025-11-07 19:14:46'),
(50, 'Alejandro Rivero Nilo', 'alejrivero@gmail.com', '+', 'MCE', NULL, NULL, 1, '2025-11-13 03:30:19');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentario`
--

CREATE TABLE `comentario` (
  `id_comentario` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `id_autor` int(11) NOT NULL,
  `tipo_autor` enum('Agente','Cliente') NOT NULL,
  `comentario` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `es_privado` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comentario`
--

INSERT INTO `comentario` (`id_comentario`, `id_ticket`, `id_autor`, `tipo_autor`, `comentario`, `fecha_creacion`, `es_privado`) VALUES
(90, 31, 45, 'Cliente', 'Ticket creado con la siguiente descripción:\n\nasdasdasddasdsaasdasd', '2025-11-04 00:06:22', 0),
(91, 32, 45, 'Cliente', 'Ticket creado con la siguiente descripción:\n\nasdasdasdasdasdasd', '2025-11-04 00:06:37', 0),
(92, 32, 40, 'Agente', 'Ticket reasignado de \'Nadie\' a \'esteban supervisor\' por Alejandro Rivero Nilo.', '2025-11-04 00:08:49', 1),
(93, 32, 40, 'Agente', 'Estado cambiado a \'Abierto\' por Alejandro Rivero Nilo.', '2025-11-04 00:08:51', 0),
(94, 32, 40, 'Agente', 'Estado cambiado a \'Resuelto\' por Alejandro Rivero Nilo.', '2025-11-04 00:08:54', 0),
(95, 32, 40, 'Agente', 'Costo actualizado por Alejandro Rivero Nilo:\nCosto: 40000 CLP\nEstado Facturación: Pagado', '2025-11-04 00:32:34', 1),
(96, 31, 40, 'Agente', 'Ticket reasignado de \'Nadie\' a \'esteban soporte\' por Alejandro Rivero Nilo.', '2025-11-04 00:39:27', 1),
(97, 31, 40, 'Agente', 'Costo actualizado por Alejandro Rivero Nilo:\nCosto: 100000 CLP\nEstado Facturación: Pagado\nMedio de Pago: Tarjeta de Crédito/Débito', '2025-11-04 00:39:39', 1),
(98, 33, 45, 'Cliente', 'Ticket creado con la siguiente descripción:\n\nasdasddsadas', '2025-11-04 01:21:06', 0),
(99, 33, 0, 'Agente', 'Estado cambiado a \'Resuelto\' por Alejandro Rivero Nilo.', '2025-11-04 01:23:30', 0),
(100, 33, 40, 'Agente', 'Costo actualizado por Alejandro Rivero Nilo:\nCosto: 1000000 CLP\nEstado Facturación: Pagado\nMedio de Pago: Tarjeta de Crédito/Débito', '2025-11-04 01:24:32', 1),
(101, 31, 0, 'Agente', 'Estado cambiado a \'Resuelto\' por Alejandro Rivero Nilo.', '2025-11-04 01:24:51', 0),
(102, 34, 45, 'Cliente', 'Ticket creado con la siguiente descripción:\n\nasdadsdadasdd', '2025-11-04 01:25:09', 0),
(103, 34, 0, 'Agente', 'Estado cambiado a \'Resuelto\' por Alejandro Rivero Nilo.', '2025-11-04 01:25:14', 0),
(104, 34, 40, 'Agente', 'Costo actualizado por Alejandro Rivero Nilo:\nCosto: 1000000 CLP\nEstado Facturación: Pagado\nMedio de Pago: Tarjeta de Crédito/Débito', '2025-11-04 01:25:23', 1),
(105, 35, 45, 'Cliente', 'Ticket creado con la siguiente descripción:\n\nasdasdasdasdasd', '2025-11-04 01:25:44', 0),
(106, 35, 40, 'Agente', 'Costo actualizado por Alejandro Rivero Nilo:\nCosto: 50000 CLP\nEstado Facturación: Pagado\nMedio de Pago: Tarjeta de Crédito/Débito', '2025-11-04 01:26:02', 1),
(107, 35, 0, 'Agente', 'Estado cambiado a \'Resuelto\' por Alejandro Rivero Nilo.', '2025-11-04 01:26:07', 0),
(108, 36, 45, 'Cliente', 'Ticket creado con la siguiente descripción:\n\nasdadasdadss', '2025-11-04 22:34:29', 0),
(109, 37, 46, 'Cliente', 'Ticket creado con la siguiente descripción:\n\nasdadasdadad', '2025-11-04 22:35:52', 0),
(110, 37, 50, 'Agente', 'Hola si', '2025-11-04 22:39:31', 0),
(111, 37, 50, 'Agente', 'si', '2025-11-04 22:43:17', 0),
(112, 37, 40, 'Agente', 'Hpola si', '2025-11-04 22:43:33', 0),
(113, 37, 50, 'Agente', 'Ticket reasignado de \'Nadie\' a \'esteban supervisor\' por Alejandro.', '2025-11-05 03:07:57', 1),
(114, 37, 50, 'Agente', 'Ticket reasignado de \'esteban supervisor\' a \'Esteban Peña\' por Alejandro.', '2025-11-05 03:09:31', 1),
(115, 37, 50, 'Agente', 'asdasd', '2025-11-05 23:47:07', 0),
(116, 37, 50, 'Agente', 'Ticket reasignado de \'Esteban Peña\' a \'esteban soporte\' por Alejandro.', '2025-11-05 23:47:19', 1),
(117, 38, 48, 'Cliente', 'Ticket creado con la siguiente descripción:\n\nAl querer ejecutar el servicio de Adobe , este lanza un mensaje de error indicando incompatibilidad del sistema', '2025-11-07 18:22:16', 0),
(118, 38, 40, 'Agente', 'Ticket reasignado de \'Nadie\' a \'Moisés Rodríguez\' por Alejandro Rivero Nilo.', '2025-11-07 19:32:28', 1),
(119, 38, 40, 'Agente', 'Costo actualizado por Alejandro Rivero Nilo:\nCosto: 50000 CLP\nEstado Facturación: Pendiente', '2025-11-07 19:32:34', 1),
(120, 38, 40, 'Agente', 'Estado cambiado a \'Resuelto\' por Alejandro Rivero Nilo.', '2025-11-07 19:32:45', 0),
(121, 38, 40, 'Agente', 'Costo actualizado por Alejandro Rivero Nilo:\nCosto: 50000 CLP\nEstado Facturación: Pagado\nMedio de Pago: Tarjeta de Crédito/Débito', '2025-11-07 19:32:52', 1),
(122, 39, 48, 'Cliente', 'Ticket creado con la siguiente descripción:\n\nasdasdaddasdsaas', '2025-11-12 02:46:53', 0),
(123, 39, 40, 'Agente', 'Costo actualizado por Alejandro Rivero Nilo:\nCosto: 110000 CLP\nEstado Facturación: Facturado', '2025-11-12 02:47:21', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizacion`
--

CREATE TABLE `cotizacion` (
  `id` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `tipo_caso` varchar(100) NOT NULL,
  `prioridad` enum('Baja','Media','Alta','Urgente') NOT NULL,
  `descripcion` text NOT NULL,
  `estado` enum('Nueva','Respondida','Cerrada') NOT NULL DEFAULT 'Nueva',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `precio_estimado` decimal(12,2) DEFAULT NULL,
  `respuesta` text DEFAULT NULL,
  `id_responsable_respuesta` int(11) DEFAULT NULL,
  `fecha_respuesta` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cotizacion`
--

INSERT INTO `cotizacion` (`id`, `id_cliente`, `tipo_caso`, `prioridad`, `descripcion`, `estado`, `fecha_creacion`, `precio_estimado`, `respuesta`, `id_responsable_respuesta`, `fecha_respuesta`) VALUES
(3, 53, 'SOFTWARE', 'Media', 'Necesito la instalación de un paquete de programas de Microsoft , incluyendo Teams y Programas de edición , Photoshop.', 'Nueva', '2025-11-07 18:21:12', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `formulario_contacto`
--

CREATE TABLE `formulario_contacto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `estado` enum('Nuevo','Respondido') NOT NULL DEFAULT 'Nuevo',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `respuesta` text DEFAULT NULL,
  `id_admin_respuesta` int(11) DEFAULT NULL,
  `fecha_respuesta` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `formulario_contacto`
--

INSERT INTO `formulario_contacto` (`id`, `nombre`, `email`, `mensaje`, `estado`, `fecha_creacion`, `respuesta`, `id_admin_respuesta`, `fecha_respuesta`) VALUES
(6, 'Alejandro Rivero Nilo', 'alejandroantorivero@gmail.com', 'ASDASDASDA', 'Respondido', '2025-11-03 20:22:15', 'ASDJHASJDASD', 40, '2025-11-03 20:22:29'),
(7, 'Prueba', 'alejandroantorivero@gmail.com', 'asdasdadsd', 'Respondido', '2025-11-05 03:10:06', 'asdasdasdasdas', 40, '2025-11-05 03:10:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_reset`
--

CREATE TABLE `password_reset` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `password_reset`
--

INSERT INTO `password_reset` (`id`, `email`, `token`, `created_at`) VALUES
(3, 'esteban.jesus.pf@gmail.com', 'c8fe186476873550b362e912df888106', '2025-10-17 05:26:57'),
(5, 'esteban.jesus.pf@gmail.com', '592f0134a242fa4723f2db6863135e67', '2025-10-17 05:31:51'),
(9, 'alejandroantorivero@gmail.com', '603823a38d3736b1d51f5166795a1999', '2025-11-01 20:21:25'),
(10, 'alejandroantorivero@gmail.com', '5121dfeb8db990fc842db2c15b77bcae', '2025-11-01 20:26:53'),
(11, 'alejandroantorivero@gmail.com', '214abaa74c788f87ce70a7f23a67ea05', '2025-11-01 20:26:58'),
(12, 'alejandroantorivero@gmail.com', 'e2b4f4d0a2a54696e80bdb92f2532c09', '2025-11-01 20:32:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre_rol`, `descripcion`) VALUES
(1, 'Administrador', 'Acceso total al sistema, gestión de usuarios y configuraciones.'),
(2, 'Agente de Soporte', 'Puede gestionar tickets asignados y ver los de su equipo.'),
(3, 'Supervisor', 'Puede ver todos los tickets y generar reportes, pero no gestiona usuarios.'),
(4, 'Cliente', 'Usuario registrado que puede crear tickets y ver su dashboard');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket`
--

CREATE TABLE `ticket` (
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
  `moneda` varchar(4) DEFAULT 'CLP',
  `estado_facturacion` enum('Pendiente','Facturado','Pagado') NOT NULL DEFAULT 'Pendiente',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `ultima_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `medio_pago` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ticket`
--

INSERT INTO `ticket` (`id_ticket`, `id_cliente`, `id_agente_asignado`, `id_tipo_caso`, `asunto`, `descripcion`, `estado`, `prioridad`, `fecha_vencimiento`, `costo`, `moneda`, `estado_facturacion`, `fecha_creacion`, `ultima_actualizacion`, `medio_pago`) VALUES
(31, 45, NULL, 2, 'Necesito Mantenimiento a un servidor', 'asdasdasddasdsaasdasd', 'Resuelto', 'Alta', NULL, 100000.00, 'CLP', 'Pagado', '2025-11-04 00:06:22', '2025-11-04 01:24:51', 'Tarjeta de Crédito/Débito'),
(32, 45, 11, 3, 'asdasd', 'asdasdasdasdasdasd', 'Resuelto', 'Urgente', NULL, 40000.00, 'CLP', 'Pagado', '2025-11-04 00:06:37', '2025-11-04 00:32:34', ''),
(33, 45, NULL, 3, 'sdadasd', 'asdasddsadas', 'Resuelto', 'Media', NULL, 1000000.00, 'CLP', 'Pagado', '2025-11-04 01:21:06', '2025-11-04 01:24:32', 'Tarjeta de Crédito/Débito'),
(34, 45, NULL, 2, 'asdasdadasdasdasd', 'asdadsdadasdd', 'Resuelto', 'Media', NULL, 1000000.00, 'CLP', 'Pagado', '2025-11-04 01:25:09', '2025-11-04 01:25:23', 'Tarjeta de Crédito/Débito'),
(35, 45, NULL, 1, 'asdasddasdasd', 'asdasdasdasdasd', 'Resuelto', 'Media', NULL, 50000.00, 'CLP', 'Pagado', '2025-11-04 01:25:44', '2025-11-04 01:26:07', 'Tarjeta de Crédito/Débito'),
(36, 45, NULL, 3, 'aaaadbbb', 'asdadasdadss', 'Abierto', 'Media', NULL, 0.00, 'CLP', 'Pendiente', '2025-11-04 22:34:29', '2025-11-04 22:34:29', NULL),
(37, 46, 13, 3, 'asdasdadsd', 'asdadasdadad', 'Abierto', 'Media', NULL, 0.00, 'CLP', 'Pendiente', '2025-11-04 22:35:52', '2025-11-05 23:47:19', NULL),
(38, 48, 15, 2, 'Problema al instalar Adobe Photoshop', 'Al querer ejecutar el servicio de Adobe , este lanza un mensaje de error indicando incompatibilidad del sistema', 'Resuelto', 'Media', NULL, 50000.00, 'CLP', 'Pagado', '2025-11-07 18:22:16', '2025-11-07 19:32:52', 'Tarjeta de Crédito/Débito'),
(39, 48, NULL, 2, 'Necesito Mantenimiento a un servidorASDASDAS', 'asdasdaddasdsaas', 'Abierto', 'Baja', NULL, 110000.00, 'CLP', 'Facturado', '2025-11-12 02:46:53', '2025-11-12 02:47:21', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ticket_evaluacion`
--

CREATE TABLE `ticket_evaluacion` (
  `id_evaluacion` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `calificacion` tinyint(1) NOT NULL COMMENT 'Calificación de 1 a 5',
  `comentario` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ticket_evaluacion`
--

INSERT INTO `ticket_evaluacion` (`id_evaluacion`, `id_ticket`, `calificacion`, `comentario`, `fecha_creacion`) VALUES
(1, 35, 1, 'adasdda', '2025-11-04 16:04:29'),
(2, 34, 1, 'asdasd', '2025-11-04 16:16:15'),
(3, 33, 1, 'asdasd', '2025-11-04 16:17:58'),
(4, 32, 5, 'asdasd', '2025-11-04 16:23:52'),
(5, 31, 5, 'adasd', '2025-11-04 16:24:28'),
(6, 38, 5, '', '2025-11-12 02:45:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipodecaso`
--

CREATE TABLE `tipodecaso` (
  `id_tipo_caso` int(11) NOT NULL,
  `nombre_tipo` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipodecaso`
--

INSERT INTO `tipodecaso` (`id_tipo_caso`, `nombre_tipo`, `descripcion`, `activo`) VALUES
(1, 'SERVIDORES DE DATOS', '', 1),
(2, 'SOFTWARE', '', 1),
(3, 'MANTENCION', '', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `ruta_foto` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `id_rol`, `nombre_completo`, `email`, `telefono`, `ruta_foto`, `password_hash`, `activo`, `fecha_creacion`) VALUES
(1, 3, 'Esteban Peña', 'esteban.jesus.pf@gmail.com', '', NULL, '$2y$10$GkH.xMT6tdmYzfi0pDzuVuRd1qVlwbAwQeIYLad8uyPb3dlueVZPa', 1, '2025-10-16 03:53:52'),
(2, 1, 'Administrador del Negocio', 'admin@correo.com', '', 'uploads/avatars/688ae90605525_usuario1.jpg', '$2y$10$AVfKtFBS05wnc99PLQwSOeakMlCYmTZ5Jk1jkaMsruN2J77fOon8i', 1, '2025-07-27 05:53:49'),
(10, 3, 'esteban soporte', 'esteban.soporte@gmail.com', '', NULL, '$2y$10$FaIvhddzXX0/VzlSWYCJEeNkK3dEZLy7rByjk05A862YKKavGgctG', 1, '2025-10-17 04:56:33'),
(12, 3, 'esteban supervisor', 'esteban.supervisor@gmail.com', '', NULL, '$2y$10$p574rE4yN7wStrP9RG2b3.Ix60kIeCqcgvS7BOBuE3di/jUEav23O', 1, '2025-10-17 18:36:15'),
(40, 1, 'Alejandro Rivero Nilo', 'alejandroantorivero@gmail.com', NULL, NULL, '$2y$10$.DAAUnTmOlVVy0yopBEGR.h2Y1Ccidm6ad2eWQy1yCMY2oKpPtlCW', 1, '2025-10-24 21:55:51'),
(50, 3, 'Alejandro', 'alej.rivero@duocuc.cl', '', NULL, '$2y$10$hLeISLM/pHCl0kLzEHDYXeQLl/kOtIbBAH/ewvkdakvwSX4XeZGMa', 1, '2025-11-04 00:05:59'),
(51, 4, 'Esteban', 'esteban@cliente.com', NULL, NULL, '$2y$10$ZND7qaZxMBgd0zHrKl8upOYNntJc/jfglKvouikg7XQQpvObkrJFK', 1, '2025-11-04 22:35:24'),
(52, 4, 'Fernando Caviedes', 'alejandro@cliente.com', '', NULL, '$2y$10$HDI6FbPbncti98HirJqAiO5kxabGxfHhI4c4dQ8vcoEGatKvQs0xa', 1, '2025-11-06 00:34:44'),
(53, 4, 'Jesus Sandoval', 'jesussgutierrez@gmail.com', NULL, NULL, '$2y$10$orVhYz8VMphnRvHJ3gXhyeoNAOZWlQLhkGH9M8hQbhCxP2nQN67X6', 1, '2025-11-07 18:14:47'),
(54, 2, 'Antonio Nilo', 'Antonio@usuario.com', '', NULL, '$2y$10$wM8IgaOWoigir9Ba75hKYOzJdemOgs9DIB2fL/PCpddrMnnCDNbHW', 1, '2025-11-07 18:17:11'),
(55, 2, 'Moisés Rodríguez', 'MoisesRcx@gmail.com', '', NULL, '$2y$10$cXsnWdUbGcD/Ynh0XwMs0uDv3ZOmMoGd491r0Y1ilb.lUwhdQeYCe', 1, '2025-11-07 18:18:34'),
(56, 4, 'jesus sandoval gutierrez', 'jesussandoval81691415@gmail.com', NULL, NULL, '$2y$10$TSQh75xybhTF4rjB3P8gSey4.AHxYCDoMQz9xB//AwXEMBDxWw1.q', 1, '2025-11-12 03:34:58'),
(57, 4, 'Alejandro Rivero Nilo', 'alejrivero@gmail.com', NULL, NULL, '$2y$10$RD9iNzTi2JyDGLvDhCm91edb2ENhmGg34anbfVrT9TdMnePa4e3ni', 1, '2025-11-13 03:30:19');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `agente`
--
ALTER TABLE `agente`
  ADD PRIMARY KEY (`id_agente`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `archivo_adjunto`
--
ALTER TABLE `archivo_adjunto`
  ADD PRIMARY KEY (`id_adjunto`),
  ADD KEY `id_ticket` (`id_ticket`),
  ADD KEY `id_comentario` (`id_comentario`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `correo_electronico` (`email`);

--
-- Indices de la tabla `comentario`
--
ALTER TABLE `comentario`
  ADD PRIMARY KEY (`id_comentario`),
  ADD KEY `id_ticket` (`id_ticket`);

--
-- Indices de la tabla `cotizacion`
--
ALTER TABLE `cotizacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_responsable_respuesta` (`id_responsable_respuesta`);

--
-- Indices de la tabla `formulario_contacto`
--
ALTER TABLE `formulario_contacto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_admin_respuesta` (`id_admin_respuesta`);

--
-- Indices de la tabla `password_reset`
--
ALTER TABLE `password_reset`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre_rol` (`nombre_rol`);

--
-- Indices de la tabla `ticket`
--
ALTER TABLE `ticket`
  ADD PRIMARY KEY (`id_ticket`),
  ADD KEY `id_cliente` (`id_cliente`),
  ADD KEY `id_agente_asignado` (`id_agente_asignado`),
  ADD KEY `id_tipo_caso` (`id_tipo_caso`);

--
-- Indices de la tabla `ticket_evaluacion`
--
ALTER TABLE `ticket_evaluacion`
  ADD PRIMARY KEY (`id_evaluacion`),
  ADD UNIQUE KEY `id_ticket_unique` (`id_ticket`);

--
-- Indices de la tabla `tipodecaso`
--
ALTER TABLE `tipodecaso`
  ADD PRIMARY KEY (`id_tipo_caso`),
  ADD UNIQUE KEY `nombre_tipo` (`nombre_tipo`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `agente`
--
ALTER TABLE `agente`
  MODIFY `id_agente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `archivo_adjunto`
--
ALTER TABLE `archivo_adjunto`
  MODIFY `id_adjunto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `cliente`
--
ALTER TABLE `cliente`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT de la tabla `comentario`
--
ALTER TABLE `comentario`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=130;

--
-- AUTO_INCREMENT de la tabla `cotizacion`
--
ALTER TABLE `cotizacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `formulario_contacto`
--
ALTER TABLE `formulario_contacto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `password_reset`
--
ALTER TABLE `password_reset`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ticket`
--
ALTER TABLE `ticket`
  MODIFY `id_ticket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `ticket_evaluacion`
--
ALTER TABLE `ticket_evaluacion`
  MODIFY `id_evaluacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tipodecaso`
--
ALTER TABLE `tipodecaso`
  MODIFY `id_tipo_caso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `agente`
--
ALTER TABLE `agente`
  ADD CONSTRAINT `agente_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `archivo_adjunto`
--
ALTER TABLE `archivo_adjunto`
  ADD CONSTRAINT `fk_adjunto_comentario` FOREIGN KEY (`id_comentario`) REFERENCES `comentario` (`id_comentario`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_adjunto_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `ticket` (`id_ticket`) ON DELETE CASCADE;

--
-- Filtros para la tabla `comentario`
--
ALTER TABLE `comentario`
  ADD CONSTRAINT `fk_comentario_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `ticket` (`id_ticket`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cotizacion`
--
ALTER TABLE `cotizacion`
  ADD CONSTRAINT `cotizaciones_fk_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `cotizaciones_fk_responsable` FOREIGN KEY (`id_responsable_respuesta`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `formulario_contacto`
--
ALTER TABLE `formulario_contacto`
  ADD CONSTRAINT `contact_messages_ibfk_1` FOREIGN KEY (`id_admin_respuesta`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL;

--
-- Filtros para la tabla `ticket`
--
ALTER TABLE `ticket`
  ADD CONSTRAINT `fk_ticket_agente` FOREIGN KEY (`id_agente_asignado`) REFERENCES `agente` (`id_agente`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_ticket_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ticket_tipocaso` FOREIGN KEY (`id_tipo_caso`) REFERENCES `tipodecaso` (`id_tipo_caso`) ON DELETE SET NULL;

--
-- Filtros para la tabla `ticket_evaluacion`
--
ALTER TABLE `ticket_evaluacion`
  ADD CONSTRAINT `fk_evaluacion_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `ticket` (`id_ticket`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
