<?php
require_once __DIR__ . '/../config.php';

$link = getDBConnection();

$q = "CREATE TABLE IF NOT EXISTS `movie_booking` (
  `booking_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `booking_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`booking_id`),
  KEY `idx_user_id` (`id`),
  CONSTRAINT `movie_booking_ibfk_1` FOREIGN KEY (`id`) REFERENCES `new_users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($link, $q)) {
    echo 'Table movie_booking ready.';
} else {
    echo 'Error creating table movie_booking: ' . mysqli_error($link);
}
?>
