-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 07-06-2025 a las 05:56:27
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
-- Base de datos: `bd_tamanaco`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `id_actividad` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `descripcion_actividad` varchar(255) NOT NULL,
  `fecha_realizar` date NOT NULL,
  `tiempo_invertido` varchar(255) NOT NULL,
  `minutos_invertidos` varchar(255) NOT NULL,
  `hora_finalizacion` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `actividades`
--

INSERT INTO `actividades` (`id_actividad`, `tarea_id`, `descripcion_actividad`, `fecha_realizar`, `tiempo_invertido`, `minutos_invertidos`, `hora_finalizacion`) VALUES
(19, 147, '123', '2025-06-06', '123', '11', '21:57:00'),
(20, 147, 'asd', '2025-06-15', '10', '19', '12:11:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `almacen`
--

CREATE TABLE `almacen` (
  `id_almacen` int(11) NOT NULL,
  `id_sede` int(11) DEFAULT NULL,
  `id_sucursal` int(11) DEFAULT NULL,
  `id_status` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `almacen`
--

INSERT INTO `almacen` (`id_almacen`, `id_sede`, `id_sucursal`, `id_status`, `nombre`, `fecha_creacion`) VALUES
(1, NULL, 1, 1, 'ALMACEN PRINCIPAL', '2025-06-02 21:59:46'),
(10, NULL, 1, 1, 'ALMACEN ACARIGUA', '2025-06-04 18:43:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `articulo`
--

CREATE TABLE `articulo` (
  `id_articulo` int(11) NOT NULL,
  `nombre_articulo` varchar(100) NOT NULL,
  `descripcion_articulo` text DEFAULT NULL,
  `fecha_ingreso` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `articulo`
--

INSERT INTO `articulo` (`id_articulo`, `nombre_articulo`, `descripcion_articulo`, `fecha_ingreso`) VALUES
(1, 'Pelota de Béisbol', 'Pelota estándar para juegos profesionales de béisbol', '2025-03-10 00:00:00'),
(2, 'Raqueta de Tenis', 'Raqueta diseñada para jugadores avanzados y profesionales', '2025-03-15 00:00:00'),
(3, 'Balón de Fútbol', 'Balón tamaño oficial FIFA, adecuado para partidos internacionales', '2025-03-20 00:00:00'),
(4, 'Guantes de Boxeo', 'Guantes acolchados para entrenamientos y competencias de boxeo', '2025-03-22 00:00:00'),
(5, 'Set de Pesas', 'Conjunto de pesas ajustables para entrenamiento físico', '2025-03-25 00:00:00'),
(6, 'Uniforme Deportivo', 'Ropa deportiva diseñada para equipos de alto rendimiento', '2025-03-30 00:00:00'),
(7, 'Tablas de Surf', 'Tablas ideales para practicar surf en cualquier nivel de experiencia', '2025-04-02 00:00:00'),
(8, 'Casco de Ciclismo', 'Casco aerodinámico con protección avanzada para ciclistas', '2025-04-05 00:00:00'),
(9, 'Patines en Línea', 'Patines duraderos diseñados para actividades recreativas y deportivas', '2025-04-10 00:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `blog`
--

CREATE TABLE `blog` (
  `id_blog` int(11) NOT NULL,
  `fecha_blog` datetime NOT NULL,
  `titulo` varchar(250) NOT NULL,
  `descripcion` text NOT NULL,
  `nombre_img` varchar(150) NOT NULL,
  `url` varchar(250) NOT NULL,
  `id_perfil` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `blog`
--

INSERT INTO `blog` (`id_blog`, `fecha_blog`, `titulo`, `descripcion`, `nombre_img`, `url`, `id_perfil`, `id_usuario`, `id_status`) VALUES
(1, '2025-05-31 00:00:00', 'Innovación que impulsa a Venezuela: Así trabaja Tamanaco cada día', 'En Tamanaco, creemos que el progreso nace de la dedicación, la tecnología y el talento local. Cada proceso, cada proyecto y cada solución están diseñados para fortalecer nuestras industrias y construir un futuro más próspero. Descubre cómo trabajamos con pasión y excelencia.', '20250530_2116_image (1).png', 'servidor_img/home/20250530_2116_image (1).png', 1, 1, 1),
(5, '2025-05-31 00:00:00', 'Historias que inspiran: Nuestro equipo, nuestro orgullo', 'Detrás de cada logro de Tamanaco hay personas con historias increíbles. Hoy queremos compartir cómo el compromiso y la experiencia de nuestro equipo hacen posible que sigamos siendo referentes en el país. Ellos son el verdadero motor de nuestro éxito.', '20250530_2122_image.png', 'servidor_img/home/20250530_2122_image.png', 1, 1, 1),
(6, '2025-05-31 00:00:00', 'Cuidar lo que somos: Mantenimiento que garantiza calidad', 'En Tamanaco Sport, sabemos que la excelencia no es casualidad: se construye con constancia, compromiso y cuidado. Por eso, nuestro equipo de mantenimiento trabaja día a día para asegurar que cada máquina, herramienta y espacio esté en condiciones óptimas. Este esfuerzo silencioso es parte esencial de lo que nos permite ofrecer productos de calidad, hechos con orgullo venezolano.', '20250530_2124_image.png', 'servidor_img/home/20250530_2124_image.png', 1, 1, 1),
(7, '2025-05-31 00:00:00', 'Más allá del juego: el sueño deportivo de Venezuela', 'En Tamanaco creemos que el deporte transforma vidas. Por eso, cada pelota, cada guante, cada uniforme que fabricamos, lleva detrás la ilusión de ver crecer a una nueva generación de atletas venezolanos. Esta es la historia de quienes no se rinden, de quienes entrenan cada día con pasión, y de cómo Tamanaco los acompaña en ese viaje. Nuestro compromiso es sembrar esperanza y construir futuro a través del deporte.', '20250530_2129_image.png', 'servidor_img/home/20250530_2129_image.png', 1, 1, 1),
(8, '2025-05-31 00:00:00', 'Raíces fuertes, futuro brillante: Tamanaco es Venezuela', 'Desde nuestras raíces hasta cada rincón del país, en Tamanaco llevamos con orgullo el nombre de Venezuela. Nos inspira la cultura, la pasión y la fuerza de nuestra gente. Cada producto que sale de nuestras manos no es solo un implemento deportivo: es identidad, es historia, es un paso más hacia el desarrollo de talentos nacionales. Porque creemos que el verdadero progreso comienza cuando invertimos en lo que somos y lo que podemos ser.', '20250530_2131_image.png', 'servidor_img/home/20250530_2131_image.png', 1, 1, 1),
(9, '2025-05-31 00:00:00', 'Tecnología al servicio del rendimiento: por qué Tamanaco apuesta por un sistema de gestión', 'En Tamanaco creemos que el orden, la eficiencia y la trazabilidad son claves para el crecimiento. Por eso, implementamos un sistema de software que permite llevar el control de nuestros inventarios, mantenimientos, tareas y recursos en tiempo real. Esta herramienta no solo mejora la productividad del equipo, sino que garantiza que la información esté siempre disponible, actualizada y segura. Apostar por tecnología es apostar por el futuro de la industria nacional.', '20250530_2140_image.png', 'servidor_img/home/20250530_2140_image.png', 1, 1, 1),
(10, '2025-05-31 00:00:00', 'El Poder del Diamante: La Pasión de un Verdadero Beisbolista', 'La dedicación, la fuerza y la precisión definen a los grandes del béisbol. En cada swing, en cada atrapada espectacular, se refleja el esfuerzo de horas de entrenamiento y el amor por el deporte. Un verdadero beisbolista no solo juega, vive el béisbol con intensidad, dejando todo en el diamante.\r\n', '20250531_1242_image.png', 'servidor_img/home/20250531_1242_image.png', 1, 1, 1),
(11, '2025-05-31 00:00:00', 'Determinación en Cada Golpe: La Fuerza de una Tenista', 'Con cada saque, cada volea y cada sprint, demuestra su pasión y entrega en la cancha. La precisión de su juego no es solo técnica, es el reflejo de su dedicación y constancia. Una tenista no solo golpea la pelota, rompe límites, desafía adversidades y lucha por la victoria.', '20250531_1259_image.png', 'servidor_img/home/20250531_1259_image.png', 1, 1, 1),
(12, '2025-05-31 00:00:00', 'Marca en Cada Juego: Patrocinio Oficial de Pelotas de Béisbol', 'Haz que tu marca esté presente en cada partido, cada jugada y cada victoria. Con un patrocinio exclusivo en pelotas de béisbol, te aseguras de estar en el centro de la acción, donde la pasión por el juego cobra vida. Una oportunidad única para conectar con deportistas y fanáticos, fortaleciendo tu identidad en cada lanzamiento.', '20250531_1254_image.png', 'servidor_img/home/20250531_1254_image.png', 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calendario`
--

CREATE TABLE `calendario` (
  `id_evento` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL,
  `titulo` varchar(100) NOT NULL,
  `tipo_evento_id` int(11) NOT NULL,
  `status_id` int(11) NOT NULL,
  `trigger_tipo` enum('fecha_fija','por_finalizacion') DEFAULT 'fecha_fija',
  `repeticion` enum('diaria','semanal','mensual','anual','ninguno') DEFAULT 'ninguno',
  `frecuencia` int(11) DEFAULT 1,
  `unidad_frecuencia` enum('dia','semana','mes','año') DEFAULT 'mes',
  `dias_seleccionados` varchar(255) DEFAULT NULL,
  `semana_del_mes` enum('primera','segunda','tercera','cuarta') DEFAULT NULL,
  `dia_del_mes` tinyint(4) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `tarea_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `calendario`
--

INSERT INTO `calendario` (`id_evento`, `fecha_inicio`, `fecha_fin`, `hora_inicio`, `hora_fin`, `titulo`, `tipo_evento_id`, `status_id`, `trigger_tipo`, `repeticion`, `frecuencia`, `unidad_frecuencia`, `dias_seleccionados`, `semana_del_mes`, `dia_del_mes`, `descripcion`, `tarea_id`) VALUES
(26, '2025-06-05', '2025-06-06', NULL, NULL, 'Cambio y Lubricación de Rodillos', 1, 1, 'fecha_fija', 'ninguno', 1, 'mes', NULL, NULL, NULL, NULL, 145),
(27, '2025-06-06', '2025-06-08', NULL, NULL, 'Cambio y Lubricación de Rodillos', 1, 1, 'fecha_fija', 'ninguno', 1, 'mes', NULL, NULL, NULL, NULL, 146),
(28, '2025-06-15', '2025-06-19', NULL, NULL, 'Cambio y Lubricación de Rodillos', 1, 1, 'fecha_fija', 'ninguno', 1, 'mes', NULL, NULL, NULL, NULL, 147),
(29, '2025-06-06', '2025-06-07', NULL, NULL, 'Cambio y Lubricación de Rodillos', 1, 1, 'fecha_fija', 'ninguno', 1, 'mes', NULL, NULL, NULL, NULL, 148),
(30, '2025-08-07', '2025-08-08', NULL, NULL, 'Cambio y Lubricación de Rodillos', 1, 1, 'fecha_fija', 'ninguno', 1, 'mes', NULL, NULL, NULL, NULL, 150),
(31, '2025-06-13', '2025-06-15', NULL, NULL, 'Cambio y Lubricación de Rodillos', 1, 1, 'fecha_fija', 'ninguno', 1, 'mes', NULL, NULL, NULL, NULL, 151),
(32, '2025-06-06', '2025-06-08', NULL, NULL, 'Prueba ', 1, 1, 'fecha_fija', 'ninguno', 1, 'mes', NULL, NULL, NULL, NULL, 152),
(33, '2025-06-11', '2025-06-13', NULL, NULL, 'Prueba ', 1, 1, 'fecha_fija', 'ninguno', 1, 'mes', NULL, NULL, NULL, NULL, 153);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `caracteristicas_maquina`
--

CREATE TABLE `caracteristicas_maquina` (
  `id_caracteristica` int(11) NOT NULL,
  `id_maquina` int(11) NOT NULL,
  `nombre_caracteristica` varchar(255) NOT NULL,
  `descripcion_caracteristica` varchar(255) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `caracteristicas_maquina`
--

INSERT INTO `caracteristicas_maquina` (`id_caracteristica`, `id_maquina`, `nombre_caracteristica`, `descripcion_caracteristica`, `fecha_creacion`) VALUES
(33, 27, 'Potencia', '60', '2025-06-06 03:26:40'),
(34, 27, 'Amperaje', '90/140', '2025-06-06 03:26:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargo`
--

CREATE TABLE `cargo` (
  `id_cargo` int(11) NOT NULL,
  `nombre_cargo` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `date_create` datetime DEFAULT current_timestamp(),
  `status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cargo`
--

INSERT INTO `cargo` (`id_cargo`, `nombre_cargo`, `descripcion`, `date_create`, `status`) VALUES
(1, 'GERENTE GENERALES', 'Responsable de la administración y dirección global de la empresa', '2025-04-02 12:42:14', 1),
(2, 'ANALISTA FINANCIERO', 'ENCARGADO DE ANALIZAR LA SITUACIÓN ECONÓMICA Y PRESUPUESTARIA', '2025-04-02 12:42:14', 1),
(3, 'DESARROLLADOR DE SOFTWARE', 'DISEÑA, DESARROLLA Y MANTIENE APLICACIONES Y SISTEMAS TECNOLÓGICOS', '2025-04-02 12:42:14', 1),
(4, 'ASISTENTE ADMINISTRATIVO', 'APOYA EN TAREAS ORGANIZATIVAS Y ADMINISTRATIVAS DE LA OFICINA', '2025-04-02 12:42:14', 1),
(5, 'JEFE DE VENTAS', 'SUPERVISA LAS ESTRATEGIAS DE VENTAS Y COORDINA AL EQUIPO COMERCIAL', '2025-04-02 12:42:14', 1),
(6, 'SUPERVISOR DE MANTENIMIENTO', 'COORDINA Y SUPERVISA LOS PROCESOS DE PRODUCCIÓN EN PLANTA', '2025-04-02 12:42:14', 1),
(7, 'ESPECIALISTA EN MARKETING', 'PLANEA Y EJECUTA ESTRATEGIAS PUBLICITARIAS Y CAMPAÑAS DE MARKETING', '2025-04-02 12:42:14', 1),
(8, 'RECURSOS HUMANOS', 'MANEJA LA SELECCIÓN, FORMACIÓN Y BIENESTAR DEL PERSONAL', '2025-04-02 12:42:14', 1),
(9, 'TÉCNICO DE SOPORTE', 'BRINDA ASISTENCIA TÉCNICA Y SOLUCIÓN DE PROBLEMAS RELACIONADOS CON TI', '2025-04-02 12:42:14', 1),
(10, 'JEFE DE MANTENIMIENTO', 'LIDER', '2025-04-02 18:43:01', 1),
(11, 'MECÁNICO', 'HACE MANTENIMIENTO', '2025-04-17 12:10:23', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clasificacion`
--

CREATE TABLE `clasificacion` (
  `id_clasificacion` int(11) NOT NULL,
  `nombre_clasificacion` varchar(100) NOT NULL,
  `abreviacion_clasificacion` varchar(10) NOT NULL,
  `id_status` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clasificacion`
--

INSERT INTO `clasificacion` (`id_clasificacion`, `nombre_clasificacion`, `abreviacion_clasificacion`, `id_status`, `fecha_creacion`) VALUES
(42, 'METRO', 'M', 1, '2025-05-31 18:16:34'),
(43, 'CENTÍMETRO', 'CM', 1, '2025-05-31 18:16:45'),
(44, 'MILÍMETRO', 'MM', 1, '2025-05-31 18:16:57'),
(45, 'MICRÓMETRO', 'MMT', 1, '2025-05-31 18:19:03'),
(46, 'NANÓMETRO', 'NM', 1, '2025-05-31 18:19:13'),
(47, 'PULGADA', 'IN', 1, '2025-05-31 18:19:25'),
(48, 'PIE', 'FT', 1, '2025-05-31 18:19:35'),
(49, 'YARDA', 'YD', 1, '2025-05-31 18:19:47'),
(50, 'MILLA', 'MI', 1, '2025-05-31 18:20:05'),
(51, 'KILÓMETRO', 'KM', 1, '2025-05-31 18:20:17'),
(52, 'METRO CÚBICO', 'MC', 1, '2025-05-31 18:21:19'),
(53, 'LITRO', 'L', 1, '2025-05-31 18:21:30'),
(54, 'MILILITRO', 'ML', 1, '2025-05-31 18:21:42'),
(55, 'GALÓN', 'GAL', 1, '2025-05-31 18:22:11'),
(56, 'BARRIL', 'BBL', 1, '2025-05-31 18:22:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `codigo`
--

CREATE TABLE `codigo` (
  `id` int(11) NOT NULL,
  `codigo` varchar(20) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `codigo`
--

INSERT INTO `codigo` (`id`, `codigo`, `fecha_creacion`) VALUES
(3, 'SERV-001', '2025-06-06 14:45:17'),
(4, 'HERR-002', '2025-06-06 14:45:17'),
(5, 'REP-003', '2025-06-06 14:45:17'),
(6, 'PROD-004', '2025-06-06 14:45:17'),
(7, 'COMP-005', '2025-06-06 14:45:17'),
(15, 'COMP-008', '2025-06-06 18:41:09'),
(16, 'HERR-008', '2025-06-06 18:41:09'),
(17, 'REP-034', '2025-06-06 18:41:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id_compra` int(11) NOT NULL,
  `codigo_compra` varchar(20) NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `id_usuario_solicitante` int(11) DEFAULT NULL,
  `id_usuario_aprobador` int(11) DEFAULT NULL,
  `id_status` int(11) NOT NULL DEFAULT 1,
  `total_productos` int(11) DEFAULT 0,
  `total_precio` decimal(10,2) DEFAULT 0.00,
  `fecha_compra` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id_compra`, `codigo_compra`, `id_solicitud`, `id_proveedor`, `id_usuario_solicitante`, `id_usuario_aprobador`, `id_status`, `total_productos`, `total_precio`, `fecha_compra`) VALUES
(4, 'COMP-008', 34, 19, 1, NULL, 1, 2, 40.00, '2025-06-06 22:41:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra_herramienta`
--

CREATE TABLE `compra_herramienta` (
  `id_compra_herramienta` int(11) NOT NULL,
  `codigo_herramienta` varchar(20) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `id_herramienta` int(11) NOT NULL,
  `cantidad_total` int(11) NOT NULL,
  `cantidad_individual` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compra_herramienta`
--

INSERT INTO `compra_herramienta` (`id_compra_herramienta`, `codigo_herramienta`, `id_compra`, `id_herramienta`, `cantidad_total`, `cantidad_individual`, `precio_unitario`, `precio_total`) VALUES
(2, 'HERR-008', 4, 8, 2, 2, 10.00, 20.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra_producto`
--

CREATE TABLE `compra_producto` (
  `id_compra_producto` int(11) NOT NULL,
  `codigo_producto` varchar(20) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad_total` int(11) NOT NULL,
  `cantidad_individual` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compra_repuesto`
--

CREATE TABLE `compra_repuesto` (
  `id_compra_repuesto` int(11) NOT NULL,
  `codigo_repuesto` varchar(20) NOT NULL,
  `id_compra` int(11) NOT NULL,
  `id_repuesto` int(11) NOT NULL,
  `cantidad_total` int(11) NOT NULL,
  `cantidad_individual` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `precio_total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `compra_repuesto`
--

INSERT INTO `compra_repuesto` (`id_compra_repuesto`, `codigo_repuesto`, `id_compra`, `id_repuesto`, `cantidad_total`, `cantidad_individual`, `precio_unitario`, `precio_total`) VALUES
(1, 'REP-034', 4, 34, 2, 2, 10.00, 20.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cotizacion`
--

CREATE TABLE `cotizacion` (
  `id_cotizacion` int(11) NOT NULL,
  `reporte_id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `descripcion` text NOT NULL,
  `total_estimado` decimal(10,2) NOT NULL,
  `fecha_cotizacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `dispositivos`
--

CREATE TABLE `dispositivos` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `device_id` varchar(64) DEFAULT NULL,
  `fecha` datetime DEFAULT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa`
--

CREATE TABLE `empresa` (
  `id_empresa` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `telefono_1` varchar(50) NOT NULL,
  `telefono_2` varchar(50) DEFAULT NULL,
  `correo_1` varchar(100) NOT NULL,
  `correo_2` varchar(100) DEFAULT NULL,
  `facebook` varchar(100) DEFAULT NULL,
  `instagram` varchar(100) DEFAULT NULL,
  `twitter` varchar(100) DEFAULT NULL,
  `youtube` varchar(100) DEFAULT NULL,
  `whatsapp` varchar(100) DEFAULT NULL,
  `rif` varchar(100) NOT NULL,
  `numero_rif` varchar(100) NOT NULL,
  `tipo_empresa` varchar(100) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `codigo_postal` varchar(155) NOT NULL,
  `historia` text DEFAULT NULL,
  `vision` text DEFAULT NULL,
  `mision` varchar(255) NOT NULL,
  `objetivo_general` text DEFAULT NULL,
  `objetivos_especificos` text DEFAULT NULL,
  `eslogan_1` varchar(255) DEFAULT NULL,
  `eslogan_2` varchar(255) DEFAULT NULL,
  `ubicacion_pais_id` int(11) DEFAULT NULL,
  `ubicacion_estado_id` int(155) DEFAULT NULL,
  `status_id` int(155) NOT NULL,
  `url` varchar(255) NOT NULL,
  `nombre_imagen` varchar(255) NOT NULL,
  `url_encabezado` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empresa`
--

INSERT INTO `empresa` (`id_empresa`, `nombre`, `direccion`, `telefono_1`, `telefono_2`, `correo_1`, `correo_2`, `facebook`, `instagram`, `twitter`, `youtube`, `whatsapp`, `rif`, `numero_rif`, `tipo_empresa`, `fecha_creacion`, `codigo_postal`, `historia`, `vision`, `mision`, `objetivo_general`, `objetivos_especificos`, `eslogan_1`, `eslogan_2`, `ubicacion_pais_id`, `ubicacion_estado_id`, `status_id`, `url`, `nombre_imagen`, `url_encabezado`) VALUES
(1, 'Tamanaco', 'Avda Universidad, Centro Parque Carabobo Torre A Piso 1, Venezuela', '0255-6216166', NULL, 'tamanacoservicio@gmail.com', NULL, 'https://www.facebook.com/tamanacosports', 'https://www.instagram.com/tamanacosports/?hl=es', 'https://x.com/tamanacovzla', 'https://www.youtube.com/user/tamanacovzla', 'https://api.whatsapp.com/message/AEMPKLX3X2FDK1?autoload=1&app_absent=0', 'J', '075041070', 'Deportiva', '2025-05-29 15:29:42', '3301', 'Fundada en 1967 bajo la denominación de Compañía Anónima (C.A.), Tamanaco fue registrada en el Registro de Comercio de la ciudad del Consejo. Desde su inicio con un capital de cien mil bolívares (100.000 Bs), divididos en 100 acciones nominativas, la empresa ha crecido y se ha adaptado a lo largo de las décadas. En 1988, decidimos ampliar nuestra duración a cincuenta años, con el objetivo de garantizar el futuro desarrollo de nuestros objetivos sociales.', 'Ser una empresa de primer nivel, líder en el mercado deportivo Venezolano, con presencia consolidada en Latino-América.', 'Impulsar el deporte en Venezuela a través de la manufactura y distribución de artículos deportivos con la mejor relación precio-valor.', 'Mejorar la eficiencia y productividad mediante soluciones tecnológicas avanzadas que optimicen y automaticen los procesos comerciales, garantizando operaciones eficientes y ágiles.', 'Optimizar procesos como la gestión de compras, la comunicación con proveedores y el control de inventarios, garantizando eficiencia, transparencia y productividad.', 'Cree en ti. Hazte Grande!', 'Cree en ti, crece sin límites.', 95, 95, 1, 'servidor_img/empresa/logo2.png', 'logo2.png', 'servidor_img/empresa/logo.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especificaciones_maquina`
--

CREATE TABLE `especificaciones_maquina` (
  `id_especificacion` int(11) NOT NULL,
  `id_maquina` int(11) NOT NULL,
  `nombre_especificacion` varchar(255) NOT NULL,
  `descripcion_especificacion` varchar(255) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especificaciones_maquina`
--

INSERT INTO `especificaciones_maquina` (`id_especificacion`, `id_maquina`, `nombre_especificacion`, `descripcion_especificacion`, `fecha_creacion`) VALUES
(44, 27, 'Voltaje', '220/380', '2025-06-06 03:26:40'),
(45, 27, 'RPM', '1120', '2025-06-06 03:26:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especificaciones_repuestos`
--

CREATE TABLE `especificaciones_repuestos` (
  `id_especificacion` int(11) NOT NULL,
  `id_repuesto` int(11) NOT NULL,
  `detalle_especificacion` varchar(255) NOT NULL,
  `valor_especificacion` varchar(255) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `especificaciones_repuestos`
--

INSERT INTO `especificaciones_repuestos` (`id_especificacion`, `id_repuesto`, `detalle_especificacion`, `valor_especificacion`, `fecha_creacion`) VALUES
(13, 34, 'codigo_lote', 'MO-01-RO', '2025-06-05 21:39:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadistica_desempeno`
--

CREATE TABLE `estadistica_desempeno` (
  `id_desempeno` int(11) NOT NULL,
  `reporte_id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `tiempo_estimado` int(11) NOT NULL,
  `tiempo_real` int(11) NOT NULL,
  `eficiencia` decimal(5,2) GENERATED ALWAYS AS (`tiempo_estimado` / `tiempo_real` * 100) VIRTUAL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadistica_gastos`
--

CREATE TABLE `estadistica_gastos` (
  `id_estadistica` int(11) NOT NULL,
  `reporte_id` int(11) NOT NULL,
  `total_gastos` decimal(10,2) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadistica_mantenimiento`
--

CREATE TABLE `estadistica_mantenimiento` (
  `id_estadistica` int(11) NOT NULL,
  `reporte_id` int(11) NOT NULL,
  `tipo_mantenimiento_id` int(11) NOT NULL,
  `cantidad_realizados` int(11) NOT NULL,
  `cantidad_pendientes` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadistica_solicitudes`
--

CREATE TABLE `estadistica_solicitudes` (
  `id_estadistica` int(11) NOT NULL,
  `reporte_id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `solicitudes_totales` int(11) NOT NULL,
  `solicitudes_aceptadas` int(11) NOT NULL,
  `solicitudes_rechazadas` int(11) NOT NULL,
  `solicitudes_pendientes` int(11) NOT NULL,
  `ultima_actualizacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado`
--

CREATE TABLE `estado` (
  `id` int(11) NOT NULL,
  `ubicacionpaisid` int(11) DEFAULT NULL,
  `estadonombre` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `estado`
--

INSERT INTO `estado` (`id`, `ubicacionpaisid`, `estadonombre`) VALUES
(1, 3, 'Azerbaijan'),
(2, 3, 'Nargorni Karabakh'),
(3, 3, 'Nakhichevanskaya Region'),
(4, 4, 'Anguilla'),
(5, 7, 'Brestskaya obl.'),
(6, 7, 'Vitebskaya obl.'),
(7, 7, 'Gomelskaya obl.'),
(8, 7, 'Grodnenskaya obl.'),
(9, 7, 'Minskaya obl.'),
(10, 7, 'Mogilevskaya obl.'),
(11, 8, 'Belize'),
(12, 10, 'Hamilton'),
(13, 15, 'Dong Bang Song Cuu Long'),
(14, 15, 'Dong Bang Song Hong'),
(15, 15, 'Dong Nam Bo'),
(16, 15, 'Duyen Hai Mien Trung'),
(17, 15, 'Khu Bon Cu'),
(18, 15, 'Mien Nui Va Trung Du'),
(19, 15, 'Thai Nguyen'),
(20, 16, 'Artibonite'),
(21, 16, 'Grand&#039;Anse'),
(22, 16, 'North West'),
(23, 16, 'West'),
(24, 16, 'South'),
(25, 16, 'South East'),
(26, 17, 'Grande-Terre'),
(27, 17, 'Basse-Terre'),
(28, 21, 'Abkhazia'),
(29, 21, 'Ajaria'),
(30, 21, 'Georgia'),
(31, 21, 'South Ossetia'),
(32, 23, 'Al QÄhira'),
(33, 23, 'Aswan'),
(34, 23, 'Asyut'),
(35, 23, 'Beni Suef'),
(36, 23, 'Gharbia'),
(37, 23, 'Damietta'),
(38, 24, 'Southern District'),
(39, 24, 'Central District'),
(40, 24, 'Northern District'),
(41, 24, 'Haifa'),
(42, 24, 'Tel Aviv'),
(43, 24, 'Jerusalem'),
(44, 25, 'Bangala'),
(45, 25, 'Chhattisgarh'),
(46, 25, 'Karnataka'),
(47, 25, 'Uttaranchal'),
(48, 25, 'Andhara Pradesh'),
(49, 25, 'Assam'),
(50, 25, 'Bihar'),
(51, 25, 'Gujarat'),
(52, 25, 'Jammu and Kashmir'),
(53, 25, 'Kerala'),
(54, 25, 'Madhya Pradesh'),
(55, 25, 'Manipur'),
(56, 25, 'Maharashtra'),
(57, 25, 'Megahalaya'),
(58, 25, 'Orissa'),
(59, 25, 'Punjab'),
(60, 25, 'Pondisheri'),
(61, 25, 'Rajasthan'),
(62, 25, 'Tamil Nadu'),
(63, 25, 'Tripura'),
(64, 25, 'Uttar Pradesh'),
(65, 25, 'Haryana'),
(66, 25, 'Chandigarh'),
(67, 26, 'Azarbayjan-e Khavari'),
(68, 26, 'Esfahan'),
(69, 26, 'Hamadan'),
(70, 26, 'Kordestan'),
(71, 26, 'Markazi'),
(72, 26, 'Sistan-e Baluches'),
(73, 26, 'Yazd'),
(74, 26, 'Kerman'),
(75, 26, 'Kermanshakhan'),
(76, 26, 'Mazenderan'),
(77, 26, 'Tehran'),
(78, 26, 'Fars'),
(79, 26, 'Horasan'),
(80, 26, 'Husistan'),
(81, 30, 'Aktyubinskaya obl.'),
(82, 30, 'Alma-Atinskaya obl.'),
(83, 30, 'Vostochno-Kazahstanskaya obl.'),
(84, 30, 'Gurevskaya obl.'),
(85, 30, 'Zhambylskaya obl. (Dzhambulskaya obl.)'),
(86, 30, 'Dzhezkazganskaya obl.'),
(87, 30, 'Karagandinskaya obl.'),
(88, 30, 'Kzyl-Ordinskaya obl.'),
(89, 30, 'Kokchetavskaya obl.'),
(90, 30, 'Kustanaiskaya obl.'),
(91, 30, 'Mangystauskaya (Mangyshlakskaya obl.)'),
(92, 30, 'Pavlodarskaya obl.'),
(93, 30, 'Severo-Kazahstanskaya obl.'),
(94, 30, 'Taldy-Kurganskaya obl.'),
(95, 30, 'Turgaiskaya obl.'),
(96, 30, 'Akmolinskaya obl. (Tselinogradskaya obl.)'),
(97, 30, 'Chimkentskaya obl.'),
(98, 31, 'Littoral'),
(99, 31, 'Southwest Region'),
(100, 31, 'North'),
(101, 31, 'Central'),
(102, 33, 'Government controlled area'),
(103, 33, 'Turkish controlled area'),
(104, 34, 'Issik Kulskaya Region'),
(105, 34, 'Kyrgyzstan'),
(106, 34, 'Narinskaya Region'),
(107, 34, 'Oshskaya Region'),
(108, 34, 'Tallaskaya Region'),
(109, 37, 'al-Jahra'),
(110, 37, 'al-Kuwait'),
(111, 38, 'Latviya'),
(112, 39, 'Tarabulus'),
(113, 39, 'Bengasi'),
(114, 40, 'Litva'),
(115, 43, 'Moldova'),
(116, 45, 'Auckland'),
(117, 45, 'Bay of Plenty'),
(118, 45, 'Canterbury'),
(119, 45, 'Gisborne'),
(120, 45, 'Hawke&#039;s Bay'),
(121, 45, 'Manawatu-Wanganui'),
(122, 45, 'Marlborough'),
(123, 45, 'Nelson'),
(124, 45, 'Northland'),
(125, 45, 'Otago'),
(126, 45, 'Southland'),
(127, 45, 'Taranaki'),
(128, 45, 'Tasman'),
(129, 45, 'Waikato'),
(130, 45, 'Wellington'),
(131, 45, 'West Coast'),
(132, 49, 'Saint-Denis'),
(133, 50, 'Altaiskii krai'),
(134, 50, 'Amurskaya obl.'),
(135, 50, 'Arhangelskaya obl.'),
(136, 50, 'Astrahanskaya obl.'),
(137, 50, 'Bashkiriya obl.'),
(138, 50, 'Belgorodskaya obl.'),
(139, 50, 'Bryanskaya obl.'),
(140, 50, 'Buryatiya'),
(141, 50, 'Vladimirskaya obl.'),
(142, 50, 'Volgogradskaya obl.'),
(143, 50, 'Vologodskaya obl.'),
(144, 50, 'Voronezhskaya obl.'),
(145, 50, 'Nizhegorodskaya obl.'),
(146, 50, 'Dagestan'),
(147, 50, 'Evreiskaya obl.'),
(148, 50, 'Ivanovskaya obl.'),
(149, 50, 'Irkutskaya obl.'),
(150, 50, 'Kabardino-Balkariya'),
(151, 50, 'Kaliningradskaya obl.'),
(152, 50, 'Tverskaya obl.'),
(153, 50, 'Kalmykiya'),
(154, 50, 'Kaluzhskaya obl.'),
(155, 50, 'Kamchatskaya obl.'),
(156, 50, 'Kareliya'),
(157, 50, 'Kemerovskaya obl.'),
(158, 50, 'Kirovskaya obl.'),
(159, 50, 'Komi'),
(160, 50, 'Kostromskaya obl.'),
(161, 50, 'Krasnodarskii krai'),
(162, 50, 'Krasnoyarskii krai'),
(163, 50, 'Kurganskaya obl.'),
(164, 50, 'Kurskaya obl.'),
(165, 50, 'Lipetskaya obl.'),
(166, 50, 'Magadanskaya obl.'),
(167, 50, 'Marii El'),
(168, 50, 'Mordoviya'),
(169, 50, 'Moscow &amp; Moscow Region'),
(170, 50, 'Murmanskaya obl.'),
(171, 50, 'Novgorodskaya obl.'),
(172, 50, 'Novosibirskaya obl.'),
(173, 50, 'Omskaya obl.'),
(174, 50, 'Orenburgskaya obl.'),
(175, 50, 'Orlovskaya obl.'),
(176, 50, 'Penzenskaya obl.'),
(177, 50, 'Permskiy krai'),
(178, 50, 'Primorskii krai'),
(179, 50, 'Pskovskaya obl.'),
(180, 50, 'Rostovskaya obl.'),
(181, 50, 'Ryazanskaya obl.'),
(182, 50, 'Samarskaya obl.'),
(183, 50, 'Saint-Petersburg and Region'),
(184, 50, 'Saratovskaya obl.'),
(185, 50, 'Saha (Yakutiya)'),
(186, 50, 'Sahalin'),
(187, 50, 'Sverdlovskaya obl.'),
(188, 50, 'Severnaya Osetiya'),
(189, 50, 'Smolenskaya obl.'),
(190, 50, 'Stavropolskii krai'),
(191, 50, 'Tambovskaya obl.'),
(192, 50, 'Tatarstan'),
(193, 50, 'Tomskaya obl.'),
(195, 50, 'Tulskaya obl.'),
(196, 50, 'Tyumenskaya obl. i Hanty-Mansiiskii AO'),
(197, 50, 'Udmurtiya'),
(198, 50, 'Ulyanovskaya obl.'),
(199, 50, 'Uralskaya obl.'),
(200, 50, 'Habarovskii krai'),
(201, 50, 'Chelyabinskaya obl.'),
(202, 50, 'Checheno-Ingushetiya'),
(203, 50, 'Chitinskaya obl.'),
(204, 50, 'Chuvashiya'),
(205, 50, 'Yaroslavskaya obl.'),
(206, 51, 'Ahuachapán'),
(207, 51, 'Cuscatlán'),
(208, 51, 'La Libertad'),
(209, 51, 'La Paz'),
(210, 51, 'La Unión'),
(211, 51, 'San Miguel'),
(212, 51, 'San Salvador'),
(213, 51, 'Santa Ana'),
(214, 51, 'Sonsonate'),
(215, 54, 'Paramaribo'),
(216, 56, 'Gorno-Badakhshan Region'),
(217, 56, 'Kuljabsk Region'),
(218, 56, 'Kurgan-Tjube Region'),
(219, 56, 'Sughd Region'),
(220, 56, 'Tajikistan'),
(221, 57, 'Ashgabat Region'),
(222, 57, 'Krasnovodsk Region'),
(223, 57, 'Mary Region'),
(224, 57, 'Tashauz Region'),
(225, 57, 'Chardzhou Region'),
(226, 58, 'Grand Turk'),
(227, 59, 'Bartin'),
(228, 59, 'Bayburt'),
(229, 59, 'Karabuk'),
(230, 59, 'Adana'),
(231, 59, 'Aydin'),
(232, 59, 'Amasya'),
(233, 59, 'Ankara'),
(234, 59, 'Antalya'),
(235, 59, 'Artvin'),
(236, 59, 'Afion'),
(237, 59, 'Balikesir'),
(238, 59, 'Bilecik'),
(239, 59, 'Bursa'),
(240, 59, 'Gaziantep'),
(241, 59, 'Denizli'),
(242, 59, 'Izmir'),
(243, 59, 'Isparta'),
(244, 59, 'Icel'),
(245, 59, 'Kayseri'),
(246, 59, 'Kars'),
(247, 59, 'Kodjaeli'),
(248, 59, 'Konya'),
(249, 59, 'Kirklareli'),
(250, 59, 'Kutahya'),
(251, 59, 'Malatya'),
(252, 59, 'Manisa'),
(253, 59, 'Sakarya'),
(254, 59, 'Samsun'),
(255, 59, 'Sivas'),
(256, 59, 'Istanbul'),
(257, 59, 'Trabzon'),
(258, 59, 'Corum'),
(259, 59, 'Edirne'),
(260, 59, 'Elazig'),
(261, 59, 'Erzincan'),
(262, 59, 'Erzurum'),
(263, 59, 'Eskisehir'),
(264, 60, 'Jinja'),
(265, 60, 'Kampala'),
(266, 61, 'Andijon Region'),
(267, 61, 'Buxoro Region'),
(268, 61, 'Jizzac Region'),
(269, 61, 'Qaraqalpaqstan'),
(270, 61, 'Qashqadaryo Region'),
(271, 61, 'Navoiy Region'),
(272, 61, 'Namangan Region'),
(273, 61, 'Samarqand Region'),
(274, 61, 'Surxondaryo Region'),
(275, 61, 'Sirdaryo Region'),
(276, 61, 'Tashkent Region'),
(277, 61, 'Fergana Region'),
(278, 61, 'Xorazm Region'),
(279, 62, 'Vinnitskaya obl.'),
(280, 62, 'Volynskaya obl.'),
(281, 62, 'Dnepropetrovskaya obl.'),
(282, 62, 'Donetskaya obl.'),
(283, 62, 'Zhitomirskaya obl.'),
(284, 62, 'Zakarpatskaya obl.'),
(285, 62, 'Zaporozhskaya obl.'),
(286, 62, 'Ivano-Frankovskaya obl.'),
(287, 62, 'Kievskaya obl.'),
(288, 62, 'Kirovogradskaya obl.'),
(289, 62, 'Krymskaya obl.'),
(290, 62, 'Luganskaya obl.'),
(291, 62, 'Lvovskaya obl.'),
(292, 62, 'Nikolaevskaya obl.'),
(293, 62, 'Odesskaya obl.'),
(294, 62, 'Poltavskaya obl.'),
(295, 62, 'Rovenskaya obl.'),
(296, 62, 'Sumskaya obl.'),
(297, 62, 'Ternopolskaya obl.'),
(298, 62, 'Harkovskaya obl.'),
(299, 62, 'Hersonskaya obl.'),
(300, 62, 'Hmelnitskaya obl.'),
(301, 62, 'Cherkasskaya obl.'),
(302, 62, 'Chernigovskaya obl.'),
(303, 62, 'Chernovitskaya obl.'),
(304, 68, 'Estoniya'),
(305, 69, 'Cheju'),
(306, 69, 'Chollabuk'),
(307, 69, 'Chollanam'),
(308, 69, 'Chungcheongbuk'),
(309, 69, 'Chungcheongnam'),
(310, 69, 'Incheon'),
(311, 69, 'Kangweon'),
(312, 69, 'Kwangju'),
(313, 69, 'Kyeonggi'),
(314, 69, 'Kyeongsangbuk'),
(315, 69, 'Kyeongsangnam'),
(316, 69, 'Pusan'),
(317, 69, 'Seoul'),
(318, 69, 'Taegu'),
(319, 69, 'Taejeon'),
(320, 69, 'Ulsan'),
(321, 70, 'Aichi'),
(322, 70, 'Akita'),
(323, 70, 'Aomori'),
(324, 70, 'Wakayama'),
(325, 70, 'Gifu'),
(326, 70, 'Gunma'),
(327, 70, 'Ibaraki'),
(328, 70, 'Iwate'),
(329, 70, 'Ishikawa'),
(330, 70, 'Kagawa'),
(331, 70, 'Kagoshima'),
(332, 70, 'Kanagawa'),
(333, 70, 'Kyoto'),
(334, 70, 'Kochi'),
(335, 70, 'Kumamoto'),
(336, 70, 'Mie'),
(337, 70, 'Miyagi'),
(338, 70, 'Miyazaki'),
(339, 70, 'Nagano'),
(340, 70, 'Nagasaki'),
(341, 70, 'Nara'),
(342, 70, 'Niigata'),
(343, 70, 'Okayama'),
(344, 70, 'Okinawa'),
(345, 70, 'Osaka'),
(346, 70, 'Saga'),
(347, 70, 'Saitama'),
(348, 70, 'Shiga'),
(349, 70, 'Shizuoka'),
(350, 70, 'Shimane'),
(351, 70, 'Tiba'),
(352, 70, 'Tokyo'),
(353, 70, 'Tokushima'),
(354, 70, 'Tochigi'),
(355, 70, 'Tottori'),
(356, 70, 'Toyama'),
(357, 70, 'Fukui'),
(358, 70, 'Fukuoka'),
(359, 70, 'Fukushima'),
(360, 70, 'Hiroshima'),
(361, 70, 'Hokkaido'),
(362, 70, 'Hyogo'),
(363, 70, 'Yoshimi'),
(364, 70, 'Yamagata'),
(365, 70, 'Yamaguchi'),
(366, 70, 'Yamanashi'),
(368, 73, 'Hong Kong'),
(369, 74, 'Indonesia'),
(370, 75, 'Jordan'),
(371, 76, 'Malaysia'),
(372, 77, 'Singapore'),
(373, 78, 'Taiwan'),
(374, 30, 'Kazahstan'),
(375, 62, 'Ukraina'),
(376, 25, 'India'),
(377, 23, 'Egypt'),
(378, 106, 'Damascus'),
(379, 131, 'Isle of Man'),
(380, 30, 'Zapadno-Kazahstanskaya obl.'),
(381, 50, 'Adygeya'),
(382, 50, 'Hakasiya'),
(383, 93, 'Dubai'),
(384, 50, 'Chukotskii AO'),
(385, 99, 'Beirut'),
(386, 137, 'Tegucigalpa'),
(387, 138, 'Santo Domingo'),
(388, 139, 'Ulan Bator'),
(389, 23, 'Sinai'),
(390, 140, 'Baghdad'),
(391, 140, 'Basra'),
(392, 140, 'Mosul'),
(393, 141, 'Johannesburg'),
(394, 104, 'Morocco'),
(395, 104, 'Tangier'),
(396, 50, 'Yamalo-Nenetskii AO'),
(397, 122, 'Tunisia'),
(398, 92, 'Thailand'),
(399, 117, 'Mozambique'),
(400, 84, 'Korea'),
(401, 87, 'Pakistan'),
(402, 142, 'Aruba'),
(403, 80, 'Bahamas'),
(404, 69, 'South Korea'),
(405, 132, 'Jamaica'),
(406, 93, 'Sharjah'),
(407, 93, 'Abu Dhabi'),
(409, 24, 'Ramat Hagolan'),
(410, 115, 'Nigeria'),
(411, 64, 'Ain'),
(412, 64, 'Haute-Savoie'),
(413, 64, 'Aisne'),
(414, 64, 'Allier'),
(415, 64, 'Alpes-de-Haute-Provence'),
(416, 64, 'Hautes-Alpes'),
(417, 64, 'Alpes-Maritimes'),
(418, 64, 'Ard&egrave;che'),
(419, 64, 'Ardennes'),
(420, 64, 'Ari&egrave;ge'),
(421, 64, 'Aube'),
(422, 64, 'Aude'),
(423, 64, 'Aveyron'),
(424, 64, 'Bouches-du-Rh&ocirc;ne'),
(425, 64, 'Calvados'),
(426, 64, 'Cantal'),
(427, 64, 'Charente'),
(428, 64, 'Charente Maritime'),
(429, 64, 'Cher'),
(430, 64, 'Corr&egrave;ze'),
(431, 64, 'Dordogne'),
(432, 64, 'Corse'),
(433, 64, 'C&ocirc;te d&#039;Or'),
(434, 64, 'Sa&ocirc;ne et Loire'),
(435, 64, 'C&ocirc;tes d&#039;Armor'),
(436, 64, 'Creuse'),
(437, 64, 'Doubs'),
(438, 64, 'Dr&ocirc;me'),
(439, 64, 'Eure'),
(440, 64, 'Eure-et-Loire'),
(441, 64, 'Finist&egrave;re'),
(442, 64, 'Gard'),
(443, 64, 'Haute-Garonne'),
(444, 64, 'Gers'),
(445, 64, 'Gironde'),
(446, 64, 'Hérault'),
(447, 64, 'Ille et Vilaine'),
(448, 64, 'Indre'),
(449, 64, 'Indre-et-Loire'),
(450, 64, 'Isère'),
(451, 64, 'Jura'),
(452, 64, 'Landes'),
(453, 64, 'Loir-et-Cher'),
(454, 64, 'Loire'),
(455, 64, 'Rh&ocirc;ne'),
(456, 64, 'Haute-Loire'),
(457, 64, 'Loire Atlantique'),
(458, 64, 'Loiret'),
(459, 64, 'Lot'),
(460, 64, 'Lot-et-Garonne'),
(461, 64, 'Loz&egrave;re'),
(462, 64, 'Maine et Loire'),
(463, 64, 'Manche'),
(464, 64, 'Marne'),
(465, 64, 'Haute-Marne'),
(466, 64, 'Mayenne'),
(467, 64, 'Meurthe-et-Moselle'),
(468, 64, 'Meuse'),
(469, 64, 'Morbihan'),
(470, 64, 'Moselle'),
(471, 64, 'Ni&egrave;vre'),
(472, 64, 'Nord'),
(473, 64, 'Oise'),
(474, 64, 'Orne'),
(475, 64, 'Pas-de-Calais'),
(476, 64, 'Puy-de-D&ocirc;me'),
(477, 64, 'Pyrénées-Atlantiques'),
(478, 64, 'Hautes-Pyrénées'),
(479, 64, 'Pyrénées-Orientales'),
(480, 64, 'Bas Rhin'),
(481, 64, 'Haut Rhin'),
(482, 64, 'Haute-Sa&ocirc;ne'),
(483, 64, 'Sarthe'),
(484, 64, 'Savoie'),
(485, 64, 'Paris'),
(486, 64, 'Seine-Maritime'),
(487, 64, 'Seine-et-Marne'),
(488, 64, 'Yvelines'),
(489, 64, 'Deux-S&egrave;vres'),
(490, 64, 'Somme'),
(491, 64, 'Tarn'),
(492, 64, 'Tarn-et-Garonne'),
(493, 64, 'Var'),
(494, 64, 'Vaucluse'),
(495, 64, 'Vendée'),
(496, 64, 'Vienne'),
(497, 64, 'Haute-Vienne'),
(498, 64, 'Vosges'),
(499, 64, 'Yonne'),
(500, 64, 'Territoire de Belfort'),
(501, 64, 'Essonne'),
(502, 64, 'Hauts-de-Seine'),
(503, 64, 'Seine-Saint-Denis'),
(504, 64, 'Val-de-Marne'),
(505, 64, 'Val-d&#039;Oise'),
(506, 29, 'Piemonte - Torino'),
(507, 29, 'Piemonte - Alessandria'),
(508, 29, 'Piemonte - Asti'),
(509, 29, 'Piemonte - Biella'),
(510, 29, 'Piemonte - Cuneo'),
(511, 29, 'Piemonte - Novara'),
(512, 29, 'Piemonte - Verbania'),
(513, 29, 'Piemonte - Vercelli'),
(514, 29, 'Valle d&#039;Aosta - Aosta'),
(515, 29, 'Lombardia - Milano'),
(516, 29, 'Lombardia - Bergamo'),
(517, 29, 'Lombardia - Brescia'),
(518, 29, 'Lombardia - Como'),
(519, 29, 'Lombardia - Cremona'),
(520, 29, 'Lombardia - Lecco'),
(521, 29, 'Lombardia - Lodi'),
(522, 29, 'Lombardia - Mantova'),
(523, 29, 'Lombardia - Pavia'),
(524, 29, 'Lombardia - Sondrio'),
(525, 29, 'Lombardia - Varese'),
(526, 29, 'Trentino Alto Adige - Trento'),
(527, 29, 'Trentino Alto Adige - Bolzano'),
(528, 29, 'Veneto - Venezia'),
(529, 29, 'Veneto - Belluno'),
(530, 29, 'Veneto - Padova'),
(531, 29, 'Veneto - Rovigo'),
(532, 29, 'Veneto - Treviso'),
(533, 29, 'Veneto - Verona'),
(534, 29, 'Veneto - Vicenza'),
(535, 29, 'Friuli Venezia Giulia - Trieste'),
(536, 29, 'Friuli Venezia Giulia - Gorizia'),
(537, 29, 'Friuli Venezia Giulia - Pordenone'),
(538, 29, 'Friuli Venezia Giulia - Udine'),
(539, 29, 'Liguria - Genova'),
(540, 29, 'Liguria - Imperia'),
(541, 29, 'Liguria - La Spezia'),
(542, 29, 'Liguria - Savona'),
(543, 29, 'Emilia Romagna - Bologna'),
(544, 29, 'Emilia Romagna - Ferrara'),
(545, 29, 'Emilia Romagna - Forlì-Cesena'),
(546, 29, 'Emilia Romagna - Modena'),
(547, 29, 'Emilia Romagna - Parma'),
(548, 29, 'Emilia Romagna - Piacenza'),
(549, 29, 'Emilia Romagna - Ravenna'),
(550, 29, 'Emilia Romagna - Reggio Emilia'),
(551, 29, 'Emilia Romagna - Rimini'),
(552, 29, 'Toscana - Firenze'),
(553, 29, 'Toscana - Arezzo'),
(554, 29, 'Toscana - Grosseto'),
(555, 29, 'Toscana - Livorno'),
(556, 29, 'Toscana - Lucca'),
(557, 29, 'Toscana - Massa Carrara'),
(558, 29, 'Toscana - Pisa'),
(559, 29, 'Toscana - Pistoia'),
(560, 29, 'Toscana - Prato'),
(561, 29, 'Toscana - Siena'),
(562, 29, 'Umbria - Perugia'),
(563, 29, 'Umbria - Terni'),
(564, 29, 'Marche - Ancona'),
(565, 29, 'Marche - Ascoli Piceno'),
(566, 29, 'Marche - Macerata'),
(567, 29, 'Marche - Pesaro - Urbino'),
(568, 29, 'Lazio - Roma'),
(569, 29, 'Lazio - Frosinone'),
(570, 29, 'Lazio - Latina'),
(571, 29, 'Lazio - Rieti'),
(572, 29, 'Lazio - Viterbo'),
(573, 29, 'Abruzzo - L´Aquila'),
(574, 29, 'Abruzzo - Chieti'),
(575, 29, 'Abruzzo - Pescara'),
(576, 29, 'Abruzzo - Teramo'),
(577, 29, 'Molise - Campobasso'),
(578, 29, 'Molise - Isernia'),
(579, 29, 'Campania - Napoli'),
(580, 29, 'Campania - Avellino'),
(581, 29, 'Campania - Benevento'),
(582, 29, 'Campania - Caserta'),
(583, 29, 'Campania - Salerno'),
(584, 29, 'Puglia - Bari'),
(585, 29, 'Puglia - Brindisi'),
(586, 29, 'Puglia - Foggia'),
(587, 29, 'Puglia - Lecce'),
(588, 29, 'Puglia - Taranto'),
(589, 29, 'Basilicata - Potenza'),
(590, 29, 'Basilicata - Matera'),
(591, 29, 'Calabria - Catanzaro'),
(592, 29, 'Calabria - Cosenza'),
(593, 29, 'Calabria - Crotone'),
(594, 29, 'Calabria - Reggio Calabria'),
(595, 29, 'Calabria - Vibo Valentia'),
(596, 29, 'Sicilia - Palermo'),
(597, 29, 'Sicilia - Agrigento'),
(598, 29, 'Sicilia - Caltanissetta'),
(599, 29, 'Sicilia - Catania'),
(600, 29, 'Sicilia - Enna'),
(601, 29, 'Sicilia - Messina'),
(602, 29, 'Sicilia - Ragusa'),
(603, 29, 'Sicilia - Siracusa'),
(604, 29, 'Sicilia - Trapani'),
(605, 29, 'Sardegna - Cagliari'),
(606, 29, 'Sardegna - Nuoro'),
(607, 29, 'Sardegna - Oristano'),
(608, 29, 'Sardegna - Sassari'),
(609, 28, 'Las Palmas'),
(610, 28, 'Soria'),
(611, 28, 'Palencia'),
(612, 28, 'Zamora'),
(613, 28, 'Cádiz'),
(614, 28, 'Navarra'),
(615, 28, 'Ourense'),
(616, 28, 'Segovia'),
(617, 28, 'Guip&uacute;zcoa'),
(618, 28, 'Ciudad Real'),
(619, 28, 'Vizcaya'),
(620, 28, 'álava'),
(621, 28, 'A Coruña'),
(622, 28, 'Cantabria'),
(623, 28, 'Almería'),
(624, 28, 'Zaragoza'),
(625, 28, 'Santa Cruz de Tenerife'),
(626, 28, 'Cáceres'),
(627, 28, 'Guadalajara'),
(628, 28, 'ávila'),
(629, 28, 'Toledo'),
(630, 28, 'Castellón'),
(631, 28, 'Tarragona'),
(632, 28, 'Lugo'),
(633, 28, 'La Rioja'),
(634, 28, 'Ceuta'),
(635, 28, 'Murcia'),
(636, 28, 'Salamanca'),
(637, 28, 'Valladolid'),
(638, 28, 'Jaén'),
(639, 28, 'Girona'),
(640, 28, 'Granada'),
(641, 28, 'Alacant'),
(642, 28, 'Córdoba'),
(643, 28, 'Albacete'),
(644, 28, 'Cuenca'),
(645, 28, 'Pontevedra'),
(646, 28, 'Teruel'),
(647, 28, 'Melilla'),
(648, 28, 'Barcelona'),
(649, 28, 'Badajoz'),
(650, 28, 'Madrid'),
(651, 28, 'Sevilla'),
(652, 28, 'Val&egrave;ncia'),
(653, 28, 'Huelva'),
(654, 28, 'Lleida'),
(655, 28, 'León'),
(656, 28, 'Illes Balears'),
(657, 28, 'Burgos'),
(658, 28, 'Huesca'),
(659, 28, 'Asturias'),
(660, 28, 'Málaga'),
(661, 144, 'Afghanistan'),
(662, 210, 'Niger'),
(663, 133, 'Mali'),
(664, 156, 'Burkina Faso'),
(665, 136, 'Togo'),
(666, 151, 'Benin'),
(667, 119, 'Angola'),
(668, 102, 'Namibia'),
(669, 100, 'Botswana'),
(670, 134, 'Madagascar'),
(671, 202, 'Mauritius'),
(672, 196, 'Laos'),
(673, 158, 'Cambodia'),
(674, 90, 'Philippines'),
(675, 88, 'Papua New Guinea'),
(676, 228, 'Solomon Islands'),
(677, 240, 'Vanuatu'),
(678, 176, 'Fiji'),
(679, 223, 'Samoa'),
(680, 206, 'Nauru'),
(681, 168, 'Cote D&#039;Ivoire'),
(682, 198, 'Liberia'),
(683, 187, 'Guinea'),
(684, 189, 'Guyana'),
(685, 98, 'Algeria'),
(686, 147, 'Antigua and Barbuda'),
(687, 127, 'Bahrain'),
(688, 149, 'Bangladesh'),
(689, 128, 'Barbados'),
(690, 152, 'Bhutan'),
(691, 155, 'Brunei'),
(692, 157, 'Burundi'),
(693, 159, 'Cape Verde'),
(694, 130, 'Chad'),
(695, 164, 'Comoros'),
(696, 112, 'Congo (Brazzaville)'),
(697, 169, 'Djibouti'),
(698, 171, 'East Timor'),
(699, 173, 'Eritrea'),
(700, 121, 'Ethiopia'),
(701, 180, 'Gabon'),
(702, 181, 'Gambia'),
(703, 105, 'Ghana'),
(704, 197, 'Lesotho'),
(705, 125, 'Malawi'),
(706, 200, 'Maldives'),
(707, 205, 'Myanmar (Burma)'),
(708, 107, 'Nepal'),
(709, 213, 'Oman'),
(710, 217, 'Rwanda'),
(711, 91, 'Saudi Arabia'),
(712, 120, 'Sri Lanka'),
(713, 232, 'Sudan'),
(714, 234, 'Swaziland'),
(715, 101, 'Tanzania'),
(716, 236, 'Tonga'),
(717, 239, 'Tuvalu'),
(718, 242, 'Western Sahara'),
(719, 243, 'Yemen'),
(720, 116, 'Zambia'),
(721, 96, 'Zimbabwe'),
(722, 66, 'Aargau'),
(723, 66, 'Appenzell Innerrhoden'),
(724, 66, 'Appenzell Ausserrhoden'),
(725, 66, 'Bern'),
(726, 66, 'Basel-Landschaft'),
(727, 66, 'Basel-Stadt'),
(728, 66, 'Fribourg'),
(729, 66, 'Gen&egrave;ve'),
(730, 66, 'Glarus'),
(731, 66, 'Graubünden'),
(732, 66, 'Jura'),
(733, 66, 'Luzern'),
(734, 66, 'Neuch&acirc;tel'),
(735, 66, 'Nidwalden'),
(736, 66, 'Obwalden'),
(737, 66, 'Sankt Gallen'),
(738, 66, 'Schaffhausen'),
(739, 66, 'Solothurn'),
(740, 66, 'Schwyz'),
(741, 66, 'Thurgau'),
(742, 66, 'Ticino'),
(743, 66, 'Uri'),
(744, 66, 'Vaud'),
(745, 66, 'Valais'),
(746, 66, 'Zug'),
(747, 66, 'Zürich'),
(749, 48, 'Aveiro'),
(750, 48, 'Beja'),
(751, 48, 'Braga'),
(752, 48, 'Braganca'),
(753, 48, 'Castelo Branco'),
(754, 48, 'Coimbra'),
(755, 48, 'Evora'),
(756, 48, 'Faro'),
(757, 48, 'Madeira'),
(758, 48, 'Guarda'),
(759, 48, 'Leiria'),
(760, 48, 'Lisboa'),
(761, 48, 'Portalegre'),
(762, 48, 'Porto'),
(763, 48, 'Santarem'),
(764, 48, 'Setubal'),
(765, 48, 'Viana do Castelo'),
(766, 48, 'Vila Real'),
(767, 48, 'Viseu'),
(768, 48, 'Azores'),
(769, 55, 'Armed Forces Americas'),
(770, 55, 'Armed Forces Europe'),
(771, 55, 'Alaska'),
(772, 55, 'Alabama'),
(773, 55, 'Armed Forces Pacific'),
(774, 55, 'Arkansas'),
(775, 55, 'American Samoa'),
(776, 55, 'Arizona'),
(777, 55, 'California'),
(778, 55, 'Colorado'),
(779, 55, 'Connecticut'),
(780, 55, 'District of Columbia'),
(781, 55, 'Delaware'),
(782, 55, 'Florida'),
(783, 55, 'Federated States of Micronesia'),
(784, 55, 'Georgia'),
(786, 55, 'Hawaii'),
(787, 55, 'Iowa'),
(788, 55, 'Idaho'),
(789, 55, 'Illinois'),
(790, 55, 'Indiana'),
(791, 55, 'Kansas'),
(792, 55, 'Kentucky'),
(793, 55, 'Louisiana'),
(794, 55, 'Massachusetts'),
(795, 55, 'Maryland'),
(796, 55, 'Maine'),
(797, 55, 'Marshall Islands'),
(798, 55, 'Michigan'),
(799, 55, 'Minnesota'),
(800, 55, 'Missouri'),
(801, 55, 'Northern Mariana Islands'),
(802, 55, 'Mississippi'),
(803, 55, 'Montana'),
(804, 55, 'North Carolina'),
(805, 55, 'North Dakota'),
(806, 55, 'Nebraska'),
(807, 55, 'New Hampshire'),
(808, 55, 'New Jersey'),
(809, 55, 'New Mexico'),
(810, 55, 'Nevada'),
(811, 55, 'New York'),
(812, 55, 'Ohio'),
(813, 55, 'Oklahoma'),
(814, 55, 'Oregon'),
(815, 55, 'Pennsylvania'),
(816, 246, 'Puerto Rico'),
(817, 55, 'Palau'),
(818, 55, 'Rhode Island'),
(819, 55, 'South Carolina'),
(820, 55, 'South Dakota'),
(821, 55, 'Tennessee'),
(822, 55, 'Texas'),
(823, 55, 'Utah'),
(824, 55, 'Virginia'),
(825, 55, 'Virgin Islands'),
(826, 55, 'Vermont'),
(827, 55, 'Washington'),
(828, 55, 'West Virginia'),
(829, 55, 'Wisconsin'),
(830, 55, 'Wyoming'),
(831, 94, 'Greenland'),
(832, 18, 'Brandenburg'),
(833, 18, 'Baden-Württemberg'),
(834, 18, 'Bayern'),
(835, 18, 'Hessen'),
(836, 18, 'Hamburg'),
(837, 18, 'Mecklenburg-Vorpommern'),
(838, 18, 'Niedersachsen'),
(839, 18, 'Nordrhein-Westfalen'),
(840, 18, 'Rheinland-Pfalz'),
(841, 18, 'Schleswig-Holstein'),
(842, 18, 'Sachsen'),
(843, 18, 'Sachsen-Anhalt'),
(844, 18, 'Thüringen'),
(845, 18, 'Berlin'),
(846, 18, 'Bremen'),
(847, 18, 'Saarland'),
(848, 13, 'Scotland North'),
(849, 13, 'England - East'),
(850, 13, 'England - West Midlands'),
(851, 13, 'England - South West'),
(852, 13, 'England - North West'),
(853, 13, 'England - Yorks &amp; Humber'),
(854, 13, 'England - South East'),
(855, 13, 'England - London'),
(856, 13, 'Northern Ireland'),
(857, 13, 'England - North East'),
(858, 13, 'Wales South'),
(859, 13, 'Wales North'),
(860, 13, 'England - East Midlands'),
(861, 13, 'Scotland Central'),
(862, 13, 'Scotland South'),
(863, 13, 'Channel Islands'),
(864, 13, 'Isle of Man'),
(865, 2, 'Burgenland'),
(866, 2, 'Kärnten'),
(867, 2, 'Niederösterreich'),
(868, 2, 'Oberösterreich'),
(869, 2, 'Salzburg'),
(870, 2, 'Steiermark'),
(871, 2, 'Tirol'),
(872, 2, 'Vorarlberg'),
(873, 2, 'Wien'),
(874, 9, 'Bruxelles'),
(875, 9, 'West-Vlaanderen'),
(876, 9, 'Oost-Vlaanderen'),
(877, 9, 'Limburg'),
(878, 9, 'Vlaams Brabant'),
(879, 9, 'Antwerpen'),
(880, 9, 'LiÄge'),
(881, 9, 'Namur'),
(882, 9, 'Hainaut'),
(883, 9, 'Luxembourg'),
(884, 9, 'Brabant Wallon'),
(887, 67, 'Blekinge Lan'),
(888, 67, 'Gavleborgs Lan'),
(890, 67, 'Gotlands Lan'),
(891, 67, 'Hallands Lan'),
(892, 67, 'Jamtlands Lan'),
(893, 67, 'Jonkopings Lan'),
(894, 67, 'Kalmar Lan'),
(895, 67, 'Dalarnas Lan'),
(897, 67, 'Kronobergs Lan'),
(899, 67, 'Norrbottens Lan'),
(900, 67, 'Orebro Lan'),
(901, 67, 'Ostergotlands Lan'),
(903, 67, 'Sodermanlands Lan'),
(904, 67, 'Uppsala Lan'),
(905, 67, 'Varmlands Lan'),
(906, 67, 'Vasterbottens Lan'),
(907, 67, 'Vasternorrlands Lan'),
(908, 67, 'Vastmanlands Lan'),
(909, 67, 'Stockholms Lan'),
(910, 67, 'Skane Lan'),
(911, 67, 'Vastra Gotaland'),
(913, 46, 'Akershus'),
(914, 46, 'Aust-Agder'),
(915, 46, 'Buskerud'),
(916, 46, 'Finnmark'),
(917, 46, 'Hedmark'),
(918, 46, 'Hordaland'),
(919, 46, 'More og Romsdal'),
(920, 46, 'Nordland'),
(921, 46, 'Nord-Trondelag'),
(922, 46, 'Oppland'),
(923, 46, 'Oslo'),
(924, 46, 'Ostfold'),
(925, 46, 'Rogaland'),
(926, 46, 'Sogn og Fjordane'),
(927, 46, 'Sor-Trondelag'),
(928, 46, 'Telemark'),
(929, 46, 'Troms'),
(930, 46, 'Vest-Agder'),
(931, 46, 'Vestfold'),
(933, 63, '&ETH;&bull;land'),
(934, 63, 'Lapland'),
(935, 63, 'Oulu'),
(936, 63, 'Southern Finland'),
(937, 63, 'Eastern Finland'),
(938, 63, 'Western Finland'),
(940, 22, 'Arhus'),
(941, 22, 'Bornholm'),
(942, 22, 'Frederiksborg'),
(943, 22, 'Fyn'),
(944, 22, 'Kobenhavn'),
(945, 22, 'Staden Kobenhavn'),
(946, 22, 'Nordjylland'),
(947, 22, 'Ribe'),
(948, 22, 'Ringkobing'),
(949, 22, 'Roskilde'),
(950, 22, 'Sonderjylland'),
(951, 22, 'Storstrom'),
(952, 22, 'Vejle'),
(953, 22, 'Vestsjalland'),
(954, 22, 'Viborg'),
(956, 65, 'Hlavni Mesto Praha'),
(957, 65, 'Jihomoravsky Kraj'),
(958, 65, 'Jihocesky Kraj'),
(959, 65, 'Vysocina'),
(960, 65, 'Karlovarsky Kraj'),
(961, 65, 'Kralovehradecky Kraj'),
(962, 65, 'Liberecky Kraj'),
(963, 65, 'Olomoucky Kraj'),
(964, 65, 'Moravskoslezsky Kraj'),
(965, 65, 'Pardubicky Kraj'),
(966, 65, 'Plzensky Kraj'),
(967, 65, 'Stredocesky Kraj'),
(968, 65, 'Ustecky Kraj'),
(969, 65, 'Zlinsky Kraj'),
(971, 114, 'Berat'),
(972, 114, 'Diber'),
(973, 114, 'Durres'),
(974, 114, 'Elbasan'),
(975, 114, 'Fier'),
(976, 114, 'Gjirokaster'),
(977, 114, 'Korce'),
(978, 114, 'Kukes'),
(979, 114, 'Lezhe'),
(980, 114, 'Shkoder'),
(981, 114, 'Tirane'),
(982, 114, 'Vlore'),
(984, 145, 'Canillo'),
(985, 145, 'Encamp'),
(986, 145, 'La Massana'),
(987, 145, 'Ordino'),
(988, 145, 'Sant Julia de Loria'),
(989, 145, 'Andorra la Vella'),
(990, 145, 'Escaldes-Engordany'),
(992, 6, 'Aragatsotn'),
(993, 6, 'Ararat'),
(994, 6, 'Armavir'),
(995, 6, 'Geghark&#039;unik&#039;'),
(996, 6, 'Kotayk&#039;'),
(997, 6, 'Lorri'),
(998, 6, 'Shirak'),
(999, 6, 'Syunik&#039;'),
(1000, 6, 'Tavush'),
(1001, 6, 'Vayots&#039; Dzor'),
(1002, 6, 'Yerevan'),
(1004, 79, 'Federation of Bosnia and Herzegovina'),
(1005, 79, 'Republika Srpska'),
(1007, 11, 'Mikhaylovgrad'),
(1008, 11, 'Blagoevgrad'),
(1009, 11, 'Burgas'),
(1010, 11, 'Dobrich'),
(1011, 11, 'Gabrovo'),
(1012, 11, 'Grad Sofiya'),
(1013, 11, 'Khaskovo'),
(1014, 11, 'Kurdzhali'),
(1015, 11, 'Kyustendil'),
(1016, 11, 'Lovech'),
(1017, 11, 'Montana'),
(1018, 11, 'Pazardzhik'),
(1019, 11, 'Pernik'),
(1020, 11, 'Pleven'),
(1021, 11, 'Plovdiv'),
(1022, 11, 'Razgrad'),
(1023, 11, 'Ruse'),
(1024, 11, 'Shumen'),
(1025, 11, 'Silistra'),
(1026, 11, 'Sliven'),
(1027, 11, 'Smolyan'),
(1028, 11, 'Sofiya'),
(1029, 11, 'Stara Zagora'),
(1030, 11, 'Turgovishte'),
(1031, 11, 'Varna'),
(1032, 11, 'Veliko Turnovo'),
(1033, 11, 'Vidin'),
(1034, 11, 'Vratsa'),
(1035, 11, 'Yambol'),
(1037, 71, 'Bjelovarsko-Bilogorska'),
(1038, 71, 'Brodsko-Posavska'),
(1039, 71, 'Dubrovacko-Neretvanska'),
(1040, 71, 'Istarska'),
(1041, 71, 'Karlovacka'),
(1042, 71, 'Koprivnicko-Krizevacka'),
(1043, 71, 'Krapinsko-Zagorska'),
(1044, 71, 'Licko-Senjska'),
(1045, 71, 'Medimurska'),
(1046, 71, 'Osjecko-Baranjska'),
(1047, 71, 'Pozesko-Slavonska'),
(1048, 71, 'Primorsko-Goranska'),
(1049, 71, 'Sibensko-Kninska'),
(1050, 71, 'Sisacko-Moslavacka'),
(1051, 71, 'Splitsko-Dalmatinska'),
(1052, 71, 'Varazdinska'),
(1053, 71, 'Viroviticko-Podravska'),
(1054, 71, 'Vukovarsko-Srijemska'),
(1055, 71, 'Zadarska'),
(1056, 71, 'Zagrebacka'),
(1057, 71, 'Grad Zagreb'),
(1059, 143, 'Gibraltar'),
(1060, 20, 'Evros'),
(1061, 20, 'Rodhopi'),
(1062, 20, 'Xanthi'),
(1063, 20, 'Drama'),
(1064, 20, 'Serrai'),
(1065, 20, 'Kilkis'),
(1066, 20, 'Pella'),
(1067, 20, 'Florina'),
(1068, 20, 'Kastoria'),
(1069, 20, 'Grevena'),
(1070, 20, 'Kozani'),
(1071, 20, 'Imathia'),
(1072, 20, 'Thessaloniki'),
(1073, 20, 'Kavala'),
(1074, 20, 'Khalkidhiki'),
(1075, 20, 'Pieria'),
(1076, 20, 'Ioannina'),
(1077, 20, 'Thesprotia'),
(1078, 20, 'Preveza'),
(1079, 20, 'Arta'),
(1080, 20, 'Larisa'),
(1081, 20, 'Trikala'),
(1082, 20, 'Kardhitsa'),
(1083, 20, 'Magnisia'),
(1084, 20, 'Kerkira'),
(1085, 20, 'Levkas'),
(1086, 20, 'Kefallinia'),
(1087, 20, 'Zakinthos'),
(1088, 20, 'Fthiotis'),
(1089, 20, 'Evritania'),
(1090, 20, 'Aitolia kai Akarnania'),
(1091, 20, 'Fokis'),
(1092, 20, 'Voiotia'),
(1093, 20, 'Evvoia'),
(1094, 20, 'Attiki'),
(1095, 20, 'Argolis'),
(1096, 20, 'Korinthia'),
(1097, 20, 'Akhaia'),
(1098, 20, 'Ilia'),
(1099, 20, 'Messinia'),
(1100, 20, 'Arkadhia'),
(1101, 20, 'Lakonia'),
(1102, 20, 'Khania'),
(1103, 20, 'Rethimni'),
(1104, 20, 'Iraklion'),
(1105, 20, 'Lasithi'),
(1106, 20, 'Dhodhekanisos'),
(1107, 20, 'Samos'),
(1108, 20, 'Kikladhes'),
(1109, 20, 'Khios'),
(1110, 20, 'Lesvos'),
(1112, 14, 'Bacs-Kiskun'),
(1113, 14, 'Baranya'),
(1114, 14, 'Bekes'),
(1115, 14, 'Borsod-Abauj-Zemplen'),
(1116, 14, 'Budapest'),
(1117, 14, 'Csongrad'),
(1118, 14, 'Debrecen'),
(1119, 14, 'Fejer'),
(1120, 14, 'Gyor-Moson-Sopron'),
(1121, 14, 'Hajdu-Bihar'),
(1122, 14, 'Heves'),
(1123, 14, 'Komarom-Esztergom'),
(1124, 14, 'Miskolc'),
(1125, 14, 'Nograd'),
(1126, 14, 'Pecs'),
(1127, 14, 'Pest'),
(1128, 14, 'Somogy'),
(1129, 14, 'Szabolcs-Szatmar-Bereg'),
(1130, 14, 'Szeged'),
(1131, 14, 'Jasz-Nagykun-Szolnok'),
(1132, 14, 'Tolna'),
(1133, 14, 'Vas'),
(1134, 14, 'Veszprem'),
(1135, 14, 'Zala'),
(1136, 14, 'Gyor'),
(1150, 14, 'Veszprem'),
(1152, 126, 'Balzers'),
(1153, 126, 'Eschen'),
(1154, 126, 'Gamprin'),
(1155, 126, 'Mauren'),
(1156, 126, 'Planken'),
(1157, 126, 'Ruggell'),
(1158, 126, 'Schaan'),
(1159, 126, 'Schellenberg'),
(1160, 126, 'Triesen'),
(1161, 126, 'Triesenberg'),
(1162, 126, 'Vaduz'),
(1163, 41, 'Diekirch'),
(1164, 41, 'Grevenmacher'),
(1165, 41, 'Luxembourg'),
(1167, 85, 'Aracinovo'),
(1168, 85, 'Bac'),
(1169, 85, 'Belcista'),
(1170, 85, 'Berovo'),
(1171, 85, 'Bistrica'),
(1172, 85, 'Bitola'),
(1173, 85, 'Blatec'),
(1174, 85, 'Bogdanci'),
(1175, 85, 'Bogomila'),
(1176, 85, 'Bogovinje'),
(1177, 85, 'Bosilovo'),
(1179, 85, 'Cair'),
(1180, 85, 'Capari'),
(1181, 85, 'Caska'),
(1182, 85, 'Cegrane'),
(1184, 85, 'Centar Zupa'),
(1187, 85, 'Debar'),
(1188, 85, 'Delcevo'),
(1190, 85, 'Demir Hisar'),
(1191, 85, 'Demir Kapija'),
(1195, 85, 'Dorce Petrov'),
(1198, 85, 'Gazi Baba'),
(1199, 85, 'Gevgelija'),
(1200, 85, 'Gostivar'),
(1201, 85, 'Gradsko'),
(1204, 85, 'Jegunovce'),
(1205, 85, 'Kamenjane'),
(1207, 85, 'Karpos'),
(1208, 85, 'Kavadarci'),
(1209, 85, 'Kicevo'),
(1210, 85, 'Kisela Voda'),
(1211, 85, 'Klecevce'),
(1212, 85, 'Kocani'),
(1214, 85, 'Kondovo'),
(1217, 85, 'Kratovo'),
(1219, 85, 'Krivogastani'),
(1220, 85, 'Krusevo'),
(1223, 85, 'Kumanovo'),
(1224, 85, 'Labunista'),
(1225, 85, 'Lipkovo'),
(1228, 85, 'Makedonska Kamenica'),
(1229, 85, 'Makedonski Brod'),
(1234, 85, 'Murtino'),
(1235, 85, 'Negotino'),
(1238, 85, 'Novo Selo'),
(1240, 85, 'Ohrid'),
(1242, 85, 'Orizari'),
(1245, 85, 'Petrovec'),
(1248, 85, 'Prilep'),
(1249, 85, 'Probistip'),
(1250, 85, 'Radovis'),
(1252, 85, 'Resen'),
(1253, 85, 'Rosoman'),
(1256, 85, 'Saraj'),
(1260, 85, 'Srbinovo'),
(1262, 85, 'Star Dojran'),
(1264, 85, 'Stip'),
(1265, 85, 'Struga'),
(1266, 85, 'Strumica'),
(1267, 85, 'Studenicani'),
(1268, 85, 'Suto Orizari'),
(1269, 85, 'Sveti Nikole'),
(1270, 85, 'Tearce'),
(1271, 85, 'Tetovo'),
(1273, 85, 'Valandovo'),
(1275, 85, 'Veles'),
(1277, 85, 'Vevcani'),
(1278, 85, 'Vinica'),
(1281, 85, 'Vrapciste'),
(1286, 85, 'Zelino'),
(1289, 85, 'Zrnovci'),
(1291, 86, 'Malta'),
(1292, 44, 'La Condamine'),
(1293, 44, 'Monaco'),
(1294, 44, 'Monte-Carlo'),
(1295, 47, 'Biala Podlaska'),
(1296, 47, 'Bialystok'),
(1297, 47, 'Bielsko'),
(1298, 47, 'Bydgoszcz'),
(1299, 47, 'Chelm'),
(1300, 47, 'Ciechanow'),
(1301, 47, 'Czestochowa'),
(1302, 47, 'Elblag'),
(1303, 47, 'Gdansk'),
(1304, 47, 'Gorzow'),
(1305, 47, 'Jelenia Gora'),
(1306, 47, 'Kalisz'),
(1307, 47, 'Katowice'),
(1308, 47, 'Kielce'),
(1309, 47, 'Konin'),
(1310, 47, 'Koszalin'),
(1311, 47, 'Krakow'),
(1312, 47, 'Krosno'),
(1313, 47, 'Legnica'),
(1314, 47, 'Leszno'),
(1315, 47, 'Lodz'),
(1316, 47, 'Lomza'),
(1317, 47, 'Lublin'),
(1318, 47, 'Nowy Sacz'),
(1319, 47, 'Olsztyn'),
(1320, 47, 'Opole'),
(1321, 47, 'Ostroleka'),
(1322, 47, 'Pila'),
(1323, 47, 'Piotrkow'),
(1324, 47, 'Plock'),
(1325, 47, 'Poznan'),
(1326, 47, 'Przemysl'),
(1327, 47, 'Radom'),
(1328, 47, 'Rzeszow'),
(1329, 47, 'Siedlce'),
(1330, 47, 'Sieradz'),
(1331, 47, 'Skierniewice'),
(1332, 47, 'Slupsk'),
(1333, 47, 'Suwalki'),
(1335, 47, 'Tarnobrzeg'),
(1336, 47, 'Tarnow'),
(1337, 47, 'Torun'),
(1338, 47, 'Walbrzych'),
(1339, 47, 'Warszawa'),
(1340, 47, 'Wloclawek'),
(1341, 47, 'Wroclaw'),
(1342, 47, 'Zamosc'),
(1343, 47, 'Zielona Gora'),
(1344, 47, 'Dolnoslaskie'),
(1345, 47, 'Kujawsko-Pomorskie'),
(1346, 47, 'Lodzkie'),
(1347, 47, 'Lubelskie'),
(1348, 47, 'Lubuskie'),
(1349, 47, 'Malopolskie'),
(1350, 47, 'Mazowieckie'),
(1351, 47, 'Opolskie'),
(1352, 47, 'Podkarpackie'),
(1353, 47, 'Podlaskie'),
(1354, 47, 'Pomorskie'),
(1355, 47, 'Slaskie'),
(1356, 47, 'Swietokrzyskie'),
(1357, 47, 'Warminsko-Mazurskie'),
(1358, 47, 'Wielkopolskie'),
(1359, 47, 'Zachodniopomorskie'),
(1361, 72, 'Alba'),
(1362, 72, 'Arad'),
(1363, 72, 'Arges'),
(1364, 72, 'Bacau'),
(1365, 72, 'Bihor'),
(1366, 72, 'Bistrita-Nasaud'),
(1367, 72, 'Botosani'),
(1368, 72, 'Braila'),
(1369, 72, 'Brasov'),
(1370, 72, 'Bucuresti'),
(1371, 72, 'Buzau'),
(1372, 72, 'Caras-Severin'),
(1373, 72, 'Cluj'),
(1374, 72, 'Constanta'),
(1375, 72, 'Covasna'),
(1376, 72, 'Dambovita'),
(1377, 72, 'Dolj'),
(1378, 72, 'Galati'),
(1379, 72, 'Gorj'),
(1380, 72, 'Harghita'),
(1381, 72, 'Hunedoara'),
(1382, 72, 'Ialomita'),
(1383, 72, 'Iasi'),
(1384, 72, 'Maramures'),
(1385, 72, 'Mehedinti'),
(1386, 72, 'Mures'),
(1387, 72, 'Neamt'),
(1388, 72, 'Olt'),
(1389, 72, 'Prahova'),
(1390, 72, 'Salaj'),
(1391, 72, 'Satu Mare'),
(1392, 72, 'Sibiu'),
(1393, 72, 'Suceava'),
(1394, 72, 'Teleorman'),
(1395, 72, 'Timis'),
(1396, 72, 'Tulcea'),
(1397, 72, 'Vaslui'),
(1398, 72, 'Valcea'),
(1399, 72, 'Vrancea'),
(1400, 72, 'Calarasi'),
(1401, 72, 'Giurgiu'),
(1404, 224, 'Acquaviva'),
(1405, 224, 'Chiesanuova'),
(1406, 224, 'Domagnano'),
(1407, 224, 'Faetano'),
(1408, 224, 'Fiorentino'),
(1409, 224, 'Borgo Maggiore'),
(1410, 224, 'San Marino'),
(1411, 224, 'Monte Giardino'),
(1412, 224, 'Serravalle'),
(1413, 52, 'Banska Bystrica'),
(1414, 52, 'Bratislava'),
(1415, 52, 'Kosice'),
(1416, 52, 'Nitra'),
(1417, 52, 'Presov'),
(1418, 52, 'Trencin'),
(1419, 52, 'Trnava'),
(1420, 52, 'Zilina'),
(1423, 53, 'Beltinci'),
(1425, 53, 'Bohinj'),
(1426, 53, 'Borovnica'),
(1427, 53, 'Bovec'),
(1428, 53, 'Brda'),
(1429, 53, 'Brezice'),
(1430, 53, 'Brezovica'),
(1432, 53, 'Cerklje na Gorenjskem'),
(1434, 53, 'Cerkno'),
(1436, 53, 'Crna na Koroskem'),
(1437, 53, 'Crnomelj'),
(1438, 53, 'Divaca'),
(1439, 53, 'Dobrepolje'),
(1440, 53, 'Dol pri Ljubljani'),
(1443, 53, 'Duplek'),
(1447, 53, 'Gornji Grad'),
(1450, 53, 'Hrastnik'),
(1451, 53, 'Hrpelje-Kozina'),
(1452, 53, 'Idrija'),
(1453, 53, 'Ig'),
(1454, 53, 'Ilirska Bistrica'),
(1455, 53, 'Ivancna Gorica'),
(1462, 53, 'Komen'),
(1463, 53, 'Koper-Capodistria'),
(1464, 53, 'Kozje'),
(1465, 53, 'Kranj'),
(1466, 53, 'Kranjska Gora'),
(1467, 53, 'Krsko'),
(1469, 53, 'Lasko'),
(1470, 53, 'Ljubljana'),
(1471, 53, 'Ljubno'),
(1472, 53, 'Logatec'),
(1475, 53, 'Medvode'),
(1476, 53, 'Menges'),
(1478, 53, 'Mezica'),
(1480, 53, 'Moravce'),
(1482, 53, 'Mozirje'),
(1483, 53, 'Murska Sobota'),
(1487, 53, 'Nova Gorica'),
(1489, 53, 'Ormoz'),
(1491, 53, 'Pesnica'),
(1494, 53, 'Postojna'),
(1497, 53, 'Radece'),
(1498, 53, 'Radenci'),
(1500, 53, 'Radovljica'),
(1502, 53, 'Rogaska Slatina'),
(1505, 53, 'Sencur'),
(1506, 53, 'Sentilj'),
(1508, 53, 'Sevnica'),
(1509, 53, 'Sezana'),
(1511, 53, 'Skofja Loka'),
(1513, 53, 'Slovenj Gradec'),
(1514, 53, 'Slovenske Konjice'),
(1515, 53, 'Smarje pri Jelsah'),
(1521, 53, 'Tolmin'),
(1522, 53, 'Trbovlje'),
(1524, 53, 'Trzic'),
(1526, 53, 'Velenje'),
(1528, 53, 'Vipava'),
(1531, 53, 'Vrhnika'),
(1532, 53, 'Vuzenica'),
(1533, 53, 'Zagorje ob Savi'),
(1535, 53, 'Zelezniki'),
(1536, 53, 'Ziri'),
(1537, 53, 'Zrece'),
(1539, 53, 'Domzale'),
(1540, 53, 'Jesenice'),
(1541, 53, 'Kamnik'),
(1542, 53, 'Kocevje'),
(1544, 53, 'Lenart'),
(1545, 53, 'Litija'),
(1546, 53, 'Ljutomer'),
(1550, 53, 'Maribor'),
(1552, 53, 'Novo Mesto'),
(1553, 53, 'Piran'),
(1554, 53, 'Preddvor'),
(1555, 53, 'Ptuj'),
(1556, 53, 'Ribnica'),
(1558, 53, 'Sentjur pri Celju'),
(1559, 53, 'Slovenska Bistrica'),
(1560, 53, 'Videm'),
(1562, 53, 'Zalec'),
(1564, 109, 'Seychelles'),
(1565, 108, 'Mauritania'),
(1566, 135, 'Senegal'),
(1567, 154, 'Road Town'),
(1568, 165, 'Congo'),
(1569, 166, 'Avarua'),
(1570, 172, 'Malabo'),
(1571, 175, 'Torshavn'),
(1572, 178, 'Papeete'),
(1573, 184, 'St George&#039;s'),
(1574, 186, 'St Peter Port'),
(1575, 188, 'Bissau'),
(1576, 193, 'Saint Helier'),
(1577, 201, 'Fort-de-France'),
(1578, 207, 'Willemstad'),
(1579, 208, 'Noumea'),
(1580, 212, 'Kingston'),
(1581, 215, 'Adamstown'),
(1582, 216, 'Doha'),
(1583, 218, 'Jamestown'),
(1584, 219, 'Basseterre'),
(1585, 220, 'Castries'),
(1586, 221, 'Saint Pierre'),
(1587, 222, 'Kingstown'),
(1588, 225, 'San Tome'),
(1589, 226, 'Belgrade'),
(1590, 227, 'Freetown'),
(1591, 229, 'Mogadishu'),
(1592, 235, 'Fakaofo'),
(1593, 237, 'Port of Spain'),
(1594, 241, 'Mata-Utu'),
(1596, 89, 'Amazonas'),
(1597, 89, 'Ancash'),
(1598, 89, 'Apurímac'),
(1599, 89, 'Arequipa'),
(1600, 89, 'Ayacucho'),
(1601, 89, 'Cajamarca'),
(1602, 89, 'Callao'),
(1603, 89, 'Cusco'),
(1604, 89, 'Huancavelica'),
(1605, 89, 'Huánuco'),
(1606, 89, 'Ica'),
(1607, 89, 'Junín'),
(1608, 89, 'La Libertad'),
(1609, 89, 'Lambayeque'),
(1610, 89, 'Lima'),
(1611, 89, 'Loreto'),
(1612, 89, 'Madre de Dios'),
(1613, 89, 'Moquegua'),
(1614, 89, 'Pasco'),
(1615, 89, 'Piura'),
(1616, 89, 'Puno'),
(1617, 89, 'San Martín'),
(1618, 89, 'Tacna'),
(1619, 89, 'Tumbes'),
(1620, 89, 'Ucayali'),
(1622, 110, 'Alto Paraná'),
(1623, 110, 'Amambay'),
(1624, 110, 'Boquerón'),
(1625, 110, 'Caaguaz&uacute;'),
(1626, 110, 'Caazapá'),
(1627, 110, 'Central'),
(1628, 110, 'Concepción'),
(1629, 110, 'Cordillera'),
(1630, 110, 'Guairá'),
(1631, 110, 'Itap&uacute;a'),
(1632, 110, 'Misiones'),
(1633, 110, 'Neembuc&uacute;'),
(1634, 110, 'Paraguarí'),
(1635, 110, 'Presidente Hayes'),
(1636, 110, 'San Pedro'),
(1637, 110, 'Alto Paraguay'),
(1638, 110, 'Canindey&uacute;'),
(1639, 110, 'Chaco'),
(1642, 111, 'Artigas'),
(1643, 111, 'Canelones'),
(1644, 111, 'Cerro Largo'),
(1645, 111, 'Colonia'),
(1646, 111, 'Durazno'),
(1647, 111, 'Flores'),
(1648, 111, 'Florida'),
(1649, 111, 'Lavalleja'),
(1650, 111, 'Maldonado'),
(1651, 111, 'Montevideo'),
(1652, 111, 'Paysand&uacute;'),
(1653, 111, 'Río Negro'),
(1654, 111, 'Rivera'),
(1655, 111, 'Rocha'),
(1656, 111, 'Salto'),
(1657, 111, 'San José'),
(1658, 111, 'Soriano'),
(1659, 111, 'Tacuarembó'),
(1660, 111, 'Treinta y Tres'),
(1662, 81, 'Región de Tarapacá'),
(1663, 81, 'Región de Antofagasta'),
(1664, 81, 'Región de Atacama'),
(1665, 81, 'Región de Coquimbo'),
(1666, 81, 'Región de Valparaíso'),
(1667, 81, 'Región del Libertador General Bernardo O&#039;Higgins'),
(1668, 81, 'Región del Maule'),
(1669, 81, 'Región del Bío Bío'),
(1670, 81, 'Región de La Araucanía'),
(1671, 81, 'Región de Los Lagos'),
(1672, 81, 'Región Aisén del General Carlos Ibáñez del Campo'),
(1673, 81, 'Región de Magallanes y de la Antártica Chilena'),
(1674, 81, 'Región Metropolitana de Santiago'),
(1676, 185, 'Alta Verapaz'),
(1677, 185, 'Baja Verapaz'),
(1678, 185, 'Chimaltenango'),
(1679, 185, 'Chiquimula'),
(1680, 185, 'El Progreso'),
(1681, 185, 'Escuintla'),
(1682, 185, 'Guatemala'),
(1683, 185, 'Huehuetenango'),
(1684, 185, 'Izabal'),
(1685, 185, 'Jalapa'),
(1686, 185, 'Jutiapa'),
(1687, 185, 'Petén'),
(1688, 185, 'Quetzaltenango'),
(1689, 185, 'Quiché'),
(1690, 185, 'Retalhuleu'),
(1691, 185, 'Sacatepéquez'),
(1692, 185, 'San Marcos'),
(1693, 185, 'Santa Rosa'),
(1694, 185, 'Sololá'),
(1695, 185, 'Suchitepequez'),
(1696, 185, 'Totonicapán'),
(1697, 185, 'Zacapa'),
(1699, 82, 'Amazonas'),
(1700, 82, 'Antioquia'),
(1701, 82, 'Arauca'),
(1702, 82, 'Atlántico'),
(1703, 82, 'Caquetá'),
(1704, 82, 'Cauca'),
(1705, 82, 'César'),
(1706, 82, 'Chocó'),
(1707, 82, 'Córdoba'),
(1708, 82, 'Guaviare'),
(1709, 82, 'Guainía'),
(1710, 82, 'Huila'),
(1711, 82, 'La Guajira'),
(1712, 82, 'Meta'),
(1713, 82, 'Narino'),
(1714, 82, 'Norte de Santander'),
(1715, 82, 'Putumayo'),
(1716, 82, 'Quindío'),
(1717, 82, 'Risaralda'),
(1718, 82, 'San Andrés y Providencia'),
(1719, 82, 'Santander'),
(1720, 82, 'Sucre'),
(1721, 82, 'Tolima'),
(1722, 82, 'Valle del Cauca'),
(1723, 82, 'Vaupés'),
(1724, 82, 'Vichada'),
(1725, 82, 'Casanare'),
(1726, 82, 'Cundinamarca'),
(1727, 82, 'Distrito Especial'),
(1730, 82, 'Caldas'),
(1731, 82, 'Magdalena'),
(1733, 42, 'Aguascalientes'),
(1734, 42, 'Baja California'),
(1735, 42, 'Baja California Sur'),
(1736, 42, 'Campeche'),
(1737, 42, 'Chiapas'),
(1738, 42, 'Chihuahua'),
(1739, 42, 'Coahuila de Zaragoza'),
(1740, 42, 'Colima'),
(1741, 42, 'Distrito Federal'),
(1742, 42, 'Durango'),
(1743, 42, 'Guanajuato'),
(1744, 42, 'Guerrero'),
(1745, 42, 'Hidalgo'),
(1746, 42, 'Jalisco'),
(1747, 42, 'México'),
(1748, 42, 'Michoacán de Ocampo'),
(1749, 42, 'Morelos'),
(1750, 42, 'Nayarit'),
(1751, 42, 'Nuevo León'),
(1752, 42, 'Oaxaca'),
(1753, 42, 'Puebla'),
(1754, 42, 'Querétaro de Arteaga'),
(1755, 42, 'Quintana Roo'),
(1756, 42, 'San Luis Potosí'),
(1757, 42, 'Sinaloa'),
(1758, 42, 'Sonora'),
(1759, 42, 'Tabasco'),
(1760, 42, 'Tamaulipas'),
(1761, 42, 'Tlaxcala'),
(1762, 42, 'Veracruz-Llave'),
(1763, 42, 'Yucatán'),
(1764, 42, 'Zacatecas'),
(1766, 124, 'Bocas del Toro'),
(1767, 124, 'Chiriquí'),
(1768, 124, 'Coclé'),
(1769, 124, 'Colón'),
(1770, 124, 'Darién'),
(1771, 124, 'Herrera'),
(1772, 124, 'Los Santos'),
(1773, 124, 'Panamá'),
(1774, 124, 'San Blas'),
(1775, 124, 'Veraguas'),
(1777, 123, 'Chuquisaca'),
(1778, 123, 'Cochabamba'),
(1779, 123, 'El Beni'),
(1780, 123, 'La Paz'),
(1781, 123, 'Oruro'),
(1782, 123, 'Pando'),
(1783, 123, 'Potosí'),
(1784, 123, 'Santa Cruz'),
(1785, 123, 'Tarija'),
(1787, 36, 'Alajuela'),
(1788, 36, 'Cartago'),
(1789, 36, 'Guanacaste'),
(1790, 36, 'Heredia'),
(1791, 36, 'Limón'),
(1792, 36, 'Puntarenas'),
(1793, 36, 'San José'),
(1795, 103, 'Galápagos'),
(1796, 103, 'Azuay'),
(1797, 103, 'Bolívar'),
(1798, 103, 'Canar'),
(1799, 103, 'Carchi'),
(1800, 103, 'Chimborazo'),
(1801, 103, 'Cotopaxi'),
(1802, 103, 'El Oro'),
(1803, 103, 'Esmeraldas'),
(1804, 103, 'Guayas'),
(1805, 103, 'Imbabura'),
(1806, 103, 'Loja'),
(1807, 103, 'Los Ríos'),
(1808, 103, 'Manabí'),
(1809, 103, 'Morona-Santiago'),
(1810, 103, 'Pastaza'),
(1811, 103, 'Pichincha'),
(1812, 103, 'Tungurahua'),
(1813, 103, 'Zamora-Chinchipe'),
(1814, 103, 'Sucumbíos'),
(1815, 103, 'Napo'),
(1816, 103, 'Orellana'),
(1818, 5, 'Buenos Aires'),
(1819, 5, 'Catamarca'),
(1820, 5, 'Chaco'),
(1821, 5, 'Chubut'),
(1822, 5, 'Córdoba'),
(1823, 5, 'Corrientes'),
(1824, 5, 'Distrito Federal'),
(1825, 5, 'Entre Ríos'),
(1826, 5, 'Formosa'),
(1827, 5, 'Jujuy'),
(1828, 5, 'La Pampa'),
(1829, 5, 'La Rioja'),
(1830, 5, 'Mendoza'),
(1831, 5, 'Misiones'),
(1832, 5, 'Neuquén'),
(1833, 5, 'Río Negro'),
(1834, 5, 'Salta'),
(1835, 5, 'San Juan'),
(1836, 5, 'San Luis'),
(1837, 5, 'Santa Cruz'),
(1838, 5, 'Santa Fe'),
(1839, 5, 'Santiago del Estero'),
(1840, 5, 'Tierra del Fuego'),
(1841, 5, 'Tucumán'),
(1843, 95, 'Amazonas'),
(1844, 95, 'Anzoategui'),
(1845, 95, 'Apure'),
(1846, 95, 'Aragua'),
(1847, 95, 'Barinas'),
(1848, 95, 'Bolívar'),
(1849, 95, 'Carabobo'),
(1850, 95, 'Cojedes'),
(1851, 95, 'Delta Amacuro'),
(1852, 95, 'Falcón'),
(1853, 95, 'Guárico'),
(1854, 95, 'Lara'),
(1855, 95, 'Mérida'),
(1856, 95, 'Miranda'),
(1857, 95, 'Monagas'),
(1858, 95, 'Nueva Esparta'),
(1859, 95, 'Portuguesa'),
(1860, 95, 'Sucre'),
(1861, 95, 'Táchira'),
(1862, 95, 'Trujillo'),
(1863, 95, 'Yaracuy'),
(1864, 95, 'Zulia'),
(1865, 95, 'Dependencias Federales'),
(1866, 95, 'Distrito Capital'),
(1867, 95, 'Vargas'),
(1869, 209, 'Boaco'),
(1870, 209, 'Carazo'),
(1871, 209, 'Chinandega'),
(1872, 209, 'Chontales'),
(1873, 209, 'Estelí'),
(1874, 209, 'Granada'),
(1875, 209, 'Jinotega'),
(1876, 209, 'León'),
(1877, 209, 'Madriz'),
(1878, 209, 'Managua'),
(1879, 209, 'Masaya'),
(1880, 209, 'Matagalpa'),
(1881, 209, 'Nueva Segovia'),
(1882, 209, 'Rio San Juan'),
(1883, 209, 'Rivas'),
(1884, 209, 'Zelaya'),
(1886, 113, 'Pinar del Rio'),
(1887, 113, 'Ciudad de la Habana'),
(1888, 113, 'Matanzas'),
(1889, 113, 'Isla de la Juventud'),
(1890, 113, 'Camaguey'),
(1891, 113, 'Ciego de Avila'),
(1892, 113, 'Cienfuegos'),
(1893, 113, 'Granma'),
(1894, 113, 'Guantanamo'),
(1895, 113, 'La Habana'),
(1896, 113, 'Holguin'),
(1897, 113, 'Las Tunas'),
(1898, 113, 'Sancti Spiritus'),
(1899, 113, 'Santiago de Cuba'),
(1900, 113, 'Villa Clara'),
(1901, 12, 'Acre'),
(1902, 12, 'Alagoas'),
(1903, 12, 'Amapa'),
(1904, 12, 'Amazonas'),
(1905, 12, 'Bahia'),
(1906, 12, 'Ceara'),
(1907, 12, 'Distrito Federal'),
(1908, 12, 'Espirito Santo'),
(1909, 12, 'Mato Grosso do Sul'),
(1910, 12, 'Maranhao'),
(1911, 12, 'Mato Grosso'),
(1912, 12, 'Minas Gerais'),
(1913, 12, 'Para'),
(1914, 12, 'Paraiba'),
(1915, 12, 'Parana'),
(1916, 12, 'Piaui'),
(1917, 12, 'Rio de Janeiro'),
(1918, 12, 'Rio Grande do Norte'),
(1919, 12, 'Rio Grande do Sul'),
(1920, 12, 'Rondonia'),
(1921, 12, 'Roraima'),
(1922, 12, 'Santa Catarina'),
(1923, 12, 'Sao Paulo'),
(1924, 12, 'Sergipe'),
(1925, 12, 'Goias'),
(1926, 12, 'Pernambuco'),
(1927, 12, 'Tocantins'),
(1930, 83, 'Akureyri'),
(1931, 83, 'Arnessysla'),
(1932, 83, 'Austur-Bardastrandarsysla'),
(1933, 83, 'Austur-Hunavatnssysla'),
(1934, 83, 'Austur-Skaftafellssysla'),
(1935, 83, 'Borgarfjardarsysla'),
(1936, 83, 'Dalasysla'),
(1937, 83, 'Eyjafjardarsysla'),
(1938, 83, 'Gullbringusysla'),
(1939, 83, 'Hafnarfjordur'),
(1943, 83, 'Kjosarsysla'),
(1944, 83, 'Kopavogur'),
(1945, 83, 'Myrasysla'),
(1946, 83, 'Neskaupstadur'),
(1947, 83, 'Nordur-Isafjardarsysla'),
(1948, 83, 'Nordur-Mulasysla'),
(1949, 83, 'Nordur-Tingeyjarsysla'),
(1950, 83, 'Olafsfjordur'),
(1951, 83, 'Rangarvallasysla'),
(1952, 83, 'Reykjavik'),
(1953, 83, 'Saudarkrokur'),
(1954, 83, 'Seydisfjordur'),
(1956, 83, 'Skagafjardarsysla'),
(1957, 83, 'Snafellsnes- og Hnappadalssysla'),
(1958, 83, 'Strandasysla'),
(1959, 83, 'Sudur-Mulasysla'),
(1960, 83, 'Sudur-Tingeyjarsysla'),
(1961, 83, 'Vestmannaeyjar'),
(1962, 83, 'Vestur-Bardastrandarsysla'),
(1964, 83, 'Vestur-Isafjardarsysla'),
(1965, 83, 'Vestur-Skaftafellssysla'),
(1966, 35, 'Anhui'),
(1967, 35, 'Zhejiang'),
(1968, 35, 'Jiangxi'),
(1969, 35, 'Jiangsu'),
(1970, 35, 'Jilin'),
(1971, 35, 'Qinghai'),
(1972, 35, 'Fujian'),
(1973, 35, 'Heilongjiang'),
(1974, 35, 'Henan'),
(1975, 35, 'Hebei'),
(1976, 35, 'Hunan'),
(1977, 35, 'Hubei'),
(1978, 35, 'Xinjiang'),
(1979, 35, 'Xizang'),
(1980, 35, 'Gansu'),
(1981, 35, 'Guangxi'),
(1982, 35, 'Guizhou'),
(1983, 35, 'Liaoning'),
(1984, 35, 'Nei Mongol'),
(1985, 35, 'Ningxia'),
(1986, 35, 'Beijing'),
(1987, 35, 'Shanghai'),
(1988, 35, 'Shanxi'),
(1989, 35, 'Shandong'),
(1990, 35, 'Shaanxi'),
(1991, 35, 'Sichuan'),
(1992, 35, 'Tianjin'),
(1993, 35, 'Yunnan'),
(1994, 35, 'Guangdong'),
(1995, 35, 'Hainan'),
(1996, 35, 'Chongqing'),
(1997, 97, 'Central'),
(1998, 97, 'Coast'),
(1999, 97, 'Eastern'),
(2000, 97, 'Nairobi Area'),
(2001, 97, 'North-Eastern'),
(2002, 97, 'Nyanza'),
(2003, 97, 'Rift Valley'),
(2004, 97, 'Western'),
(2006, 195, 'Gilbert Islands'),
(2007, 195, 'Line Islands'),
(2008, 195, 'Phoenix Islands'),
(2010, 1, 'Australian Capital Territory'),
(2011, 1, 'New South Wales'),
(2012, 1, 'Northern Territory'),
(2013, 1, 'Queensland'),
(2014, 1, 'South Australia'),
(2015, 1, 'Tasmania'),
(2016, 1, 'Victoria'),
(2017, 1, 'Western Australia'),
(2018, 27, 'Dublin'),
(2019, 27, 'Galway'),
(2020, 27, 'Kildare'),
(2021, 27, 'Leitrim'),
(2022, 27, 'Limerick'),
(2023, 27, 'Mayo'),
(2024, 27, 'Meath'),
(2025, 27, 'Carlow'),
(2026, 27, 'Kilkenny'),
(2027, 27, 'Laois'),
(2028, 27, 'Longford'),
(2029, 27, 'Louth'),
(2030, 27, 'Offaly'),
(2031, 27, 'Westmeath'),
(2032, 27, 'Wexford'),
(2033, 27, 'Wicklow'),
(2034, 27, 'Roscommon'),
(2035, 27, 'Sligo'),
(2036, 27, 'Clare'),
(2037, 27, 'Cork'),
(2038, 27, 'Kerry'),
(2039, 27, 'Tipperary'),
(2040, 27, 'Waterford'),
(2041, 27, 'Cavan'),
(2042, 27, 'Donegal'),
(2043, 27, 'Monaghan'),
(2044, 50, 'Karachaeva-Cherkesskaya Respublica'),
(2045, 50, 'Raimirskii (Dolgano-Nenetskii) AO'),
(2046, 50, 'Respublica Tiva'),
(2047, 32, 'Newfoundland'),
(2048, 32, 'Nova Scotia'),
(2049, 32, 'Prince Edward Island'),
(2050, 32, 'New Brunswick'),
(2051, 32, 'Quebec'),
(2052, 32, 'Ontario'),
(2053, 32, 'Manitoba'),
(2054, 32, 'Saskatchewan'),
(2055, 32, 'Alberta'),
(2056, 32, 'British Columbia'),
(2057, 32, 'Nunavut'),
(2058, 32, 'Northwest Territories'),
(2059, 32, 'Yukon Territory'),
(2060, 19, 'Drenthe'),
(2061, 19, 'Friesland'),
(2062, 19, 'Gelderland'),
(2063, 19, 'Groningen'),
(2064, 19, 'Limburg'),
(2065, 19, 'Noord-Brabant'),
(2066, 19, 'Noord-Holland'),
(2067, 19, 'Utrecht'),
(2068, 19, 'Zeeland'),
(2069, 19, 'Zuid-Holland'),
(2071, 19, 'Overijssel'),
(2072, 19, 'Flevoland'),
(2073, 138, 'Duarte'),
(2074, 138, 'Puerto Plata'),
(2075, 138, 'Valverde'),
(2076, 138, 'María Trinidad Sánchez'),
(2077, 138, 'Azua'),
(2078, 138, 'Santiago'),
(2079, 138, 'San Cristóbal'),
(2080, 138, 'Peravia'),
(2081, 138, 'Elías Piña'),
(2082, 138, 'Barahona'),
(2083, 138, 'Monte Plata'),
(2084, 138, 'Salcedo'),
(2085, 138, 'La Altagracia'),
(2086, 138, 'San Juan'),
(2087, 138, 'Monseñor Nouel'),
(2088, 138, 'Monte Cristi'),
(2089, 138, 'Espaillat'),
(2090, 138, 'Sánchez Ramírez'),
(2091, 138, 'La Vega'),
(2092, 138, 'San Pedro de Macorís'),
(2093, 138, 'Independencia'),
(2094, 138, 'Dajabón'),
(2095, 138, 'Baoruco'),
(2096, 138, 'El Seibo'),
(2097, 138, 'Hato Mayor'),
(2098, 138, 'La Romana'),
(2099, 138, 'Pedernales'),
(2100, 138, 'Samaná'),
(2101, 138, 'Santiago Rodríguez'),
(2102, 138, 'San José de Ocoa'),
(2103, 70, 'Chiba'),
(2104, 70, 'Ehime'),
(2105, 70, 'Oita'),
(2106, 85, 'Skopje'),
(2108, 35, 'Schanghai'),
(2109, 35, 'Hongkong'),
(2110, 35, 'Neimenggu'),
(2111, 35, 'Aomen'),
(2112, 92, 'Amnat Charoen'),
(2113, 92, 'Ang Thong'),
(2114, 92, 'Bangkok'),
(2115, 92, 'Buri Ram'),
(2116, 92, 'Chachoengsao'),
(2117, 92, 'Chai Nat'),
(2118, 92, 'Chaiyaphum'),
(2119, 92, 'Chanthaburi'),
(2120, 92, 'Chiang Mai'),
(2121, 92, 'Chiang Rai'),
(2122, 92, 'Chon Buri'),
(2124, 92, 'Kalasin'),
(2126, 92, 'Kanchanaburi'),
(2127, 92, 'Khon Kaen'),
(2128, 92, 'Krabi'),
(2129, 92, 'Lampang'),
(2131, 92, 'Loei'),
(2132, 92, 'Lop Buri'),
(2133, 92, 'Mae Hong Son'),
(2134, 92, 'Maha Sarakham'),
(2137, 92, 'Nakhon Pathom'),
(2139, 92, 'Nakhon Ratchasima'),
(2140, 92, 'Nakhon Sawan'),
(2141, 92, 'Nakhon Si Thammarat'),
(2143, 92, 'Narathiwat'),
(2144, 92, 'Nong Bua Lam Phu'),
(2145, 92, 'Nong Khai'),
(2146, 92, 'Nonthaburi'),
(2147, 92, 'Pathum Thani'),
(2148, 92, 'Pattani'),
(2149, 92, 'Phangnga'),
(2150, 92, 'Phatthalung'),
(2154, 92, 'Phichit'),
(2155, 92, 'Phitsanulok'),
(2156, 92, 'Phra Nakhon Si Ayutthaya'),
(2157, 92, 'Phrae'),
(2158, 92, 'Phuket'),
(2159, 92, 'Prachin Buri'),
(2160, 92, 'Prachuap Khiri Khan'),
(2162, 92, 'Ratchaburi'),
(2163, 92, 'Rayong'),
(2164, 92, 'Roi Et'),
(2165, 92, 'Sa Kaeo'),
(2166, 92, 'Sakon Nakhon'),
(2167, 92, 'Samut Prakan'),
(2168, 92, 'Samut Sakhon'),
(2169, 92, 'Samut Songkhran'),
(2170, 92, 'Saraburi'),
(2172, 92, 'Si Sa Ket'),
(2173, 92, 'Sing Buri'),
(2174, 92, 'Songkhla'),
(2175, 92, 'Sukhothai'),
(2176, 92, 'Suphan Buri'),
(2177, 92, 'Surat Thani'),
(2178, 92, 'Surin'),
(2180, 92, 'Trang'),
(2182, 92, 'Ubon Ratchathani'),
(2183, 92, 'Udon Thani'),
(2184, 92, 'Uthai Thani'),
(2185, 92, 'Uttaradit'),
(2186, 92, 'Yala'),
(2187, 92, 'Yasothon'),
(2188, 69, 'Busan'),
(2189, 69, 'Daegu'),
(2191, 69, 'Gangwon'),
(2192, 69, 'Gwangju'),
(2193, 69, 'Gyeonggi'),
(2194, 69, 'Gyeongsangbuk'),
(2195, 69, 'Gyeongsangnam'),
(2196, 69, 'Jeju'),
(2201, 25, 'Delhi');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estado_maquina_mantenimiento`
--

CREATE TABLE `estado_maquina_mantenimiento` (
  `id` int(11) NOT NULL,
  `id_maquina_unica` int(11) NOT NULL,
  `id_status` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `id_tarea` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estado_maquina_mantenimiento`
--

INSERT INTO `estado_maquina_mantenimiento` (`id`, `id_maquina_unica`, `id_status`, `fecha_hora`, `id_tarea`) VALUES
(5, 1, 13, '2025-06-05 22:02:24', 145),
(8, 1, 1, '2025-06-05 22:06:56', 145),
(9, 1, 1, '2025-06-05 22:28:51', 145),
(10, 1, 1, '2025-06-05 22:29:09', 145),
(11, 1, 1, '2025-06-05 22:29:11', 145),
(12, 1, 1, '2025-06-05 22:29:27', 145),
(13, 1, 1, '2025-06-05 22:29:28', 145),
(14, 1, 1, '2025-06-05 22:29:29', 145),
(15, 1, 1, '2025-06-05 22:31:39', 145),
(16, 1, 1, '2025-06-05 22:34:49', 145),
(17, 1, 1, '2025-06-05 22:34:58', 145),
(18, 1, 1, '2025-06-05 22:35:03', 145),
(19, 1, 1, '2025-06-05 22:35:22', 145),
(20, 1, 1, '2025-06-05 22:37:03', 145),
(21, 1, 1, '2025-06-05 22:37:10', 145),
(22, 1, 1, '2025-06-05 22:37:11', 145),
(23, 1, 1, '2025-06-05 22:37:19', 145),
(24, 1, 1, '2025-06-05 22:37:32', 145),
(25, 1, 1, '2025-06-05 22:37:47', 145),
(26, 1, 1, '2025-06-05 22:37:48', 145),
(27, 1, 1, '2025-06-05 22:38:03', 145),
(28, 1, 1, '2025-06-05 22:38:04', 145),
(29, 1, 1, '2025-06-05 22:38:14', 145),
(30, 1, 1, '2025-06-05 22:38:15', 145),
(31, 1, 1, '2025-06-05 22:38:16', 145),
(32, 1, 1, '2025-06-05 22:40:00', 145),
(33, 1, 1, '2025-06-06 13:44:00', 145),
(34, 1, 13, '2025-06-06 14:12:09', 148),
(35, 1, 1, '2025-06-06 14:16:50', 148),
(36, 1, 13, '2025-06-06 16:29:52', 146),
(37, 1, 1, '2025-06-06 16:30:46', 146),
(38, 1, 1, '2025-06-06 16:30:53', 146),
(39, 1, 1, '2025-06-06 16:31:54', 146),
(40, 1, 1, '2025-06-06 16:32:01', 146),
(41, 1, 1, '2025-06-06 16:34:57', 146),
(42, 1, 13, '2025-06-06 17:58:55', 152);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `filtros_guardados`
--

CREATE TABLE `filtros_guardados` (
  `id_filtro` int(11) NOT NULL,
  `nombre_filtro` varchar(255) NOT NULL,
  `tabla_destino` varchar(255) NOT NULL,
  `criterios` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`criterios`)),
  `fecha_guardado` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario_id_filtro` int(155) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `herramientas`
--

CREATE TABLE `herramientas` (
  `id_herramienta` int(11) NOT NULL,
  `nombre_herramienta` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `url` varchar(155) NOT NULL,
  `nombre_imagen` varchar(255) NOT NULL,
  `id_status` int(11) DEFAULT NULL,
  `id_marca` int(11) DEFAULT NULL,
  `id_modelo` int(11) DEFAULT NULL,
  `id_tipo` int(11) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `herramientas`
--

INSERT INTO `herramientas` (`id_herramienta`, `nombre_herramienta`, `descripcion`, `url`, `nombre_imagen`, `id_status`, `id_marca`, `id_modelo`, `id_tipo`, `date_created`) VALUES
(8, 'LLAVE DE IMPACTO', 'Para desmontar y ajustar componentes mecánicos.', '../public/servidor_img/herramientas/684247256ff22_s-l1200.jpg', '684247256', 1, 1, 1, 22222229, '2025-06-06 03:40:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `herramienta_actividad`
--

CREATE TABLE `herramienta_actividad` (
  `id_actividad` int(11) NOT NULL,
  `herramienta_id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `fecha_actividad` date NOT NULL,
  `cantidad` int(11) DEFAULT 0,
  `status_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `herramienta_plan`
--

CREATE TABLE `herramienta_plan` (
  `id_plan_asociado` int(11) NOT NULL,
  `herramienta_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `fecha_asociacion` date NOT NULL,
  `status_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `herramienta_plan`
--

INSERT INTO `herramienta_plan` (`id_plan_asociado`, `herramienta_id`, `plan_id`, `fecha_asociacion`, `status_id`, `cantidad`) VALUES
(9, 8, 12, '2025-06-05', 26, 2),
(10, 8, 13, '2025-06-05', 26, 2),
(11, 8, 14, '2025-06-06', 26, 2),
(12, 8, 15, '2025-06-06', 26, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `herramienta_tarea`
--

CREATE TABLE `herramienta_tarea` (
  `id_asignacion` int(11) NOT NULL,
  `herramienta_id` int(11) NOT NULL,
  `cantidad` int(255) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `fecha_asignacion` date NOT NULL,
  `status_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `herramienta_tarea`
--

INSERT INTO `herramienta_tarea` (`id_asignacion`, `herramienta_id`, `cantidad`, `tarea_id`, `fecha_asignacion`, `status_id`) VALUES
(48, 8, 2, 145, '2025-06-06', 26),
(49, 8, 2, 146, '2025-06-06', 26),
(50, 8, 2, 147, '2025-06-06', 25),
(51, 8, 2, 148, '2025-06-06', 26),
(52, 8, 2, 150, '2025-06-06', 26),
(53, 8, 2, 151, '2025-06-06', 26),
(54, 8, 2, 152, '2025-06-06', 26),
(55, 8, 2, 153, '2025-06-07', 26);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_compra`
--

CREATE TABLE `historial_compra` (
  `id_compra` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `cantidad` decimal(10,2) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `total` decimal(10,2) GENERATED ALWAYS AS (`cantidad` * `precio`) STORED,
  `id_codigo` int(11) NOT NULL,
  `id_relacion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_solicitudes`
--

CREATE TABLE `historial_solicitudes` (
  `id_historial` int(11) NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  `id_perfil` int(11) NOT NULL,
  `estado_anterior` varchar(50) DEFAULT NULL,
  `estado_nuevo` varchar(50) DEFAULT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `imagen_blog`
--

CREATE TABLE `imagen_blog` (
  `id_imagen` int(11) NOT NULL,
  `id_blog` int(11) NOT NULL,
  `url_imagen` varchar(255) NOT NULL,
  `descripcion_imagen` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_herramientas`
--

CREATE TABLE `inventario_herramientas` (
  `id_inventario_herramienta` int(11) NOT NULL,
  `herramienta_id` int(11) NOT NULL,
  `cantidad` int(11) DEFAULT 1,
  `status_id` int(11) DEFAULT NULL,
  `stock_minimo` int(11) NOT NULL,
  `stock_maximo` int(11) NOT NULL,
  `punto_reorden` int(11) DEFAULT NULL,
  `fecha_ultima_reposicion` date DEFAULT NULL,
  `id_sede` int(11) DEFAULT 1,
  `id_almacen` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario_herramientas`
--

INSERT INTO `inventario_herramientas` (`id_inventario_herramienta`, `herramienta_id`, `cantidad`, `status_id`, `stock_minimo`, `stock_maximo`, `punto_reorden`, `fecha_ultima_reposicion`, `id_sede`, `id_almacen`) VALUES
(5, 8, 0, 1, 10, 20, 15, NULL, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_maquina`
--

CREATE TABLE `inventario_maquina` (
  `id_inventario_maquina` int(11) NOT NULL,
  `id_maquina` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `sede_id` int(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_producto`
--

CREATE TABLE `inventario_producto` (
  `id_inventario_producto` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costo_total` decimal(15,2) DEFAULT NULL,
  `id_almacen` int(11) NOT NULL DEFAULT 1,
  `stock_minimo` int(11) NOT NULL,
  `stock_maximo` int(11) NOT NULL,
  `punto_reorden` int(11) DEFAULT NULL,
  `fecha_ultima_reposicion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_repuesto`
--

CREATE TABLE `inventario_repuesto` (
  `id_inventario_repuesto` int(11) NOT NULL,
  `id_repuesto` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `Costo_Total` decimal(15,2) DEFAULT NULL,
  `id_almacen` int(11) NOT NULL DEFAULT 1,
  `stock_minimo` int(11) NOT NULL,
  `stock_maximo` int(11) NOT NULL,
  `punto_reorden` int(11) DEFAULT NULL,
  `fecha_ultima_reposicion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario_repuesto`
--

INSERT INTO `inventario_repuesto` (`id_inventario_repuesto`, `id_repuesto`, `Cantidad`, `Costo_Total`, `id_almacen`, `stock_minimo`, `stock_maximo`, `punto_reorden`, `fecha_ultima_reposicion`) VALUES
(8, 34, 0, NULL, 1, 10, 20, 15, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maquina`
--

CREATE TABLE `maquina` (
  `id_maquina` int(11) NOT NULL,
  `codigo_maquina` varchar(255) NOT NULL,
  `nombre_maquina` varchar(100) NOT NULL,
  `descripcion_funcionamiento` varchar(255) NOT NULL,
  `elaborada_por` varchar(255) NOT NULL,
  `id_marca` int(11) NOT NULL,
  `id_modelo` int(11) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `sugerencia_mantenimiento` varchar(255) NOT NULL,
  `nombre_imagen` varchar(100) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `color` varchar(255) NOT NULL,
  `id_status` int(11) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `maquina`
--

INSERT INTO `maquina` (`id_maquina`, `codigo_maquina`, `nombre_maquina`, `descripcion_funcionamiento`, `elaborada_por`, `id_marca`, `id_modelo`, `id_tipo`, `sugerencia_mantenimiento`, `nombre_imagen`, `url`, `color`, `id_status`, `date_created`) VALUES
(27, 'MO', 'MOLINO', '<p>Equipo encargado de mezclar MBTS, TMTM, Óxido Zinc, Acelerantes, Azufre, Aceite Naftenico, Ácido Estearico, Struktol W16, entre otros químicos, para dar origen al Butilo, el cual se usa para elaborar los balones.</p>', '', 1, 1, 22222229, 'mensual', '684243d040ce8_molino.jpg', '../public/servidor_img/maquina/684243d040ce8_molino.jpg', '#000a9c', 1, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maquina_repuesto`
--

CREATE TABLE `maquina_repuesto` (
  `id_maquina_repuesto` int(11) NOT NULL,
  `id_maquina` int(11) NOT NULL,
  `id_repuesto` int(11) NOT NULL,
  `id_status` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `maquina_unica`
--

CREATE TABLE `maquina_unica` (
  `id_maquina_unica` int(11) NOT NULL,
  `id_maquina` int(11) NOT NULL,
  `CodigoUnico` varchar(100) NOT NULL,
  `Almacen` varchar(100) NOT NULL,
  `id_sede` int(50) DEFAULT 1,
  `id_status` int(50) DEFAULT 1,
  `FechaUltimaActualizacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `maquina_unica`
--

INSERT INTO `maquina_unica` (`id_maquina_unica`, `id_maquina`, `CodigoUnico`, `Almacen`, `id_sede`, `id_status`, `FechaUltimaActualizacion`) VALUES
(1, 27, 'MO-1', '', 1, 1, '2025-06-06'),
(2, 27, 'MO-2', '', 1, 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca`
--

CREATE TABLE `marca` (
  `id_marca` int(11) NOT NULL,
  `nombre_marca` varchar(50) NOT NULL,
  `id_status` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marca`
--

INSERT INTO `marca` (`id_marca`, `nombre_marca`, `id_status`, `fecha_creacion`) VALUES
(1, 'MARCA SIN IDENTIFICACIÓN (MSI)', 1, '2025-05-31 12:12:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `marca_modelo`
--

CREATE TABLE `marca_modelo` (
  `id_marca` int(11) NOT NULL,
  `id_modelo` int(11) NOT NULL,
  `id_status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `marca_modelo`
--

INSERT INTO `marca_modelo` (`id_marca`, `id_modelo`, `id_status`) VALUES
(1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `menus`
--

CREATE TABLE `menus` (
  `id_menu` int(11) NOT NULL,
  `nombre_menu` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `url_menu` varchar(255) NOT NULL,
  `id_status` int(11) NOT NULL DEFAULT 1,
  `tipo_menu` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `menus`
--

INSERT INTO `menus` (`id_menu`, `nombre_menu`, `descripcion`, `url_menu`, `id_status`, `tipo_menu`) VALUES
(1, 'Inicio', 'Inicio', 'dashboard.php', 1, 1),
(2, 'Empleado', 'Empleado', 'empleado.php', 1, 1),
(3, 'Inventario', 'Inventario', 'Inventario.php', 1, 1),
(4, 'Mantenimiento', 'Mantenimiento', 'mantenimiento.php', 1, 1),
(5, 'Reporte', 'Reporte', 'reporte.php', 1, 1),
(6, 'Solicitudes', 'Solicitudes', 'solicitudes.php', 1, 1),
(7, 'Configuración', 'Configuracion', 'configuracion.php', 1, 2),
(8, 'Perfil', 'Perfil', 'perfil.php', 1, 2),
(9, 'Empresa', 'Empresa', 'empresa.php', 1, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modelo`
--

CREATE TABLE `modelo` (
  `id_modelo` int(11) NOT NULL,
  `nombre_modelo` varchar(50) NOT NULL,
  `año` varchar(255) NOT NULL,
  `id_status` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `modelo`
--

INSERT INTO `modelo` (`id_modelo`, `nombre_modelo`, `año`, `id_status`, `fecha_creacion`) VALUES
(1, 'MODELO SIN IDENTIFICACIÓN (MSI)', '2025', 1, '2025-05-31 18:12:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_herramientas`
--

CREATE TABLE `movimiento_herramientas` (
  `id_movimiento` int(11) NOT NULL,
  `herramienta_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `id_tipo_movimiento` int(100) NOT NULL,
  `fecha_movimiento` datetime DEFAULT current_timestamp(),
  `status_id` int(11) DEFAULT NULL,
  `id_almacen_origen` int(11) DEFAULT 1,
  `id_almacen_destino` int(11) DEFAULT 1,
  `cantidad` int(11) DEFAULT 1,
  `descripcion` varchar(255) NOT NULL,
  `id_solicitud` int(155) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_maquina`
--

CREATE TABLE `movimiento_maquina` (
  `id_movimiento_maquina` int(11) NOT NULL,
  `id_maquina` int(11) NOT NULL,
  `id_almacen_origen` int(11) DEFAULT NULL,
  `id_almacen_destino` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_movimiento` datetime NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_tipo_movimiento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_producto`
--

CREATE TABLE `movimiento_producto` (
  `id_movimiento_producto` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_almacen_origen` int(11) DEFAULT NULL,
  `id_almacen_destino` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_movimiento` datetime NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_tipo_movimiento` int(11) NOT NULL,
  `id_solicitud` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimiento_repuesto`
--

CREATE TABLE `movimiento_repuesto` (
  `id_movimiento_repuesto` int(11) NOT NULL,
  `id_repuesto` int(11) NOT NULL,
  `id_almacen_origen` int(11) DEFAULT NULL,
  `id_almacen_destino` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_movimiento` datetime NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_tipo_movimiento` int(11) NOT NULL,
  `id_solicitud` int(155) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_perfil` int(11) NOT NULL,
  `tipo_notificacion` varchar(50) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp(),
  `id_status` int(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_perfil`, `tipo_notificacion`, `mensaje`, `fecha_envio`, `id_status`) VALUES
(1, 2, 'compra', 'La compra con insumos ha sido aprobada para la solicitud 34.', '2025-06-06 22:11:17', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_compra`
--

CREATE TABLE `orden_compra` (
  `id_orden` int(11) NOT NULL,
  `reporte_id` int(11) NOT NULL,
  `proveedor_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `fecha_orden` date NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pais`
--

CREATE TABLE `pais` (
  `id` int(11) NOT NULL,
  `paisnombre` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pais`
--

INSERT INTO `pais` (`id`, `paisnombre`) VALUES
(1, 'Australia'),
(2, 'Austria'),
(3, 'Azerbaiyán'),
(4, 'Anguilla'),
(5, 'Argentina'),
(6, 'Armenia'),
(7, 'Bielorrusia'),
(8, 'Belice'),
(9, 'Bélgica'),
(10, 'Bermudas'),
(11, 'Bulgaria'),
(12, 'Brasil'),
(13, 'Reino Unido'),
(14, 'Hungría'),
(15, 'Vietnam'),
(16, 'Haiti'),
(17, 'Guadalupe'),
(18, 'Alemania'),
(19, 'Países Bajos, Holanda'),
(20, 'Grecia'),
(21, 'Georgia'),
(22, 'Dinamarca'),
(23, 'Egipto'),
(24, 'Israel'),
(25, 'India'),
(26, 'Irán'),
(27, 'Irlanda'),
(28, 'España'),
(29, 'Italia'),
(30, 'Kazajstán'),
(31, 'Camerún'),
(32, 'Canadá'),
(33, 'Chipre'),
(34, 'Kirguistán'),
(35, 'China'),
(36, 'Costa Rica'),
(37, 'Kuwait'),
(38, 'Letonia'),
(39, 'Libia'),
(40, 'Lituania'),
(41, 'Luxemburgo'),
(42, 'México'),
(43, 'Moldavia'),
(44, 'Mónaco'),
(45, 'Nueva Zelanda'),
(46, 'Noruega'),
(47, 'Polonia'),
(48, 'Portugal'),
(49, 'Reunión'),
(50, 'Rusia'),
(51, 'El Salvador'),
(52, 'Eslovaquia'),
(53, 'Eslovenia'),
(54, 'Surinam'),
(55, 'Estados Unidos'),
(56, 'Tadjikistan'),
(57, 'Turkmenistan'),
(58, 'Islas Turcas y Caicos'),
(59, 'Turquía'),
(60, 'Uganda'),
(61, 'Uzbekistán'),
(62, 'Ucrania'),
(63, 'Finlandia'),
(64, 'Francia'),
(65, 'República Checa'),
(66, 'Suiza'),
(67, 'Suecia'),
(68, 'Estonia'),
(69, 'Corea del Sur'),
(70, 'Japón'),
(71, 'Croacia'),
(72, 'Rumanía'),
(73, 'Hong Kong'),
(74, 'Indonesia'),
(75, 'Jordania'),
(76, 'Malasia'),
(77, 'Singapur'),
(78, 'Taiwan'),
(79, 'Bosnia y Herzegovina'),
(80, 'Bahamas'),
(81, 'Chile'),
(82, 'Colombia'),
(83, 'Islandia'),
(84, 'Corea del Norte'),
(85, 'Macedonia'),
(86, 'Malta'),
(87, 'Pakistán'),
(88, 'Papúa-Nueva Guinea'),
(89, 'Perú'),
(90, 'Filipinas'),
(91, 'Arabia Saudita'),
(92, 'Tailandia'),
(93, 'Emiratos árabes Unidos'),
(94, 'Groenlandia'),
(95, 'Venezuela'),
(96, 'Zimbabwe'),
(97, 'Kenia'),
(98, 'Algeria'),
(99, 'Líbano'),
(100, 'Botsuana'),
(101, 'Tanzania'),
(102, 'Namibia'),
(103, 'Ecuador'),
(104, 'Marruecos'),
(105, 'Ghana'),
(106, 'Siria'),
(107, 'Nepal'),
(108, 'Mauritania'),
(109, 'Seychelles'),
(110, 'Paraguay'),
(111, 'Uruguay'),
(112, 'Congo (Brazzaville)'),
(113, 'Cuba'),
(114, 'Albania'),
(115, 'Nigeria'),
(116, 'Zambia'),
(117, 'Mozambique'),
(119, 'Angola'),
(120, 'Sri Lanka'),
(121, 'Etiopía'),
(122, 'Túnez'),
(123, 'Bolivia'),
(124, 'Panamá'),
(125, 'Malawi'),
(126, 'Liechtenstein'),
(127, 'Bahrein'),
(128, 'Barbados'),
(130, 'Chad'),
(131, 'Man, Isla de'),
(132, 'Jamaica'),
(133, 'Malí'),
(134, 'Madagascar'),
(135, 'Senegal'),
(136, 'Togo'),
(137, 'Honduras'),
(138, 'República Dominicana'),
(139, 'Mongolia'),
(140, 'Irak'),
(141, 'Sudáfrica'),
(142, 'Aruba'),
(143, 'Gibraltar'),
(144, 'Afganistán'),
(145, 'Andorra'),
(147, 'Antigua y Barbuda'),
(149, 'Bangladesh'),
(151, 'Benín'),
(152, 'Bután'),
(154, 'Islas Virgenes Británicas'),
(155, 'Brunéi'),
(156, 'Burkina Faso'),
(157, 'Burundi'),
(158, 'Camboya'),
(159, 'Cabo Verde'),
(164, 'Comores'),
(165, 'Congo (Kinshasa)'),
(166, 'Cook, Islas'),
(168, 'Costa de Marfil'),
(169, 'Djibouti, Yibuti'),
(171, 'Timor Oriental'),
(172, 'Guinea Ecuatorial'),
(173, 'Eritrea'),
(175, 'Feroe, Islas'),
(176, 'Fiyi'),
(178, 'Polinesia Francesa'),
(180, 'Gabón'),
(181, 'Gambia'),
(184, 'Granada'),
(185, 'Guatemala'),
(186, 'Guernsey'),
(187, 'Guinea'),
(188, 'Guinea-Bissau'),
(189, 'Guyana'),
(193, 'Jersey'),
(195, 'Kiribati'),
(196, 'Laos'),
(197, 'Lesotho'),
(198, 'Liberia'),
(200, 'Maldivas'),
(201, 'Martinica'),
(202, 'Mauricio'),
(205, 'Myanmar'),
(206, 'Nauru'),
(207, 'Antillas Holandesas'),
(208, 'Nueva Caledonia'),
(209, 'Nicaragua'),
(210, 'Níger'),
(212, 'Norfolk Island'),
(213, 'Omán'),
(215, 'Isla Pitcairn'),
(216, 'Qatar'),
(217, 'Ruanda'),
(218, 'Santa Elena'),
(219, 'San Cristobal y Nevis'),
(220, 'Santa Lucía'),
(221, 'San Pedro y Miquelón'),
(222, 'San Vincente y Granadinas'),
(223, 'Samoa'),
(224, 'San Marino'),
(225, 'San Tomé y Príncipe'),
(226, 'Serbia y Montenegro'),
(227, 'Sierra Leona'),
(228, 'Islas Salomón'),
(229, 'Somalia'),
(232, 'Sudán'),
(234, 'Swazilandia'),
(235, 'Tokelau'),
(236, 'Tonga'),
(237, 'Trinidad y Tobago'),
(239, 'Tuvalu'),
(240, 'Vanuatu'),
(241, 'Wallis y Futuna'),
(242, 'Sáhara Occidental'),
(243, 'Yemen'),
(246, 'Puerto Rico');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfiles`
--

CREATE TABLE `perfiles` (
  `id_perfil` int(11) NOT NULL,
  `nombre_perfil` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `id_status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `perfiles`
--

INSERT INTO `perfiles` (`id_perfil`, `nombre_perfil`, `descripcion`, `id_status`) VALUES
(1, 'Administrador', 'Acceso completo al sistema', 1),
(2, 'Ingeniero', 'Planificación de mantenimiento y gestión de inventario.', 1),
(3, 'Gerente', 'Acepta la solicitudes del Ingeniero y pude ver todo lo que se hace en el software.\r\n', 1),
(4, 'Mecánico', 'Realiza los mantenimiento', 1),
(5, 'Asistente', 'Depende de sus permisos', 1),
(6, 'Recursos Humanos', 'Encargado de registrar y actualizar información del personal en el sistema', 1),
(7, 'Coordinador de Compras', 'Responsable de gestionar proveedores y aprobar adquisiciones en el sistema', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil_menu`
--

CREATE TABLE `perfil_menu` (
  `id_perfil` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `id_status` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `perfil_menu`
--

INSERT INTO `perfil_menu` (`id_perfil`, `id_menu`, `id_status`) VALUES
(1, 1, 1),
(1, 2, 1),
(1, 3, 1),
(1, 4, 1),
(1, 5, 1),
(1, 6, 1),
(1, 7, 1),
(1, 8, 1),
(1, 9, 1),
(2, 1, 1),
(2, 2, 1),
(2, 3, 1),
(2, 4, 1),
(2, 5, 1),
(2, 7, 1),
(2, 8, 1),
(3, 1, 1),
(3, 2, 1),
(3, 3, 1),
(3, 4, 1),
(3, 5, 1),
(3, 6, 1),
(3, 7, 1),
(3, 8, 1),
(3, 9, 1),
(4, 8, 1),
(5, 2, 1),
(5, 8, 1),
(6, 1, 1),
(6, 2, 1),
(6, 5, 1),
(6, 7, 1),
(6, 8, 1),
(7, 1, 1),
(7, 5, 1),
(7, 6, 1),
(7, 7, 1),
(7, 8, 1),
(2, 6, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil_permiso_menu`
--

CREATE TABLE `perfil_permiso_menu` (
  `id_perfil` int(11) NOT NULL,
  `id_menu` int(11) NOT NULL,
  `id_permiso` int(11) NOT NULL,
  `id_status` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `perfil_permiso_menu`
--

INSERT INTO `perfil_permiso_menu` (`id_perfil`, `id_menu`, `id_permiso`, `id_status`) VALUES
(1, 1, 1, 1),
(1, 2, 1, 1),
(1, 2, 2, 1),
(1, 2, 3, 1),
(1, 2, 4, 1),
(1, 5, 4, 1),
(1, 5, 5, 1),
(1, 6, 1, 1),
(1, 6, 3, 1),
(1, 6, 4, 1),
(2, 1, 1, 1),
(2, 2, 1, 1),
(2, 5, 4, 1),
(2, 5, 5, 1),
(3, 1, 1, 1),
(3, 2, 1, 1),
(3, 5, 4, 1),
(3, 5, 5, 1),
(3, 6, 1, 1),
(3, 6, 3, 1),
(3, 6, 4, 1),
(5, 2, 1, 1),
(5, 2, 3, 1),
(5, 2, 4, 1),
(6, 1, 1, 1),
(6, 2, 1, 1),
(6, 2, 2, 1),
(6, 2, 3, 1),
(6, 2, 4, 1),
(6, 5, 4, 1),
(6, 5, 5, 1),
(7, 1, 1, 1),
(7, 5, 4, 1),
(7, 5, 5, 1),
(7, 6, 1, 1),
(7, 6, 3, 1),
(7, 6, 4, 1),
(1, 1, 2, 2),
(2, 2, 2, 2),
(2, 2, 3, 2),
(2, 2, 4, 2),
(2, 6, 1, 2),
(2, 6, 3, 2),
(2, 6, 4, 2),
(5, 2, 2, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil_permiso_submenu`
--

CREATE TABLE `perfil_permiso_submenu` (
  `id_perfil` int(11) NOT NULL,
  `id_submenu` int(11) NOT NULL,
  `id_permiso` int(11) NOT NULL,
  `id_status` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `perfil_permiso_submenu`
--

INSERT INTO `perfil_permiso_submenu` (`id_perfil`, `id_submenu`, `id_permiso`, `id_status`) VALUES
(1, 1, 3, 1),
(1, 2, 3, 1),
(1, 3, 3, 1),
(1, 4, 3, 1),
(1, 5, 1, 1),
(1, 5, 2, 1),
(1, 5, 3, 1),
(1, 6, 1, 1),
(1, 6, 2, 1),
(1, 6, 3, 1),
(1, 6, 4, 1),
(1, 7, 1, 1),
(1, 7, 2, 1),
(1, 7, 3, 1),
(1, 7, 4, 1),
(1, 8, 1, 1),
(1, 8, 2, 1),
(1, 8, 3, 1),
(1, 8, 4, 1),
(1, 9, 1, 1),
(1, 9, 2, 1),
(1, 9, 3, 1),
(1, 9, 4, 1),
(1, 10, 1, 1),
(1, 10, 2, 1),
(1, 10, 3, 1),
(1, 10, 4, 1),
(1, 11, 1, 1),
(1, 11, 2, 1),
(1, 11, 3, 1),
(1, 11, 4, 1),
(1, 12, 1, 1),
(1, 12, 2, 1),
(1, 12, 3, 1),
(1, 12, 4, 1),
(1, 13, 1, 1),
(1, 13, 2, 1),
(1, 13, 3, 1),
(1, 13, 4, 1),
(1, 14, 1, 1),
(1, 14, 2, 1),
(1, 14, 3, 1),
(1, 14, 4, 1),
(1, 15, 1, 1),
(1, 15, 2, 1),
(1, 15, 3, 1),
(1, 15, 4, 1),
(1, 16, 1, 1),
(1, 16, 2, 1),
(1, 16, 3, 1),
(1, 16, 4, 1),
(1, 17, 1, 1),
(1, 17, 2, 1),
(1, 17, 3, 1),
(1, 17, 4, 1),
(1, 18, 1, 1),
(1, 18, 2, 1),
(1, 18, 3, 1),
(1, 18, 4, 1),
(1, 19, 1, 1),
(1, 19, 2, 1),
(1, 19, 3, 1),
(1, 19, 4, 1),
(1, 20, 1, 1),
(1, 20, 2, 1),
(1, 20, 3, 1),
(1, 20, 4, 1),
(1, 21, 3, 1),
(1, 22, 3, 1),
(1, 23, 3, 1),
(1, 24, 3, 1),
(1, 25, 3, 1),
(1, 26, 1, 1),
(1, 27, 1, 1),
(1, 27, 2, 1),
(1, 27, 3, 1),
(1, 27, 4, 1),
(1, 28, 1, 1),
(1, 29, 1, 1),
(1, 29, 2, 1),
(1, 29, 4, 1),
(1, 29, 5, 1),
(1, 30, 1, 1),
(1, 30, 2, 1),
(1, 30, 4, 1),
(1, 30, 5, 1),
(1, 31, 1, 1),
(1, 31, 2, 1),
(1, 31, 4, 1),
(1, 31, 5, 1),
(1, 32, 1, 1),
(1, 32, 2, 1),
(1, 32, 3, 1),
(1, 32, 4, 1),
(1, 33, 1, 1),
(1, 33, 2, 1),
(1, 33, 3, 1),
(1, 33, 4, 1),
(1, 34, 1, 1),
(1, 34, 2, 1),
(1, 34, 3, 1),
(1, 34, 4, 1),
(1, 36, 1, 1),
(1, 36, 2, 1),
(1, 36, 3, 1),
(1, 36, 4, 1),
(2, 11, 1, 1),
(2, 11, 2, 1),
(2, 11, 3, 1),
(2, 11, 4, 1),
(2, 12, 1, 1),
(2, 12, 2, 1),
(2, 12, 3, 1),
(2, 12, 4, 1),
(2, 13, 1, 1),
(2, 13, 2, 1),
(2, 13, 3, 1),
(2, 13, 4, 1),
(2, 14, 1, 1),
(2, 14, 2, 1),
(2, 14, 3, 1),
(2, 14, 4, 1),
(2, 15, 1, 1),
(2, 15, 2, 1),
(2, 15, 3, 1),
(2, 15, 4, 1),
(2, 16, 1, 1),
(2, 16, 2, 1),
(2, 16, 3, 1),
(2, 16, 4, 1),
(2, 17, 1, 1),
(2, 17, 2, 1),
(2, 17, 3, 1),
(2, 17, 4, 1),
(2, 18, 1, 1),
(2, 19, 1, 1),
(2, 19, 2, 1),
(2, 19, 3, 1),
(2, 19, 4, 1),
(2, 20, 1, 1),
(2, 21, 3, 1),
(2, 22, 3, 1),
(2, 23, 3, 1),
(2, 24, 3, 1),
(2, 25, 3, 1),
(2, 26, 1, 1),
(2, 29, 1, 1),
(2, 29, 2, 1),
(2, 29, 4, 1),
(2, 29, 5, 1),
(2, 30, 1, 1),
(2, 30, 2, 1),
(2, 30, 4, 1),
(2, 30, 5, 1),
(2, 31, 1, 1),
(2, 31, 2, 1),
(2, 31, 4, 1),
(2, 31, 5, 1),
(2, 32, 1, 1),
(2, 32, 2, 1),
(2, 32, 3, 1),
(2, 32, 4, 1),
(2, 33, 1, 1),
(2, 33, 2, 1),
(2, 33, 3, 1),
(2, 33, 4, 1),
(2, 34, 1, 1),
(2, 34, 2, 1),
(2, 34, 3, 1),
(2, 34, 4, 1),
(2, 36, 1, 1),
(2, 36, 2, 1),
(2, 36, 3, 1),
(2, 36, 4, 1),
(3, 1, 3, 1),
(3, 2, 3, 1),
(3, 3, 3, 1),
(3, 4, 3, 1),
(3, 5, 1, 1),
(3, 6, 1, 1),
(3, 7, 1, 1),
(3, 8, 1, 1),
(3, 9, 1, 1),
(3, 10, 1, 1),
(3, 11, 1, 1),
(3, 12, 1, 1),
(3, 13, 1, 1),
(3, 14, 1, 1),
(3, 15, 1, 1),
(3, 16, 1, 1),
(3, 17, 1, 1),
(3, 18, 1, 1),
(3, 19, 1, 1),
(3, 20, 1, 1),
(3, 21, 3, 1),
(3, 22, 3, 1),
(3, 23, 3, 1),
(3, 24, 3, 1),
(3, 25, 3, 1),
(3, 26, 1, 1),
(3, 29, 1, 1),
(3, 30, 1, 1),
(3, 31, 1, 1),
(3, 32, 1, 1),
(3, 33, 1, 1),
(3, 34, 1, 1),
(3, 36, 1, 1),
(4, 21, 3, 1),
(4, 22, 3, 1),
(4, 23, 3, 1),
(4, 24, 3, 1),
(4, 25, 3, 1),
(4, 26, 1, 1),
(5, 21, 3, 1),
(5, 22, 3, 1),
(5, 23, 3, 1),
(5, 24, 3, 1),
(5, 25, 3, 1),
(5, 26, 1, 1),
(6, 20, 1, 1),
(6, 20, 2, 1),
(6, 20, 3, 1),
(6, 20, 4, 1),
(6, 21, 3, 1),
(6, 22, 3, 1),
(6, 23, 3, 1),
(6, 24, 3, 1),
(6, 25, 3, 1),
(6, 26, 1, 1),
(7, 18, 1, 1),
(7, 18, 2, 1),
(7, 18, 3, 1),
(7, 18, 4, 1),
(7, 19, 1, 1),
(7, 19, 2, 1),
(7, 19, 3, 1),
(7, 19, 4, 1),
(7, 21, 3, 1),
(7, 22, 3, 1),
(7, 23, 3, 1),
(7, 24, 3, 1),
(7, 25, 3, 1),
(7, 26, 1, 1),
(3, 27, 1, 2),
(3, 28, 1, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil_submenu`
--

CREATE TABLE `perfil_submenu` (
  `id_perfil` int(11) NOT NULL,
  `id_submenu` int(11) NOT NULL,
  `id_status` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `perfil_submenu`
--

INSERT INTO `perfil_submenu` (`id_perfil`, `id_submenu`, `id_status`) VALUES
(1, 1, 1),
(1, 2, 1),
(1, 3, 1),
(1, 4, 1),
(1, 5, 1),
(1, 6, 1),
(1, 7, 1),
(1, 8, 1),
(1, 9, 1),
(1, 10, 1),
(1, 11, 1),
(1, 12, 1),
(1, 13, 1),
(1, 14, 1),
(1, 15, 1),
(1, 16, 1),
(1, 17, 1),
(1, 18, 1),
(1, 19, 1),
(1, 20, 1),
(1, 21, 1),
(1, 22, 1),
(1, 23, 1),
(1, 24, 1),
(1, 25, 1),
(1, 26, 1),
(1, 27, 1),
(1, 28, 1),
(1, 29, 1),
(1, 30, 1),
(1, 31, 1),
(1, 32, 1),
(1, 33, 1),
(1, 34, 1),
(1, 35, 1),
(1, 36, 1),
(2, 11, 1),
(2, 12, 1),
(2, 13, 1),
(2, 14, 1),
(2, 15, 1),
(2, 16, 1),
(2, 17, 1),
(2, 18, 1),
(2, 19, 1),
(2, 20, 1),
(2, 21, 1),
(2, 22, 1),
(2, 23, 1),
(2, 24, 1),
(2, 25, 1),
(2, 26, 1),
(2, 29, 1),
(2, 30, 1),
(2, 31, 1),
(2, 32, 1),
(2, 33, 1),
(2, 34, 1),
(2, 36, 1),
(3, 1, 1),
(3, 2, 1),
(3, 3, 1),
(3, 4, 1),
(3, 5, 1),
(3, 6, 1),
(3, 7, 1),
(3, 8, 1),
(3, 9, 1),
(3, 10, 1),
(3, 11, 1),
(3, 12, 1),
(3, 13, 1),
(3, 14, 1),
(3, 15, 1),
(3, 16, 1),
(3, 17, 1),
(3, 18, 1),
(3, 19, 1),
(3, 20, 1),
(3, 21, 1),
(3, 22, 1),
(3, 23, 1),
(3, 24, 1),
(3, 25, 1),
(3, 26, 1),
(3, 29, 1),
(3, 30, 1),
(3, 31, 1),
(3, 32, 1),
(3, 33, 1),
(3, 34, 1),
(3, 36, 1),
(4, 21, 1),
(4, 22, 1),
(4, 23, 1),
(4, 24, 1),
(4, 25, 1),
(4, 26, 1),
(5, 21, 1),
(5, 22, 1),
(5, 23, 1),
(5, 24, 1),
(5, 25, 1),
(5, 26, 1),
(6, 20, 1),
(6, 21, 1),
(6, 22, 1),
(6, 23, 1),
(6, 24, 1),
(6, 25, 1),
(6, 26, 1),
(7, 18, 1),
(7, 19, 1),
(7, 21, 1),
(7, 22, 1),
(7, 23, 1),
(7, 24, 1),
(7, 25, 1),
(7, 26, 1),
(3, 27, 2),
(3, 28, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `perfil_widget`
--

CREATE TABLE `perfil_widget` (
  `id` int(11) NOT NULL,
  `id_perfil` int(11) NOT NULL,
  `id_widget` int(11) NOT NULL,
  `id_status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id_permiso` int(11) NOT NULL,
  `nombre_permiso` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `id_status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id_permiso`, `nombre_permiso`, `descripcion`, `id_status`) VALUES
(1, 'Ver', 'Permite ver los datos', 1),
(2, 'Registrar', 'Permite Guardar registros', 1),
(3, 'Modificar', 'Permite modificar registros existentes', 1),
(4, 'Imprimir', 'Permite imprimir informes y datos', 1),
(5, 'Consultar', 'inquirir datos específicos', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permiso_menu`
--

CREATE TABLE `permiso_menu` (
  `id_menu` int(11) NOT NULL,
  `id_permiso` int(11) NOT NULL,
  `status_id_status` int(155) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permiso_menu`
--

INSERT INTO `permiso_menu` (`id_menu`, `id_permiso`, `status_id_status`) VALUES
(1, 1, 1),
(2, 1, 1),
(2, 2, 1),
(2, 3, 1),
(2, 4, 1),
(5, 4, 1),
(5, 5, 1),
(6, 1, 1),
(6, 3, 1),
(6, 4, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permiso_submenu`
--

CREATE TABLE `permiso_submenu` (
  `id_submenu` int(11) NOT NULL,
  `id_permiso` int(11) NOT NULL,
  `status_id_status` int(155) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permiso_submenu`
--

INSERT INTO `permiso_submenu` (`id_submenu`, `id_permiso`, `status_id_status`) VALUES
(1, 3, 1),
(2, 3, 1),
(3, 3, 1),
(4, 3, 1),
(5, 1, 1),
(5, 2, 1),
(5, 3, 1),
(6, 1, 1),
(6, 2, 1),
(6, 3, 1),
(6, 4, 1),
(7, 1, 1),
(7, 2, 1),
(7, 3, 1),
(7, 4, 1),
(8, 1, 1),
(8, 2, 1),
(8, 3, 1),
(8, 4, 1),
(9, 1, 1),
(9, 2, 1),
(9, 3, 1),
(9, 4, 1),
(10, 1, 1),
(10, 2, 1),
(10, 3, 1),
(10, 4, 1),
(11, 1, 1),
(11, 2, 1),
(11, 3, 1),
(11, 4, 1),
(12, 1, 1),
(12, 2, 1),
(12, 3, 1),
(12, 4, 1),
(13, 1, 1),
(13, 2, 1),
(13, 3, 1),
(13, 4, 1),
(14, 1, 1),
(14, 2, 1),
(14, 3, 1),
(14, 4, 1),
(15, 1, 1),
(15, 2, 1),
(15, 3, 1),
(15, 4, 1),
(16, 1, 1),
(16, 2, 1),
(16, 3, 1),
(16, 4, 1),
(17, 1, 1),
(17, 2, 1),
(17, 3, 1),
(17, 4, 1),
(18, 1, 1),
(18, 2, 1),
(18, 3, 1),
(18, 4, 1),
(19, 1, 1),
(19, 2, 1),
(19, 3, 1),
(19, 4, 1),
(20, 1, 1),
(20, 2, 1),
(20, 3, 1),
(20, 4, 1),
(21, 3, 1),
(22, 3, 1),
(23, 3, 1),
(24, 3, 1),
(25, 3, 1),
(26, 1, 1),
(27, 1, 1),
(27, 2, 1),
(27, 3, 1),
(27, 4, 1),
(28, 1, 1),
(29, 1, 1),
(29, 2, 1),
(29, 4, 1),
(29, 5, 1),
(30, 1, 1),
(30, 2, 1),
(30, 4, 1),
(30, 5, 1),
(31, 1, 1),
(31, 2, 1),
(31, 4, 1),
(31, 5, 1),
(32, 1, 1),
(32, 2, 1),
(32, 3, 1),
(32, 4, 1),
(33, 1, 1),
(33, 2, 1),
(33, 3, 1),
(33, 4, 1),
(34, 1, 1),
(34, 2, 1),
(34, 3, 1),
(34, 4, 1),
(36, 1, 1),
(36, 2, 1),
(36, 3, 1),
(36, 4, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `personas`
--

CREATE TABLE `personas` (
  `id_persona` int(11) NOT NULL,
  `cedula` varchar(10) NOT NULL,
  `nacionalidad` enum('V','E') NOT NULL,
  `primer_nombre` varchar(50) NOT NULL,
  `segundo_nombre` varchar(50) DEFAULT NULL,
  `primer_apellido` varchar(50) NOT NULL,
  `segundo_apellido` varchar(50) DEFAULT NULL,
  `correo_electronico` varchar(150) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `edad` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `genero` enum('Masculino','Femenino','Otro') NOT NULL,
  `direccion` text NOT NULL,
  `id_cargo` int(11) DEFAULT NULL,
  `id_status` int(11) NOT NULL DEFAULT 1,
  `pais_id` int(11) DEFAULT NULL,
  `estado_id` int(11) DEFAULT NULL,
  `correo_nuevo` varchar(255) DEFAULT NULL,
  `token_verificacion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `personas`
--

INSERT INTO `personas` (`id_persona`, `cedula`, `nacionalidad`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`, `correo_electronico`, `telefono`, `fecha_nacimiento`, `edad`, `fecha_creacion`, `genero`, `direccion`, `id_cargo`, `id_status`, `pais_id`, `estado_id`, `correo_nuevo`, `token_verificacion`) VALUES
(1, '27123456', 'V', 'SANTIAGO', 'ALEJANDRO', 'RAMIREZ', 'PEÑA', 'ASANTIAGO.RAMIREZ@GMAIL.COM', '+58 4126789012', '1995-01-19', 30, '2025-05-29 14:26:21', 'Masculino', 'AV. BELLA VISTA, MARACAIBO, ZULIA', 1, 1, 95, 1866, NULL, NULL),
(17, '29384710', 'V', 'ISABELLA ', 'VALENTINA ', 'ROJAS ', 'MENDOZA ', 'joandergallardo1@gmail.com', '+584123456789', '1998-02-05', NULL, '2025-05-29 21:58:59', 'Femenino', 'Av. Bolívar Norte, Valencia, Carabobo', 2, 1, 95, 1859, NULL, NULL),
(18, '31578492', 'V', 'Maria', 'Alejandra', 'Torres', 'Gutiérrez', 'mariana.torres@gmail.com', '+58 04147896541', '1998-06-18', NULL, '2025-05-29 22:14:21', 'Femenino', 'Urb. La Trigaleña, Valencia, Carabobo', 3, 1, 95, 1859, NULL, NULL),
(21, '30411568', 'V', 'CAMILA', 'SOFÍA', 'FERNÁNDEZ', 'SALAZAR', 'SEBASTIANFJMOSQUERAPETIT@GMAIL.COM', '+58 4164567890', '1997-06-12', NULL, '2025-05-30 00:02:45', 'Femenino', 'AV. URDANETA, CARACAS, DISTRITO CAPITAL', 4, 1, 95, 1859, NULL, NULL),
(22, '25508757', 'V', 'JUAN', 'JOSE', 'MARTIN', 'SAAVEDRA', 'SUAREZLUCIANO617@GMAIL.COM', '+5849551156', '1996-02-15', NULL, '2025-05-30 00:40:05', 'Masculino', 'CALLE 5 EDIFICIO TAMANACO ', 6, 1, 95, 1859, NULL, NULL),
(23, '29045678', 'V', 'LEONARDO', 'DAVID', 'MORALES', 'FIGUEROA', 'LEONARDOMORALES@GMAIL.COM', '+58416321789', '1997-02-24', NULL, '2025-05-30 00:48:25', 'Masculino', 'URB. EL BOSQUE, BARQUISIMETO, LARA', 1, 1, 95, 1859, NULL, NULL),
(24, '29919366', 'V', 'DANIEL', 'ERNESTO', 'BETANCOURT', 'MOYETONES', 'DANIELBETANCOURT335@GMAIL.COM', '+5841455261', '2000-09-11', NULL, '2025-05-30 00:58:07', 'Masculino', 'URB NUEVA ESPARTA', 11, 1, 95, 1859, NULL, NULL),
(25, '123', 'V', 'SDA', 'ASDAS', 'SADAS', 'ASDAS', 'JOANDERGALLARDO123@GMAIL.COM', '+1123213', '2003-02-12', 12, '2025-05-31 05:53:26', 'Masculino', '123', 10, 1, 187, 683, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pieza_unica`
--

CREATE TABLE `pieza_unica` (
  `id_unico` int(11) NOT NULL,
  `id_repuesto` int(11) NOT NULL,
  `NumeroSerie` varchar(100) NOT NULL,
  `Almacen` varchar(100) NOT NULL,
  `Sede` varchar(100) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `FechaUltimaActualizacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes`
--

CREATE TABLE `planes` (
  `id_plan` int(11) NOT NULL,
  `nombre_plan` varchar(100) NOT NULL,
  `descripcion_plan` text NOT NULL,
  `trigger_opcion` varchar(255) NOT NULL,
  `frecuencia` int(11) DEFAULT NULL,
  `tipo_frecuencia` enum('Días','Semanas','Meses') DEFAULT NULL,
  `dia_mes` int(11) DEFAULT NULL,
  `semana_mes` enum('Primera','Segunda','Tercera','Cuarta') DEFAULT NULL,
  `dia_semana` enum('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo') DEFAULT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `fecha_asociacion` datetime DEFAULT current_timestamp(),
  `costo_aprox` decimal(10,2) DEFAULT 0.00,
  `duracion` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `planes`
--

INSERT INTO `planes` (`id_plan`, `nombre_plan`, `descripcion_plan`, `trigger_opcion`, `frecuencia`, `tipo_frecuencia`, `dia_mes`, `semana_mes`, `dia_semana`, `proveedor_id`, `fecha_asociacion`, `costo_aprox`, `duracion`) VALUES
(12, 'Cambio y Lubricación de Rodillos', '<p>Este mantenimiento tiene como objetivo el reemplazo de los rodillos de caucho desgastados del <strong>Molino</strong>, asegurando su correcto funcionamiento y reduciendo la fricción excesiva en el proceso de mezcla de materiales. Se utilizará <strong>Lubricante Industrial EP 220</strong> para mejorar la durabilidad y minimizar el desgaste en componentes móviles.</p><p><strong>Procedimiento:</strong></p><ol><li>Inspección del desgaste de los rodillos existentes.</li><li>Desmontaje con llaves de impacto y extractor de rodillos.</li><li>Aplicación de limpiador industrial para eliminar contaminantes.</li><li>Instalación de rodillos nuevos y ajuste con calibrador de torque.</li><li>Lubricación con EP 220 para evitar fricción y desgaste prematuro.</li></ol>', 'fecha_fija', 5, 'Días', NULL, NULL, NULL, NULL, '2025-06-05 23:06:20', 0.00, 2),
(13, 'Cambio y Lubricación de Rodillos', '<p>Este mantenimiento tiene como objetivo el reemplazo de los rodillos de caucho desgastados del <strong>Molino</strong>, asegurando su correcto funcionamiento y reduciendo la fricción excesiva en el proceso de mezcla de materiales. Se utilizará <strong>Lubricante Industrial EP 220</strong> para mejorar la durabilidad y minimizar el desgaste en componentes móviles.</p><p><strong>Procedimiento:</strong></p><ol><li>Inspección del desgaste de los rodillos existentes.</li><li>Desmontaje con llaves de impacto y extractor de rodillos.</li><li>Aplicación de limpiador industrial para eliminar contaminantes.</li><li>Instalación de rodillos nuevos y ajuste con calibrador de torque.</li><li>Lubricación con EP 220 para evitar fricción y desgaste prematuro.</li></ol>', 'al_terminar', 1, 'Semanas', NULL, NULL, NULL, NULL, '2025-06-05 23:07:12', 0.00, 4),
(14, 'Cambio y Lubricación de Rodillos', '<p>Este mantenimiento tiene como objetivo el reemplazo de los rodillos de caucho desgastados del <strong>Molino</strong>, asegurando su correcto funcionamiento y reduciendo la fricción excesiva en el proceso de mezcla de materiales. Se utilizará <strong>Lubricante Industrial EP 220</strong> para mejorar la durabilidad y minimizar el desgaste en componentes móviles.</p><p><strong>Procedimiento:</strong></p><ol><li>Inspección del desgaste de los rodillos existentes.</li><li>Desmontaje con llaves de impacto y extractor de rodillos.</li><li>Aplicación de limpiador industrial para eliminar contaminantes.</li><li>Instalación de rodillos nuevos y ajuste con calibrador de torque.</li><li>Lubricación con EP 220 para evitar fricción y desgaste prematuro.</li></ol>', 'al_terminar', 2, 'Meses', NULL, NULL, NULL, NULL, '2025-06-06 13:34:03', 0.00, 1),
(15, 'Prueba ', '<p>Este mantenimiento tiene como objetivo el reemplazo de los rodillos de caucho desgastados del <strong>Molino</strong>, asegurando su correcto funcionamiento y reduciendo la fricción excesiva en el proceso de mezcla de materiales. Se utilizará <strong>Lubricante Industrial EP 220</strong> para mejorar la durabilidad y minimizar el desgaste en componentes móviles.</p><p><strong>Procedimiento:</strong></p><ol><li>Inspección del desgaste de los rodillos existentes.</li><li>Desmontaje con llaves de impacto y extractor de rodillos.</li><li>Aplicación de limpiador industrial para eliminar contaminantes.</li><li>Instalación de rodillos nuevos y ajuste con calibrador de torque.</li><li>Lubricación con EP 220 para evitar fricción y desgaste prematuro.</li></ol>', 'al_terminar', 3, 'Días', NULL, NULL, NULL, NULL, '2025-06-06 17:56:04', 0.00, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planta`
--

CREATE TABLE `planta` (
  `id_planta` int(11) NOT NULL,
  `id_sede` int(11) DEFAULT NULL,
  `id_status` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planta_articulo`
--

CREATE TABLE `planta_articulo` (
  `id_relacion` int(11) NOT NULL,
  `id_planta` int(11) NOT NULL,
  `id_articulo` int(11) NOT NULL,
  `fecha_asociacion` datetime DEFAULT current_timestamp(),
  `id_status` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `plan_ejecuciones`
--

CREATE TABLE `plan_ejecuciones` (
  `id_ejecucion` int(11) NOT NULL,
  `id_plan` int(11) NOT NULL,
  `id_tarea` int(11) NOT NULL,
  `fecha_ejecucion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `plan_ejecuciones`
--

INSERT INTO `plan_ejecuciones` (`id_ejecucion`, `id_plan`, `id_tarea`, `fecha_ejecucion`) VALUES
(10, 12, 146, '2025-06-05 23:06:20'),
(11, 13, 147, '2025-06-05 23:07:12'),
(12, 14, 148, '2025-06-06 13:34:04'),
(14, 14, 150, '2025-06-06 14:22:54'),
(15, 12, 151, '2025-06-06 16:37:25'),
(16, 15, 152, '2025-06-06 17:56:05'),
(17, 15, 153, '2025-06-06 18:02:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prioridad`
--

CREATE TABLE `prioridad` (
  `id_importancia` int(11) NOT NULL,
  `nivel` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prioridad`
--

INSERT INTO `prioridad` (`id_importancia`, `nivel`) VALUES
(3, 'Alta'),
(1, 'Baja'),
(2, 'Media');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL,
  `nombre_producto` varchar(100) NOT NULL,
  `id_marca` int(11) NOT NULL,
  `id_modelo` int(11) NOT NULL,
  `id_tipo` int(11) NOT NULL,
  `id_clasificacion` int(11) NOT NULL,
  `unidad_medida` varchar(20) NOT NULL,
  `nombre_imagen` varchar(100) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `id_status` int(11) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id_producto`, `nombre_producto`, `id_marca`, `id_modelo`, `id_tipo`, `id_clasificacion`, `unidad_medida`, `nombre_imagen`, `url`, `id_status`, `date_created`) VALUES
(39, 'LUBRICANTE INDUSTRIAL EP 220', 1, 1, 22222230, 53, '19', '6842495f02001_images.jpeg', 'servidor_img/producto/6842495f02001_images.jpeg', 1, '2025-06-06 03:50:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_actividad`
--

CREATE TABLE `producto_actividad` (
  `id_producto_actividad` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `actividad_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costo` decimal(10,2) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_plan`
--

CREATE TABLE `producto_plan` (
  `id_producto_plan` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costo` decimal(10,2) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_tarea`
--

CREATE TABLE `producto_tarea` (
  `id_producto_tarea` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costo` decimal(10,2) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor`
--

CREATE TABLE `proveedor` (
  `id_proveedor` int(11) NOT NULL,
  `nombre_proveedor` varchar(100) NOT NULL,
  `telefono` varchar(15) NOT NULL,
  `email` varchar(100) NOT NULL,
  `direccion` text NOT NULL,
  `id_status` int(11) NOT NULL DEFAULT 1,
  `date_created` datetime DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `id_pais` int(11) DEFAULT NULL,
  `id_estado` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proveedor`
--

INSERT INTO `proveedor` (`id_proveedor`, `nombre_proveedor`, `telefono`, `email`, `direccion`, `id_status`, `date_created`, `date_updated`, `id_pais`, `id_estado`) VALUES
(19, 'dadas', '04241605247', 'joandergallardo1@gmail.com', '32', 1, '2025-05-31 21:21:44', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor_producto`
--

CREATE TABLE `proveedor_producto` (
  `id_proveedor_producto` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor_repuesto`
--

CREATE TABLE `proveedor_repuesto` (
  `id_proveedor_repuesto` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `id_repuesto` int(11) NOT NULL,
  `Precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedor_servicio`
--

CREATE TABLE `proveedor_servicio` (
  `id_proveedor` int(11) NOT NULL,
  `id_servicio` int(11) NOT NULL,
  `status_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `registro_actividades`
--

CREATE TABLE `registro_actividades` (
  `id_registro` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `accion` varchar(255) NOT NULL,
  `actividad` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `modulo` varchar(100) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `dispositivo` varchar(100) DEFAULT NULL,
  `estado` enum('exitoso','fallido') DEFAULT 'exitoso',
  `importancia` enum('baja','media','alta') DEFAULT 'baja'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `registro_actividades`
--

INSERT INTO `registro_actividades` (`id_registro`, `id_usuario`, `accion`, `actividad`, `fecha`, `modulo`, `ip_address`, `dispositivo`, `estado`, `importancia`) VALUES
(253, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 14:58:36', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(254, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 14:59:27', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(255, 35, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 18:59:00', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(256, 32, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 19:03:07', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(257, 33, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 19:04:22', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(258, 34, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 19:07:09', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(259, NULL, 'Intento de inicio', 'Acceso denegado: No tiene permisos para este perfil', '2025-05-29 19:08:01', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(260, 30, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 19:08:14', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(261, 31, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 19:09:27', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(262, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 19:12:30', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(263, 32, 'Intento de inicio', 'Contraseña incorrecta', '2025-05-29 20:24:03', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(264, 32, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 20:24:20', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(265, 35, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 20:25:48', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(266, 33, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 20:26:48', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(267, 30, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 20:28:58', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(268, 31, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 20:31:58', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(269, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-29 20:32:57', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(270, 30, 'Intento de inicio', 'Contraseña incorrecta', '2025-05-30 02:36:58', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(271, 30, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 02:37:25', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(272, 32, 'Intento de inicio', 'Contraseña incorrecta', '2025-05-30 02:37:36', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(273, 30, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 02:40:35', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(274, 30, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 02:50:50', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(275, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 03:35:25', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(276, 30, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 13:48:29', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(277, 32, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 13:57:31', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(278, 33, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 13:58:22', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(279, 35, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 13:59:32', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(280, 30, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 14:00:21', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(281, NULL, 'Intento de inicio', 'Acceso denegado: No tiene permisos para este perfil', '2025-05-30 14:00:44', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(282, 31, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 14:00:48', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(283, 34, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 14:01:17', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(284, 34, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 14:01:31', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(285, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 14:01:49', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(286, 34, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 14:02:25', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(287, 1, 'Intento de inicio', 'Contraseña incorrecta', '2025-05-30 14:03:29', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(288, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 14:03:35', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(289, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 17:50:33', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(290, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 17:59:43', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(291, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 20:08:58', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(292, NULL, 'Intento de inicio', 'Acceso denegado: No tiene permisos para este perfil', '2025-05-30 20:32:43', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(293, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 20:34:08', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(294, NULL, 'Intento de inicio', 'Acceso denegado: No tiene permisos para este perfil', '2025-05-30 20:45:04', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(295, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 20:45:15', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(296, 30, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 20:52:03', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(297, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 20:54:19', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(298, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 21:15:39', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(299, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-30 21:52:33', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(300, 1, 'Editó', 'Dirección Habitación', '2025-05-30 21:58:31', 'Perfil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Sa', 'exitoso', 'baja'),
(301, 1, 'Editó', 'Dirección Habitación', '2025-05-30 21:58:35', 'Perfil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Sa', 'exitoso', 'baja'),
(302, 1, 'Edito', 'Datos Personales', '2025-05-30 21:59:20', 'Perfil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Sa', 'exitoso', 'baja'),
(303, 1, 'Edito', 'Datos Personales', '2025-05-30 21:59:25', 'Perfil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Sa', 'exitoso', 'baja'),
(304, 1, 'Editó', 'Datos Telefono', '2025-05-30 22:00:57', 'Perfil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Sa', 'exitoso', 'baja'),
(305, 1, 'Editó', 'Datos Telefono', '2025-05-30 22:01:01', 'Perfil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Sa', 'exitoso', 'baja'),
(306, 1, 'Edito', 'Datos Correo', '2025-05-30 22:12:05', 'Perfil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Sa', 'exitoso', 'baja'),
(307, 1, 'Editó', 'Dirección Habitación', '2025-05-30 22:42:14', 'Perfil', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Sa', 'exitoso', 'baja'),
(308, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-31 14:57:55', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(309, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-31 15:04:32', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(310, 32, 'Inicio de sesión', 'Acceso permitido', '2025-05-31 17:44:32', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(311, 33, 'Inicio de sesión', 'Acceso permitido', '2025-05-31 17:45:30', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(312, 35, 'Intento de inicio', 'Contraseña incorrecta', '2025-05-31 17:46:14', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(313, 35, 'Intento de inicio', 'Contraseña incorrecta', '2025-05-31 17:46:24', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(314, 35, 'Inicio de sesión', 'Acceso permitido', '2025-05-31 17:47:15', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(315, 30, 'Inicio de sesión', 'Acceso permitido', '2025-05-31 17:48:04', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(316, NULL, 'Intento de inicio', 'Acceso denegado: No tiene permisos para este perfil', '2025-05-31 17:49:07', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(317, 31, 'Intento de inicio', 'Contraseña incorrecta', '2025-05-31 17:49:17', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(318, 31, 'Inicio de sesión', 'Acceso permitido', '2025-05-31 17:49:33', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(319, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-31 17:50:01', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(320, 30, 'Inicio de sesión', 'Acceso permitido', '2025-05-31 19:11:47', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(321, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-31 19:25:51', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(322, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-31 23:30:36', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(323, 35, 'Inicio de sesión', 'Acceso permitido', '2025-05-31 23:31:22', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(324, 1, 'Inicio de sesión', 'Acceso permitido', '2025-05-31 23:52:45', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(325, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-01 00:11:21', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(326, NULL, 'Intento de inicio', 'Perfil no válido', '2025-06-01 00:58:34', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'alta'),
(327, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-01 00:58:41', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(328, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-01 13:40:30', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(329, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-01 23:11:08', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(330, NULL, 'Intento de inicio', 'Acceso denegado: No tiene permisos para este perfil', '2025-06-02 13:38:40', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(331, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 13:38:54', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(332, NULL, 'Intento de inicio', 'Acceso denegado: No tiene permisos para este perfil', '2025-06-02 13:45:52', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(333, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 13:46:02', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(334, 31, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 14:23:21', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(335, 30, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 14:24:12', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(336, 34, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 14:25:02', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(337, 35, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 14:25:54', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(338, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 14:26:29', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(339, 34, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 14:31:29', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(340, 32, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 14:46:14', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(341, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 14:47:18', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(342, 33, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 14:50:13', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(343, 35, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 14:51:04', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(344, 30, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 14:51:46', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(345, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 14:52:30', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(346, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 15:05:43', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(347, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 15:37:39', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(348, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-02 23:42:53', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(349, 33, 'Inicio de sesión', 'Acceso permitido', '2025-06-03 14:06:57', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(350, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-03 14:07:36', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(351, NULL, 'Intento de inicio', 'Perfil no válido', '2025-06-03 19:00:39', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'alta'),
(352, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-03 19:00:50', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(353, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-03 20:02:28', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(354, NULL, 'Intento de inicio', 'Acceso denegado: No tiene permisos para este perfil', '2025-06-03 20:03:34', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(355, 1, 'Intento de inicio', 'Contraseña incorrecta', '2025-06-03 20:03:58', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(356, 1, 'Intento de inicio', 'Usuario inactivo', '2025-06-03 20:05:02', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(357, 1, 'Intento de inicio', 'Usuario bloqueado temporalmente', '2025-06-03 20:05:36', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(358, 1, 'Intento de inicio', 'Usuario bloqueado permanentemente', '2025-06-03 20:06:06', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'alta'),
(359, 1, 'Intento de inicio', 'Usuario inactivo', '2025-06-03 20:07:09', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(360, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-03 20:07:42', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(361, 32, 'Inicio de sesión', 'Acceso permitido', '2025-06-03 20:15:55', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(362, 33, 'Inicio de sesión', 'Acceso permitido', '2025-06-03 20:16:34', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(363, 35, 'Inicio de sesión', 'Acceso permitido', '2025-06-03 20:17:34', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(364, 30, 'Inicio de sesión', 'Acceso permitido', '2025-06-03 20:18:20', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(365, 31, 'Intento de inicio', 'Contraseña incorrecta', '2025-06-03 20:18:54', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(366, 31, 'Inicio de sesión', 'Acceso permitido', '2025-06-03 20:19:37', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(367, 34, 'Inicio de sesión', 'Acceso permitido', '2025-06-03 20:21:19', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(368, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-03 20:22:32', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(369, 33, 'Inicio de sesión', 'Acceso permitido', '2025-06-04 01:35:32', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(370, 35, 'Inicio de sesión', 'Acceso permitido', '2025-06-04 01:36:57', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(371, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-04 02:14:00', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(372, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-04 16:22:43', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(373, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-05 15:57:45', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(374, 32, 'Inicio de sesión', 'Acceso permitido', '2025-06-05 16:28:43', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(375, NULL, 'Intento de inicio', 'Perfil no válido', '2025-06-05 23:11:27', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'alta'),
(376, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-05 23:11:34', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(377, 33, 'Intento de inicio', 'Contraseña incorrecta', '2025-06-06 22:44:05', 'validar_usuario', '::1', 'Windows NT 10.0', 'fallido', 'media'),
(378, 33, 'Inicio de sesión', 'Acceso permitido', '2025-06-06 22:44:23', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media'),
(379, 1, 'Inicio de sesión', 'Acceso permitido', '2025-06-07 03:12:29', 'validar_usuario', '::1', 'Windows NT 10.0', 'exitoso', 'media');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reporte`
--

CREATE TABLE `reporte` (
  `id_reporte` int(11) NOT NULL,
  `tipo_reporte_id` int(11) NOT NULL,
  `titulo_reporte` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_generacion` date NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repuesto`
--

CREATE TABLE `repuesto` (
  `id_repuesto` int(11) NOT NULL,
  `nombre_repuesto` varchar(100) NOT NULL,
  `id_marca` int(11) NOT NULL,
  `id_modelo` int(11) NOT NULL,
  `nombre_imagen` varchar(100) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sugerencia_mantenimiento` varchar(255) NOT NULL,
  `id_status` int(11) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `id_tipo` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `repuesto`
--

INSERT INTO `repuesto` (`id_repuesto`, `nombre_repuesto`, `id_marca`, `id_modelo`, `nombre_imagen`, `url`, `sugerencia_mantenimiento`, `id_status`, `date_created`, `id_tipo`) VALUES
(34, 'RODILLOS DE CAUCHO', 1, 1, '684246be6c7ba_RODILLO-DE-CAUCHO.jpg', '../public/servidor_img/repuesto/684246be6c7ba_RODILLO-DE-CAUCHO.jpg', 'mensual', 1, '2025-06-06 03:39:10', 22222229);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repuesto_actividad`
--

CREATE TABLE `repuesto_actividad` (
  `id_repuesto_actividad` int(11) NOT NULL,
  `repuesto_id` int(11) NOT NULL,
  `actividad_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costo` decimal(10,2) NOT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repuesto_plan`
--

CREATE TABLE `repuesto_plan` (
  `id_repuesto_plan` int(11) NOT NULL,
  `repuesto_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costo` decimal(10,2) DEFAULT 0.00,
  `costo_aprox` decimal(10,2) DEFAULT 0.00,
  `status_id` int(11) NOT NULL,
  `proveedor_id` int(11) DEFAULT NULL,
  `fecha_asociacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `repuesto_plan`
--

INSERT INTO `repuesto_plan` (`id_repuesto_plan`, `repuesto_id`, `plan_id`, `cantidad`, `costo`, `costo_aprox`, `status_id`, `proveedor_id`, `fecha_asociacion`) VALUES
(9, 34, 12, 2, 0.00, 0.00, 26, NULL, '2025-06-05 23:06:20'),
(10, 34, 13, 2, 0.00, 0.00, 26, NULL, '2025-06-05 23:07:12'),
(11, 34, 14, 2, 0.00, 0.00, 26, NULL, '2025-06-06 13:34:04'),
(12, 34, 15, 2, 0.00, 0.00, 26, NULL, '2025-06-06 17:56:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `repuesto_tarea`
--

CREATE TABLE `repuesto_tarea` (
  `id_repuesto_tarea` int(11) NOT NULL,
  `repuesto_id` int(11) NOT NULL,
  `tarea_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `costo` decimal(10,2) DEFAULT NULL,
  `status_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `repuesto_tarea`
--

INSERT INTO `repuesto_tarea` (`id_repuesto_tarea`, `repuesto_id`, `tarea_id`, `cantidad`, `costo`, `status_id`) VALUES
(64, 34, 145, 2, 0.00, 26),
(65, 34, 146, 2, 0.00, 26),
(66, 34, 147, 2, 0.00, 25),
(67, 34, 148, 2, 0.00, 26),
(69, 34, 150, 2, 0.00, 26),
(70, 34, 151, 2, 0.00, 26),
(71, 34, 152, 2, 0.00, 26),
(72, 34, 153, 2, 0.00, 26);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `responsable`
--

CREATE TABLE `responsable` (
  `id_responsable` int(11) NOT NULL,
  `persona_id` int(11) NOT NULL,
  `tarea_id` int(11) DEFAULT NULL,
  `actividad_id` int(11) DEFAULT NULL,
  `id_usuario` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `responsable`
--

INSERT INTO `responsable` (`id_responsable`, `persona_id`, `tarea_id`, `actividad_id`, `id_usuario`) VALUES
(75, 24, 145, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sede`
--

CREATE TABLE `sede` (
  `id_sede` int(11) NOT NULL,
  `id_empresa` int(11) DEFAULT NULL,
  `id_status` int(11) DEFAULT NULL,
  `nombre_sede` varchar(100) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `id_sucursal_fija` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sede`
--

INSERT INTO `sede` (`id_sede`, `id_empresa`, `id_status`, `nombre_sede`, `fecha_creacion`, `id_sucursal_fija`) VALUES
(1, 1, 1, 'SEDE PRINCIPAL', '2025-06-02 23:35:39', 1),
(2002, 1, 1, 'Sede Acarigua', '2025-06-01 10:02:35', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sede_sucursal`
--

CREATE TABLE `sede_sucursal` (
  `id_sede_sucursal` int(11) NOT NULL,
  `id_sede` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `fecha_asociacion` datetime DEFAULT current_timestamp(),
  `id_status` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicio`
--

CREATE TABLE `servicio` (
  `id_servicio` int(11) NOT NULL,
  `nombre_servicio` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_status` int(11) NOT NULL DEFAULT 1,
  `date_created` datetime DEFAULT current_timestamp(),
  `tiempo_programado` varchar(255) DEFAULT NULL,
  `tiempo_paro_maquina` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicio`
--

INSERT INTO `servicio` (`id_servicio`, `nombre_servicio`, `descripcion`, `id_status`, `date_created`, `tiempo_programado`, `tiempo_paro_maquina`) VALUES
(29, 'Cambio y Lubricación de Rodillos', '<p>Este mantenimiento tiene como objetivo el reemplazo de los rodillos de caucho desgastados del <strong>Molino</strong>, asegurando su correcto funcionamiento y reduciendo la fricción excesiva en el proceso de mezcla de materiales. Se utilizará <strong>Lubricante Industrial EP 220</strong> para mejorar la durabilidad y minimizar el desgaste en componentes móviles.</p><p><strong>Procedimiento:</strong></p><ol><li>Inspección del desgaste de los rodillos existentes.</li><li>Desmontaje con llaves de impacto y extractor de rodillos.</li><li>Aplicación de limpiador industrial para eliminar contaminantes.</li><li>Instalación de rodillos nuevos y ajuste con calibrador de torque.</li><li>Lubricación con EP 220 para evitar fricción y desgaste prematuro.</li></ol>', 1, '2025-06-05 21:54:44', '5', '6');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicio_herramienta`
--

CREATE TABLE `servicio_herramienta` (
  `id` int(11) NOT NULL,
  `id_servicio` int(11) DEFAULT NULL,
  `id_herramienta` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicio_herramienta`
--

INSERT INTO `servicio_herramienta` (`id`, `id_servicio`, `id_herramienta`, `cantidad`) VALUES
(33, 29, 8, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicio_maquina`
--

CREATE TABLE `servicio_maquina` (
  `id` int(11) NOT NULL,
  `id_servicio` int(11) DEFAULT NULL,
  `id_maquina` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicio_maquina`
--

INSERT INTO `servicio_maquina` (`id`, `id_servicio`, `id_maquina`) VALUES
(19, 29, 27);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicio_piezas`
--

CREATE TABLE `servicio_piezas` (
  `id` int(11) NOT NULL,
  `id_servicio` int(11) DEFAULT NULL,
  `id_repuesto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicio_producto`
--

CREATE TABLE `servicio_producto` (
  `id` int(11) NOT NULL,
  `id_servicio` int(11) DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicio_repuesto`
--

CREATE TABLE `servicio_repuesto` (
  `id` int(11) NOT NULL,
  `id_servicio` int(11) DEFAULT NULL,
  `id_repuesto` int(11) DEFAULT NULL,
  `cantidad` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicio_repuesto`
--

INSERT INTO `servicio_repuesto` (`id`, `id_servicio`, `id_repuesto`, `cantidad`) VALUES
(24, 29, 34, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes`
--

CREATE TABLE `solicitudes` (
  `id_solicitud` int(11) NOT NULL,
  `id_tipo_solicitud` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_status` int(11) NOT NULL DEFAULT 1,
  `id_perfil` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes`
--

INSERT INTO `solicitudes` (`id_solicitud`, `id_tipo_solicitud`, `id_usuario`, `fecha_solicitud`, `id_status`, `id_perfil`) VALUES
(34, 2, 1, '2025-06-06 22:41:09', 4, 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes_tareas`
--

CREATE TABLE `solicitudes_tareas` (
  `id_solicitud` int(11) NOT NULL,
  `id_tarea` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `solicitudes_tareas`
--

INSERT INTO `solicitudes_tareas` (`id_solicitud`, `id_tarea`) VALUES
(34, 147);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `status`
--

CREATE TABLE `status` (
  `id_status` int(11) NOT NULL,
  `nombre_status` varchar(20) NOT NULL,
  `descripcion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `status`
--

INSERT INTO `status` (`id_status`, `nombre_status`, `descripcion`) VALUES
(1, 'Activo', 'Registro activo'),
(2, 'Inactivo', 'Registro inactivo'),
(3, 'Planificado', 'El ingeniero ha planificado el mantenimiento, esperando aprobación del gerente'),
(4, 'Aprobado', 'El gerente ha aceptado la planificación y está listo para comenzar'),
(5, 'En progreso', 'Los mecánicos están realizando el mantenimiento'),
(6, 'Retrasado', 'El mantenimiento ha sido retrasado por alguna circunstancia'),
(7, 'Finalizado', 'El mantenimiento ha sido completado exitosamente'),
(8, 'Bloqueo temporal', 'Usuario bloqueado por demasiados intentos fallidos'),
(9, 'Bloqueo total', 'El usuario ha sido bloqueado automáticamente después de alcanzar el límite de intentos fallidos. Sol'),
(10, 'Disponible', 'La herramienta está lista para su uso'),
(11, 'En uso', 'La herramienta está siendo utilizada actualmente'),
(12, 'Dañada', 'La herramienta presenta daños y requiere reparación'),
(13, 'En mantenimiento', 'La herramienta está en proceso de mantenimiento'),
(14, 'Pendiente de revisió', 'La herramienta necesita ser inspeccionada antes de su uso'),
(15, 'Reparación completad', 'La herramienta ha sido reparada y está disponible'),
(16, 'Extraviada', 'La herramienta ha sido reportada como perdida'),
(17, 'Retirada', 'La herramienta ha sido retirada del inventario'),
(18, 'Obsoleta', 'La herramienta ya no cumple con los estándares y será reemplazada'),
(19, 'Reservada', 'La herramienta ha sido apartada para un uso futuro'),
(20, 'Alquilada', 'La herramienta ha sido prestada temporalmente'),
(21, 'En garantía', 'La herramienta está cubierta por una garantía activa'),
(22, 'Desactivada', 'La herramienta ha sido marcada como inactiva en el sistema'),
(23, 'En camino', 'La herramienta está en proceso de entrega a su destino'),
(24, 'Entregada', 'La herramienta ha llegado a su destino y está disponible para uso'),
(25, 'Planificado', 'El producto/repuesto/herramienta ha sido planificado para una tarea'),
(26, 'Pendiente', 'El producto/repuesto/herramienta está pendiente en la tarea');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `submenus`
--

CREATE TABLE `submenus` (
  `id_submenu` int(150) NOT NULL,
  `id_menu` int(150) NOT NULL,
  `nombre_submenu` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `url_submenu` varchar(255) NOT NULL,
  `tipo_submenu` int(155) NOT NULL,
  `id_status` int(150) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `submenus`
--

INSERT INTO `submenus` (`id_submenu`, `id_menu`, `nombre_submenu`, `descripcion`, `url_submenu`, `tipo_submenu`, `id_status`) VALUES
(1, 9, 'Datos', 'consultar datos de la empresa', 'datos_empresa.php', 1, 1),
(2, 9, 'Contactos', 'Consulta Los Contactos De La Empresa', 'contactos_empresa.php', 1, 1),
(3, 9, 'Redes Sociales', 'Consulta Las Redes Sociales De La Empresa', 'redes_sociales_empresa.php', 1, 1),
(4, 9, 'Sobre Nosotros', 'Consultar Sobre Nosotros', 'sobre_nosotros.php', 1, 1),
(5, 9, 'Blog', 'Consulta El Blog', 'blog_empresa.php', 1, 1),
(6, 9, 'Sede', 'Ingresa A La Gestion De Sede', 'sede.php', 2, 1),
(7, 9, 'Sucursal', 'Ingresa A La Gestión De Sucursal ', 'sucursal.php', 2, 1),
(8, 9, 'Almacén', 'Ingresa A la Gestión De Almacén', 'almacen.php', 2, 1),
(9, 9, 'Planta', 'Ingresa A la Gestión Planta', 'planta.php', 2, 1),
(10, 9, 'Articulo', 'Ingresa A La Gestión De Los Articulos De La Empresa ', 'articulo.php', 2, 1),
(11, 7, 'Marca', 'Ingresa A La Gestión De Marcas', 'marca.php', 1, 1),
(12, 7, 'Modelo', 'Ingresa A La Gestión De Modelos', 'modelo.php', 1, 1),
(13, 7, 'Tipo', 'Ingresa A La Gestión De Tipos', 'tipo.php', 1, 1),
(14, 7, 'Clasificacion ', 'Ingresa A La Gestión De Clasificación(Medidas,Peso,Capacidad,etc)', 'clasificacion.php', 1, 1),
(15, 7, 'Producto', 'Ingresa A La Gestión De Productos', 'producto.php', 1, 1),
(16, 7, 'Maquina', 'Ingresa A La Gestión De Maquinas o Maquinarias', 'maquina.php', 1, 1),
(17, 7, 'Repuesto', 'Ingresa A La Gestión De Repuesto Para Las Maquinas', 'repuesto.php', 1, 1),
(18, 7, 'Proveedor', 'Ingresa A La Gestión De Proveedores', 'proveedor.php', 1, 1),
(19, 7, 'Servicio', 'Ingresa A La Gestión De Los Servicios Para La Empresa', 'servicio.php', 1, 1),
(20, 7, 'Cargo', 'Ingresa A La Gestión De Cargos De Los Empleados', 'cargo.php', 1, 1),
(21, 8, 'Datos Personales', 'consulta los datos personales', 'datos_personales.php', 1, 1),
(22, 8, 'Dirección de Habitación', 'Consulta la Dirección de Habitación', 'direccion_habitacion.php', 1, 1),
(23, 8, 'Seguridad', 'Seguridad', 'seguridad.php', 1, 1),
(24, 8, 'Correo', 'Consulta los Correo ', 'correo.php', 1, 1),
(25, 8, 'Teléfono', 'Consulta los datos Del Teléfono ', 'telefono.php', 1, 1),
(26, 8, 'Actividad', 'Consulta los Datos de Sus Actividades', 'actividad_perfil.php', 1, 1),
(27, 2, 'Usuario', 'Gestionar Los Usuarios ', 'usuario.php', 1, 1),
(28, 2, 'Actividad', 'Consulta los Datos de las Actividades del Usuario', 'actividad_empleado.php', 1, 1),
(29, 3, 'Productos ', 'Gestionar Productos ', 'productos.php', 1, 1),
(30, 3, 'Maquinas', 'Gestión de Maquinas', 'maquinas.php', 1, 1),
(31, 3, 'Repuestos', 'Gestionar Repuestos', 'repuestos.php', 1, 1),
(32, 4, 'Tareas', 'Gestionar tareas implica controlar las acciones específicas que se realizarán durante el mantenimiento. Son el nivel más básico del mantenimiento y están enfocadas en un objetivo muy concreto.', 'tareas.php', 1, 1),
(33, 4, 'Actividades', 'La gestión de actividades implica organizar y coordinar un conjunto de *tareas planificadas* con un propósito más amplio. Una actividad abarca varias tareas relacionadas que se deben realizar juntas o en una secuencia.', 'actividades.php', 1, 1),
(34, 4, 'Planes', 'Los planes son la estructura global que coordina actividades y tareas en un intervalo de tiempo específico. Un plan define todo lo que se debe hacer en el mantenimiento y establece un cronograma claro.', 'planes.php', 1, 1),
(35, 2, 'Permisos', 'asignar permisos a los usuarios', 'permisos.php', 1, 1),
(36, 7, 'Herramienta', 'Gestión de herramientas', 'herramienta.php', 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sucursal`
--

CREATE TABLE `sucursal` (
  `id_sucursal` int(11) NOT NULL,
  `id_status` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `pais_id_pais` int(155) DEFAULT NULL,
  `estado_id_estado` int(155) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sucursal`
--

INSERT INTO `sucursal` (`id_sucursal`, `id_status`, `nombre`, `direccion`, `pais_id_pais`, `estado_id_estado`, `fecha_creacion`) VALUES
(1, 1, 'Sucursal Este', 'Calle 1, Caracas', 1, 1, '2025-03-21 18:25:02'),
(28, 1, 'ESTHER', '32', 144, NULL, '2025-05-23 15:00:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tareas`
--

CREATE TABLE `tareas` (
  `id_tarea` int(11) NOT NULL,
  `titulo_tarea` varchar(100) NOT NULL,
  `descripcion_tarea` text NOT NULL,
  `tipo_mantenimiento_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `hora_inicio` time DEFAULT NULL,
  `fecha_fin` date NOT NULL,
  `hora_fin` time DEFAULT NULL,
  `categoria_mantenimiento` varchar(255) NOT NULL,
  `costo` int(255) DEFAULT NULL,
  `tiempo_programado` varchar(255) DEFAULT NULL,
  `tiempo_paro_maquina` varchar(255) DEFAULT NULL,
  `status_id` int(255) NOT NULL,
  `proveedor_id` int(255) DEFAULT NULL,
  `id_importancia` int(11) NOT NULL,
  `id_maquina_unica` int(11) NOT NULL,
  `id_servicio` int(11) DEFAULT NULL,
  `id_sede` int(11) DEFAULT NULL,
  `notificar_email` tinyint(1) DEFAULT 0,
  `id_solicitud` int(11) DEFAULT NULL,
  `observacion` text DEFAULT NULL,
  `fecha_hora_finalizacion` datetime DEFAULT NULL,
  `id_plan` int(11) DEFAULT NULL,
  `tiempo_paro_programado` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tareas`
--

INSERT INTO `tareas` (`id_tarea`, `titulo_tarea`, `descripcion_tarea`, `tipo_mantenimiento_id`, `fecha_inicio`, `hora_inicio`, `fecha_fin`, `hora_fin`, `categoria_mantenimiento`, `costo`, `tiempo_programado`, `tiempo_paro_maquina`, `status_id`, `proveedor_id`, `id_importancia`, `id_maquina_unica`, `id_servicio`, `id_sede`, `notificar_email`, `id_solicitud`, `observacion`, `fecha_hora_finalizacion`, `id_plan`, `tiempo_paro_programado`) VALUES
(145, 'Cambio y Lubricación de Rodillos', '<p>Este mantenimiento tiene como objetivo el reemplazo de los rodillos de caucho desgastados del <strong>Molino</strong>, asegurando su correcto funcionamiento y reduciendo la fricción excesiva en el proceso de mezcla de materiales. Se utilizará <strong>Lubricante Industrial EP 220</strong> para mejorar la durabilidad y minimizar el desgaste en componentes móviles.</p><p><strong>Procedimiento:</strong></p><ol><li>Inspección del desgaste de los rodillos existentes.</li><li>Desmontaje con llaves de impacto y extractor de rodillos.</li><li>Aplicación de limpiador industrial para eliminar contaminantes.</li><li>Instalación de rodillos nuevos y ajuste con calibrador de torque.</li><li>Lubricación con EP 220 para evitar fricción y desgaste prematuro.</li></ol>', 1, '2025-06-05', '22:01:00', '2025-06-06', '03:01:00', 'interno', 0, '0 días, 5 horas, 0 minutos', '0 días, 16 horas, 6 minutos', 7, NULL, 2, 1, 29, NULL, 0, NULL, 'hola', '2025-06-06 14:07:00', NULL, '37min'),
(146, 'Cambio y Lubricación de Rodillos', '<p>Este mantenimiento tiene como objetivo el reemplazo de los rodillos de caucho desgastados del <strong>Molino</strong>, asegurando su correcto funcionamiento y reduciendo la fricción excesiva en el proceso de mezcla de materiales. Se utilizará <strong>Lubricante Industrial EP 220</strong> para mejorar la durabilidad y minimizar el desgaste en componentes móviles.</p><p><strong>Procedimiento:</strong></p><ol><li>Inspección del desgaste de los rodillos existentes.</li><li>Desmontaje con llaves de impacto y extractor de rodillos.</li><li>Aplicación de limpiador industrial para eliminar contaminantes.</li><li>Instalación de rodillos nuevos y ajuste con calibrador de torque.</li><li>Lubricación con EP 220 para evitar fricción y desgaste prematuro.</li></ol>', 1, '2025-06-06', '00:00:00', '2025-06-08', '00:00:00', 'interno', 0, '2 días, 0 horas, 0 minutos', '0 días, 16 horas, 36 minutos', 7, NULL, 2, 1, 29, NULL, 0, NULL, 'todo correcto.', '2025-06-06 16:36:00', 12, '5min'),
(147, 'Cambio y Lubricación de Rodillos', 'hola mundo', 1, '2025-06-15', '00:00:00', '2025-06-19', '00:00:00', 'interno', 0, '6480', '7740', 1, NULL, 2, 2, 29, NULL, 0, NULL, NULL, NULL, 13, ''),
(148, 'Cambio y Lubricación de Rodillos', '<p>Este mantenimiento tiene como objetivo el reemplazo de los rodillos de caucho desgastados del <strong>Molino</strong>, asegurando su correcto funcionamiento y reduciendo la fricción excesiva en el proceso de mezcla de materiales. Se utilizará <strong>Lubricante Industrial EP 220</strong> para mejorar la durabilidad y minimizar el desgaste en componentes móviles.</p><p><strong>Procedimiento:</strong></p><ol><li>Inspección del desgaste de los rodillos existentes.</li><li>Desmontaje con llaves de impacto y extractor de rodillos.</li><li>Aplicación de limpiador industrial para eliminar contaminantes.</li><li>Instalación de rodillos nuevos y ajuste con calibrador de torque.</li><li>Lubricación con EP 220 para evitar fricción y desgaste prematuro.</li></ol>', 1, '2025-06-06', '00:00:00', '2025-06-07', '00:00:00', 'interno', 0, '1 días, 0 horas, 0 minutos', '0 días, 14 horas, 17 minutos', 7, NULL, 1, 1, 29, NULL, 0, NULL, 'terminado', '2025-06-06 14:17:00', 14, '4min'),
(150, 'Cambio y Lubricación de Rodillos', '<p>Este mantenimiento tiene como objetivo el reemplazo de los rodillos de caucho desgastados del <strong>Molino</strong>, asegurando su correcto funcionamiento y reduciendo la fricción excesiva en el proceso de mezcla de materiales. Se utilizará <strong>Lubricante Industrial EP 220</strong> para mejorar la durabilidad y minimizar el desgaste en componentes móviles.</p><p><strong>Procedimiento:</strong></p><ol><li>Inspección del desgaste de los rodillos existentes.</li><li>Desmontaje con llaves de impacto y extractor de rodillos.</li><li>Aplicación de limpiador industrial para eliminar contaminantes.</li><li>Instalación de rodillos nuevos y ajuste con calibrador de torque.</li><li>Lubricación con EP 220 para evitar fricción y desgaste prematuro.</li></ol>', 1, '2025-08-07', '00:00:00', '2025-08-08', '00:00:00', 'interno', 0, '', '', 1, NULL, 1, 1, 29, NULL, 0, NULL, NULL, NULL, 14, ''),
(151, 'Cambio y Lubricación de Rodillos', '<p>Este mantenimiento tiene como objetivo el reemplazo de los rodillos de caucho desgastados del <strong>Molino</strong>, asegurando su correcto funcionamiento y reduciendo la fricción excesiva en el proceso de mezcla de materiales. Se utilizará <strong>Lubricante Industrial EP 220</strong> para mejorar la durabilidad y minimizar el desgaste en componentes móviles.</p><p><strong>Procedimiento:</strong></p><ol><li>Inspección del desgaste de los rodillos existentes.</li><li>Desmontaje con llaves de impacto y extractor de rodillos.</li><li>Aplicación de limpiador industrial para eliminar contaminantes.</li><li>Instalación de rodillos nuevos y ajuste con calibrador de torque.</li><li>Lubricación con EP 220 para evitar fricción y desgaste prematuro.</li></ol>', 1, '2025-06-13', '00:00:00', '2025-06-15', '00:00:00', 'interno', 0, '', '', 1, NULL, 2, 1, 29, NULL, 0, NULL, NULL, NULL, 12, ''),
(152, 'Prueba ', '<p>Este mantenimiento tiene como objetivo el reemplazo de los rodillos de caucho desgastados del <strong>Molino</strong>, asegurando su correcto funcionamiento y reduciendo la fricción excesiva en el proceso de mezcla de materiales. Se utilizará <strong>Lubricante Industrial EP 220</strong> para mejorar la durabilidad y minimizar el desgaste en componentes móviles.</p><p><strong>Procedimiento:</strong></p><ol><li>Inspección del desgaste de los rodillos existentes.</li><li>Desmontaje con llaves de impacto y extractor de rodillos.</li><li>Aplicación de limpiador industrial para eliminar contaminantes.</li><li>Instalación de rodillos nuevos y ajuste con calibrador de torque.</li><li>Lubricación con EP 220 para evitar fricción y desgaste prematuro.</li></ol>', 1, '2025-06-06', '00:00:00', '2025-06-08', '00:00:00', 'interno', 0, '2 días, 0 horas, 0 minutos', '0 días, 18 horas, 2 minutos', 7, NULL, 2, 1, 29, NULL, 0, NULL, 'todo correcto', '2025-06-06 18:02:00', 15, ''),
(153, 'Prueba ', '<p>Este mantenimiento tiene como objetivo el reemplazo de los rodillos de caucho desgastados del <strong>Molino</strong>, asegurando su correcto funcionamiento y reduciendo la fricción excesiva en el proceso de mezcla de materiales. Se utilizará <strong>Lubricante Industrial EP 220</strong> para mejorar la durabilidad y minimizar el desgaste en componentes móviles.</p><p><strong>Procedimiento:</strong></p><ol><li>Inspección del desgaste de los rodillos existentes.</li><li>Desmontaje con llaves de impacto y extractor de rodillos.</li><li>Aplicación de limpiador industrial para eliminar contaminantes.</li><li>Instalación de rodillos nuevos y ajuste con calibrador de torque.</li><li>Lubricación con EP 220 para evitar fricción y desgaste prematuro.</li></ol>', 1, '2025-06-11', '00:00:00', '2025-06-13', '00:00:00', 'interno', 0, '', '', 1, NULL, 2, 1, 29, NULL, 0, NULL, NULL, NULL, 15, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo`
--

CREATE TABLE `tipo` (
  `id_tipo` int(11) NOT NULL,
  `nombre_tipo` varchar(50) NOT NULL,
  `id_status` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo`
--

INSERT INTO `tipo` (`id_tipo`, `nombre_tipo`, `id_status`, `fecha_creacion`) VALUES
(22222229, 'MECANICO', 1, '2025-05-31 12:26:14'),
(22222230, 'ENGRASANTE', 1, '2025-06-05 21:50:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipos_solicitudes`
--

CREATE TABLE `tipos_solicitudes` (
  `id_tipo_solicitud` int(11) NOT NULL,
  `nombre_tipo` varchar(255) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipos_solicitudes`
--

INSERT INTO `tipos_solicitudes` (`id_tipo_solicitud`, `nombre_tipo`, `descripcion`) VALUES
(1, 'Planificación de mantenimiento', 'Solicitud para programar un mantenimiento'),
(2, 'Compra', 'Solicitud para las compras');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_clasificacion`
--

CREATE TABLE `tipo_clasificacion` (
  `id_tipo` int(11) NOT NULL,
  `id_clasificacion` int(11) NOT NULL,
  `id_status` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_clasificacion`
--

INSERT INTO `tipo_clasificacion` (`id_tipo`, `id_clasificacion`, `id_status`) VALUES
(22222229, 42, 1),
(22222229, 43, 1),
(22222229, 48, 1),
(22222230, 53, 1),
(22222230, 55, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_evento`
--

CREATE TABLE `tipo_evento` (
  `id_tipo_evento` int(11) NOT NULL,
  `nombre_tipo_evento` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_evento`
--

INSERT INTO `tipo_evento` (`id_tipo_evento`, `nombre_tipo_evento`, `descripcion`) VALUES
(1, 'Tarea', 'Evento asociado a una tarea específica con objetivos definidos'),
(2, 'Planes', 'Eventos relacionados con la planificación estratégica y organizativa'),
(3, 'Actividades', 'Eventos que incluyen tareas operativas, reuniones o eventos generales');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_mantenimiento`
--

CREATE TABLE `tipo_mantenimiento` (
  `id_tipo` int(11) NOT NULL,
  `nombre_tipo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_mantenimiento`
--

INSERT INTO `tipo_mantenimiento` (`id_tipo`, `nombre_tipo`, `descripcion`) VALUES
(1, 'Preventivo', 'Mantenimiento programado para prevenir fallos'),
(2, 'Correctivo', 'Mantenimiento realizado tras detectar una falla');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_movimiento`
--

CREATE TABLE `tipo_movimiento` (
  `id_tipo_movimiento` int(11) NOT NULL,
  `nombre_tipo_movimiento` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tipo_movimiento`
--

INSERT INTO `tipo_movimiento` (`id_tipo_movimiento`, `nombre_tipo_movimiento`, `descripcion`) VALUES
(1, 'entrada', 'Movimiento de ingreso de productos o dinero'),
(2, 'salida', 'Movimiento de egreso de productos o dinero'),
(3, 'traslado', 'Transferencia de productos entre almacenes o áreas'),
(4, 'retiro', 'Retiro de productos o dinero de la entidad');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_reporte`
--

CREATE TABLE `tipo_reporte` (
  `id_tipo_reporte` int(11) NOT NULL,
  `nombre_tipo_reporte` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_widgets`
--

CREATE TABLE `tipo_widgets` (
  `id_tipo` int(11) NOT NULL,
  `nombre_tipo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_persona` int(11) NOT NULL,
  `id_perfil` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `usuario` varchar(255) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `nombre_imagen` varchar(100) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `intento_fallidos` int(155) NOT NULL DEFAULT 0,
  `intento_bloqueo` int(155) NOT NULL DEFAULT 0,
  `id_status` int(11) NOT NULL DEFAULT 1,
  `token_recuperacion` varchar(255) DEFAULT NULL,
  `expiracion_token` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_persona`, `id_perfil`, `fecha_creacion`, `usuario`, `clave`, `nombre_imagen`, `url`, `intento_fallidos`, `intento_bloqueo`, `id_status`, `token_recuperacion`, `expiracion_token`) VALUES
(1, 1, 1, '2025-05-29 14:55:26', 'admin', '$2y$10$LN9nok10QGBVLbm12gcA7umtaER4.CuU23I1O//hTbFtH0BhV.dAq', 'administrador.jpeg', 'servidor_img/perfil/administrador.jpeg', 0, 0, 1, 'c086b4', '0000-00-00 00:00:00'),
(30, 17, 6, '2025-05-30 00:42:50', 'isabella', '$2y$10$v0qOCd65J1vNqF3O9SHVduVMW6/7qY3wj2cag0MU8UC153eSdxnee', '6838aaaabdfcb.jpeg', 'servidor_img/perfil/6838aaaabdfcb.jpeg', 0, 0, 1, 'd67bc5', '2025-06-03 22:10:35'),
(31, 21, 7, '2025-05-30 00:44:05', 'camila', '$2y$10$gk3N8UwojszMFOjJ8peytOQq418oa4HBRJooH1c4uoLc8PA3NmWLS', '6838aaf5c0c21.jpeg', 'servidor_img/perfil/6838aaf5c0c21.jpeg', 0, 0, 1, 'a3488d', '2025-06-02 16:31:10'),
(32, 22, 2, '2025-05-30 00:44:37', 'juan', '$2y$10$Q5alkE4Ld7TmVuLFsEwHz.jdM6cpjEY7J60v8vg6nacEeOoEF57Q2', '6838ab15a2181.jpeg', 'servidor_img/perfil/6838ab15a2181.jpeg', 0, 0, 1, 'cfddd2', '2025-06-02 15:55:04'),
(33, 23, 3, '2025-05-30 00:49:14', 'leonardo', '$2y$10$5Kdr7HNQafOMBE08mqFRXelybImjT0MSM7wyoFRqnRg6Jc7NvkH7a', '6838ac2a264c7.jpeg', 'servidor_img/perfil/6838ac2a264c7.jpeg', 0, 0, 1, NULL, NULL),
(34, 18, 5, '2025-05-30 00:49:52', 'maria', '$2y$10$gk3N8UwojszMFOjJ8peytOQq418oa4HBRJooH1c4uoLc8PA3NmWLS', '6838ac5028d31.jpeg', 'servidor_img/perfil/6838ac5028d31.jpeg', 0, 0, 1, NULL, NULL),
(35, 24, 4, '2025-05-30 00:58:43', 'daniel', '$2y$10$gk3N8UwojszMFOjJ8peytOQq418oa4HBRJooH1c4uoLc8PA3NmWLS', '6838ae63ecf8e.jpeg', 'servidor_img/perfil/6838ae63ecf8e.jpeg', 0, 0, 1, '118d16', '2025-06-02 15:57:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `widgets`
--

CREATE TABLE `widgets` (
  `id_widget` int(11) NOT NULL,
  `nombre_widget` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo_widget` int(11) NOT NULL,
  `id_status` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`id_actividad`),
  ADD KEY `tarea_id` (`tarea_id`);

--
-- Indices de la tabla `almacen`
--
ALTER TABLE `almacen`
  ADD PRIMARY KEY (`id_almacen`),
  ADD KEY `id_sede` (`id_sede`),
  ADD KEY `id_sucursal` (`id_sucursal`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `articulo`
--
ALTER TABLE `articulo`
  ADD PRIMARY KEY (`id_articulo`);

--
-- Indices de la tabla `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id_blog`),
  ADD KEY `conexion_perfil_blog` (`id_perfil`),
  ADD KEY `conexion_usuario_blog` (`id_usuario`),
  ADD KEY `conexion_status_blog` (`id_status`);

--
-- Indices de la tabla `calendario`
--
ALTER TABLE `calendario`
  ADD PRIMARY KEY (`id_evento`),
  ADD KEY `tipo_evento_id` (`tipo_evento_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `fk_calendario_tarea` (`tarea_id`);

--
-- Indices de la tabla `caracteristicas_maquina`
--
ALTER TABLE `caracteristicas_maquina`
  ADD PRIMARY KEY (`id_caracteristica`),
  ADD KEY `id_maquina` (`id_maquina`);

--
-- Indices de la tabla `cargo`
--
ALTER TABLE `cargo`
  ADD PRIMARY KEY (`id_cargo`),
  ADD KEY `idx_status` (`status`);

--
-- Indices de la tabla `clasificacion`
--
ALTER TABLE `clasificacion`
  ADD PRIMARY KEY (`id_clasificacion`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `codigo`
--
ALTER TABLE `codigo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id_compra`),
  ADD UNIQUE KEY `fk_compra_6` (`codigo_compra`),
  ADD KEY `id_solicitud` (`id_solicitud`),
  ADD KEY `id_proveedor` (`id_proveedor`),
  ADD KEY `id_usuario_solicitante` (`id_usuario_solicitante`),
  ADD KEY `id_usuario_aprobador` (`id_usuario_aprobador`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `compra_herramienta`
--
ALTER TABLE `compra_herramienta`
  ADD PRIMARY KEY (`id_compra_herramienta`),
  ADD KEY `id_compra` (`id_compra`),
  ADD KEY `id_herramienta` (`id_herramienta`);

--
-- Indices de la tabla `compra_producto`
--
ALTER TABLE `compra_producto`
  ADD PRIMARY KEY (`id_compra_producto`),
  ADD KEY `id_compra` (`id_compra`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `compra_repuesto`
--
ALTER TABLE `compra_repuesto`
  ADD PRIMARY KEY (`id_compra_repuesto`),
  ADD KEY `id_compra` (`id_compra`),
  ADD KEY `id_repuesto` (`id_repuesto`);

--
-- Indices de la tabla `cotizacion`
--
ALTER TABLE `cotizacion`
  ADD PRIMARY KEY (`id_cotizacion`),
  ADD KEY `reporte_id` (`reporte_id`),
  ADD KEY `proveedor_id` (`proveedor_id`);

--
-- Indices de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD PRIMARY KEY (`id_empresa`),
  ADD KEY `conexion_pais_empresa` (`ubicacion_pais_id`),
  ADD KEY `conexion_estado_empresa` (`ubicacion_estado_id`),
  ADD KEY `conexion_status_empresa` (`status_id`);

--
-- Indices de la tabla `especificaciones_maquina`
--
ALTER TABLE `especificaciones_maquina`
  ADD PRIMARY KEY (`id_especificacion`),
  ADD KEY `id_maquina` (`id_maquina`);

--
-- Indices de la tabla `especificaciones_repuestos`
--
ALTER TABLE `especificaciones_repuestos`
  ADD PRIMARY KEY (`id_especificacion`),
  ADD KEY `id_repuesto` (`id_repuesto`);

--
-- Indices de la tabla `estadistica_desempeno`
--
ALTER TABLE `estadistica_desempeno`
  ADD PRIMARY KEY (`id_desempeno`),
  ADD KEY `reporte_id` (`reporte_id`),
  ADD KEY `tarea_id` (`tarea_id`);

--
-- Indices de la tabla `estadistica_gastos`
--
ALTER TABLE `estadistica_gastos`
  ADD PRIMARY KEY (`id_estadistica`),
  ADD KEY `reporte_id` (`reporte_id`);

--
-- Indices de la tabla `estadistica_mantenimiento`
--
ALTER TABLE `estadistica_mantenimiento`
  ADD PRIMARY KEY (`id_estadistica`),
  ADD KEY `reporte_id` (`reporte_id`),
  ADD KEY `tipo_mantenimiento_id` (`tipo_mantenimiento_id`);

--
-- Indices de la tabla `estadistica_solicitudes`
--
ALTER TABLE `estadistica_solicitudes`
  ADD PRIMARY KEY (`id_estadistica`),
  ADD KEY `reporte_id` (`reporte_id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `estado`
--
ALTER TABLE `estado`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_4786469191104EC2` (`ubicacionpaisid`);

--
-- Indices de la tabla `estado_maquina_mantenimiento`
--
ALTER TABLE `estado_maquina_mantenimiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_maquina_unica` (`id_maquina_unica`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `filtros_guardados`
--
ALTER TABLE `filtros_guardados`
  ADD PRIMARY KEY (`id_filtro`),
  ADD KEY `usuario_id_filtro` (`usuario_id_filtro`);

--
-- Indices de la tabla `herramientas`
--
ALTER TABLE `herramientas`
  ADD PRIMARY KEY (`id_herramienta`),
  ADD KEY `status_id` (`id_status`),
  ADD KEY `id_marca` (`id_marca`),
  ADD KEY `id_modelo` (`id_modelo`),
  ADD KEY `id_tipo` (`id_tipo`);

--
-- Indices de la tabla `herramienta_actividad`
--
ALTER TABLE `herramienta_actividad`
  ADD PRIMARY KEY (`id_actividad`),
  ADD KEY `herramienta_id` (`herramienta_id`),
  ADD KEY `tarea_id` (`tarea_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indices de la tabla `herramienta_plan`
--
ALTER TABLE `herramienta_plan`
  ADD PRIMARY KEY (`id_plan_asociado`),
  ADD KEY `herramienta_id` (`herramienta_id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indices de la tabla `herramienta_tarea`
--
ALTER TABLE `herramienta_tarea`
  ADD PRIMARY KEY (`id_asignacion`),
  ADD KEY `herramienta_id` (`herramienta_id`),
  ADD KEY `tarea_id` (`tarea_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indices de la tabla `historial_compra`
--
ALTER TABLE `historial_compra`
  ADD PRIMARY KEY (`id_compra`);

--
-- Indices de la tabla `historial_solicitudes`
--
ALTER TABLE `historial_solicitudes`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `id_solicitud` (`id_solicitud`),
  ADD KEY `id_perfil` (`id_perfil`);

--
-- Indices de la tabla `imagen_blog`
--
ALTER TABLE `imagen_blog`
  ADD PRIMARY KEY (`id_imagen`),
  ADD KEY `id_blog` (`id_blog`);

--
-- Indices de la tabla `inventario_herramientas`
--
ALTER TABLE `inventario_herramientas`
  ADD PRIMARY KEY (`id_inventario_herramienta`),
  ADD KEY `herramienta_id` (`herramienta_id`),
  ADD KEY `status_id` (`status_id`),
  ADD KEY `fk_id_sede` (`id_sede`),
  ADD KEY `fk_id_almacen` (`id_almacen`);

--
-- Indices de la tabla `inventario_maquina`
--
ALTER TABLE `inventario_maquina`
  ADD PRIMARY KEY (`id_inventario_maquina`),
  ADD KEY `id_maquina` (`id_maquina`),
  ADD KEY `fk_inventario_maquina_almacen` (`sede_id`);

--
-- Indices de la tabla `inventario_producto`
--
ALTER TABLE `inventario_producto`
  ADD PRIMARY KEY (`id_inventario_producto`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `fk_inventario_producto_almacen` (`id_almacen`);

--
-- Indices de la tabla `inventario_repuesto`
--
ALTER TABLE `inventario_repuesto`
  ADD PRIMARY KEY (`id_inventario_repuesto`),
  ADD KEY `id_repuesto` (`id_repuesto`),
  ADD KEY `fk_inventario_repuesto_almacen` (`id_almacen`);

--
-- Indices de la tabla `maquina`
--
ALTER TABLE `maquina`
  ADD PRIMARY KEY (`id_maquina`),
  ADD UNIQUE KEY `unique_codigo_maquina` (`codigo_maquina`),
  ADD KEY `id_marca` (`id_marca`),
  ADD KEY `id_modelo` (`id_modelo`),
  ADD KEY `id_tipo` (`id_tipo`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `maquina_repuesto`
--
ALTER TABLE `maquina_repuesto`
  ADD PRIMARY KEY (`id_maquina_repuesto`),
  ADD KEY `id_maquina` (`id_maquina`),
  ADD KEY `id_repuesto` (`id_repuesto`),
  ADD KEY `fk_status_maquina_repuesto` (`id_status`);

--
-- Indices de la tabla `maquina_unica`
--
ALTER TABLE `maquina_unica`
  ADD PRIMARY KEY (`id_maquina_unica`),
  ADD UNIQUE KEY `CodigoUnico` (`CodigoUnico`),
  ADD KEY `fk_sede` (`id_sede`),
  ADD KEY `fk_status_maquina` (`id_status`);

--
-- Indices de la tabla `marca`
--
ALTER TABLE `marca`
  ADD PRIMARY KEY (`id_marca`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `marca_modelo`
--
ALTER TABLE `marca_modelo`
  ADD PRIMARY KEY (`id_marca`,`id_modelo`),
  ADD KEY `id_modelo` (`id_modelo`),
  ADD KEY `status_id_status_ibfk_3` (`id_status`);

--
-- Indices de la tabla `menus`
--
ALTER TABLE `menus`
  ADD PRIMARY KEY (`id_menu`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `modelo`
--
ALTER TABLE `modelo`
  ADD PRIMARY KEY (`id_modelo`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `movimiento_herramientas`
--
ALTER TABLE `movimiento_herramientas`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `herramienta_id` (`herramienta_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indices de la tabla `movimiento_maquina`
--
ALTER TABLE `movimiento_maquina`
  ADD PRIMARY KEY (`id_movimiento_maquina`),
  ADD KEY `id_maquina` (`id_maquina`),
  ADD KEY `id_almacen_origen` (`id_almacen_origen`),
  ADD KEY `id_almacen_destino` (`id_almacen_destino`),
  ADD KEY `fk_movimiento_maquina_tipo_movimiento` (`id_tipo_movimiento`);

--
-- Indices de la tabla `movimiento_producto`
--
ALTER TABLE `movimiento_producto`
  ADD PRIMARY KEY (`id_movimiento_producto`),
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `id_almacen_origen` (`id_almacen_origen`),
  ADD KEY `id_almacen_destino` (`id_almacen_destino`),
  ADD KEY `fk_movimiento_producto_tipo_movimiento` (`id_tipo_movimiento`),
  ADD KEY `movimiento_producto_solicitud_ibf` (`id_solicitud`);

--
-- Indices de la tabla `movimiento_repuesto`
--
ALTER TABLE `movimiento_repuesto`
  ADD PRIMARY KEY (`id_movimiento_repuesto`),
  ADD KEY `id_repuesto` (`id_repuesto`),
  ADD KEY `id_almacen_origen` (`id_almacen_origen`),
  ADD KEY `id_almacen_destino` (`id_almacen_destino`),
  ADD KEY `fk_movimiento_repuesto_tipo_movimiento` (`id_tipo_movimiento`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD UNIQUE KEY `fk_notificaciones_status` (`id_status`),
  ADD KEY `id_perfil` (`id_perfil`);

--
-- Indices de la tabla `orden_compra`
--
ALTER TABLE `orden_compra`
  ADD PRIMARY KEY (`id_orden`),
  ADD KEY `reporte_id` (`reporte_id`),
  ADD KEY `proveedor_id` (`proveedor_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indices de la tabla `pais`
--
ALTER TABLE `pais`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `perfiles`
--
ALTER TABLE `perfiles`
  ADD PRIMARY KEY (`id_perfil`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `perfil_menu`
--
ALTER TABLE `perfil_menu`
  ADD PRIMARY KEY (`id_perfil`,`id_menu`),
  ADD KEY `id_menu` (`id_menu`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `perfil_permiso_menu`
--
ALTER TABLE `perfil_permiso_menu`
  ADD PRIMARY KEY (`id_perfil`,`id_menu`,`id_permiso`),
  ADD KEY `id_menu` (`id_menu`),
  ADD KEY `id_permiso` (`id_permiso`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `perfil_permiso_submenu`
--
ALTER TABLE `perfil_permiso_submenu`
  ADD PRIMARY KEY (`id_perfil`,`id_submenu`,`id_permiso`),
  ADD KEY `id_submenu` (`id_submenu`),
  ADD KEY `id_permiso` (`id_permiso`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `perfil_submenu`
--
ALTER TABLE `perfil_submenu`
  ADD PRIMARY KEY (`id_perfil`,`id_submenu`),
  ADD KEY `id_submenu` (`id_submenu`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `perfil_widget`
--
ALTER TABLE `perfil_widget`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_perfil` (`id_perfil`),
  ADD KEY `id_widget` (`id_widget`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id_permiso`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `permiso_menu`
--
ALTER TABLE `permiso_menu`
  ADD PRIMARY KEY (`id_menu`,`id_permiso`),
  ADD KEY `id_permiso` (`id_permiso`),
  ADD KEY `conexion_permisos_menu` (`status_id_status`);

--
-- Indices de la tabla `permiso_submenu`
--
ALTER TABLE `permiso_submenu`
  ADD PRIMARY KEY (`id_submenu`,`id_permiso`),
  ADD KEY `id_permiso` (`id_permiso`),
  ADD KEY `conexion_permisos_submenu` (`status_id_status`);

--
-- Indices de la tabla `personas`
--
ALTER TABLE `personas`
  ADD PRIMARY KEY (`id_persona`),
  ADD UNIQUE KEY `correo_electronico` (`correo_electronico`),
  ADD KEY `id_status` (`id_status`),
  ADD KEY `id_cargo` (`id_cargo`),
  ADD KEY `fk_pais_id` (`pais_id`),
  ADD KEY `fk_estado_id` (`estado_id`);

--
-- Indices de la tabla `pieza_unica`
--
ALTER TABLE `pieza_unica`
  ADD PRIMARY KEY (`id_unico`),
  ADD UNIQUE KEY `NumeroSerie` (`NumeroSerie`),
  ADD KEY `id_repuesto` (`id_repuesto`);

--
-- Indices de la tabla `planes`
--
ALTER TABLE `planes`
  ADD PRIMARY KEY (`id_plan`),
  ADD KEY `fk_planes_proveedor` (`proveedor_id`);

--
-- Indices de la tabla `planta`
--
ALTER TABLE `planta`
  ADD PRIMARY KEY (`id_planta`),
  ADD KEY `id_sede` (`id_sede`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `planta_articulo`
--
ALTER TABLE `planta_articulo`
  ADD PRIMARY KEY (`id_relacion`),
  ADD UNIQUE KEY `fk_planta_articulo` (`id_relacion`),
  ADD UNIQUE KEY `fk_planta_articulo2` (`id_articulo`),
  ADD KEY `id_planta` (`id_planta`);

--
-- Indices de la tabla `plan_ejecuciones`
--
ALTER TABLE `plan_ejecuciones`
  ADD PRIMARY KEY (`id_ejecucion`),
  ADD KEY `id_plan` (`id_plan`),
  ADD KEY `id_tarea` (`id_tarea`);

--
-- Indices de la tabla `prioridad`
--
ALTER TABLE `prioridad`
  ADD PRIMARY KEY (`id_importancia`),
  ADD UNIQUE KEY `nivel` (`nivel`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `id_marca` (`id_marca`),
  ADD KEY `id_modelo` (`id_modelo`),
  ADD KEY `id_tipo` (`id_tipo`),
  ADD KEY `id_clasificacion` (`id_clasificacion`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `producto_actividad`
--
ALTER TABLE `producto_actividad`
  ADD PRIMARY KEY (`id_producto_actividad`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `actividad_id` (`actividad_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indices de la tabla `producto_plan`
--
ALTER TABLE `producto_plan`
  ADD PRIMARY KEY (`id_producto_plan`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indices de la tabla `producto_tarea`
--
ALTER TABLE `producto_tarea`
  ADD PRIMARY KEY (`id_producto_tarea`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `tarea_id` (`tarea_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indices de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`id_proveedor`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `status_id_status` (`id_status`),
  ADD KEY `fk_pais` (`id_pais`),
  ADD KEY `fk_estado` (`id_estado`);

--
-- Indices de la tabla `proveedor_producto`
--
ALTER TABLE `proveedor_producto`
  ADD PRIMARY KEY (`id_proveedor_producto`),
  ADD KEY `id_proveedor` (`id_proveedor`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `proveedor_repuesto`
--
ALTER TABLE `proveedor_repuesto`
  ADD PRIMARY KEY (`id_proveedor_repuesto`),
  ADD KEY `id_proveedor` (`id_proveedor`),
  ADD KEY `id_repuesto` (`id_repuesto`);

--
-- Indices de la tabla `proveedor_servicio`
--
ALTER TABLE `proveedor_servicio`
  ADD PRIMARY KEY (`id_proveedor`,`id_servicio`),
  ADD KEY `id_servicio` (`id_servicio`),
  ADD KEY `fk_status` (`status_id`);

--
-- Indices de la tabla `registro_actividades`
--
ALTER TABLE `registro_actividades`
  ADD PRIMARY KEY (`id_registro`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `reporte`
--
ALTER TABLE `reporte`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `tipo_reporte_id` (`tipo_reporte_id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `status_id` (`status_id`);

--
-- Indices de la tabla `repuesto`
--
ALTER TABLE `repuesto`
  ADD PRIMARY KEY (`id_repuesto`),
  ADD KEY `id_modelo` (`id_modelo`),
  ADD KEY `id_status` (`id_status`),
  ADD KEY `fk_repuesto_marca` (`id_marca`),
  ADD KEY `fk_tipo_repuesto` (`id_tipo`) USING BTREE;

--
-- Indices de la tabla `repuesto_actividad`
--
ALTER TABLE `repuesto_actividad`
  ADD PRIMARY KEY (`id_repuesto_actividad`),
  ADD KEY `repuesto_id` (`repuesto_id`),
  ADD KEY `actividad_id` (`actividad_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indices de la tabla `repuesto_plan`
--
ALTER TABLE `repuesto_plan`
  ADD PRIMARY KEY (`id_repuesto_plan`),
  ADD KEY `fk_repuesto_plan` (`repuesto_id`),
  ADD KEY `fk_plan_repuesto` (`plan_id`),
  ADD KEY `fk_status_plan_repuesto` (`status_id`),
  ADD KEY `fk_proveedor_plan_repuesto` (`proveedor_id`);

--
-- Indices de la tabla `repuesto_tarea`
--
ALTER TABLE `repuesto_tarea`
  ADD PRIMARY KEY (`id_repuesto_tarea`),
  ADD KEY `repuesto_id` (`repuesto_id`),
  ADD KEY `tarea_id` (`tarea_id`),
  ADD KEY `status_id` (`status_id`);

--
-- Indices de la tabla `responsable`
--
ALTER TABLE `responsable`
  ADD PRIMARY KEY (`id_responsable`),
  ADD KEY `persona_id` (`persona_id`),
  ADD KEY `tarea_id` (`tarea_id`),
  ADD KEY `actividad_id` (`actividad_id`),
  ADD KEY `fk_usuario` (`id_usuario`);

--
-- Indices de la tabla `sede`
--
ALTER TABLE `sede`
  ADD PRIMARY KEY (`id_sede`),
  ADD KEY `id_empresa` (`id_empresa`),
  ADD KEY `id_status` (`id_status`),
  ADD KEY `id_sucursal_fija` (`id_sucursal_fija`);

--
-- Indices de la tabla `sede_sucursal`
--
ALTER TABLE `sede_sucursal`
  ADD PRIMARY KEY (`id_sede_sucursal`),
  ADD KEY `id_sede` (`id_sede`),
  ADD KEY `id_sucursal` (`id_sucursal`),
  ADD KEY `fk_status2` (`id_status`);

--
-- Indices de la tabla `servicio`
--
ALTER TABLE `servicio`
  ADD PRIMARY KEY (`id_servicio`);

--
-- Indices de la tabla `servicio_herramienta`
--
ALTER TABLE `servicio_herramienta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_servicio` (`id_servicio`),
  ADD KEY `id_herramienta` (`id_herramienta`);

--
-- Indices de la tabla `servicio_maquina`
--
ALTER TABLE `servicio_maquina`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_servicio` (`id_servicio`),
  ADD KEY `id_maquina` (`id_maquina`);

--
-- Indices de la tabla `servicio_piezas`
--
ALTER TABLE `servicio_piezas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_servicio` (`id_servicio`),
  ADD KEY `id_repuesto` (`id_repuesto`);

--
-- Indices de la tabla `servicio_producto`
--
ALTER TABLE `servicio_producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_servicio` (`id_servicio`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `servicio_repuesto`
--
ALTER TABLE `servicio_repuesto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_servicio` (`id_servicio`),
  ADD KEY `id_repuesto` (`id_repuesto`);

--
-- Indices de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD KEY `id_tipo_solicitud` (`id_tipo_solicitud`),
  ADD KEY `id_status` (`id_status`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `fk_perfil` (`id_perfil`);

--
-- Indices de la tabla `solicitudes_tareas`
--
ALTER TABLE `solicitudes_tareas`
  ADD PRIMARY KEY (`id_solicitud`,`id_tarea`),
  ADD KEY `id_tarea` (`id_tarea`);

--
-- Indices de la tabla `status`
--
ALTER TABLE `status`
  ADD PRIMARY KEY (`id_status`);

--
-- Indices de la tabla `submenus`
--
ALTER TABLE `submenus`
  ADD PRIMARY KEY (`id_submenu`),
  ADD KEY `id_menu` (`id_menu`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `sucursal`
--
ALTER TABLE `sucursal`
  ADD PRIMARY KEY (`id_sucursal`),
  ADD KEY `id_status` (`id_status`),
  ADD KEY `conexion_pais_sucursal` (`pais_id_pais`),
  ADD KEY `conexion_estado_sucursal` (`estado_id_estado`);

--
-- Indices de la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD PRIMARY KEY (`id_tarea`),
  ADD KEY `tareas_ibfk_1` (`tipo_mantenimiento_id`),
  ADD KEY `fk_importancia` (`id_importancia`),
  ADD KEY `fk_maquina_unica` (`id_maquina_unica`),
  ADD KEY `fk_servicio` (`id_servicio`),
  ADD KEY `fk_sede2` (`id_sede`),
  ADD KEY `fk_status_tarea` (`status_id`),
  ADD KEY `fk_proveedor_tarea` (`proveedor_id`),
  ADD KEY `fk_tareas_solicitud` (`id_solicitud`),
  ADD KEY `fk_tarea_plan` (`id_plan`);

--
-- Indices de la tabla `tipo`
--
ALTER TABLE `tipo`
  ADD PRIMARY KEY (`id_tipo`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `tipos_solicitudes`
--
ALTER TABLE `tipos_solicitudes`
  ADD PRIMARY KEY (`id_tipo_solicitud`);

--
-- Indices de la tabla `tipo_clasificacion`
--
ALTER TABLE `tipo_clasificacion`
  ADD PRIMARY KEY (`id_tipo`,`id_clasificacion`),
  ADD KEY `id_clasificacion` (`id_clasificacion`),
  ADD KEY `fk_id_status` (`id_status`);

--
-- Indices de la tabla `tipo_evento`
--
ALTER TABLE `tipo_evento`
  ADD PRIMARY KEY (`id_tipo_evento`);

--
-- Indices de la tabla `tipo_mantenimiento`
--
ALTER TABLE `tipo_mantenimiento`
  ADD PRIMARY KEY (`id_tipo`);

--
-- Indices de la tabla `tipo_movimiento`
--
ALTER TABLE `tipo_movimiento`
  ADD PRIMARY KEY (`id_tipo_movimiento`);

--
-- Indices de la tabla `tipo_reporte`
--
ALTER TABLE `tipo_reporte`
  ADD PRIMARY KEY (`id_tipo_reporte`);

--
-- Indices de la tabla `tipo_widgets`
--
ALTER TABLE `tipo_widgets`
  ADD PRIMARY KEY (`id_tipo`),
  ADD KEY `id_status` (`id_status`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `id_persona` (`id_persona`),
  ADD KEY `id_perfil` (`id_perfil`),
  ADD KEY `fk_usuarios_status` (`id_status`);

--
-- Indices de la tabla `widgets`
--
ALTER TABLE `widgets`
  ADD PRIMARY KEY (`id_widget`),
  ADD KEY `tipo_widget` (`tipo_widget`),
  ADD KEY `id_status` (`id_status`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `id_actividad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `almacen`
--
ALTER TABLE `almacen`
  MODIFY `id_almacen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `articulo`
--
ALTER TABLE `articulo`
  MODIFY `id_articulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `blog`
--
ALTER TABLE `blog`
  MODIFY `id_blog` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `calendario`
--
ALTER TABLE `calendario`
  MODIFY `id_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `caracteristicas_maquina`
--
ALTER TABLE `caracteristicas_maquina`
  MODIFY `id_caracteristica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `cargo`
--
ALTER TABLE `cargo`
  MODIFY `id_cargo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `clasificacion`
--
ALTER TABLE `clasificacion`
  MODIFY `id_clasificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `codigo`
--
ALTER TABLE `codigo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `compra_herramienta`
--
ALTER TABLE `compra_herramienta`
  MODIFY `id_compra_herramienta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `compra_producto`
--
ALTER TABLE `compra_producto`
  MODIFY `id_compra_producto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `compra_repuesto`
--
ALTER TABLE `compra_repuesto`
  MODIFY `id_compra_repuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cotizacion`
--
ALTER TABLE `cotizacion`
  MODIFY `id_cotizacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `dispositivos`
--
ALTER TABLE `dispositivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `empresa`
--
ALTER TABLE `empresa`
  MODIFY `id_empresa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `especificaciones_maquina`
--
ALTER TABLE `especificaciones_maquina`
  MODIFY `id_especificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT de la tabla `especificaciones_repuestos`
--
ALTER TABLE `especificaciones_repuestos`
  MODIFY `id_especificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `estadistica_desempeno`
--
ALTER TABLE `estadistica_desempeno`
  MODIFY `id_desempeno` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estadistica_gastos`
--
ALTER TABLE `estadistica_gastos`
  MODIFY `id_estadistica` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estadistica_mantenimiento`
--
ALTER TABLE `estadistica_mantenimiento`
  MODIFY `id_estadistica` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estadistica_solicitudes`
--
ALTER TABLE `estadistica_solicitudes`
  MODIFY `id_estadistica` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estado`
--
ALTER TABLE `estado`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2202;

--
-- AUTO_INCREMENT de la tabla `estado_maquina_mantenimiento`
--
ALTER TABLE `estado_maquina_mantenimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `filtros_guardados`
--
ALTER TABLE `filtros_guardados`
  MODIFY `id_filtro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `herramientas`
--
ALTER TABLE `herramientas`
  MODIFY `id_herramienta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `herramienta_actividad`
--
ALTER TABLE `herramienta_actividad`
  MODIFY `id_actividad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `herramienta_plan`
--
ALTER TABLE `herramienta_plan`
  MODIFY `id_plan_asociado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `herramienta_tarea`
--
ALTER TABLE `herramienta_tarea`
  MODIFY `id_asignacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `historial_compra`
--
ALTER TABLE `historial_compra`
  MODIFY `id_compra` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_solicitudes`
--
ALTER TABLE `historial_solicitudes`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `imagen_blog`
--
ALTER TABLE `imagen_blog`
  MODIFY `id_imagen` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `inventario_herramientas`
--
ALTER TABLE `inventario_herramientas`
  MODIFY `id_inventario_herramienta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `inventario_maquina`
--
ALTER TABLE `inventario_maquina`
  MODIFY `id_inventario_maquina` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `inventario_producto`
--
ALTER TABLE `inventario_producto`
  MODIFY `id_inventario_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `inventario_repuesto`
--
ALTER TABLE `inventario_repuesto`
  MODIFY `id_inventario_repuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `maquina`
--
ALTER TABLE `maquina`
  MODIFY `id_maquina` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `maquina_repuesto`
--
ALTER TABLE `maquina_repuesto`
  MODIFY `id_maquina_repuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=213124;

--
-- AUTO_INCREMENT de la tabla `maquina_unica`
--
ALTER TABLE `maquina_unica`
  MODIFY `id_maquina_unica` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12324;

--
-- AUTO_INCREMENT de la tabla `marca`
--
ALTER TABLE `marca`
  MODIFY `id_marca` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT de la tabla `menus`
--
ALTER TABLE `menus`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `modelo`
--
ALTER TABLE `modelo`
  MODIFY `id_modelo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT de la tabla `movimiento_herramientas`
--
ALTER TABLE `movimiento_herramientas`
  MODIFY `id_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `movimiento_maquina`
--
ALTER TABLE `movimiento_maquina`
  MODIFY `id_movimiento_maquina` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `movimiento_producto`
--
ALTER TABLE `movimiento_producto`
  MODIFY `id_movimiento_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `movimiento_repuesto`
--
ALTER TABLE `movimiento_repuesto`
  MODIFY `id_movimiento_repuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `orden_compra`
--
ALTER TABLE `orden_compra`
  MODIFY `id_orden` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pais`
--
ALTER TABLE `pais`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=247;

--
-- AUTO_INCREMENT de la tabla `perfiles`
--
ALTER TABLE `perfiles`
  MODIFY `id_perfil` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `perfil_widget`
--
ALTER TABLE `perfil_widget`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id_permiso` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `personas`
--
ALTER TABLE `personas`
  MODIFY `id_persona` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `pieza_unica`
--
ALTER TABLE `pieza_unica`
  MODIFY `id_unico` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `planes`
--
ALTER TABLE `planes`
  MODIFY `id_plan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `planta`
--
ALTER TABLE `planta`
  MODIFY `id_planta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `planta_articulo`
--
ALTER TABLE `planta_articulo`
  MODIFY `id_relacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `plan_ejecuciones`
--
ALTER TABLE `plan_ejecuciones`
  MODIFY `id_ejecucion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `prioridad`
--
ALTER TABLE `prioridad`
  MODIFY `id_importancia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `producto_actividad`
--
ALTER TABLE `producto_actividad`
  MODIFY `id_producto_actividad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `producto_plan`
--
ALTER TABLE `producto_plan`
  MODIFY `id_producto_plan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `producto_tarea`
--
ALTER TABLE `producto_tarea`
  MODIFY `id_producto_tarea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `id_proveedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `proveedor_producto`
--
ALTER TABLE `proveedor_producto`
  MODIFY `id_proveedor_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `proveedor_repuesto`
--
ALTER TABLE `proveedor_repuesto`
  MODIFY `id_proveedor_repuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `registro_actividades`
--
ALTER TABLE `registro_actividades`
  MODIFY `id_registro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=380;

--
-- AUTO_INCREMENT de la tabla `reporte`
--
ALTER TABLE `reporte`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `repuesto`
--
ALTER TABLE `repuesto`
  MODIFY `id_repuesto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `repuesto_actividad`
--
ALTER TABLE `repuesto_actividad`
  MODIFY `id_repuesto_actividad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `repuesto_plan`
--
ALTER TABLE `repuesto_plan`
  MODIFY `id_repuesto_plan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `repuesto_tarea`
--
ALTER TABLE `repuesto_tarea`
  MODIFY `id_repuesto_tarea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT de la tabla `responsable`
--
ALTER TABLE `responsable`
  MODIFY `id_responsable` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=76;

--
-- AUTO_INCREMENT de la tabla `sede`
--
ALTER TABLE `sede`
  MODIFY `id_sede` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2003;

--
-- AUTO_INCREMENT de la tabla `sede_sucursal`
--
ALTER TABLE `sede_sucursal`
  MODIFY `id_sede_sucursal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `servicio`
--
ALTER TABLE `servicio`
  MODIFY `id_servicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de la tabla `servicio_herramienta`
--
ALTER TABLE `servicio_herramienta`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `servicio_maquina`
--
ALTER TABLE `servicio_maquina`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `servicio_piezas`
--
ALTER TABLE `servicio_piezas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `servicio_producto`
--
ALTER TABLE `servicio_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `servicio_repuesto`
--
ALTER TABLE `servicio_repuesto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `submenus`
--
ALTER TABLE `submenus`
  MODIFY `id_submenu` int(150) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `sucursal`
--
ALTER TABLE `sucursal`
  MODIFY `id_sucursal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `tareas`
--
ALTER TABLE `tareas`
  MODIFY `id_tarea` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=154;

--
-- AUTO_INCREMENT de la tabla `tipo`
--
ALTER TABLE `tipo`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22222231;

--
-- AUTO_INCREMENT de la tabla `tipos_solicitudes`
--
ALTER TABLE `tipos_solicitudes`
  MODIFY `id_tipo_solicitud` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tipo_evento`
--
ALTER TABLE `tipo_evento`
  MODIFY `id_tipo_evento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tipo_mantenimiento`
--
ALTER TABLE `tipo_mantenimiento`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tipo_movimiento`
--
ALTER TABLE `tipo_movimiento`
  MODIFY `id_tipo_movimiento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tipo_reporte`
--
ALTER TABLE `tipo_reporte`
  MODIFY `id_tipo_reporte` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_widgets`
--
ALTER TABLE `tipo_widgets`
  MODIFY `id_tipo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `widgets`
--
ALTER TABLE `widgets`
  MODIFY `id_widget` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD CONSTRAINT `actividades_ibfk_1` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id_tarea`);

--
-- Filtros para la tabla `almacen`
--
ALTER TABLE `almacen`
  ADD CONSTRAINT `almacen_ibfk_1` FOREIGN KEY (`id_sede`) REFERENCES `sede` (`id_sede`) ON DELETE CASCADE,
  ADD CONSTRAINT `almacen_ibfk_2` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursal` (`id_sucursal`) ON DELETE SET NULL,
  ADD CONSTRAINT `almacen_ibfk_3` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`) ON DELETE SET NULL;

--
-- Filtros para la tabla `blog`
--
ALTER TABLE `blog`
  ADD CONSTRAINT `conexion_perfil_blog` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id_perfil`),
  ADD CONSTRAINT `conexion_status_blog` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`),
  ADD CONSTRAINT `conexion_usuario_blog` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `calendario`
--
ALTER TABLE `calendario`
  ADD CONSTRAINT `calendario_ibfk_1` FOREIGN KEY (`tipo_evento_id`) REFERENCES `tipo_evento` (`id_tipo_evento`),
  ADD CONSTRAINT `calendario_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`),
  ADD CONSTRAINT `fk_calendario_tarea` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id_tarea`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `caracteristicas_maquina`
--
ALTER TABLE `caracteristicas_maquina`
  ADD CONSTRAINT `caracteristicas_maquina_ibfk_1` FOREIGN KEY (`id_maquina`) REFERENCES `maquina` (`id_maquina`);

--
-- Filtros para la tabla `clasificacion`
--
ALTER TABLE `clasificacion`
  ADD CONSTRAINT `clasificacion_ibfk_1` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `compras_ibfk_1` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes` (`id_solicitud`),
  ADD CONSTRAINT `compras_ibfk_2` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedor` (`id_proveedor`),
  ADD CONSTRAINT `compras_ibfk_3` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `compras_ibfk_4` FOREIGN KEY (`id_usuario_aprobador`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `compras_ibfk_5` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `compra_herramienta`
--
ALTER TABLE `compra_herramienta`
  ADD CONSTRAINT `codigo_herramienta_ibfk` FOREIGN KEY (`codigo_herramienta`) REFERENCES `codigo` (`codigo`),
  ADD CONSTRAINT `compra_herramienta_ibfk_1` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`),
  ADD CONSTRAINT `compra_herramienta_ibfk_2` FOREIGN KEY (`id_herramienta`) REFERENCES `herramientas` (`id_herramienta`);

--
-- Filtros para la tabla `compra_producto`
--
ALTER TABLE `compra_producto`
  ADD CONSTRAINT `compra_producto_ibfk_1` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`),
  ADD CONSTRAINT `compra_producto_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`);

--
-- Filtros para la tabla `compra_repuesto`
--
ALTER TABLE `compra_repuesto`
  ADD CONSTRAINT `codigo_repuesto_ibfk` FOREIGN KEY (`codigo_repuesto`) REFERENCES `codigo` (`codigo`),
  ADD CONSTRAINT `compra_repuesto_ibfk_1` FOREIGN KEY (`id_compra`) REFERENCES `compras` (`id_compra`),
  ADD CONSTRAINT `compra_repuesto_ibfk_2` FOREIGN KEY (`id_repuesto`) REFERENCES `repuesto` (`id_repuesto`);

--
-- Filtros para la tabla `cotizacion`
--
ALTER TABLE `cotizacion`
  ADD CONSTRAINT `cotizacion_ibfk_1` FOREIGN KEY (`reporte_id`) REFERENCES `reporte` (`id_reporte`),
  ADD CONSTRAINT `cotizacion_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedor` (`id_proveedor`);

--
-- Filtros para la tabla `empresa`
--
ALTER TABLE `empresa`
  ADD CONSTRAINT `conexion_estado_empresa` FOREIGN KEY (`ubicacion_estado_id`) REFERENCES `estado` (`id`),
  ADD CONSTRAINT `conexion_pais_empresa` FOREIGN KEY (`ubicacion_pais_id`) REFERENCES `pais` (`id`),
  ADD CONSTRAINT `conexion_status_empresa` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `especificaciones_maquina`
--
ALTER TABLE `especificaciones_maquina`
  ADD CONSTRAINT `especificaciones_maquina_ibfk_1` FOREIGN KEY (`id_maquina`) REFERENCES `maquina` (`id_maquina`);

--
-- Filtros para la tabla `especificaciones_repuestos`
--
ALTER TABLE `especificaciones_repuestos`
  ADD CONSTRAINT `especificaciones_repuestos_ibfk_1` FOREIGN KEY (`id_repuesto`) REFERENCES `repuesto` (`id_repuesto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `estadistica_desempeno`
--
ALTER TABLE `estadistica_desempeno`
  ADD CONSTRAINT `estadistica_desempeno_ibfk_1` FOREIGN KEY (`reporte_id`) REFERENCES `reporte` (`id_reporte`),
  ADD CONSTRAINT `estadistica_desempeno_ibfk_2` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id_tarea`);

--
-- Filtros para la tabla `estadistica_gastos`
--
ALTER TABLE `estadistica_gastos`
  ADD CONSTRAINT `estadistica_gastos_ibfk_1` FOREIGN KEY (`reporte_id`) REFERENCES `reporte` (`id_reporte`);

--
-- Filtros para la tabla `estadistica_mantenimiento`
--
ALTER TABLE `estadistica_mantenimiento`
  ADD CONSTRAINT `estadistica_mantenimiento_ibfk_1` FOREIGN KEY (`reporte_id`) REFERENCES `reporte` (`id_reporte`),
  ADD CONSTRAINT `estadistica_mantenimiento_ibfk_2` FOREIGN KEY (`tipo_mantenimiento_id`) REFERENCES `tipo_mantenimiento` (`id_tipo`);

--
-- Filtros para la tabla `estadistica_solicitudes`
--
ALTER TABLE `estadistica_solicitudes`
  ADD CONSTRAINT `estadistica_solicitudes_ibfk_1` FOREIGN KEY (`reporte_id`) REFERENCES `reporte` (`id_reporte`),
  ADD CONSTRAINT `estadistica_solicitudes_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `estado`
--
ALTER TABLE `estado`
  ADD CONSTRAINT `conexion_pais_estado` FOREIGN KEY (`ubicacionpaisid`) REFERENCES `pais` (`id`);

--
-- Filtros para la tabla `estado_maquina_mantenimiento`
--
ALTER TABLE `estado_maquina_mantenimiento`
  ADD CONSTRAINT `estado_maquina_mantenimiento_ibfk_1` FOREIGN KEY (`id_maquina_unica`) REFERENCES `maquina_unica` (`id_maquina_unica`),
  ADD CONSTRAINT `estado_maquina_mantenimiento_ibfk_2` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`),
  ADD CONSTRAINT `id_status_tarea_maquina` FOREIGN KEY (`id_tarea`) REFERENCES `tareas` (`id_tarea`);

--
-- Filtros para la tabla `filtros_guardados`
--
ALTER TABLE `filtros_guardados`
  ADD CONSTRAINT `usuario_id_filtro` FOREIGN KEY (`usuario_id_filtro`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `herramientas`
--
ALTER TABLE `herramientas`
  ADD CONSTRAINT `herramientas_ibfk_1` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`) ON DELETE SET NULL,
  ADD CONSTRAINT `herramientas_ibfk_2` FOREIGN KEY (`id_marca`) REFERENCES `marca` (`id_marca`) ON DELETE SET NULL,
  ADD CONSTRAINT `herramientas_ibfk_3` FOREIGN KEY (`id_modelo`) REFERENCES `modelo` (`id_modelo`) ON DELETE SET NULL,
  ADD CONSTRAINT `herramientas_ibfk_4` FOREIGN KEY (`id_tipo`) REFERENCES `tipo` (`id_tipo`) ON DELETE SET NULL;

--
-- Filtros para la tabla `herramienta_actividad`
--
ALTER TABLE `herramienta_actividad`
  ADD CONSTRAINT `herramienta_actividad_ibfk_1` FOREIGN KEY (`herramienta_id`) REFERENCES `herramientas` (`id_herramienta`) ON DELETE CASCADE,
  ADD CONSTRAINT `herramienta_actividad_ibfk_2` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id_tarea`) ON DELETE CASCADE,
  ADD CONSTRAINT `herramienta_actividad_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`) ON DELETE SET NULL;

--
-- Filtros para la tabla `herramienta_plan`
--
ALTER TABLE `herramienta_plan`
  ADD CONSTRAINT `herramienta_plan_ibfk_1` FOREIGN KEY (`herramienta_id`) REFERENCES `herramientas` (`id_herramienta`) ON DELETE CASCADE,
  ADD CONSTRAINT `herramienta_plan_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `planes` (`id_plan`) ON DELETE CASCADE,
  ADD CONSTRAINT `herramienta_plan_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`) ON DELETE SET NULL;

--
-- Filtros para la tabla `herramienta_tarea`
--
ALTER TABLE `herramienta_tarea`
  ADD CONSTRAINT `herramienta_tarea_ibfk_1` FOREIGN KEY (`herramienta_id`) REFERENCES `herramientas` (`id_herramienta`) ON DELETE CASCADE,
  ADD CONSTRAINT `herramienta_tarea_ibfk_2` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id_tarea`) ON DELETE CASCADE,
  ADD CONSTRAINT `herramienta_tarea_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`) ON DELETE SET NULL;

--
-- Filtros para la tabla `historial_solicitudes`
--
ALTER TABLE `historial_solicitudes`
  ADD CONSTRAINT `historial_solicitudes_ibfk_1` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes` (`id_solicitud`),
  ADD CONSTRAINT `historial_solicitudes_ibfk_2` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id_perfil`);

--
-- Filtros para la tabla `imagen_blog`
--
ALTER TABLE `imagen_blog`
  ADD CONSTRAINT `imagen_blog_ibfk_1` FOREIGN KEY (`id_blog`) REFERENCES `blog` (`id_blog`);

--
-- Filtros para la tabla `inventario_herramientas`
--
ALTER TABLE `inventario_herramientas`
  ADD CONSTRAINT `fk_id_almacen` FOREIGN KEY (`id_almacen`) REFERENCES `almacen` (`id_almacen`),
  ADD CONSTRAINT `fk_id_sede` FOREIGN KEY (`id_sede`) REFERENCES `sede` (`id_sede`),
  ADD CONSTRAINT `inventario_herramientas_ibfk_1` FOREIGN KEY (`herramienta_id`) REFERENCES `herramientas` (`id_herramienta`) ON DELETE CASCADE,
  ADD CONSTRAINT `inventario_herramientas_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`) ON DELETE SET NULL;

--
-- Filtros para la tabla `inventario_maquina`
--
ALTER TABLE `inventario_maquina`
  ADD CONSTRAINT `fk_inventario_maquina_almacen` FOREIGN KEY (`sede_id`) REFERENCES `sede` (`id_sede`),
  ADD CONSTRAINT `inventario_maquina_ibfk_1` FOREIGN KEY (`id_maquina`) REFERENCES `maquina` (`id_maquina`);

--
-- Filtros para la tabla `inventario_producto`
--
ALTER TABLE `inventario_producto`
  ADD CONSTRAINT `fk_inventario_producto_almacen` FOREIGN KEY (`id_almacen`) REFERENCES `almacen` (`id_almacen`),
  ADD CONSTRAINT `inventario_producto_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`);

--
-- Filtros para la tabla `inventario_repuesto`
--
ALTER TABLE `inventario_repuesto`
  ADD CONSTRAINT `fk_inventario_repuesto_almacen` FOREIGN KEY (`id_almacen`) REFERENCES `almacen` (`id_almacen`),
  ADD CONSTRAINT `inventario_repuesto_ibfk_1` FOREIGN KEY (`id_repuesto`) REFERENCES `repuesto` (`id_repuesto`);

--
-- Filtros para la tabla `maquina`
--
ALTER TABLE `maquina`
  ADD CONSTRAINT `maquina_ibfk_1` FOREIGN KEY (`id_marca`) REFERENCES `marca` (`id_marca`),
  ADD CONSTRAINT `maquina_ibfk_2` FOREIGN KEY (`id_modelo`) REFERENCES `modelo` (`id_modelo`),
  ADD CONSTRAINT `maquina_ibfk_3` FOREIGN KEY (`id_tipo`) REFERENCES `tipo` (`id_tipo`),
  ADD CONSTRAINT `maquina_ibfk_4` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `maquina_repuesto`
--
ALTER TABLE `maquina_repuesto`
  ADD CONSTRAINT `fk_status_maquina_repuesto` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`),
  ADD CONSTRAINT `maquina_repuesto_ibfk_1` FOREIGN KEY (`id_maquina`) REFERENCES `maquina_unica` (`id_maquina_unica`),
  ADD CONSTRAINT `maquina_repuesto_ibfk_2` FOREIGN KEY (`id_repuesto`) REFERENCES `repuesto` (`id_repuesto`);

--
-- Filtros para la tabla `maquina_unica`
--
ALTER TABLE `maquina_unica`
  ADD CONSTRAINT `fk_maquina_maquina` FOREIGN KEY (`id_maquina`) REFERENCES `maquina` (`id_maquina`),
  ADD CONSTRAINT `fk_sede` FOREIGN KEY (`id_sede`) REFERENCES `sede` (`id_sede`),
  ADD CONSTRAINT `fk_status_maquina` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `marca`
--
ALTER TABLE `marca`
  ADD CONSTRAINT `marca_ibfk_1` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `marca_modelo`
--
ALTER TABLE `marca_modelo`
  ADD CONSTRAINT `marca_modelo_ibfk_1` FOREIGN KEY (`id_marca`) REFERENCES `marca` (`id_marca`),
  ADD CONSTRAINT `marca_modelo_ibfk_2` FOREIGN KEY (`id_modelo`) REFERENCES `modelo` (`id_modelo`),
  ADD CONSTRAINT `status_id_status_ibfk_3` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `menus`
--
ALTER TABLE `menus`
  ADD CONSTRAINT `menus_ibfk_1` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `modelo`
--
ALTER TABLE `modelo`
  ADD CONSTRAINT `modelo_ibfk_2` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `movimiento_herramientas`
--
ALTER TABLE `movimiento_herramientas`
  ADD CONSTRAINT `movimiento_herramientas_ibfk_1` FOREIGN KEY (`herramienta_id`) REFERENCES `herramientas` (`id_herramienta`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimiento_herramientas_ibfk_2` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`) ON DELETE SET NULL;

--
-- Filtros para la tabla `movimiento_maquina`
--
ALTER TABLE `movimiento_maquina`
  ADD CONSTRAINT `fk_movimiento_maquina_tipo_movimiento` FOREIGN KEY (`id_tipo_movimiento`) REFERENCES `tipo_movimiento` (`id_tipo_movimiento`),
  ADD CONSTRAINT `movimiento_maquina_ibfk_1` FOREIGN KEY (`id_maquina`) REFERENCES `maquina` (`id_maquina`),
  ADD CONSTRAINT `movimiento_maquina_ibfk_2` FOREIGN KEY (`id_almacen_origen`) REFERENCES `almacen` (`id_almacen`),
  ADD CONSTRAINT `movimiento_maquina_ibfk_3` FOREIGN KEY (`id_almacen_destino`) REFERENCES `almacen` (`id_almacen`);

--
-- Filtros para la tabla `movimiento_producto`
--
ALTER TABLE `movimiento_producto`
  ADD CONSTRAINT `fk_movimiento_producto_tipo_movimiento` FOREIGN KEY (`id_tipo_movimiento`) REFERENCES `tipo_movimiento` (`id_tipo_movimiento`),
  ADD CONSTRAINT `movimiento_producto_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`),
  ADD CONSTRAINT `movimiento_producto_ibfk_2` FOREIGN KEY (`id_almacen_origen`) REFERENCES `almacen` (`id_almacen`),
  ADD CONSTRAINT `movimiento_producto_ibfk_3` FOREIGN KEY (`id_almacen_destino`) REFERENCES `almacen` (`id_almacen`),
  ADD CONSTRAINT `movimiento_producto_solicitud_ibf` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes` (`id_solicitud`);

--
-- Filtros para la tabla `movimiento_repuesto`
--
ALTER TABLE `movimiento_repuesto`
  ADD CONSTRAINT `fk_movimiento_repuesto_tipo_movimiento` FOREIGN KEY (`id_tipo_movimiento`) REFERENCES `tipo_movimiento` (`id_tipo_movimiento`),
  ADD CONSTRAINT `movimiento_repuesto_ibfk_1` FOREIGN KEY (`id_repuesto`) REFERENCES `repuesto` (`id_repuesto`),
  ADD CONSTRAINT `movimiento_repuesto_ibfk_2` FOREIGN KEY (`id_almacen_origen`) REFERENCES `almacen` (`id_almacen`),
  ADD CONSTRAINT `movimiento_repuesto_ibfk_3` FOREIGN KEY (`id_almacen_destino`) REFERENCES `almacen` (`id_almacen`);

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notificaciones_status` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`),
  ADD CONSTRAINT `notificaciones_ibfk_1` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id_perfil`);

--
-- Filtros para la tabla `orden_compra`
--
ALTER TABLE `orden_compra`
  ADD CONSTRAINT `orden_compra_ibfk_1` FOREIGN KEY (`reporte_id`) REFERENCES `reporte` (`id_reporte`),
  ADD CONSTRAINT `orden_compra_ibfk_2` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedor` (`id_proveedor`),
  ADD CONSTRAINT `orden_compra_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `perfiles`
--
ALTER TABLE `perfiles`
  ADD CONSTRAINT `perfiles_ibfk_1` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `perfil_menu`
--
ALTER TABLE `perfil_menu`
  ADD CONSTRAINT `perfil_menu_ibfk_1` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id_perfil`),
  ADD CONSTRAINT `perfil_menu_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menus` (`id_menu`),
  ADD CONSTRAINT `perfil_menu_ibfk_3` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `perfil_permiso_menu`
--
ALTER TABLE `perfil_permiso_menu`
  ADD CONSTRAINT `perfil_permiso_menu_ibfk_1` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id_perfil`),
  ADD CONSTRAINT `perfil_permiso_menu_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menus` (`id_menu`),
  ADD CONSTRAINT `perfil_permiso_menu_ibfk_3` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`),
  ADD CONSTRAINT `perfil_permiso_menu_ibfk_4` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `perfil_permiso_submenu`
--
ALTER TABLE `perfil_permiso_submenu`
  ADD CONSTRAINT `perfil_permiso_submenu_ibfk_1` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id_perfil`),
  ADD CONSTRAINT `perfil_permiso_submenu_ibfk_2` FOREIGN KEY (`id_submenu`) REFERENCES `submenus` (`id_submenu`),
  ADD CONSTRAINT `perfil_permiso_submenu_ibfk_3` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`),
  ADD CONSTRAINT `perfil_permiso_submenu_ibfk_4` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `perfil_submenu`
--
ALTER TABLE `perfil_submenu`
  ADD CONSTRAINT `perfil_submenu_ibfk_1` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id_perfil`),
  ADD CONSTRAINT `perfil_submenu_ibfk_2` FOREIGN KEY (`id_submenu`) REFERENCES `submenus` (`id_submenu`),
  ADD CONSTRAINT `perfil_submenu_ibfk_3` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `perfil_widget`
--
ALTER TABLE `perfil_widget`
  ADD CONSTRAINT `perfil_widget_ibfk_1` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id_perfil`),
  ADD CONSTRAINT `perfil_widget_ibfk_2` FOREIGN KEY (`id_widget`) REFERENCES `widgets` (`id_widget`),
  ADD CONSTRAINT `perfil_widget_ibfk_3` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD CONSTRAINT `permisos_ibfk_1` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `permiso_menu`
--
ALTER TABLE `permiso_menu`
  ADD CONSTRAINT `conexion_permisos_menu` FOREIGN KEY (`status_id_status`) REFERENCES `status` (`id_status`),
  ADD CONSTRAINT `permiso_menu_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `menus` (`id_menu`),
  ADD CONSTRAINT `permiso_menu_ibfk_2` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`);

--
-- Filtros para la tabla `permiso_submenu`
--
ALTER TABLE `permiso_submenu`
  ADD CONSTRAINT `conexion_permisos_submenu` FOREIGN KEY (`status_id_status`) REFERENCES `status` (`id_status`),
  ADD CONSTRAINT `permiso_submenu_ibfk_1` FOREIGN KEY (`id_submenu`) REFERENCES `submenus` (`id_submenu`),
  ADD CONSTRAINT `permiso_submenu_ibfk_2` FOREIGN KEY (`id_permiso`) REFERENCES `permisos` (`id_permiso`);

--
-- Filtros para la tabla `personas`
--
ALTER TABLE `personas`
  ADD CONSTRAINT `fk_estado_id` FOREIGN KEY (`estado_id`) REFERENCES `estado` (`id`),
  ADD CONSTRAINT `fk_pais_id` FOREIGN KEY (`pais_id`) REFERENCES `pais` (`id`),
  ADD CONSTRAINT `personas_ibfk_1` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`),
  ADD CONSTRAINT `personas_ibfk_2` FOREIGN KEY (`id_cargo`) REFERENCES `cargo` (`id_cargo`);

--
-- Filtros para la tabla `pieza_unica`
--
ALTER TABLE `pieza_unica`
  ADD CONSTRAINT `pieza_unica_ibfk_1` FOREIGN KEY (`id_repuesto`) REFERENCES `repuesto` (`id_repuesto`);

--
-- Filtros para la tabla `planes`
--
ALTER TABLE `planes`
  ADD CONSTRAINT `fk_planes_proveedor` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedor` (`id_proveedor`);

--
-- Filtros para la tabla `planta`
--
ALTER TABLE `planta`
  ADD CONSTRAINT `planta_ibfk_1` FOREIGN KEY (`id_sede`) REFERENCES `sede` (`id_sede`) ON DELETE CASCADE,
  ADD CONSTRAINT `planta_ibfk_2` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`) ON DELETE SET NULL;

--
-- Filtros para la tabla `planta_articulo`
--
ALTER TABLE `planta_articulo`
  ADD CONSTRAINT `fk_status3` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`),
  ADD CONSTRAINT `planta_articulo_ibfk_1` FOREIGN KEY (`id_planta`) REFERENCES `planta` (`id_planta`) ON DELETE CASCADE;

--
-- Filtros para la tabla `plan_ejecuciones`
--
ALTER TABLE `plan_ejecuciones`
  ADD CONSTRAINT `plan_ejecuciones_ibfk_1` FOREIGN KEY (`id_plan`) REFERENCES `planes` (`id_plan`),
  ADD CONSTRAINT `plan_ejecuciones_ibfk_2` FOREIGN KEY (`id_tarea`) REFERENCES `tareas` (`id_tarea`);

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`id_marca`) REFERENCES `marca` (`id_marca`),
  ADD CONSTRAINT `producto_ibfk_2` FOREIGN KEY (`id_modelo`) REFERENCES `modelo` (`id_modelo`),
  ADD CONSTRAINT `producto_ibfk_3` FOREIGN KEY (`id_tipo`) REFERENCES `tipo` (`id_tipo`),
  ADD CONSTRAINT `producto_ibfk_4` FOREIGN KEY (`id_clasificacion`) REFERENCES `clasificacion` (`id_clasificacion`),
  ADD CONSTRAINT `producto_ibfk_5` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `producto_actividad`
--
ALTER TABLE `producto_actividad`
  ADD CONSTRAINT `producto_actividad_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `inventario_producto` (`id_producto`),
  ADD CONSTRAINT `producto_actividad_ibfk_2` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id_actividad`),
  ADD CONSTRAINT `producto_actividad_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `producto_plan`
--
ALTER TABLE `producto_plan`
  ADD CONSTRAINT `producto_plan_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `inventario_producto` (`id_producto`),
  ADD CONSTRAINT `producto_plan_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `planes` (`id_plan`),
  ADD CONSTRAINT `producto_plan_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `producto_tarea`
--
ALTER TABLE `producto_tarea`
  ADD CONSTRAINT `producto_tarea_ibfk_1` FOREIGN KEY (`producto_id`) REFERENCES `inventario_producto` (`id_producto`),
  ADD CONSTRAINT `producto_tarea_ibfk_2` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id_tarea`),
  ADD CONSTRAINT `producto_tarea_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `proveedor`
--
ALTER TABLE `proveedor`
  ADD CONSTRAINT `fk_estado` FOREIGN KEY (`id_estado`) REFERENCES `estado` (`id`),
  ADD CONSTRAINT `fk_pais` FOREIGN KEY (`id_pais`) REFERENCES `pais` (`id`),
  ADD CONSTRAINT `status_id_status` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `proveedor_producto`
--
ALTER TABLE `proveedor_producto`
  ADD CONSTRAINT `proveedor_producto_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedor` (`id_proveedor`),
  ADD CONSTRAINT `proveedor_producto_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`);

--
-- Filtros para la tabla `proveedor_repuesto`
--
ALTER TABLE `proveedor_repuesto`
  ADD CONSTRAINT `proveedor_repuesto_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedor` (`id_proveedor`),
  ADD CONSTRAINT `proveedor_repuesto_ibfk_2` FOREIGN KEY (`id_repuesto`) REFERENCES `repuesto` (`id_repuesto`);

--
-- Filtros para la tabla `proveedor_servicio`
--
ALTER TABLE `proveedor_servicio`
  ADD CONSTRAINT `fk_status` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`),
  ADD CONSTRAINT `proveedor_servicio_ibfk_1` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedor` (`id_proveedor`) ON DELETE CASCADE,
  ADD CONSTRAINT `proveedor_servicio_ibfk_2` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`) ON DELETE CASCADE;

--
-- Filtros para la tabla `registro_actividades`
--
ALTER TABLE `registro_actividades`
  ADD CONSTRAINT `conexion_usuario_actividad` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `reporte`
--
ALTER TABLE `reporte`
  ADD CONSTRAINT `reporte_ibfk_1` FOREIGN KEY (`tipo_reporte_id`) REFERENCES `tipo_reporte` (`id_tipo_reporte`),
  ADD CONSTRAINT `reporte_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `reporte_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `repuesto`
--
ALTER TABLE `repuesto`
  ADD CONSTRAINT `fk_repuesto_marca` FOREIGN KEY (`id_marca`) REFERENCES `marca` (`id_marca`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_tipo_repuesto` FOREIGN KEY (`id_tipo`) REFERENCES `tipo` (`id_tipo`),
  ADD CONSTRAINT `repuesto_ibfk_2` FOREIGN KEY (`id_modelo`) REFERENCES `modelo` (`id_modelo`),
  ADD CONSTRAINT `repuesto_ibfk_3` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `repuesto_actividad`
--
ALTER TABLE `repuesto_actividad`
  ADD CONSTRAINT `repuesto_actividad_ibfk_1` FOREIGN KEY (`repuesto_id`) REFERENCES `inventario_repuesto` (`id_repuesto`),
  ADD CONSTRAINT `repuesto_actividad_ibfk_2` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id_actividad`),
  ADD CONSTRAINT `repuesto_actividad_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `repuesto_plan`
--
ALTER TABLE `repuesto_plan`
  ADD CONSTRAINT `fk_plan_repuesto` FOREIGN KEY (`plan_id`) REFERENCES `planes` (`id_plan`),
  ADD CONSTRAINT `fk_proveedor_plan_repuesto` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedor` (`id_proveedor`),
  ADD CONSTRAINT `fk_repuesto_plan` FOREIGN KEY (`repuesto_id`) REFERENCES `repuesto` (`id_repuesto`),
  ADD CONSTRAINT `fk_status_plan_repuesto` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `repuesto_tarea`
--
ALTER TABLE `repuesto_tarea`
  ADD CONSTRAINT `repuesto_tarea_ibfk_1` FOREIGN KEY (`repuesto_id`) REFERENCES `inventario_repuesto` (`id_repuesto`),
  ADD CONSTRAINT `repuesto_tarea_ibfk_2` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id_tarea`),
  ADD CONSTRAINT `repuesto_tarea_ibfk_3` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `responsable`
--
ALTER TABLE `responsable`
  ADD CONSTRAINT `fk_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `responsable_ibfk_1` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id_persona`),
  ADD CONSTRAINT `responsable_ibfk_2` FOREIGN KEY (`tarea_id`) REFERENCES `tareas` (`id_tarea`),
  ADD CONSTRAINT `responsable_ibfk_3` FOREIGN KEY (`actividad_id`) REFERENCES `actividades` (`id_actividad`);

--
-- Filtros para la tabla `sede`
--
ALTER TABLE `sede`
  ADD CONSTRAINT `sede_ibfk_1` FOREIGN KEY (`id_empresa`) REFERENCES `empresa` (`id_empresa`) ON DELETE CASCADE,
  ADD CONSTRAINT `sede_ibfk_2` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`) ON DELETE SET NULL,
  ADD CONSTRAINT `sede_ibfk_3` FOREIGN KEY (`id_sucursal_fija`) REFERENCES `sucursal` (`id_sucursal`);

--
-- Filtros para la tabla `sede_sucursal`
--
ALTER TABLE `sede_sucursal`
  ADD CONSTRAINT `fk_status2` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`),
  ADD CONSTRAINT `sede_sucursal_ibfk_1` FOREIGN KEY (`id_sede`) REFERENCES `sede` (`id_sede`),
  ADD CONSTRAINT `sede_sucursal_ibfk_2` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursal` (`id_sucursal`);

--
-- Filtros para la tabla `servicio_herramienta`
--
ALTER TABLE `servicio_herramienta`
  ADD CONSTRAINT `servicio_herramienta_ibfk_1` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`),
  ADD CONSTRAINT `servicio_herramienta_ibfk_2` FOREIGN KEY (`id_herramienta`) REFERENCES `herramientas` (`id_herramienta`);

--
-- Filtros para la tabla `servicio_maquina`
--
ALTER TABLE `servicio_maquina`
  ADD CONSTRAINT `servicio_maquina_ibfk_1` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`),
  ADD CONSTRAINT `servicio_maquina_ibfk_2` FOREIGN KEY (`id_maquina`) REFERENCES `maquina` (`id_maquina`);

--
-- Filtros para la tabla `servicio_piezas`
--
ALTER TABLE `servicio_piezas`
  ADD CONSTRAINT `servicio_piezas_ibfk_1` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`),
  ADD CONSTRAINT `servicio_piezas_ibfk_2` FOREIGN KEY (`id_repuesto`) REFERENCES `repuesto` (`id_repuesto`);

--
-- Filtros para la tabla `servicio_producto`
--
ALTER TABLE `servicio_producto`
  ADD CONSTRAINT `servicio_producto_ibfk_1` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`),
  ADD CONSTRAINT `servicio_producto_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`);

--
-- Filtros para la tabla `servicio_repuesto`
--
ALTER TABLE `servicio_repuesto`
  ADD CONSTRAINT `servicio_repuesto_ibfk_1` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`),
  ADD CONSTRAINT `servicio_repuesto_ibfk_2` FOREIGN KEY (`id_repuesto`) REFERENCES `repuesto` (`id_repuesto`);

--
-- Filtros para la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD CONSTRAINT `fk_perfil` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id_perfil`),
  ADD CONSTRAINT `solicitudes_ibfk_1` FOREIGN KEY (`id_tipo_solicitud`) REFERENCES `tipos_solicitudes` (`id_tipo_solicitud`),
  ADD CONSTRAINT `solicitudes_ibfk_2` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`),
  ADD CONSTRAINT `solicitudes_ibfk_3` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `solicitudes_tareas`
--
ALTER TABLE `solicitudes_tareas`
  ADD CONSTRAINT `solicitudes_tareas_ibfk_1` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes` (`id_solicitud`),
  ADD CONSTRAINT `solicitudes_tareas_ibfk_2` FOREIGN KEY (`id_tarea`) REFERENCES `tareas` (`id_tarea`);

--
-- Filtros para la tabla `submenus`
--
ALTER TABLE `submenus`
  ADD CONSTRAINT `submenus_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `menus` (`id_menu`),
  ADD CONSTRAINT `submenus_ibfk_2` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `sucursal`
--
ALTER TABLE `sucursal`
  ADD CONSTRAINT `conexion_estado_sucursal` FOREIGN KEY (`estado_id_estado`) REFERENCES `estado` (`id`),
  ADD CONSTRAINT `conexion_pais_sucursal` FOREIGN KEY (`pais_id_pais`) REFERENCES `pais` (`id`),
  ADD CONSTRAINT `sucursal_ibfk_2` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`) ON DELETE SET NULL;

--
-- Filtros para la tabla `tareas`
--
ALTER TABLE `tareas`
  ADD CONSTRAINT `fk_importancia` FOREIGN KEY (`id_importancia`) REFERENCES `prioridad` (`id_importancia`),
  ADD CONSTRAINT `fk_maquina_unica` FOREIGN KEY (`id_maquina_unica`) REFERENCES `maquina_unica` (`id_maquina_unica`),
  ADD CONSTRAINT `fk_proveedor_tarea` FOREIGN KEY (`proveedor_id`) REFERENCES `proveedor` (`id_proveedor`),
  ADD CONSTRAINT `fk_sede2` FOREIGN KEY (`id_sede`) REFERENCES `sede` (`id_sede`),
  ADD CONSTRAINT `fk_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `servicio` (`id_servicio`),
  ADD CONSTRAINT `fk_status_tarea` FOREIGN KEY (`status_id`) REFERENCES `status` (`id_status`),
  ADD CONSTRAINT `fk_tarea_plan` FOREIGN KEY (`id_plan`) REFERENCES `planes` (`id_plan`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_tareas_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitudes` (`id_solicitud`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `tareas_ibfk_1` FOREIGN KEY (`tipo_mantenimiento_id`) REFERENCES `tipo_mantenimiento` (`id_tipo`);

--
-- Filtros para la tabla `tipo`
--
ALTER TABLE `tipo`
  ADD CONSTRAINT `tipo_ibfk_1` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `tipo_clasificacion`
--
ALTER TABLE `tipo_clasificacion`
  ADD CONSTRAINT `fk_id_status` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`),
  ADD CONSTRAINT `tipo_clasificacion_ibfk_1` FOREIGN KEY (`id_tipo`) REFERENCES `tipo` (`id_tipo`),
  ADD CONSTRAINT `tipo_clasificacion_ibfk_2` FOREIGN KEY (`id_clasificacion`) REFERENCES `clasificacion` (`id_clasificacion`);

--
-- Filtros para la tabla `tipo_widgets`
--
ALTER TABLE `tipo_widgets`
  ADD CONSTRAINT `tipo_widgets_ibfk_1` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_status` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_persona`) REFERENCES `personas` (`id_persona`),
  ADD CONSTRAINT `usuarios_ibfk_2` FOREIGN KEY (`id_perfil`) REFERENCES `perfiles` (`id_perfil`);

--
-- Filtros para la tabla `widgets`
--
ALTER TABLE `widgets`
  ADD CONSTRAINT `widgets_ibfk_1` FOREIGN KEY (`tipo_widget`) REFERENCES `tipo_widgets` (`id_tipo`),
  ADD CONSTRAINT `widgets_ibfk_2` FOREIGN KEY (`id_status`) REFERENCES `status` (`id_status`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
