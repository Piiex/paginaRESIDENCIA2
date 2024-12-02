-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-10-2024 a las 03:46:37
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `case2`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asesorias_documentos`
--

CREATE TABLE `asesorias_documentos` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` varchar(50) DEFAULT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `comment` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

CREATE TABLE `docentes` (
  `id` int(11) NOT NULL,
  `especialidad` varchar(255) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `docentes`
--

INSERT INTO `docentes` (`id`, `especialidad`, `usuario_id`) VALUES
(1, 'Matemáticas', 2),
(2, 'Física', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docente_semestre`
--

CREATE TABLE `docente_semestre` (
  `docente_id` int(11) NOT NULL,
  `semestre_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `docente_semestre`
--

INSERT INTO `docente_semestre` (`docente_id`, `semestre_id`) VALUES
(1, 1),
(2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documentos`
--

CREATE TABLE `documentos` (
  `id` int(11) NOT NULL,
  `titulo` varchar(255) DEFAULT NULL,
  `ruta` varchar(255) DEFAULT NULL,
  `fecha_subida` datetime DEFAULT current_timestamp(),
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL,
  `asesorias` tinyint(4) DEFAULT 0,
  `tutorias` tinyint(4) DEFAULT 0,
  `residencia_profesional` tinyint(4) DEFAULT 0,
  `educacion_dual` tinyint(4) DEFAULT 0,
  `trayectoria_escolar` tinyint(4) DEFAULT 0,
  `investigacion` tinyint(4) DEFAULT 0,
  `atributos_egreso` tinyint(4) DEFAULT 0,
  `acuerdos_academia` tinyint(4) DEFAULT 0,
  `instrumentacion_didactica` tinyint(4) DEFAULT 0,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `semestre`
--

CREATE TABLE `semestre` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `semestre`
--

INSERT INTO `semestre` (`id`, `nombre`) VALUES
(1, 'Semestre 1'),
(2, 'Semestre 2');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tutorias_documentos`
--

CREATE TABLE `tutorias_documentos` (
  `id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` enum('pdf','docx','pptx') NOT NULL,
  `upload_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `comment` text DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `correo` varchar(255) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `estado` enum('activo','inactivo') DEFAULT 'activo',
  `role` enum('docente','admin') NOT NULL,
  `permissions` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `clave`, `estado`, `role`, `permissions`) VALUES
(1, 'Demetrio', 'demetrio@example.com', '$2y$10$r3M0BNXiFpA9QBqL7/Unt.01QXkvHYsQTFqI.dps6mkFx/oSlY.6u', 'activo', 'admin', NULL),
(2, 'Arisbeth Alvares Marin', 'arisbeth@example.com', '$2y$10$r3M0BNXiFpA9QBqL7/Unt.01QXkvHYsQTFqI.dps6mkFx/oSlY.6u', 'activo', 'docente', '{\"tutorias\":\"1\",\"asesorias\":\"1\",\"educacion_dual\":\"1\",\"residencia_profesional\":\"1\",\"instrumentacion_didactica\":\"1\",\"trayectoria_escolar\":\"1\",\"atributos_egreso\":\"1\",\"investigacion\":\"1\",\"acuerdos_academia\":\"1\"}'),
(3, 'Maria Guadalupe', 'maria.guadalupe@example.com', '$2y$10$r3M0BNXiFpA9QBqL7/Unt.01QXkvHYsQTFqI.dps6mkFx/oSlY.6u', 'activo', 'docente', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `asesorias_documentos`
--
ALTER TABLE `asesorias_documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `docente_semestre`
--
ALTER TABLE `docente_semestre`
  ADD PRIMARY KEY (`docente_id`,`semestre_id`),
  ADD KEY `semestre_id` (`semestre_id`);

--
-- Indices de la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `semestre`
--
ALTER TABLE `semestre`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tutorias_documentos`
--
ALTER TABLE `tutorias_documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `asesorias_documentos`
--
ALTER TABLE `asesorias_documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `docentes`
--
ALTER TABLE `docentes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `documentos`
--
ALTER TABLE `documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `semestre`
--
ALTER TABLE `semestre`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tutorias_documentos`
--
ALTER TABLE `tutorias_documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asesorias_documentos`
--
ALTER TABLE `asesorias_documentos`
  ADD CONSTRAINT `asesorias_documentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD CONSTRAINT `docentes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `docente_semestre`
--
ALTER TABLE `docente_semestre`
  ADD CONSTRAINT `docente_semestre_ibfk_1` FOREIGN KEY (`docente_id`) REFERENCES `docentes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `docente_semestre_ibfk_2` FOREIGN KEY (`semestre_id`) REFERENCES `semestre` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `documentos`
--
ALTER TABLE `documentos`
  ADD CONSTRAINT `documentos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD CONSTRAINT `permisos_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tutorias_documentos`
--
ALTER TABLE `tutorias_documentos`
  ADD CONSTRAINT `tutorias_documentos_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
