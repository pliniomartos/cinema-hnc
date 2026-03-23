<?php
# Open database connection.
require 'connect_db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$review_id = mysqli_real_escape_string($link, $_POST['review_id']);
	$query = "SELECT * FROM reviews WHERE id='$review_id'";
	$result = mysqli_query($link, $query);
	$review = mysqli_fetch_array($result, MYSQLI_ASSOC);

	if ($review) {
		echo '
        <div class="container mt-5">
            <h2>Update Your Review</h2>
            <form action="save_review.php" method="post">
                <input type="hidden" name="review_id" value="' . $review['id'] . '">
                <div class="form-group">
                    <label for="rating">Rating:</label>
                    <select class="form-control" id="rating" name="rating">
                        <option value="1"' . ($review['rating'] == 1 ? ' selected' : '') . '>1</option>
                        <option value="2"' . ($review['rating'] == 2 ? ' selected' : '') . '>2</option>
                        <option value="3"' . ($review['rating'] == 3 ? ' selected' : '') . '>3</option>
                        <option value="4"' . ($review['rating'] == 4 ? ' selected' : '') . '>4</option>
                        <option value="5"' . ($review['rating'] == 5 ? ' selected' : '') . '>5</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="review_text">Comment (optional):</label>
                    <textarea class="form-control" id="review_text" name="review_text" rows="3">' . htmlspecialchars($review['review_text']) . '</textarea>
                </div>
                <button type="submit" class="btn btn-primary">Save Review</button>
            </form>
        </div>';
	} else {
		echo 'Review not found.';
	}

	mysqli_close($link);
}
?>