-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-03-2026 a las 12:35:17
-- Versión del servidor: 10.11.6-MariaDB
-- Versión de PHP: 8.4.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `energy`
--
CREATE DATABASE IF NOT EXISTS `energy` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_520_ci;
USE `energy`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contracts`
--

CREATE TABLE `contracts` (
  `id` int(11) NOT NULL,
  `client_id` int(11) DEFAULT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `plan_id` int(11) DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('pending','active','cancelled','completed') DEFAULT 'pending',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `commission_amount` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Volcado de datos para la tabla `contracts`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `energy_plans`
--

CREATE TABLE `energy_plans` (
  `id` int(11) NOT NULL,
  `provider_id` int(11) NOT NULL,
  `seller_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price_per_kwh` decimal(10,4) NOT NULL,
  `monthly_fee` decimal(10,2) DEFAULT 0.00,
  `contract_duration_months` int(11) DEFAULT 12,
  `renewable_energy_percentage` int(11) DEFAULT 0,
  `features` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`features`)),
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Volcado de datos para la tabla `energy_plans`
--

INSERT INTO `energy_plans` (`id`, `provider_id`, `seller_id`, `name`, `description`, `price_per_kwh`, `monthly_fee`, `contract_duration_months`, `renewable_energy_percentage`, `features`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, NULL, 'Plan Estable', 'Precio fijo durante todo el contrato', 0.1250, 5.00, 12, 30, NULL, 1, '2026-02-24 18:21:21', '2026-02-24 18:21:21'),
(2, 1, NULL, 'Plan Verde', 'Energía 100% renovable', 0.1380, 8.00, 24, 100, NULL, 1, '2026-02-24 18:21:21', '2026-02-24 18:21:21'),
(3, 2, NULL, 'Plan Ahorro', 'El plan más económico', 0.1180, 3.50, 12, 20, NULL, 1, '2026-02-24 18:21:21', '2026-02-24 18:21:21'),
(4, 2, NULL, 'Plan Hogar', 'Ideal para familias', 0.1290, 6.00, 12, 50, NULL, 1, '2026-02-24 18:21:21', '2026-02-24 18:21:21'),
(5, 3, NULL, 'Plan Eco', 'Compromiso con el medio ambiente', 0.1350, 7.00, 24, 100, NULL, 1, '2026-02-24 18:21:21', '2026-02-24 18:21:21'),
(6, 4, NULL, 'Plan Luz', 'Electricidad sin permanencia', 0.1400, 0.00, 1, 25, NULL, 1, '2026-02-24 18:21:21', '2026-02-24 18:21:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `energy_providers`
--

CREATE TABLE `energy_providers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Volcado de datos para la tabla `energy_providers`
--

INSERT INTO `energy_providers` (`id`, `name`, `description`, `logo`, `contact_email`, `contact_phone`, `website`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Iberdrola', 'Uno de los principales proveedores de energía en España', NULL, 'contacto@iberdrola.es', '+34900100100', 'https://www.iberdrola.es', 1, '2026-02-24 18:21:21', '2026-02-24 18:21:21'),
(2, 'Endesa', 'Proveedor líder de energía eléctrica', NULL, 'info@endesa.es', '+34800760000', 'https://www.endesa.com', 1, '2026-02-24 18:21:21', '2026-02-24 18:21:21'),
(3, 'Naturgy', 'Energía natural y sostenible', NULL, 'atencion@naturgy.es', '+34900100251', 'https://www.naturgy.es', 1, '2026-02-24 18:21:21', '2026-02-24 18:21:21'),
(4, 'Repsol', 'Energía eléctrica y gas', NULL, 'clientes@repsol.com', '+34901100100', 'https://www.repsol.es', 1, '2026-02-24 18:21:21', '2026-02-24 18:21:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'admin', 'Administrador del sistema', '2026-02-24 18:21:21'),
(2, 'seller', 'Vendedor de proveedores de energía', '2026-02-24 18:21:21'),
(3, 'user', 'Cliente final', '2026-02-24 18:21:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `second_last_name` varchar(50) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `profile_img` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  `activation_token` varchar(64) DEFAULT NULL,
  `activation_token_expires` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `first_name`, `last_name`, `second_last_name`, `phone`, `profile_img`, `is_active`, `activation_token`, `activation_token_expires`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@energy.com', '$2y$12$EhxewlfrYNk8diFqxy/a/uBjfP9J895TkkW3pXCeqIDaXLxYJlPF6', 'Admin', 'Sistema', NULL, NULL, NULL, 1, NULL, NULL, '2026-02-24 18:21:21', '2026-02-24 19:34:17');
-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `assigned_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Volcado de datos para la tabla `user_roles`
--

INSERT INTO `user_roles` (`id`, `user_id`, `role_id`, `assigned_at`) VALUES
(1, 1, 1, '2026-02-24 18:21:21');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `contracts`
--
ALTER TABLE `contracts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `plan_id` (`plan_id`),
  ADD KEY `idx_contracts_client` (`client_id`),
  ADD KEY `idx_contracts_seller` (`seller_id`),
  ADD KEY `idx_contracts_status` (`status`);

--
-- Indices de la tabla `energy_plans`
--
ALTER TABLE `energy_plans`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_plans_provider` (`provider_id`),
  ADD KEY `idx_plans_seller` (`seller_id`);

--
-- Indices de la tabla `energy_providers`
--
ALTER TABLE `energy_providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_user_email` (`email`),
  ADD KEY `idx_activation_token` (`activation_token`);

--
-- Indices de la tabla `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_user_role` (`user_id`,`role_id`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_user_roles_user` (`user_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `contracts`
--
ALTER TABLE `contracts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `energy_plans`
--
ALTER TABLE `energy_plans`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `energy_providers`
--
ALTER TABLE `energy_providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `contracts`
--
ALTER TABLE `contracts`
  ADD CONSTRAINT `contracts_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `contracts_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `contracts_ibfk_3` FOREIGN KEY (`plan_id`) REFERENCES `energy_plans` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `energy_plans`
--
ALTER TABLE `energy_plans`
  ADD CONSTRAINT `energy_plans_ibfk_1` FOREIGN KEY (`provider_id`) REFERENCES `energy_providers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `energy_plans_ibfk_2` FOREIGN KEY (`seller_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `user_roles`
--
ALTER TABLE `user_roles`
  ADD CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
