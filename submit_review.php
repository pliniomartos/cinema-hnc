<?php
session_start();

if (!isset($_SESSION['id'])) {
	require 'login_tools.php';
	load();
}

# Open database connection.
include 'includes/navbar.php';
require 'connect_db.php';

# Create the reviews table if it does not exist.
$query = "CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movie_id VARCHAR(20) NOT NULL,
    review_text TEXT NOT NULL,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    booking_id INT(11),
    likes BOOLEAN DEFAULT FALSE,
    dislikes BOOLEAN DEFAULT FALSE
)";
mysqli_query($link, $query);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$movie_id = mysqli_real_escape_string($link, $_POST['movie_id']);
	$review_text = mysqli_real_escape_string($link, $_POST['review_text']);
	$booking_id = mysqli_real_escape_string($link, $_POST['booking_id']);
	$user_id = $_SESSION['id'];

	$query = "INSERT INTO reviews (user_id, movie_id, review_text, review_date, booking_id) VALUES ('$user_id', '$movie_id', '$review_text', NOW(), '$booking_id')";
	$result = mysqli_query($link, $query);

	if ($result) {
		echo "Review submitted successfully.";
	} else {
		echo "Error: " . mysqli_error($link);
	}

	mysqli_close($link);
}
include 'includes/footer_element.php';
?>
