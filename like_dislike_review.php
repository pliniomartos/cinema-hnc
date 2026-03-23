<?php
# Open database connection.
require 'connect_db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $review_id = mysqli_real_escape_string($link, $_POST['review_id']);
    $action = mysqli_real_escape_string($link, $_POST['action']);

    if ($action == 'like') {
        $query = "UPDATE reviews SET likes = TRUE, dislikes = FALSE WHERE id='$review_id'";
    } elseif ($action == 'dislike') {
        $query = "UPDATE reviews SET likes = FALSE, dislikes = TRUE WHERE id='$review_id'";
    }

    $result = mysqli_query($link, $query);

    if ($result) {
        echo "Action successful.";
    } else {
        echo "Error: " . mysqli_error($link);
    }

    mysqli_close($link);
}
?>