-- ============================================================
-- King's Cut Saloon — Membership Management System
-- Database import file (schema + seed data)
-- Engine: MySQL 5.7+ / MariaDB 10.3+   Charset: utf8mb4
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `kings_cut_saloon`
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `kings_cut_saloon`;

-- ------------------------------------------------------------
-- customers  (portal accounts — the "Customer" role in the PRD)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `customers`;
CREATE TABLE `customers` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `status` ENUM('active','suspended') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customers_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- password_resets  (OTP-based "forgot password" flow for customers)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT UNSIGNED NOT NULL,
  `otp_hash` CHAR(64) NOT NULL COMMENT 'SHA-256 of the 6-digit code — the plain code is never stored',
  `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` DATETIME NOT NULL,
  `used_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_resets_customer` (`customer_id`),
  CONSTRAINT `fk_resets_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- admins  (Admin & Super Admin roles)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `admins`;
CREATE TABLE `admins` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('admin','super_admin') NOT NULL DEFAULT 'admin',
  `status` ENUM('active','deactivated') NOT NULL DEFAULT 'active',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_admins_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- plans  (Single / Couple / Family / Corporate)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `plans`;
CREATE TABLE `plans` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(20) NOT NULL,
  `name` VARCHAR(60) NOT NULL,
  `tagline` VARCHAR(160) DEFAULT NULL,
  `max_secondary_ids` INT UNSIGNED NOT NULL DEFAULT 0,
  `discount_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `is_custom_pricing` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plans_code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- plan_prices  (one row per plan + duration)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `plan_prices`;
CREATE TABLE `plan_prices` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `plan_id` INT UNSIGNED NOT NULL,
  `duration` ENUM('monthly','3m','6m','yearly') NOT NULL,
  `price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `compare_at_price` DECIMAL(12,2) DEFAULT NULL COMMENT 'Optional "was" price shown struck through when this duration is on sale',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_plan_duration` (`plan_id`,`duration`),
  CONSTRAINT `fk_planprices_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- subscriptions  (a purchased membership; each has its own
-- Membership ID + QR token, per PRD: "Users can own multiple
-- subscriptions. Each subscription has unique Membership ID and QR.")
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `subscriptions`;
CREATE TABLE `subscriptions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT UNSIGNED NOT NULL,
  `plan_id` INT UNSIGNED NOT NULL,
  `membership_id` VARCHAR(20) NOT NULL COMMENT 'e.g. KC-0417-SG',
  `qr_token` VARCHAR(64) NOT NULL,
  `duration` ENUM('monthly','3m','6m','yearly') NOT NULL,
  `price_paid` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('active','expired','cancelled') NOT NULL DEFAULT 'active',
  `cancelled_by_admin_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sub_membership_id` (`membership_id`),
  UNIQUE KEY `uq_sub_qr_token` (`qr_token`),
  KEY `idx_sub_customer` (`customer_id`),
  KEY `idx_sub_plan` (`plan_id`),
  CONSTRAINT `fk_sub_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sub_plan` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`),
  CONSTRAINT `fk_sub_admin` FOREIGN KEY (`cancelled_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- secondary_ids  (family / guest sub-IDs tied to a subscription)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `secondary_ids`;
CREATE TABLE `secondary_ids` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `subscription_id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(80) NOT NULL,
  `secondary_code` VARCHAR(20) NOT NULL COMMENT 'e.g. KC-0417-G1',
  `qr_token` VARCHAR(64) NOT NULL,
  `type` ENUM('temporary','permanent') NOT NULL DEFAULT 'permanent',
  `max_uses` INT UNSIGNED DEFAULT NULL COMMENT 'NULL = unlimited (permanent)',
  `uses_count` INT UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` DATETIME DEFAULT NULL,
  `status` ENUM('active','revoked','expired') NOT NULL DEFAULT 'active',
  `last_used_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_secondary_code` (`secondary_code`),
  UNIQUE KEY `uq_secondary_qr` (`qr_token`),
  KEY `idx_secondary_sub` (`subscription_id`),
  CONSTRAINT `fk_secondary_sub` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- id_shares  (log of QR/ID share actions — email, social, copy link)
-- Covers sharing both a primary membership ticket and a secondary ID.
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `id_shares`;
CREATE TABLE `id_shares` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `subscription_id` INT UNSIGNED DEFAULT NULL COMMENT 'set when sharing a primary ticket',
  `secondary_id_id` INT UNSIGNED DEFAULT NULL COMMENT 'set when sharing a secondary/guest ID',
  `channel` ENUM('email','whatsapp','twitter','facebook','copy_link','native') NOT NULL,
  `recipient` VARCHAR(150) DEFAULT NULL COMMENT 'email address, when channel = email',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_shares_subscription` (`subscription_id`),
  KEY `idx_shares_secondary` (`secondary_id_id`),
  CONSTRAINT `fk_shares_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_shares_secondary` FOREIGN KEY (`secondary_id_id`) REFERENCES `secondary_ids` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- wallets  (one per customer)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `wallets`;
CREATE TABLE `wallets` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT UNSIGNED NOT NULL,
  `balance` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_wallet_customer` (`customer_id`),
  CONSTRAINT `fk_wallet_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_wallet_balance` CHECK (`balance` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- wallet_transactions
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `wallet_transactions`;
CREATE TABLE `wallet_transactions` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `wallet_id` INT UNSIGNED NOT NULL,
  `subscription_id` INT UNSIGNED DEFAULT NULL COMMENT 'Which plan this charge relates to, if any — NULL for plain top-ups',
  `type` ENUM('credit','debit') NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `description` VARCHAR(160) NOT NULL,
  `reference_type` ENUM('topup','service_payment','subscription_payment','admin_adjustment','refund') NOT NULL,
  `reference_id` INT UNSIGNED DEFAULT NULL,
  `created_by_admin_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_wtx_wallet` (`wallet_id`),
  KEY `idx_wtx_subscription` (`subscription_id`),
  CONSTRAINT `fk_wtx_wallet` FOREIGN KEY (`wallet_id`) REFERENCES `wallets` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_wtx_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_wtx_admin` FOREIGN KEY (`created_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- service_categories / services  (the "Chair Menu")
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `service_categories`;
CREATE TABLE `service_categories` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(80) NOT NULL,
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

DROP TABLE IF EXISTS `services`;
CREATE TABLE `services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` INT UNSIGNED NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `description` VARCHAR(200) DEFAULT NULL,
  `duration_minutes` SMALLINT UNSIGNED NOT NULL DEFAULT 30,
  `standard_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `compare_at_price` DECIMAL(12,2) DEFAULT NULL COMMENT 'Optional "was" price shown struck through when this service is on sale',
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_services_category` (`category_id`),
  CONSTRAINT `fk_services_category` FOREIGN KEY (`category_id`) REFERENCES `service_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- attendance  (check-in log; QR scan or manual entry by Admin)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `attendance`;
CREATE TABLE `attendance` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `subscription_id` INT UNSIGNED NOT NULL,
  `secondary_id_id` INT UNSIGNED DEFAULT NULL COMMENT 'NULL = primary ID used',
  `verified_by` ENUM('qr_scan','manual_entry','admin_override') NOT NULL DEFAULT 'qr_scan',
  `admin_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('granted','denied_expired','denied_cancelled') NOT NULL DEFAULT 'granted',
  `checked_in_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_att_sub` (`subscription_id`),
  KEY `idx_att_secondary` (`secondary_id_id`),
  CONSTRAINT `fk_att_sub` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_att_secondary` FOREIGN KEY (`secondary_id_id`) REFERENCES `secondary_ids` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_att_admin` FOREIGN KEY (`admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- authorization_codes  (Super Admin generates, Admin redeems,
-- for manual/offline subscription payments)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `authorization_codes`;
CREATE TABLE `authorization_codes` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(20) NOT NULL,
  `generated_by_admin_id` INT UNSIGNED NOT NULL,
  `status` ENUM('unused','used','expired') NOT NULL DEFAULT 'unused',
  `used_by_admin_id` INT UNSIGNED DEFAULT NULL,
  `used_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_authcode_code` (`code`),
  CONSTRAINT `fk_authcode_generator` FOREIGN KEY (`generated_by_admin_id`) REFERENCES `admins` (`id`),
  CONSTRAINT `fk_authcode_user` FOREIGN KEY (`used_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- payments  (subscription purchases + service charges ledger)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `payments`;
CREATE TABLE `payments` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT UNSIGNED NOT NULL,
  `subscription_id` INT UNSIGNED DEFAULT NULL,
  `service_id` INT UNSIGNED DEFAULT NULL,
  `description` VARCHAR(160) NOT NULL,
  `amount` DECIMAL(12,2) NOT NULL,
  `discount_applied` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `method` ENUM('card','wallet','manual_auth_code') NOT NULL,
  `authorization_code_id` INT UNSIGNED DEFAULT NULL,
  `status` ENUM('paid','failed','pending') NOT NULL DEFAULT 'paid',
  `processed_by_admin_id` INT UNSIGNED DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_payments_customer` (`customer_id`),
  CONSTRAINT `fk_pay_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pay_subscription` FOREIGN KEY (`subscription_id`) REFERENCES `subscriptions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pay_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pay_authcode` FOREIGN KEY (`authorization_code_id`) REFERENCES `authorization_codes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_pay_admin` FOREIGN KEY (`processed_by_admin_id`) REFERENCES `admins` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- contact_messages  (public Contact page submissions)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `contact_messages`;
CREATE TABLE `contact_messages` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `full_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `subject` VARCHAR(80) NOT NULL DEFAULT 'General enquiry',
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- audit_log  ("All actions audited" — PRD business rule)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `audit_log`;
CREATE TABLE `audit_log` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `actor_type` ENUM('customer','admin','system') NOT NULL,
  `actor_id` INT UNSIGNED DEFAULT NULL,
  `action` VARCHAR(120) NOT NULL,
  `target` VARCHAR(160) DEFAULT NULL,
  `details` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- session_pricing  (admin-controlled rates for booking a private
-- session — combination of time slot + location type)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `session_pricing`;
CREATE TABLE `session_pricing` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `session_type` ENUM('morning','afternoon','evening','whole_day') NOT NULL,
  `location_type` ENUM('vip_office','vip_outside') NOT NULL,
  `label` VARCHAR(80) NOT NULL COMMENT 'e.g. "Morning — VIP Office"',
  `base_price` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Flat fee for the session slot itself',
  `price_per_person` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'Added per person on top of the base fee',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_session_location` (`session_type`,`location_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- reservations  (customer booking requests — pending admin confirmation)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `reservations`;
CREATE TABLE `reservations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` INT UNSIGNED DEFAULT NULL COMMENT 'set if the booker was logged in',
  `full_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) NOT NULL,
  `session_type` ENUM('morning','afternoon','evening','whole_day') NOT NULL,
  `location_type` ENUM('vip_office','vip_outside') NOT NULL,
  `number_of_people` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `reservation_date` DATE NOT NULL,
  `notes` VARCHAR(500) DEFAULT NULL,
  `estimated_total` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status` ENUM('pending','confirmed','cancelled') NOT NULL DEFAULT 'pending',
  `admin_note` VARCHAR(300) DEFAULT NULL COMMENT 'internal note, e.g. confirmed time or reason for cancellation',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reservations_customer` (`customer_id`),
  KEY `idx_reservations_status` (`status`),
  CONSTRAINT `fk_reservations_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- reservation_services  (many-to-many: which services were requested)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `reservation_services`;
CREATE TABLE `reservation_services` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `reservation_id` INT UNSIGNED NOT NULL,
  `service_id` INT UNSIGNED NOT NULL,
  `price_at_booking` DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT 'snapshot of the service price when booked',
  PRIMARY KEY (`id`),
  KEY `idx_resvsvc_reservation` (`reservation_id`),
  KEY `idx_resvsvc_service` (`service_id`),
  CONSTRAINT `fk_resvsvc_reservation` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_resvsvc_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- work_items  (public "Our Work" gallery — admin-uploaded images
-- and YouTube video links)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS `work_items`;
CREATE TABLE `work_items` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` ENUM('image','video') NOT NULL,
  `title` VARCHAR(120) DEFAULT NULL,
  `image_path` VARCHAR(255) DEFAULT NULL COMMENT 'relative path under assets/uploads/work/, set when type=image',
  `youtube_url` VARCHAR(255) DEFAULT NULL COMMENT 'original URL as pasted by admin, set when type=video',
  `youtube_video_id` VARCHAR(30) DEFAULT NULL COMMENT 'extracted ID used to build the embed URL',
  `sort_order` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- SEED DATA
-- ============================================================

-- Admins  (password for both demo accounts: "password123")
INSERT INTO `admins` (`id`,`full_name`,`email`,`password_hash`,`role`,`status`) VALUES
(1,'Tunde Falade','tunde@kingscutsaloon.com','$2y$10$zDlYSvgcFrNLG/1o6qWhUekAZcgW0AUAe/q2O9v76Dri1xJtG1oOq','admin','active'),
(2,'Samuel Edet','samuel@kingscutsaloon.com','$2y$10$zDlYSvgcFrNLG/1o6qWhUekAZcgW0AUAe/q2O9v76Dri1xJtG1oOq','super_admin','active');

-- Plans
INSERT INTO `plans` (`id`,`code`,`name`,`tagline`,`max_secondary_ids`,`discount_percent`,`is_featured`,`is_custom_pricing`,`sort_order`) VALUES
(1,'single','Single','One member, one chair.',0,18.00,0,0,1),
(2,'couple','Couple','You, plus one.',1,20.00,1,0,2),
(3,'family','Family','The whole household.',3,22.00,0,0,3),
(4,'corporate','Corporate','Teams & organizations.',99,25.00,0,1,4);

-- Plan prices
INSERT INTO `plan_prices` (`plan_id`,`duration`,`price`,`compare_at_price`) VALUES
(1,'monthly',8000.00,NULL),(1,'3m',22000.00,NULL),(1,'6m',42000.00,NULL),(1,'yearly',80000.00,NULL),
(2,'monthly',14000.00,16000.00),(2,'3m',39000.00,NULL),(2,'6m',74000.00,NULL),(2,'yearly',140000.00,168000.00),
(3,'monthly',24000.00,NULL),(3,'3m',67000.00,NULL),(3,'6m',128000.00,NULL),(3,'yearly',240000.00,NULL),
(4,'monthly',0.00,NULL),(4,'3m',0.00,NULL),(4,'6m',0.00,NULL),(4,'yearly',0.00,NULL);

-- Service categories
INSERT INTO `service_categories` (`id`,`name`,`sort_order`) VALUES
(1,'Cuts & Fades',1),
(2,'Shaves & Beard Work',2),
(3,'Grooming Packages',3);

-- Services
INSERT INTO `services` (`category_id`,`name`,`description`,`duration_minutes`,`standard_price`,`compare_at_price`,`sort_order`) VALUES
(1,'Signature Fade','Skin fade, line-up, and finish with clippers + shears.',40,5500.00,NULL,1),
(1,'Classic Cut','Scissor-over-comb trim, wash, and style.',30,3600.00,NULL,2),
(1,'Kids Cut','Ages 10 & under, any style on the board.',25,2400.00,NULL,3),
(1,'Buzz Cut','All-over clipper cut, one guard length.',15,1800.00,NULL,4),
(2,'Hot Towel Shave','Straight-razor shave with pre-shave oil and hot towel.',35,4200.00,NULL,1),
(2,'Beard Sculpt','Shape, line-up, and beard oil finish.',20,2400.00,NULL,2),
(2,'Beard Trim','Quick tidy-up between full sculpts.',10,1500.00,NULL,3),
(3,'The Full King','Fade, hot towel shave, beard sculpt, and scalp massage.',75,9500.00,10800.00,1),
(3,'Wash & Groom','Cut, wash, and beard trim — the every-two-weeks package.',50,6200.00,NULL,2);

-- Demo customer  (password: "password123")
INSERT INTO `customers` (`id`,`full_name`,`email`,`phone`,`password_hash`,`status`) VALUES
(1,'Alex Morgan','alex.morgan@example.com','+2348030001147','$2y$10$zDlYSvgcFrNLG/1o6qWhUekAZcgW0AUAe/q2O9v76Dri1xJtG1oOq','active');

-- Wallet for demo customer
INSERT INTO `wallets` (`id`,`customer_id`,`balance`) VALUES (1,1,18400.00);

-- Demo subscriptions: Alex Morgan owns TWO active plans, to demonstrate
-- the plan filter on Wallet / Attendance / Family & Guest IDs / Payments.
INSERT INTO `subscriptions` (`id`,`customer_id`,`plan_id`,`membership_id`,`qr_token`,`duration`,`price_paid`,`start_date`,`end_date`,`status`) VALUES
(1,1,1,'KC-0417-SG','a1b2c3d4e5f60417sg','yearly',80000.00,'2026-03-04','2027-03-04','active'),
(2,1,2,'KC-0592-CP','b2c3d4e5f6a70592cp','monthly',14000.00,'2026-06-10','2026-08-10','active');

-- Secondary IDs for subscription 1 (Single — Yearly)
INSERT INTO `secondary_ids` (`subscription_id`,`label`,`secondary_code`,`qr_token`,`type`,`max_uses`,`uses_count`,`expires_at`,`status`,`last_used_at`) VALUES
(1,'Grace M.','KC-0417-G1','a1b2c3d4e5f60417g1','permanent',NULL,6,NULL,'active','2026-06-27 09:30:00'),
(1,'Guest Pass','KC-0417-G2','a1b2c3d4e5f60417g2','temporary',3,1,'2026-08-01 00:00:00','active','2026-06-12 14:00:00'),
(1,'Weekend Guest','KC-0417-G3','a1b2c3d4e5f60417g3','temporary',2,2,'2026-06-01 00:00:00','expired','2026-05-30 11:00:00');

-- Secondary ID for subscription 2 (Couple — Monthly)
INSERT INTO `secondary_ids` (`subscription_id`,`label`,`secondary_code`,`qr_token`,`type`,`max_uses`,`uses_count`,`expires_at`,`status`,`last_used_at`) VALUES
(2,'Jordan P.','KC-0592-G1','b2c3d4e5f6a70592g1','permanent',NULL,2,NULL,'active','2026-06-20 10:00:00');

-- Wallet transactions — tagged with which plan (subscription) the charge relates to
INSERT INTO `wallet_transactions` (`wallet_id`,`subscription_id`,`type`,`amount`,`description`,`reference_type`,`reference_id`,`created_at`) VALUES
(1,1,'debit',4500.00,'Signature Fade (member rate)','service_payment',1,'2026-07-02 10:44:00'),
(1,NULL,'credit',20000.00,'Wallet top-up via card','topup',NULL,'2026-06-30 10:20:00'),
(1,1,'debit',2000.00,'Beard Sculpt (member rate)','service_payment',6,'2026-06-18 09:52:00'),
(1,2,'debit',3000.00,'Classic Cut (member rate)','service_payment',2,'2026-06-21 08:30:00');

-- Attendance history — split across both subscriptions
INSERT INTO `attendance` (`subscription_id`,`secondary_id_id`,`verified_by`,`admin_id`,`status`,`checked_in_at`) VALUES
(1,NULL,'qr_scan',NULL,'granted','2026-07-02 10:42:00'),
(1,NULL,'qr_scan',NULL,'granted','2026-06-29 16:15:00'),
(1,1,'qr_scan',NULL,'granted','2026-06-27 09:30:00'),
(1,NULL,'manual_entry',1,'granted','2026-06-18 13:05:00'),
(2,NULL,'qr_scan',NULL,'granted','2026-06-21 08:28:00'),
(2,4,'qr_scan',NULL,'granted','2026-06-20 10:00:00');

-- Payments ledger — subscription_id set on service charges too, so the
-- Payments page can filter "which plan funded this" the same way Wallet does
INSERT INTO `payments` (`customer_id`,`subscription_id`,`service_id`,`description`,`amount`,`discount_applied`,`method`,`status`,`created_at`) VALUES
-- Foundational subscription purchases (kept at fixed dates to match the subscriptions above)
(1,1,NULL,'Single Plan — Yearly renewal',88000.00,0.00,'card','paid','2026-03-04 09:00:00'),
(1,2,NULL,'Couple Plan — Monthly subscription',14000.00,0.00,'card','paid','2026-06-10 09:00:00'),
-- Today, spread across different hours — populates the "Hour" view regardless of import date/time
(1,1,1,'Signature Fade',4500.00,1000.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(1,2,2,'Classic Cut',3000.00,600.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 3 HOUR)),
(1,1,6,'Beard Sculpt',2000.00,400.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(1,1,3,'Kids Cut',2400.00,0.00,'card','paid',DATE_SUB(NOW(), INTERVAL 9 HOUR)),
-- Past week — populates the "Day" view
(1,2,5,'Hot Towel Shave',4200.00,0.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 1 DAY)),
(1,1,8,'The Full King',9500.00,1300.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 2 DAY)),
(1,1,4,'Buzz Cut',1800.00,0.00,'card','paid',DATE_SUB(NOW(), INTERVAL 3 DAY)),
(1,2,7,'Beard Trim',1500.00,0.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 4 DAY)),
(1,1,9,'Wash & Groom',6200.00,0.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 5 DAY)),
(1,2,1,'Signature Fade',4500.00,1000.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 6 DAY)),
-- Past 8 weeks — populates the "Week" view
(1,1,2,'Classic Cut',3000.00,600.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 2 WEEK)),
(1,2,6,'Beard Sculpt',2000.00,400.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 3 WEEK)),
(1,1,1,'Signature Fade',4500.00,1000.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 4 WEEK)),
(1,2,8,'The Full King',9500.00,1300.00,'card','paid',DATE_SUB(NOW(), INTERVAL 5 WEEK)),
(1,1,3,'Kids Cut',2400.00,0.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 6 WEEK)),
(1,2,4,'Buzz Cut',1800.00,0.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 7 WEEK)),
-- Past 11 months — populates the "Month" view
(1,1,NULL,'Single Plan — Monthly renewal',8000.00,0.00,'card','paid',DATE_SUB(NOW(), INTERVAL 2 MONTH)),
(1,2,2,'Classic Cut',3000.00,600.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 3 MONTH)),
(1,1,9,'Wash & Groom',6200.00,0.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 4 MONTH)),
(1,2,1,'Signature Fade',4500.00,1000.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 5 MONTH)),
(1,1,6,'Beard Sculpt',2000.00,400.00,'card','paid',DATE_SUB(NOW(), INTERVAL 6 MONTH)),
(1,2,8,'The Full King',9500.00,1300.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 7 MONTH)),
(1,1,5,'Hot Towel Shave',4200.00,0.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 8 MONTH)),
(1,2,3,'Kids Cut',2400.00,0.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 9 MONTH)),
(1,1,4,'Buzz Cut',1800.00,0.00,'card','paid',DATE_SUB(NOW(), INTERVAL 10 MONTH)),
-- Prior years — populates the "Year" view
(1,1,NULL,'Single Plan — Yearly renewal',80000.00,0.00,'card','paid',DATE_SUB(NOW(), INTERVAL 14 MONTH)),
(1,1,1,'Signature Fade',4500.00,1000.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 20 MONTH)),
(1,1,2,'Classic Cut',3000.00,600.00,'wallet','paid',DATE_SUB(NOW(), INTERVAL 30 MONTH));

-- Sample authorization codes (Super Admin generated)
INSERT INTO `authorization_codes` (`code`,`generated_by_admin_id`,`status`,`used_by_admin_id`,`used_at`) VALUES
('KX9-2214',2,'unused',NULL,NULL),
('PL4-8830',2,'used',1,'2026-07-06 09:10:00'),
('RT2-5591',2,'expired',NULL,NULL);

-- Sample audit log entries
INSERT INTO `audit_log` (`actor_type`,`actor_id`,`action`,`target`,`details`) VALUES
('system',NULL,'Secondary ID auto-expired','Weekend Guest (Alex Morgan)','2-use limit reached'),
('admin',1,'Manual payment processed','Chuka Obi','Auth code PL4-8830'),
('system',NULL,'Subscription activated','Alex Morgan — KC-0417-SG','Online card payment');

-- Session pricing — 4 time slots × 2 location types, admin-editable
INSERT INTO `session_pricing` (`session_type`,`location_type`,`label`,`base_price`,`price_per_person`) VALUES
('morning','vip_office','Morning — VIP Office',15000.00,3000.00),
('morning','vip_outside','Morning — VIP Outside',25000.00,4500.00),
('afternoon','vip_office','Afternoon — VIP Office',18000.00,3000.00),
('afternoon','vip_outside','Afternoon — VIP Outside',28000.00,4500.00),
('evening','vip_office','Evening — VIP Office',20000.00,3500.00),
('evening','vip_outside','Evening — VIP Outside',32000.00,5000.00),
('whole_day','vip_office','Whole Day — VIP Office',60000.00,5000.00),
('whole_day','vip_outside','Whole Day — VIP Outside',95000.00,7500.00);

-- Sample reservations
INSERT INTO `reservations` (`id`,`customer_id`,`full_name`,`email`,`phone`,`session_type`,`location_type`,`number_of_people`,`reservation_date`,`notes`,`estimated_total`,`status`,`admin_note`,`created_at`) VALUES
(1,1,'Alex Morgan','alex.morgan@example.com','+2348030001147','evening','vip_office',3,DATE_ADD(CURDATE(), INTERVAL 5 DAY),'Birthday grooming session for my groomsmen.',33000.00,'confirmed','Confirmed for 5pm — chairs 2 & 3 reserved.',DATE_SUB(NOW(), INTERVAL 2 DAY)),
(2,NULL,'Daniel Okafor','daniel.okafor@example.com','+2348012223344','whole_day','vip_outside',8,DATE_ADD(CURDATE(), INTERVAL 12 DAY),'Corporate event — need the team looking sharp before a product launch.',155000.00,'pending',NULL,DATE_SUB(NOW(), INTERVAL 6 HOUR)),
(3,NULL,'Ifeoma Nwosu','ifeoma.n@example.com','+2348099887766','morning','vip_office',1,DATE_ADD(CURDATE(), INTERVAL 2 DAY),NULL,18000.00,'pending',NULL,DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT INTO `reservation_services` (`reservation_id`,`service_id`,`price_at_booking`) VALUES
(1,1,5500.00), (1,6,2400.00),
(2,8,9500.00), (2,5,4200.00),
(3,1,5500.00);

-- Sample "Our Work" gallery items (image_path files are shipped alongside
-- the app under assets/uploads/work/ — replace with real photos any time
-- from the admin Work page)
INSERT INTO `work_items` (`type`,`title`,`image_path`,`youtube_url`,`youtube_video_id`,`sort_order`) VALUES
('image','Signature Fade — Before & After','uploads/work/sample-fade.svg',NULL,NULL,1),
('image','VIP Office Setup','uploads/work/sample-vip-office.svg',NULL,NULL,2),
('image','Full King Grooming Package','uploads/work/sample-full-king.svg',NULL,NULL,3),
('video','A Look Inside the Chair','https://www.youtube.com/watch?v=jNQXAC9IVRw',NULL,'jNQXAC9IVRw',4);

-- ============================================================
-- End of file
-- ============================================================
