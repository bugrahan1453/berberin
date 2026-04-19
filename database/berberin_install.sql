-- ============================================
-- BERBERiN — Tam Kurulum SQL
-- phpMyAdmin'de glorins_vb veritabanına çalıştır
-- ============================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

-- Laravel migrations tablosu
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `migration` varchar(255) NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- KULLANICILAR
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `gender` enum('male','female') NOT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `lat` decimal(10,8) DEFAULT NULL,
  `lng` decimal(11,8) DEFAULT NULL,
  `trust_score` int DEFAULT 50,
  `status` enum('active','suspended','banned') DEFAULT 'active',
  `otp_code` varchar(6) DEFAULT NULL,
  `otp_expires_at` timestamp NULL DEFAULT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `fcm_token` varchar(500) DEFAULT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_phone_unique` (`phone`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- DÜKKANLAR
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `shops` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `owner_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `address` text NOT NULL,
  `city` varchar(100) DEFAULT NULL,
  `district` varchar(100) DEFAULT NULL,
  `lat` decimal(10,8) NOT NULL,
  `lng` decimal(11,8) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `logo` varchar(500) DEFAULT NULL,
  `cover_image` varchar(500) DEFAULT NULL,
  `instagram` varchar(255) DEFAULT NULL,
  `tiktok` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `is_full` tinyint(1) DEFAULT 0,
  `is_verified` tinyint(1) DEFAULT 0,
  `total_seats` int DEFAULT 1,
  `gender_filter` enum('male','female','both') DEFAULT 'both',
  `avg_rating` decimal(2,1) DEFAULT 0.0,
  `total_reviews` int DEFAULT 0,
  `subscription_plan` enum('starter','pro','premium') DEFAULT 'starter',
  `subscription_expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `shops_slug_unique` (`slug`),
  KEY `shops_owner_id_foreign` (`owner_id`),
  CONSTRAINT `shops_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- ÇALIŞMA SAATLERİ
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_hours` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `shop_id` bigint UNSIGNED NOT NULL,
  `day_of_week` tinyint NOT NULL,
  `open_time` time DEFAULT NULL,
  `close_time` time DEFAULT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `is_closed` tinyint(1) DEFAULT 0,
  KEY `shop_hours_shop_id_foreign` (`shop_id`),
  CONSTRAINT `shop_hours_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- PERSONEL
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `staff` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `shop_id` bigint UNSIGNED NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `avatar` varchar(500) DEFAULT NULL,
  `role` enum('owner','manager','staff') DEFAULT 'staff',
  `specialties` json DEFAULT NULL,
  `commission_rate` decimal(4,2) DEFAULT 0.00,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `staff_shop_id_foreign` (`shop_id`),
  KEY `staff_user_id_foreign` (`user_id`),
  CONSTRAINT `staff_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `staff_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- KOLTUKLAR
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `seats` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `shop_id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `assigned_staff_id` bigint UNSIGNED DEFAULT NULL,
  `status` enum('empty','busy','reserved','inactive') DEFAULT 'empty',
  `is_vip` tinyint(1) DEFAULT 0,
  `current_appointment_id` bigint UNSIGNED DEFAULT NULL,
  `busy_since` timestamp NULL DEFAULT NULL,
  `estimated_free_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `seats_shop_id_foreign` (`shop_id`),
  KEY `seats_assigned_staff_id_foreign` (`assigned_staff_id`),
  CONSTRAINT `seats_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE,
  CONSTRAINT `seats_assigned_staff_id_foreign` FOREIGN KEY (`assigned_staff_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- PERSONEL VARDİYALARI
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `staff_shifts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `staff_id` bigint UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `is_off` tinyint(1) DEFAULT 0,
  KEY `staff_shifts_staff_id_foreign` (`staff_id`),
  CONSTRAINT `staff_shifts_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- HİZMETLER
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `services` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `shop_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_min` int NOT NULL DEFAULT 30,
  `category` varchar(100) DEFAULT NULL,
  `gender` enum('male','female','both') DEFAULT 'both',
  `is_active` tinyint(1) DEFAULT 1,
  `sort_order` int DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `services_shop_id_foreign` (`shop_id`),
  CONSTRAINT `services_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- RANDEVULAR
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `shop_id` bigint UNSIGNED NOT NULL,
  `staff_id` bigint UNSIGNED DEFAULT NULL,
  `service_id` bigint UNSIGNED NOT NULL,
  `seat_id` bigint UNSIGNED DEFAULT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `end_time` time NOT NULL,
  `status` enum('pending','confirmed','in_progress','completed','cancelled','no_show') DEFAULT 'pending',
  `price` decimal(10,2) NOT NULL,
  `source` enum('app','web','walkin','phone','qr') DEFAULT 'app',
  `customer_name` varchar(255) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` enum('customer','shop','system') DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `appointments_user_id_foreign` (`user_id`),
  KEY `appointments_shop_id_foreign` (`shop_id`),
  KEY `appointments_staff_id_foreign` (`staff_id`),
  KEY `appointments_service_id_foreign` (`service_id`),
  KEY `appointments_seat_id_foreign` (`seat_id`),
  CONSTRAINT `appointments_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `appointments_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`),
  CONSTRAINT `appointments_staff_id_foreign` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE SET NULL,
  CONSTRAINT `appointments_service_id_foreign` FOREIGN KEY (`service_id`) REFERENCES `services` (`id`),
  CONSTRAINT `appointments_seat_id_foreign` FOREIGN KEY (`seat_id`) REFERENCES `seats` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- seats tablosuna appointment foreign key (circular ref sonra)
ALTER TABLE `seats`
  ADD CONSTRAINT `seats_current_appointment_id_foreign`
  FOREIGN KEY (`current_appointment_id`) REFERENCES `appointments` (`id`) ON DELETE SET NULL;

-- -----------------------------------------------
-- YORUMLAR
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` bigint UNSIGNED NOT NULL,
  `shop_id` bigint UNSIGNED NOT NULL,
  `appointment_id` bigint UNSIGNED NOT NULL,
  `rating` tinyint NOT NULL,
  `comment` text DEFAULT NULL,
  `reply` text DEFAULT NULL,
  `replied_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `reviews_user_id_foreign` (`user_id`),
  KEY `reviews_shop_id_foreign` (`shop_id`),
  KEY `reviews_appointment_id_foreign` (`appointment_id`),
  CONSTRAINT `reviews_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `reviews_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`),
  CONSTRAINT `reviews_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- FAVORİLER
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `favorites` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` bigint UNSIGNED NOT NULL,
  `shop_id` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `favorites_user_id_shop_id_unique` (`user_id`,`shop_id`),
  KEY `favorites_shop_id_foreign` (`shop_id`),
  CONSTRAINT `favorites_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `favorites_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- BİLDİRİMLER
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `target_type` enum('user','shop','staff') NOT NULL,
  `target_id` bigint UNSIGNED NOT NULL,
  `channel` enum('push','sms','whatsapp','email') DEFAULT 'push',
  `type` varchar(100) NOT NULL,
  `title` varchar(255) NOT NULL,
  `body` text NOT NULL,
  `data` json DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `sent_at` timestamp NULL DEFAULT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- BİLDİRİM AYARLARI
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `notification_settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `shop_id` bigint UNSIGNED NOT NULL,
  `morning_summary_enabled` tinyint(1) DEFAULT 1,
  `morning_summary_time` time DEFAULT '08:00:00',
  `evening_summary_enabled` tinyint(1) DEFAULT 1,
  `weekly_report_enabled` tinyint(1) DEFAULT 1,
  `sms_enabled` tinyint(1) DEFAULT 1,
  `reminder_hours` int DEFAULT 2,
  `no_show_auto_mark_minutes` int DEFAULT 30,
  `review_notification_enabled` tinyint(1) DEFAULT 1,
  `campaign_notification_enabled` tinyint(1) DEFAULT 1,
  KEY `notification_settings_shop_id_foreign` (`shop_id`),
  CONSTRAINT `notification_settings_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- DÜKKAN AYARLARI
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_settings` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `shop_id` bigint UNSIGNED NOT NULL,
  `slot_interval` int DEFAULT 30,
  `min_advance_hours` int DEFAULT 2,
  `max_advance_days` int DEFAULT 14,
  `cancel_hours` int DEFAULT 2,
  `auto_approve` tinyint(1) DEFAULT 1,
  `deposit_required` tinyint(1) DEFAULT 0,
  `deposit_amount` decimal(10,2) DEFAULT 50.00,
  `deposit_percentage` decimal(4,2) DEFAULT NULL,
  `max_daily_per_user` int DEFAULT 1,
  `walkin_enabled` tinyint(1) DEFAULT 1,
  UNIQUE KEY `shop_settings_shop_id_unique` (`shop_id`),
  CONSTRAINT `shop_settings_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- SIRA DURUMU
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `queue_status` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `shop_id` bigint UNSIGNED NOT NULL,
  `current_waiting` int DEFAULT 0,
  `avg_wait_minutes` int DEFAULT 0,
  `is_full` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `queue_status_shop_id_unique` (`shop_id`),
  CONSTRAINT `queue_status_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- NO-SHOW KAYITLARI
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `no_show_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `user_id` bigint UNSIGNED NOT NULL,
  `appointment_id` bigint UNSIGNED NOT NULL,
  `total_count` int DEFAULT 1,
  `penalty_type` enum('warning','ban_24h','ban_7d','ban_30d','permanent') DEFAULT NULL,
  `penalty_until` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `no_show_logs_user_id_foreign` (`user_id`),
  KEY `no_show_logs_appointment_id_foreign` (`appointment_id`),
  CONSTRAINT `no_show_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `no_show_logs_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- ÖDEMELER
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `appointment_id` bigint UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `deposit_amount` decimal(10,2) DEFAULT 0.00,
  `method` enum('cash','card','online') DEFAULT 'cash',
  `status` enum('pending','paid','refunded','forfeited') DEFAULT 'pending',
  `iyzico_ref` varchar(255) DEFAULT NULL,
  `paid_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  KEY `payments_appointment_id_foreign` (`appointment_id`),
  CONSTRAINT `payments_appointment_id_foreign` FOREIGN KEY (`appointment_id`) REFERENCES `appointments` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- DÜKKAN GALERİ
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `shop_gallery` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `shop_id` bigint UNSIGNED NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `sort_order` int DEFAULT 0,
  `created_at` timestamp NULL DEFAULT NULL,
  KEY `shop_gallery_shop_id_foreign` (`shop_id`),
  CONSTRAINT `shop_gallery_shop_id_foreign` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- SANCTUM — Personal Access Tokens
-- -----------------------------------------------
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------
-- MİGRATION KAYITLARI (Laravel için)
-- -----------------------------------------------
INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2024_01_01_000001_create_users_table', 1),
('2024_01_01_000002_create_shops_table', 1),
('2024_01_01_000003_create_shop_hours_table', 1),
('2024_01_01_000004_create_staff_table', 1),
('2024_01_01_000005_create_seats_table', 1),
('2024_01_01_000006_create_staff_shifts_table', 1),
('2024_01_01_000007_create_services_table', 1),
('2024_01_01_000008_create_appointments_table', 1),
('2024_01_01_000009_create_reviews_table', 1),
('2024_01_01_000010_create_favorites_table', 1),
('2024_01_01_000011_create_notifications_table', 1),
('2024_01_01_000012_create_notification_settings_table', 1),
('2024_01_01_000013_create_shop_settings_table', 1),
('2024_01_01_000014_create_queue_status_table', 1),
('2024_01_01_000015_create_no_show_logs_table', 1),
('2024_01_01_000016_create_payments_table', 1),
('2024_01_01_000017_create_shop_gallery_table', 1),
('2024_01_01_000018_create_personal_access_tokens_table', 1),
('2024_01_01_000019_add_fcm_token_to_users', 1);

SET FOREIGN_KEY_CHECKS = 1;
