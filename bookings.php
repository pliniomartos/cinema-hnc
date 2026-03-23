<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

global $link;
include 'includes/navbar.php';

# Open database connection.
require 'connect_db.php';


# Prepare the SQL query.
$q = "SELECT mb.booking_id, mb.total, mb.booking_date, ml.movie_title, ml.img, bc.movie_id
     FROM movie_booking mb
     JOIN booking_content bc ON mb.booking_id = bc.booking_id
     LEFT JOIN movie_listings ml ON bc.movie_id = ml.movie_id
     WHERE mb.id = ?
     ORDER BY mb.booking_date DESC";

# Prepare the statement.
$stmt = mysqli_prepare($link, $q);

# Check if the statement was prepared successfully.
if ($stmt === false) {
	die('MySQL prepare statement error: ' . mysqli_error($link));
}

# Bind the session ID to the query.
mysqli_stmt_bind_param($stmt, 'i', $_SESSION['id']);

# Execute the statement.
mysqli_stmt_execute($stmt);

# Bind result variables.
mysqli_stmt_bind_result($stmt, $booking_id, $total, $booking_date, $movie_title, $img, $movie_id);

# Fetch the results.
echo '
<div class="container mt-5 sticky-top " style="width: fit-content;"><h1 class="text-center text-orange0">My Bookings</h1></div>
<div class="container"><p class="text-center text-orange0">View your booking history below.</p></div>
	</div>
<div class="container mt-5">
	<div class="row">';  // Open the row.
while (mysqli_stmt_fetch($stmt)) {
	echo '
	<div class="col-lg-4">
    <div class="card mb-3 bg-transparent" data-bs-theme="dark">
	        <div class="row g-0">
	            <div class="col-4 bg-black">
	                <img src="' . htmlspecialchars($img) . '" class="img-fluid rounded-start" alt="Movie Image">
	            </div>
	            <div class="col-8">
        <div class="card-body">
            <h5 class="card-title text-orange0">Booking Reference: #EC1000' . $booking_id . '</h5>';
	if ($movie_id == '0') {
		echo '<p class="card-text text-bg-red0">Error: Movie ID is missing for this booking.</p>';
	} else {
		echo '<p class="card-text text-bg-orange0">' . htmlspecialchars($movie_title) . '</p>';
	}
	echo '
            <p class="card-text">Total Paid: &pound ' . $total . '</p>
            <p class="card-text">Booking Date: ' . date('d/m/Y', strtotime($booking_date)) . '</p>
	                </div>
	            </div>
        </div>
    </div>
    </div>';
}
echo '</div></div></div>';  // Close the column and row divs.

# Close the statement.
mysqli_stmt_close($stmt);

# Close database connection.
mysqli_close($link);

include 'includes/footer_element.php';
?>