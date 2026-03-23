<?php
global $link;
$q = $_GET['q'];

# Open database connection.
require 'connect_db.php';

# Check if the connection was successful
if (!$link) {
	die('Could not connect: ' . mysqli_error($link));
}

# Prepare the SQL query to fetch the selected movie
$sql = "SELECT * FROM movie_listings WHERE movie_id = '$q'";
$result = mysqli_query($link, $sql);

# Check if the query returned any results
if (mysqli_num_rows($result) > 0) {
	while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
		echo '<div class="container my-5 bg-black" data-bs-theme="dark">
            <div class="row py-3">
                <div class="col-md-6 text-center">
                    <iframe class="rounded h-auto w-100" src="' . $row['preview'] . '"
                    frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                </div>
                <div class="col-md-6 text-white">
                    <h1 class="display-4 fw-bold text-orange0">' . $row['movie_title'] . '</h1>
                    <p class="lead">Release Date: ' . date('d/m/Y', strtotime($row['release'])) . '</p>
                    <p>Genre: ' . $row['genre'] . '</p>
                </div>
                <div class="col-md-6 text-white">
                    <img src="' . $row['age_rating'] . '" alt="Movie" width="50px">
                <p>' . $row['further_info'] . '</p>
                </div>
                <div class="col-md-6">
                    <h4 class="text-orange0">Show Times</h4>
                    <hr>
                    <div class="card bg-black border-0">
                        <div class="card-body btn-group btn-group-sm">
                            <h5 class="card-title">' . $row['theatre'] . '</h5>
                        </div>
                        <div class="btn-group btn-group-sm">
                          <a href="show1.php?movie_id=' . $row['movie_id'] . '" class="btn btn-sm btn-outline-orange0" role="button"> Book > ' . $row['show1'] . ' </a>
                          <a href="show2.php?movie_id=' . $row['movie_id'] . '" class="btn btn-sm btn-outline-orange0" role="button"> Book > ' . $row['show2'] . ' </a>
                          <a href="show3.php?movie_id=' . $row['movie_id'] . '" class="btn btn-sm btn-outline-orange0" role="button"> Book > ' . $row['show3'] . ' </a>
                        </div>
                    </div>
                    <hr>
                </div>
            </div>
        </div>';
	}
} else {
	echo '<p>No movie found with the given ID.</p>';
}

# Close the database connection
mysqli_close($link);
?>
<script>
    document.querySelectorAll('.btn-outline-orange0').forEach(function (element) {
        element.addEventListener('click', function (event) {
            event.preventDefault();
            window.location.href = this.href;
        });
    });
</script>