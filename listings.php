<?php
include 'includes/navbar.php';
require 'connect_db.php';

echo '<div class="container"><div class="row">';

$q = "SELECT * FROM movie_listings ORDER BY `release` DESC";
$r = mysqli_query($link, $q);

if ($r && mysqli_num_rows($r) > 0) {
    while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
        echo '<div class="col-md-4 d-flex justify-content-center mb-3">
            <div class="card bg-black text-white" style="width: 18rem;">
              <img src="' . htmlspecialchars($row['img']) . '" class="card-img-top" alt="Movie Poster">
              <div class="card-body">
                <h5 class="card-title text-center">' . htmlspecialchars($row['movie_title']) . '</h5>
                <p class="card-text text-center">' . htmlspecialchars(substr($row['further_info'], 0, 120)) . '...</p>
              </div>
              <ul class="list-group list-group-flush">
                <li class="list-group-item"><p class="text-center">Release Date: ' . htmlspecialchars($row['release']) . '</p></li>
                <li class="list-group-item"><p class="text-center">Genre: ' . htmlspecialchars($row['genre']) . '</p></li>
                <li class="list-group-item text-center"><a class="btn btn-outline-orange0" href="movie.php?movie_id=' . urlencode($row['movie_id']) . '">View / Book</a></li>
              </ul>
            </div>
          </div>';
    }
} else {
    echo '<p>There are currently no movies in this table.</p>';
}

mysqli_close($link);
echo '</div></div>';
include 'includes/footer_element.php';
?>
