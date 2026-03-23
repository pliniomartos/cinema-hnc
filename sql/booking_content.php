<?php
require_once __DIR__ . '/../config.php';

$link = getDBConnection();

$q = "CREATE TABLE IF NOT EXISTS `booking_content` (
  `content_id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `movie_id` varchar(20) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  PRIMARY KEY (`content_id`),
  KEY `idx_booking_id` (`booking_id`),
  CONSTRAINT `booking_content_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `movie_booking` (`booking_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (mysqli_query($link, $q)) {
    echo 'Table booking_content ready.';
} else {
    echo 'Error creating table booking_content: ' . mysqli_error($link);
}
?>
