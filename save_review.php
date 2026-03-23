<?php
session_start();
# Open database connection.
require 'connect_db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_SESSION['id'])) {
        http_response_code(403);
        exit('Not authorised.');
    }
    $review_id = mysqli_real_escape_string($link, $_POST['review_id']);
    $rating = mysqli_real_escape_string($link, $_POST['rating']);
    $review_text = mysqli_real_escape_string($link, $_POST['review_text']);
    $user_id = (int)$_SESSION['id'];

    $query = "UPDATE reviews SET rating='$rating', review_text='$review_text' WHERE id='$review_id' AND user_id='$user_id'";
    $result = mysqli_query($link, $query);

    if ($result) {
        echo "Review updated successfully.";
    } else {
        echo "Error: " . mysqli_error($link);
    }

    mysqli_close($link);
}
?>
