-- SQL Schema for SmartNote (Mekar Mulya)
-- Versi: 1.2 (Terbaru)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. Tabel User (Autentikasi)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Akun Admin Default: admin / admin
INSERT INTO `users` (`username`, `password`) VALUES
('admin', '$2y$10$8S8oUoR1UvO8Q.Lw5pZ9v.oV.fK6Zl.9Zl.9Zl.9Zl.9Zl.9Zl.')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- --------------------------------------------------------
-- 2. Tabel Settings (Profil Toko & Konfigurasi)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `settings` (
  `meta_key` varchar(100) NOT NULL,
  `meta_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`meta_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 3. Tabel Numbering Formats (Templat Penomoran)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `numbering_formats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `format_pattern` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 4. Tabel Customers (Data Pelanggan)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `hp` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. Tabel Master Items (Barang & Jasa)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `master_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` enum('products','services') NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 6. Tabel Invoices (Header Nota)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `invoices` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_number` varchar(50) NOT NULL,
  `invoice_date` date NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `discount` decimal(15,2) DEFAULT 0.00,
  `transport` decimal(15,2) DEFAULT 0.00,
  `service_fee` decimal(15,2) DEFAULT 0.00,
  `grand_total` decimal(15,2) DEFAULT 0.00,
  `note_footer` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `invoice_number` (`invoice_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 7. Tabel Invoice Items (Rincian Barang per Nota)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `invoice_id` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `qty` int(11) DEFAULT 1,
  `price` decimal(15,2) DEFAULT 0.00,
  `total` decimal(15,2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  KEY `invoice_id` (`invoice_id`),
  CONSTRAINT `invoice_items_ibfk_1` FOREIGN KEY (`invoice_id`) REFERENCES `invoices` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

COMMIT;
