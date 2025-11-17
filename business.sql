-- phpMyAdmin SQL Dump
-- version 5.2.1deb3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 17-11-2025 a las 10:05:50
-- Versión del servidor: 8.4.3
-- Versión de PHP: 8.3.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `business`
--
CREATE DATABASE IF NOT EXISTS `business` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `business`;

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `clean_abandoned_carts` ()   BEGIN
    DELETE FROM carts 
    WHERE status = 'abandoned' 
    AND modification_date < DATE_SUB(NOW(), INTERVAL 30 DAY)$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `clean_expired_sessions` ()   BEGIN
    DELETE FROM sessions 
    WHERE expires_at < NOW() 
    OR revoked = 1$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `addresses`
--

CREATE TABLE `addresses` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `type` enum('shipping','billing','both') COLLATE utf8mb4_unicode_ci DEFAULT 'shipping',
  `is_default` tinyint(1) DEFAULT '0',
  `alias` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Casa, Oficina, etc.',
  `full_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line1` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address_line2` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `state` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'España',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `audit_log`
--

CREATE TABLE `audit_log` (
  `id` bigint UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'login, logout, register, update_profile, etc.',
  `entity_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'user, product, order, etc.',
  `entity_id` int UNSIGNED DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `audit_log`
--

INSERT INTO `audit_log` (`id`, `user_id`, `action`, `entity_type`, `entity_id`, `description`, `old_values`, `new_values`, `ip_address`, `user_agent`, `creation_date`) VALUES
(1, NULL, 'update_user', 'user', 6, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César Osvaldo\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 0}', '{\"name\": \"César Osvaldo\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 0}', '0.0.0.0', NULL, '2025-11-14 00:11:48'),
(2, NULL, 'register', 'user', 6, 'Usuario registrado: cesarmatelat@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 00:11:52'),
(3, NULL, 'update_user', 'user', 6, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César Osvaldo\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 0}', '{\"name\": \"César Osvaldo\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 00:15:03'),
(4, NULL, 'update_user', 'user', 6, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César Osvaldo\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '{\"name\": \"César Osvaldo\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 00:15:03'),
(5, NULL, 'email_verified', 'user', 6, 'Email verificado: cesarmatelat@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 00:15:03'),
(6, 1, 'register', 'user', 1, 'Usuario registrado: cesarmatelat@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 00:16:45'),
(7, 1, 'update_user', 'user', 1, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 0}', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 00:17:20'),
(8, 1, 'update_user', 'user', 1, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 00:17:20'),
(9, 1, 'email_verified', 'user', 1, 'Email verificado: cesarmatelat@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 00:17:20'),
(10, 1, 'update_user', 'user', 1, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 10:34:48'),
(11, 1, 'update_user', 'user', 1, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 10:34:57'),
(12, 1, 'update_user', 'user', 1, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 10:37:30'),
(13, 1, 'update_user', 'user', 1, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 10:40:07'),
(14, 1, 'login', 'user', 1, 'Inicio de sesión exitoso', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 10:40:07'),
(15, 1, 'update_user', 'user', 1, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 10:43:26'),
(16, 1, 'login', 'user', 1, 'Inicio de sesión exitoso', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 10:43:27'),
(17, 1, 'update_user', 'user', 1, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 10:44:10'),
(18, 1, 'login', 'user', 1, 'Inicio de sesión exitoso', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 10:44:10'),
(19, 1, 'update_user', 'user', 1, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 10:47:21'),
(20, 1, 'login', 'user', 1, 'Inicio de sesión exitoso', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 10:47:21'),
(21, 1, 'update_user', 'user', 1, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 10:49:33'),
(22, 1, 'login', 'user', 1, 'Inicio de sesión exitoso', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 10:49:33'),
(23, 1, 'logout', 'user', 1, 'Cierre de sesión', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 10:49:44'),
(24, 1, 'update_user', 'user', 1, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 11:15:23'),
(25, 1, 'update_user', 'user', 1, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 11:22:27'),
(26, 1, 'login', 'user', 1, 'Inicio de sesión exitoso', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 11:22:27'),
(27, 1, 'update_user', 'user', 1, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 11:23:08'),
(28, 1, 'update_user', 'user', 1, 'Usuario actualizado: cesarmatelat@gmail.com', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"cesarmatelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 19:37:49'),
(29, 1, 'login', 'user', 1, 'Inicio de sesión exitoso', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 19:37:49'),
(30, 1, 'logout', 'user', 1, 'Cierre de sesión', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 19:38:11'),
(31, 2, 'register', 'user', 2, 'Usuario registrado: orions68@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 19:46:22'),
(32, 2, 'update_user', 'user', 2, 'Usuario actualizado: orions68@gmail.com', '{\"name\": \"Pepe\", \"email\": \"orions68@gmail.com\", \"active\": 0}', '{\"name\": \"Pepe\", \"email\": \"orions68@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 19:47:26'),
(33, 2, 'email_verified', 'user', 2, 'Email verificado: orions68@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 19:47:26'),
(34, 2, 'update_user', 'user', 2, 'Usuario actualizado: orions68@gmail.com', '{\"name\": \"Pepe\", \"email\": \"orions68@gmail.com\", \"active\": 1}', '{\"name\": \"Pepe\", \"email\": \"orions68@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 19:47:39'),
(35, 2, 'login', 'user', 2, 'Inicio de sesión exitoso', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 19:47:39'),
(36, 2, 'create_product', 'product', 1, 'Producto creado: Guitarra Eléctrica (SKU: PROD-891A654C)', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 19:52:50'),
(37, 2, 'logout', 'user', 2, 'Cierre de sesión', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 19:52:59'),
(38, 2, 'update_user', 'user', 2, 'Usuario actualizado: orions68@gmail.com', '{\"name\": \"Pepe\", \"email\": \"orions68@gmail.com\", \"active\": 1}', '{\"name\": \"Pepe\", \"email\": \"orions68@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 20:01:35'),
(39, 2, 'login', 'user', 2, 'Inicio de sesión exitoso', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 20:01:35'),
(40, 2, 'logout', 'user', 2, 'Cierre de sesión', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 20:01:43'),
(41, NULL, 'register', 'user', 3, 'Usuario registrado: matesar@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 20:03:24'),
(42, NULL, 'update_user', 'user', 3, 'Usuario actualizado: matesar68@gmail.com', '{\"name\": \"Otro\", \"email\": \"matesar@gmail.com\", \"active\": 0}', '{\"name\": \"Otro\", \"email\": \"matesar68@gmail.com\", \"active\": 0}', '0.0.0.0', NULL, '2025-11-14 20:06:48'),
(43, NULL, 'update_user', 'user', 3, 'Usuario actualizado: matesar68@gmail.com', '{\"name\": \"Otro\", \"email\": \"matesar68@gmail.com\", \"active\": 0}', '{\"name\": \"Otro\", \"email\": \"matesar68@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 20:09:19'),
(44, NULL, 'email_verified', 'user', 3, 'Email verificado: matesar68@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 20:09:19'),
(45, NULL, 'update_user', 'user', 3, 'Usuario actualizado: matesar68@gmail.com', '{\"name\": \"Otro\", \"email\": \"matesar68@gmail.com\", \"active\": 1}', '{\"name\": \"Otro\", \"email\": \"matesar68@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-14 20:10:00'),
(46, NULL, 'login', 'user', 3, 'Inicio de sesión exitoso', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-14 20:10:00'),
(47, NULL, 'register', 'user', 4, 'Usuario registrado: matelat@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-16 01:21:48'),
(48, NULL, 'update_user', 'user', 4, 'Usuario actualizado: matelat@gmail.com', '{\"name\": \"César\", \"email\": \"matelat@gmail.com\", \"active\": 0}', '{\"name\": \"César\", \"email\": \"matelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-16 01:22:56'),
(49, NULL, 'email_verified', 'user', 4, 'Email verificado: matelat@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-16 01:22:56'),
(50, NULL, 'update_user', 'user', 4, 'Usuario actualizado: matelat@gmail.com', '{\"name\": \"César\", \"email\": \"matelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"matelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-16 01:23:25'),
(51, NULL, 'login', 'user', 4, 'Inicio de sesión exitoso', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-16 01:23:25'),
(52, NULL, 'logout', 'user', 4, 'Cierre de sesión', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-16 01:24:00'),
(53, 3, 'update_user', 'user', 3, 'Usuario actualizado: matelat@gmail.com', '{\"name\": \"César\", \"email\": \"matelat@gmail.com\", \"active\": 0}', '{\"name\": \"César\", \"email\": \"matelat@gmail.com\", \"active\": 0}', '0.0.0.0', NULL, '2025-11-16 01:31:49'),
(54, 3, 'register', 'user', 3, 'Usuario registrado: matelat@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-16 01:31:53'),
(55, 3, 'update_user', 'user', 3, 'Usuario actualizado: matelat@gmail.com', '{\"name\": \"César\", \"email\": \"matelat@gmail.com\", \"active\": 0}', '{\"name\": \"César\", \"email\": \"matelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-16 01:32:34'),
(56, 3, 'email_verified', 'user', 3, 'Email verificado: matelat@gmail.com', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-16 01:32:34'),
(57, 3, 'update_user', 'user', 3, 'Usuario actualizado: matelat@gmail.com', '{\"name\": \"César\", \"email\": \"matelat@gmail.com\", \"active\": 1}', '{\"name\": \"César\", \"email\": \"matelat@gmail.com\", \"active\": 1}', '0.0.0.0', NULL, '2025-11-16 01:32:42'),
(58, 3, 'login', 'user', 3, 'Inicio de sesión exitoso', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-16 01:32:42'),
(59, 3, 'logout', 'user', 3, 'Cierre de sesión', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-16 01:32:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carts`
--

CREATE TABLE `carts` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Para usuarios no autenticados',
  `status` enum('active','abandoned','converted') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cart_expiration_emails`
--

CREATE TABLE `cart_expiration_emails` (
  `id` int UNSIGNED NOT NULL,
  `cart_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `email_type` enum('warning_7days','final_3days') COLLATE utf8mb4_unicode_ci NOT NULL,
  `sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cart_items`
--

CREATE TABLE `cart_items` (
  `id` int UNSIGNED NOT NULL,
  `cart_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `quantity` int NOT NULL DEFAULT '1',
  `price` decimal(10,2) NOT NULL COMMENT 'Precio al momento de agregar',
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `id` int UNSIGNED NOT NULL,
  `parent_id` int UNSIGNED DEFAULT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `active` tinyint(1) DEFAULT '1',
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categories`
--

INSERT INTO `categories` (`id`, `parent_id`, `name`, `slug`, `description`, `image_path`, `sort_order`, `active`, `creation_date`, `modification_date`) VALUES
(1, NULL, 'Electrónica', 'electronica', 'Productos electrónicos y tecnología', NULL, 0, 1, '2025-11-13 19:44:59', '2025-11-13 19:44:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orders`
--

CREATE TABLE `orders` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `seller_id` int UNSIGNED DEFAULT NULL,
  `order_group_id` int UNSIGNED DEFAULT NULL,
  `order_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `shipping_address_id` int UNSIGNED DEFAULT NULL,
  `billing_address_id` int UNSIGNED DEFAULT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax` decimal(10,2) DEFAULT '0.00',
  `shipping_cost` decimal(10,2) DEFAULT '0.00',
  `discount` decimal(10,2) DEFAULT '0.00',
  `platform_commission_rate` decimal(5,2) DEFAULT '0.00',
  `platform_commission_amount` decimal(10,2) DEFAULT '0.00',
  `seller_amount` decimal(10,2) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` enum('pending','processing','shipped','delivered','cancelled','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_status` enum('pending','paid','failed','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seller_payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_notes` text COLLATE utf8mb4_unicode_ci,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `paid_at` timestamp NULL DEFAULT NULL,
  `shipped_at` timestamp NULL DEFAULT NULL,
  `delivered_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_groups`
--

CREATE TABLE `order_groups` (
  `id` int UNSIGNED NOT NULL,
  `buyer_id` int UNSIGNED NOT NULL,
  `group_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','partial','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `order_items`
--

CREATE TABLE `order_items` (
  `id` int UNSIGNED NOT NULL,
  `order_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `product_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_sku` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `payment_transactions`
--

CREATE TABLE `payment_transactions` (
  `id` int UNSIGNED NOT NULL,
  `order_id` int UNSIGNED NOT NULL,
  `order_group_id` int UNSIGNED DEFAULT NULL,
  `seller_id` int UNSIGNED NOT NULL,
  `buyer_id` int UNSIGNED NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci DEFAULT 'EUR',
  `status` enum('pending','processing','completed','failed','refunded') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `transaction_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gateway_response` json DEFAULT NULL,
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `products`
--

CREATE TABLE `products` (
  `id` int UNSIGNED NOT NULL,
  `category_id` int UNSIGNED DEFAULT NULL,
  `seller_id` int UNSIGNED DEFAULT NULL,
  `name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sku` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `short_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `compare_price` decimal(10,2) DEFAULT NULL COMMENT 'Precio antes de descuento',
  `cost_price` decimal(10,2) DEFAULT NULL COMMENT 'Costo de adquisición',
  `stock` int DEFAULT '0',
  `reserved_stock` int DEFAULT '0',
  `low_stock_threshold` int DEFAULT '5',
  `low_stock_alert_sent` tinyint(1) DEFAULT '0',
  `last_stock_alert_date` timestamp NULL DEFAULT NULL,
  `weight` decimal(10,3) DEFAULT NULL COMMENT 'Peso en kg',
  `length` decimal(10,2) DEFAULT NULL COMMENT 'Largo en cm',
  `width` decimal(10,2) DEFAULT NULL COMMENT 'Ancho en cm',
  `height` decimal(10,2) DEFAULT NULL COMMENT 'Alto en cm',
  `meta_title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_keywords` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `active` tinyint(1) DEFAULT '1',
  `featured` tinyint(1) DEFAULT '0',
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `products`
--

INSERT INTO `products` (`id`, `category_id`, `seller_id`, `name`, `slug`, `sku`, `description`, `short_description`, `price`, `compare_price`, `cost_price`, `stock`, `reserved_stock`, `low_stock_threshold`, `low_stock_alert_sent`, `last_stock_alert_date`, `weight`, `length`, `width`, `height`, `meta_title`, `meta_description`, `meta_keywords`, `active`, `featured`, `creation_date`, `modification_date`) VALUES
(1, NULL, 2, 'Guitarra Eléctrica', 'guitarra-el-ctrica', 'PROD-891A654C', 'Guitarra Eléctrica Fender Estratocaster Modelo 2026 ¡Exclusiva!', 'EG', 1500.00, NULL, NULL, 3, 0, 5, 1, '2025-11-16 00:48:47', NULL, NULL, NULL, NULL, 'Guitarra Eléctrica', 'EG', NULL, 1, 0, '2025-11-14 19:52:49', '2025-11-16 00:48:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `product_images`
--

CREATE TABLE `product_images` (
  `id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `path` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `width` int DEFAULT NULL,
  `height` int DEFAULT NULL,
  `file_size` int DEFAULT NULL COMMENT 'Tamaño en bytes',
  `alt_text` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int DEFAULT '0',
  `is_primary` tinyint(1) DEFAULT '0',
  `ai_score` decimal(5,2) DEFAULT NULL COMMENT 'Puntuación de calidad de la IA (0-100)',
  `ai_analysis` json DEFAULT NULL COMMENT 'Análisis detallado de la IA',
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `path`, `width`, `height`, `file_size`, `alt_text`, `sort_order`, `is_primary`, `ai_score`, `ai_analysis`, `creation_date`) VALUES
(1, 1, 'assets/products/1/product_0_1763149969.webp', 1200, 1200, 75896, 'Guitarra Eléctrica', 1, 1, NULL, NULL, '2025-11-14 19:52:50'),
(2, 1, 'assets/products/1/product_1_1763149969.webp', 600, 600, 20708, 'Guitarra Eléctrica', 2, 0, NULL, NULL, '2025-11-14 19:52:50'),
(3, 1, 'assets/products/1/product_2_1763149969.webp', 2000, 2000, 189052, 'Guitarra Eléctrica', 3, 0, NULL, NULL, '2025-11-14 19:52:50'),
(4, 1, 'assets/products/1/product_3_1763149970.webp', 600, 600, 33866, 'Guitarra Eléctrica', 4, 0, NULL, NULL, '2025-11-14 19:52:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reviews`
--

CREATE TABLE `reviews` (
  `id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `rating` tinyint NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `content` text COLLATE utf8mb4_unicode_ci,
  `approved` tinyint(1) DEFAULT '0',
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sales`
--

CREATE TABLE `sales` (
  `id` int UNSIGNED NOT NULL,
  `order_id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED DEFAULT NULL,
  `seller_id` int UNSIGNED DEFAULT NULL,
  `quantity` int NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `commission_rate` decimal(5,2) DEFAULT '0.00',
  `commission_amount` decimal(10,2) DEFAULT '0.00',
  `sale_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seller_payment_methods`
--

CREATE TABLE `seller_payment_methods` (
  `id` int UNSIGNED NOT NULL,
  `seller_id` int UNSIGNED NOT NULL,
  `payment_method` enum('stripe','paypal','mercadopago','transferencia','efectivo') COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `config` json DEFAULT NULL,
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `token_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SHA-256 del JWT token',
  `refresh_token_hash` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Para renovar tokens',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'mobile, desktop, tablet',
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `revoked` tinyint(1) DEFAULT '0',
  `revoked_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `settings`
--

CREATE TABLE `settings` (
  `id` int UNSIGNED NOT NULL,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text COLLATE utf8mb4_unicode_ci,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `type` enum('string','number','boolean','json') COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `description`, `type`, `creation_date`, `modification_date`) VALUES
(1, 'site_name', 'Business E-commerce', 'Nombre del sitio', 'string', '2025-11-13 19:44:59', '2025-11-13 19:44:59'),
(2, 'site_email', 'admin@business.local', 'Email principal', 'string', '2025-11-13 19:44:59', '2025-11-13 19:44:59'),
(3, 'jwt_secret', '', 'Secret key para JWT (generar con openssl)', 'string', '2025-11-13 19:44:59', '2025-11-13 19:44:59'),
(4, 'jwt_expiration', '3600', 'Tiempo de expiración del JWT en segundos', 'number', '2025-11-13 19:44:59', '2025-11-13 19:44:59'),
(5, 'max_login_attempts', '5', 'Máximo de intentos de login', 'number', '2025-11-13 19:44:59', '2025-11-13 19:44:59'),
(6, 'lockout_duration', '900', 'Duración del bloqueo en segundos', 'number', '2025-11-13 19:44:59', '2025-11-13 19:44:59'),
(7, 'password_min_length', '8', 'Longitud mínima de contraseña', 'number', '2025-11-13 19:44:59', '2025-11-13 19:44:59'),
(8, 'require_email_verification', '1', 'Requiere verificación de email', 'boolean', '2025-11-13 19:44:59', '2025-11-13 19:44:59'),
(9, 'tax_rate', '0.21', 'Tasa de impuesto (IVA)', 'number', '2025-11-13 19:44:59', '2025-11-13 19:44:59'),
(10, 'currency', 'EUR', 'Moneda del sistema', 'string', '2025-11-13 19:44:59', '2025-11-13 19:44:59'),
(11, 'currency_symbol', '€', 'Símbolo de moneda', 'string', '2025-11-13 19:44:59', '2025-11-13 19:44:59');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `stock_alert_log`
--

CREATE TABLE `stock_alert_log` (
  `id` int UNSIGNED NOT NULL,
  `product_id` int UNSIGNED NOT NULL,
  `seller_id` int UNSIGNED NOT NULL,
  `stock_level` int NOT NULL,
  `alert_sent_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `stock_alert_log`
--

INSERT INTO `stock_alert_log` (`id`, `product_id`, `seller_id`, `stock_level`, `alert_sent_at`) VALUES
(1, 1, 2, 3, '2025-11-16 00:43:28'),
(2, 1, 2, 3, '2025-11-16 00:48:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `surname1` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `surname2` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Hash con Argon2id (compatible con ASP.NET)',
  `email_hash` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'SHA-256 hash del email para verificación',
  `path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ruta a imagen de perfil',
  `active` tinyint(1) DEFAULT '1' COMMENT '1=activo, 0=inactivo',
  `email_verified` tinyint(1) DEFAULT '0' COMMENT '1=verificado, 0=no verificado',
  `role` enum('seller_basic','seller_premium','buyer_basic','buyer_premium','admin','manager') COLLATE utf8mb4_unicode_ci DEFAULT 'buyer_basic' COMMENT 'Roles: seller/buyer (basic/premium), admin, manager',
  `verification_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `gender` enum('male','female','other','prefer_not_to_say') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `creation_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `modification_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  `last_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Soporta IPv4 e IPv6',
  `login_attempts` int DEFAULT '0' COMMENT 'Contador de intentos fallidos',
  `locked_until` timestamp NULL DEFAULT NULL COMMENT 'Bloqueo temporal por intentos fallidos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `name`, `surname1`, `surname2`, `phone`, `email`, `password`, `email_hash`, `path`, `active`, `email_verified`, `role`, `verification_token`, `reset_token`, `reset_token_expires`, `birth_date`, `gender`, `creation_date`, `modification_date`, `last_login`, `last_ip`, `login_attempts`, `locked_until`) VALUES
(1, 'César', 'Matelat', '', '+34664774821', 'cesarmatelat@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$SER2N3ZZVTdKVDh3Yk9BTA$lTL3L2xu6yC27QcYCWm/OHOk9ObONo0LXdyUscLrJbs', '4bd718ab038e45aba3efb05daa396d307c475d43dc1fb8d446bb300bbb96df86', 'assets/media/profile.jpg', 1, 1, 'admin', NULL, NULL, NULL, NULL, NULL, '2025-11-14 00:16:43', '2025-11-14 19:37:49', '2025-11-14 19:37:49', '127.0.0.1', 0, NULL),
(2, 'Pepe', 'Ventas', '', '', 'orions68@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$Q3dVaFJoUWRaemFwUDdTUA$3yNLTPlmURoukSZPYTw06bTEi8sTv007OcqZObz9r1k', 'c250c6b951fc9d79fcd778a1a4280bbb2fa158a69ff3dbdf47e5cdb639ebf1ff', 'assets/media/profile.jpg', 1, 1, 'seller_basic', NULL, NULL, NULL, NULL, NULL, '2025-11-14 19:46:20', '2025-11-14 20:01:35', '2025-11-14 20:01:35', '127.0.0.1', 0, NULL),
(3, 'César', 'Matelat', 'Borneo', '611111111', 'matelat@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$eHpKYy44Q3hscFJHMVNtNQ$31YB+WzGjp7qx03HYdhHffQM4QL/G2ilC2LaGdOtI8E', '7672b52d5437e8f6716fdb493d4227c96b7125fb31f175a4a0377cb1a61c134d', 'assets/profiles/3/profile.png', 1, 1, 'seller_basic', NULL, NULL, NULL, NULL, NULL, '2025-11-16 01:31:49', '2025-11-16 01:32:42', '2025-11-16 01:32:42', '127.0.0.1', 0, NULL);

--
-- Disparadores `users`
--
DELIMITER $$
CREATE TRIGGER `users_after_update` AFTER UPDATE ON `users` FOR EACH ROW BEGIN
    INSERT INTO audit_log (user_id, action, entity_type, entity_id, description, old_values, new_values, ip_address)
    VALUES (
        NEW.id,
        'update_user',
        'user',
        NEW.id,
        CONCAT('Usuario actualizado: ', NEW.email),
        JSON_OBJECT('name', OLD.name, 'email', OLD.email, 'active', OLD.active),
        JSON_OBJECT('name', NEW.name, 'email', NEW.email, 'active', NEW.active),
        '0.0.0.0'
    )$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `user_summary`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `user_summary` (
`active` tinyint(1)
,`creation_date` timestamp
,`email` varchar(255)
,`email_verified` tinyint(1)
,`id` int unsigned
,`last_login` timestamp
,`name` varchar(100)
,`phone` varchar(20)
,`role` enum('seller_basic','seller_premium','buyer_basic','buyer_premium','admin','manager')
,`surname1` varchar(100)
,`surname2` varchar(100)
,`total_orders` bigint
,`total_spent` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `user_summary`
--
DROP TABLE IF EXISTS `user_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `user_summary`  AS SELECT `u`.`id` AS `id`, `u`.`name` AS `name`, `u`.`surname1` AS `surname1`, `u`.`surname2` AS `surname2`, `u`.`email` AS `email`, `u`.`phone` AS `phone`, `u`.`role` AS `role`, `u`.`active` AS `active`, `u`.`email_verified` AS `email_verified`, `u`.`creation_date` AS `creation_date`, `u`.`last_login` AS `last_login`, count(distinct `o`.`id`) AS `total_orders`, coalesce(sum(`o`.`total`),0) AS `total_spent` FROM (`users` `u` left join `orders` `o` on((`u`.`id` = `o`.`user_id`))) GROUP BY `u`.`id` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `addresses`
--
ALTER TABLE `addresses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_is_default` (`is_default`);

--
-- Indices de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_action` (`action`),
  ADD KEY `idx_entity` (`entity_type`,`entity_id`),
  ADD KEY `idx_creation_date` (`creation_date`);

--
-- Indices de la tabla `carts`
--
ALTER TABLE `carts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_session_id` (`session_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_expires` (`expires_at`,`status`);

--
-- Indices de la tabla `cart_expiration_emails`
--
ALTER TABLE `cart_expiration_emails`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cart` (`cart_id`),
  ADD KEY `idx_user_type` (`user_id`,`email_type`);

--
-- Indices de la tabla `cart_items`
--
ALTER TABLE `cart_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_cart_id` (`cart_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_parent_id` (`parent_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_active` (`active`);

--
-- Indices de la tabla `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_number` (`order_number`),
  ADD KEY `shipping_address_id` (`shipping_address_id`),
  ADD KEY `billing_address_id` (`billing_address_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_order_number` (`order_number`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_creation_date` (`creation_date`),
  ADD KEY `idx_seller_status` (`seller_id`,`status`),
  ADD KEY `idx_order_group` (`order_group_id`);

--
-- Indices de la tabla `order_groups`
--
ALTER TABLE `order_groups`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `group_number` (`group_number`),
  ADD KEY `idx_buyer_date` (`buyer_id`,`creation_date`),
  ADD KEY `idx_status` (`status`);

--
-- Indices de la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`);

--
-- Indices de la tabla `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order` (`order_id`),
  ADD KEY `idx_group` (`order_group_id`),
  ADD KEY `idx_seller` (`seller_id`),
  ADD KEY `idx_buyer_date` (`buyer_id`,`creation_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_transaction` (`transaction_id`);

--
-- Indices de la tabla `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `idx_category_id` (`category_id`),
  ADD KEY `idx_slug` (`slug`),
  ADD KEY `idx_sku` (`sku`),
  ADD KEY `idx_active` (`active`),
  ADD KEY `idx_featured` (`featured`),
  ADD KEY `idx_seller_id` (`seller_id`);

--
-- Indices de la tabla `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_is_primary` (`is_primary`);

--
-- Indices de la tabla `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_product` (`user_id`,`product_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_approved` (`approved`),
  ADD KEY `idx_rating` (`rating`);

--
-- Indices de la tabla `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_product_id` (`product_id`),
  ADD KEY `idx_seller_id` (`seller_id`),
  ADD KEY `idx_sale_date` (`sale_date`);

--
-- Indices de la tabla `seller_payment_methods`
--
ALTER TABLE `seller_payment_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_seller_method` (`seller_id`,`payment_method`),
  ADD KEY `idx_seller_active` (`seller_id`,`is_active`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_token_hash` (`token_hash`),
  ADD KEY `idx_expires_at` (`expires_at`),
  ADD KEY `idx_revoked` (`revoked`);

--
-- Indices de la tabla `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`),
  ADD KEY `idx_key` (`key`);

--
-- Indices de la tabla `stock_alert_log`
--
ALTER TABLE `stock_alert_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product` (`product_id`),
  ADD KEY `idx_seller_date` (`seller_id`,`alert_sent_at`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_active` (`active`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `idx_creation_date` (`creation_date`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `addresses`
--
ALTER TABLE `addresses`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT de la tabla `carts`
--
ALTER TABLE `carts`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cart_expiration_emails`
--
ALTER TABLE `cart_expiration_emails`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cart_items`
--
ALTER TABLE `cart_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `order_groups`
--
ALTER TABLE `order_groups`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `payment_transactions`
--
ALTER TABLE `payment_transactions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `products`
--
ALTER TABLE `products`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `seller_payment_methods`
--
ALTER TABLE `seller_payment_methods`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sessions`
--
ALTER TABLE `sessions`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `stock_alert_log`
--
ALTER TABLE `stock_alert_log`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `addresses`
--
ALTER TABLE `addresses`
  ADD CONSTRAINT `addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `carts`
--
ALTER TABLE `carts`
  ADD CONSTRAINT `carts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cart_expiration_emails`
--
ALTER TABLE `cart_expiration_emails`
  ADD CONSTRAINT `cart_expiration_emails_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_expiration_emails_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `cart_items`
--
ALTER TABLE `cart_items`
  ADD CONSTRAINT `cart_items_ibfk_1` FOREIGN KEY (`cart_id`) REFERENCES `carts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`parent_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`shipping_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`billing_address_id`) REFERENCES `addresses` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `orders_ibfk_5` FOREIGN KEY (`order_group_id`) REFERENCES `order_groups` (`id`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `order_groups`
--
ALTER TABLE `order_groups`
  ADD CONSTRAINT `order_groups_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `payment_transactions`
--
ALTER TABLE `payment_transactions`
  ADD CONSTRAINT `payment_transactions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_transactions_ibfk_2` FOREIGN KEY (`order_group_id`) REFERENCES `order_groups` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payment_transactions_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  ADD CONSTRAINT `payment_transactions_ibfk_4` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT;

--
-- Filtros para la tabla `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `sales_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `sales_ibfk_3` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `seller_payment_methods`
--
ALTER TABLE `seller_payment_methods`
  ADD CONSTRAINT `seller_payment_methods_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `stock_alert_log`
--
ALTER TABLE `stock_alert_log`
  ADD CONSTRAINT `stock_alert_log_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_alert_log_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `cleanup_sessions` ON SCHEDULE EVERY 1 HOUR STARTS '2025-11-13 19:44:59' ON COMPLETION NOT PRESERVE ENABLE DO CALL clean_expired_sessions()$$

CREATE DEFINER=`root`@`localhost` EVENT `cleanup_carts` ON SCHEDULE EVERY 1 DAY STARTS '2025-11-13 19:44:59' ON COMPLETION NOT PRESERVE ENABLE DO CALL clean_abandoned_carts()$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
