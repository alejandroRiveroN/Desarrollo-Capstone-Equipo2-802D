-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: soporte_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `agentes`
--

DROP TABLE IF EXISTS `agentes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `agentes` (
  `id_agente` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `puesto` varchar(50) DEFAULT NULL,
  `fecha_contratacion` date DEFAULT NULL,
  PRIMARY KEY (`id_agente`),
  UNIQUE KEY `id_usuario` (`id_usuario`),
  CONSTRAINT `agentes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `agentes`
--

LOCK TABLES `agentes` WRITE;
/*!40000 ALTER TABLE `agentes` DISABLE KEYS */;
INSERT INTO `agentes` VALUES (4,2,'Administrador','2025-08-29'),(5,6,'Soporte nivel 3','2025-09-30'),(9,10,'Soporte nivel junior','2025-10-17'),(11,12,'Supervisor en practica','2025-10-17');
/*!40000 ALTER TABLE `agentes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `archivos_adjuntos`
--

DROP TABLE IF EXISTS `archivos_adjuntos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archivos_adjuntos` (
  `id_adjunto` int(11) NOT NULL AUTO_INCREMENT,
  `id_ticket` int(11) NOT NULL,
  `id_comentario` int(11) DEFAULT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_guardado` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `tipo_mime` varchar(100) NOT NULL,
  `fecha_subida` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_adjunto`),
  KEY `id_ticket` (`id_ticket`),
  KEY `id_comentario` (`id_comentario`),
  CONSTRAINT `fk_adjunto_comentario` FOREIGN KEY (`id_comentario`) REFERENCES `comentarios` (`id_comentario`) ON DELETE CASCADE,
  CONSTRAINT `fk_adjunto_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_ticket`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `archivos_adjuntos`
--

LOCK TABLES `archivos_adjuntos` WRITE;
/*!40000 ALTER TABLE `archivos_adjuntos` DISABLE KEYS */;
/*!40000 ALTER TABLE `archivos_adjuntos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `clientes`
--

DROP TABLE IF EXISTS `clientes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `empresa` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `telegram` varchar(50) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_cliente`),
  UNIQUE KEY `correo_electronico` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `clientes`
--

LOCK TABLES `clientes` WRITE;
/*!40000 ALTER TABLE `clientes` DISABLE KEYS */;
INSERT INTO `clientes` VALUES (1,'CLIENTE 1','cliente1@correo.com','12121212','EMPRESA 1',NULL,NULL,NULL,NULL,1,'2025-08-29 14:22:30'),(3,'Profesor','profesor@correo.com',NULL,'DUOC','chile','melipilla','912342678',NULL,1,'2025-10-17 19:58:12'),(31,'Cliente esteban','cliente.esteban@correo.com','+56928374657','TebiCompany','Peru','Lima','+56928374657','Stephenn',1,'2025-10-23 14:32:19');
/*!40000 ALTER TABLE `clientes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comentarios`
--

DROP TABLE IF EXISTS `comentarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `comentarios` (
  `id_comentario` int(11) NOT NULL AUTO_INCREMENT,
  `id_ticket` int(11) NOT NULL,
  `id_autor` int(11) NOT NULL,
  `tipo_autor` enum('Agente','Cliente') NOT NULL,
  `comentario` text NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `es_privado` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id_comentario`),
  KEY `id_ticket` (`id_ticket`),
  CONSTRAINT `fk_comentario_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_ticket`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=61 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comentarios`
--

LOCK TABLES `comentarios` WRITE;
/*!40000 ALTER TABLE `comentarios` DISABLE KEYS */;
INSERT INTO `comentarios` VALUES (6,2,1,'Cliente','Ticket creado con la siguiente descripción:\n\nfalla en la conexión entre el servidor del cliente y las camaras instaladas en el recinto','2025-09-30 17:26:39',0),(7,2,5,'Agente','Estado cambiado a \'Resuelto\' por Alejandro.','2025-09-30 17:26:49',0),(8,2,6,'Agente','Se actualizaron los detalles de facturación por Esteban Peña:\n- Costo cambiado de \'0.00\' a \'40.00\'.\n- Moneda cambiada de \'USD\' a \'CLP\'.','2025-10-01 07:41:39',1),(58,23,31,'Cliente','Ticket creado con la siguiente descripción:\n\ninstalar minecraft','2025-10-23 17:14:11',0),(59,23,0,'Agente','hola apuren','2025-10-23 17:16:40',0),(60,23,0,'Agente','yapo apuren','2025-10-23 17:20:39',0);
/*!40000 ALTER TABLE `comentarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_resets`
--

LOCK TABLES `password_resets` WRITE;
/*!40000 ALTER TABLE `password_resets` DISABLE KEYS */;
INSERT INTO `password_resets` VALUES (3,'esteban.jesus.pf@gmail.com','c8fe186476873550b362e912df888106','2025-10-17 05:26:57'),(5,'esteban.jesus.pf@gmail.com','592f0134a242fa4723f2db6863135e67','2025-10-17 05:31:51');
/*!40000 ALTER TABLE `password_resets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre_rol` (`nombre_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador','Acceso total al sistema, gestión de usuarios y configuraciones.'),(2,'Agente de Soporte','Puede gestionar tickets asignados y ver los de su equipo.'),(3,'Supervisor','Puede ver todos los tickets y generar reportes, pero no gestiona usuarios.'),(4,'Cliente','Usuario registrado que puede crear tickets y ver su dashboard');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tickets` (
  `id_ticket` int(11) NOT NULL AUTO_INCREMENT,
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
  `medio_pago` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_ticket`),
  KEY `id_cliente` (`id_cliente`),
  KEY `id_agente_asignado` (`id_agente_asignado`),
  KEY `id_tipo_caso` (`id_tipo_caso`),
  CONSTRAINT `fk_ticket_agente` FOREIGN KEY (`id_agente_asignado`) REFERENCES `agentes` (`id_agente`) ON DELETE SET NULL,
  CONSTRAINT `fk_ticket_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`) ON DELETE CASCADE,
  CONSTRAINT `fk_ticket_tipocaso` FOREIGN KEY (`id_tipo_caso`) REFERENCES `tiposdecaso` (`id_tipo_caso`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tickets`
--

LOCK TABLES `tickets` WRITE;
/*!40000 ALTER TABLE `tickets` DISABLE KEYS */;
INSERT INTO `tickets` VALUES (2,1,NULL,1,'Mantenimiento servidor de Camaras DHCP','falla en la conexión entre el servidor del cliente y las camaras instaladas en el recinto','Resuelto','Urgente',NULL,50.00,'CLP','Facturado','2025-09-30 17:26:39','2025-10-17 03:22:15',NULL),(23,31,NULL,3,'computadores juna','instalar minecraft','Abierto','Urgente',NULL,0.00,'USD','Pendiente','2025-10-23 17:14:11','2025-10-23 17:14:11',NULL);
/*!40000 ALTER TABLE `tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tiposdecaso`
--

DROP TABLE IF EXISTS `tiposdecaso`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tiposdecaso` (
  `id_tipo_caso` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_tipo` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_tipo_caso`),
  UNIQUE KEY `nombre_tipo` (`nombre_tipo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tiposdecaso`
--

LOCK TABLES `tiposdecaso` WRITE;
/*!40000 ALTER TABLE `tiposdecaso` DISABLE KEYS */;
INSERT INTO `tiposdecaso` VALUES (1,'SERVIDORES DE DATOS','',1),(2,'SOFTWARE','',1),(3,'MANTENCION','',1);
/*!40000 ALTER TABLE `tiposdecaso` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `id_rol` int(11) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `whatsapp` varchar(20) DEFAULT NULL,
  `telegram` varchar(50) DEFAULT NULL,
  `ruta_foto` varchar(255) DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `email` (`email`),
  KEY `id_rol` (`id_rol`),
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,1,'Esteban Peña','esteban.jesus.pf@gmail.com',NULL,'+56912345678',NULL,NULL,'$2y$10$GkH.xMT6tdmYzfi0pDzuVuRd1qVlwbAwQeIYLad8uyPb3dlueVZPa',1,'2025-10-16 03:53:52'),(2,1,'Administrador del Negocio','admin@correo.com','','','','uploads/avatars/688ae90605525_usuario1.jpg','$2y$10$AVfKtFBS05wnc99PLQwSOeakMlCYmTZ5Jk1jkaMsruN2J77fOon8i',1,'2025-07-27 05:53:49'),(6,3,'Alejandro','alejandro@correo.com','','','',NULL,'$2y$10$RTGi8Rt35ECqlbEPe.KDb.tUm/0YAuIm3I3JsTR.trzVpx4vi7bMC',1,'2025-09-30 17:25:31'),(10,2,'esteban soporte','esteban.soporte@gmail.com','','','',NULL,'$2y$10$FaIvhddzXX0/VzlSWYCJEeNkK3dEZLy7rByjk05A862YKKavGgctG',1,'2025-10-17 04:56:33'),(12,3,'esteban supervisor','esteban.supervisor@gmail.com','','','',NULL,'$2y$10$p574rE4yN7wStrP9RG2b3.Ix60kIeCqcgvS7BOBuE3di/jUEav23O',1,'2025-10-17 18:36:15'),(38,4,'Cliente esteban','cliente.esteban@correo.com',NULL,NULL,NULL,NULL,'$2y$10$2VKrdwaI/BVRUbCFKKfyUulgfpVAlD9yO02n/zzT.g6UE2R.p3Bz2',1,'2025-10-23 14:32:19');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-23 21:28:00
