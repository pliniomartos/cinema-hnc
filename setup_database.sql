-- Cinema DB bootstrap for local WAMP
CREATE DATABASE IF NOT EXISTS `cinema_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cinema_db`;

CREATE TABLE IF NOT EXISTS `new_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(30) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_admin` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `movie_listings` (
  `movie_id` varchar(20) NOT NULL,
  `movie_title` varchar(100) NOT NULL,
  `genre` varchar(100) DEFAULT NULL,
  `age_rating` varchar(50) DEFAULT NULL,
  `show1` varchar(10) DEFAULT '10:00',
  `show2` varchar(10) DEFAULT '14:00',
  `show3` varchar(10) DEFAULT '18:00',
  `theatre` varchar(50) DEFAULT 'Main Theatre',
  `further_info` text,
  `release` date DEFAULT NULL,
  `img` varchar(255) DEFAULT NULL,
  `preview` varchar(300) DEFAULT NULL,
  `mov_price` decimal(5,2) DEFAULT 10.00,
  PRIMARY KEY (`movie_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `movie_booking` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `booking_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  KEY `idx_user_id` (`id`),
  CONSTRAINT `movie_booking_ibfk_1` FOREIGN KEY (`id`) REFERENCES `new_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `booking_content` (
  `content_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `movie_id` varchar(20) NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`content_id`),
  KEY `idx_booking_id` (`booking_id`),
  CONSTRAINT `booking_content_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `movie_booking` (`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `movie_id` varchar(20) NOT NULL,
  `review_text` text,
  `rating` int(11) DEFAULT NULL,
  `review_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `booking_id` int(11) DEFAULT NULL,
  `likes` tinyint(1) DEFAULT 0,
  `dislikes` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_movie_id` (`movie_id`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `new_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin login: admin@eccinema.com / admin123
INSERT INTO `new_users` (`username`,`email`,`password`,`is_admin`)
VALUES ('admin','admin@eccinema.com','240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9',1)
ON DUPLICATE KEY UPDATE `is_admin`=1;
