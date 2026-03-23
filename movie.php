<?php
session_start();
include 'includes/navbar.php';
require 'connect_db.php';

$movie_id = isset($_GET['movie_id']) ? mysqli_real_escape_string($link, $_GET['movie_id']) : '';
$reviewErrors = [];
$reviewSuccess = '';

function resolveAgeRatingImage($ageRatingRaw) {
    $value = trim((string)$ageRatingRaw);

    if ($value === '') {
        return ['img/pg.JPG', 'PG'];
    }

    $normalizedPath = str_replace('\\', '/', $value);
    if (preg_match('#^(https?://|img/)#i', $normalizedPath)) {
        return [$normalizedPath, $value];
    }

    $ratingMap = [
        'U' => 'img/u.JPG',
        'PG' => 'img/pg.JPG',
        '12' => 'img/12.JPG',
        '12A' => 'img/12a.JPG',
        '15' => 'img/15.JPG',
        '18' => 'img/18.JPG'
    ];

    $key = strtoupper(str_replace(' ', '', $value));
    if (isset($ratingMap[$key])) {
        return [$ratingMap[$key], $value];
    }

    return ['img/pg.JPG', $value];
}

function resolveTrailerUrls($previewRaw, $movieTitle) {
    $preview = trim((string)$previewRaw);
    $fallbackSearch = 'https://www.youtube.com/results?search_query=' . urlencode(trim((string)$movieTitle) . ' official trailer');

    if ($preview === '') {
        return ['', $fallbackSearch];
    }

    if (preg_match('#^https?://#i', $preview) !== 1) {
        return ['', $fallbackSearch];
    }

    // youtu.be/<id>
    if (preg_match('#https?://(?:www\.)?youtu\.be/([A-Za-z0-9_-]{6,})#i', $preview, $m)) {
        $id = $m[1];
        return ['https://www.youtube.com/embed/' . $id, 'https://www.youtube.com/watch?v=' . $id];
    }

    // youtube watch?v=<id>
    if (preg_match('#https?://(?:www\.)?youtube\.com/watch\?[^\s]*v=([A-Za-z0-9_-]{6,})#i', $preview, $m)) {
        $id = $m[1];
        return ['https://www.youtube.com/embed/' . $id, 'https://www.youtube.com/watch?v=' . $id];
    }

    // youtube embed/<id>
    if (preg_match('#https?://(?:www\.)?youtube\.com/embed/([A-Za-z0-9_-]{6,})#i', $preview, $m)) {
        $id = $m[1];
        return ['https://www.youtube.com/embed/' . $id, 'https://www.youtube.com/watch?v=' . $id];
    }

    // If this is a YouTube search or any non-embeddable URL, show as link only
    return ['', $preview];
}

if (!$link) {
    die('Could not connect: ' . mysqli_error($link));
}

$createReviewsTable = "CREATE TABLE IF NOT EXISTS reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movie_id VARCHAR(20) NOT NULL,
    rating TINYINT NOT NULL DEFAULT 5,
    review_text TEXT NOT NULL,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    booking_id INT(11) NULL,
    likes BOOLEAN DEFAULT FALSE,
    dislikes BOOLEAN DEFAULT FALSE,
    INDEX idx_movie_id (movie_id),
    INDEX idx_user_id (user_id)
)";
mysqli_query($link, $createReviewsTable);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_submit'])) {
    if (!isset($_SESSION['id'])) {
        $reviewErrors[] = 'Please log in to submit a review.';
    } else {
        $postedMovieId = isset($_POST['movie_id']) ? mysqli_real_escape_string($link, $_POST['movie_id']) : '';
        $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : 5;
        $reviewText = trim($_POST['review_text'] ?? '');

        if ($postedMovieId !== $movie_id) {
            $reviewErrors[] = 'Invalid movie selection.';
        }
        if ($rating < 1 || $rating > 5) {
            $reviewErrors[] = 'Rating must be between 1 and 5.';
        }
        if ($reviewText === '') {
            $reviewErrors[] = 'Please write your review.';
        }

        if (empty($reviewErrors)) {
            $userId = (int)$_SESSION['id'];
            $safeReviewText = mysqli_real_escape_string($link, $reviewText);

            $checkSql = "SELECT id FROM reviews WHERE user_id = $userId AND movie_id = '$movie_id' LIMIT 1";
            $checkRes = mysqli_query($link, $checkSql);

            if ($checkRes && mysqli_num_rows($checkRes) > 0) {
                $existing = mysqli_fetch_assoc($checkRes);
                $reviewId = (int)$existing['id'];
                $updateSql = "UPDATE reviews SET rating = $rating, review_text = '$safeReviewText', review_date = NOW() WHERE id = $reviewId";
                if (mysqli_query($link, $updateSql)) {
                    $reviewSuccess = 'Your review was updated.';
                } else {
                    $reviewErrors[] = 'Could not update your review. Please try again.';
                }
            } else {
                $insertSql = "INSERT INTO reviews (user_id, movie_id, rating, review_text, review_date) VALUES ($userId, '$movie_id', $rating, '$safeReviewText', NOW())";
                if (mysqli_query($link, $insertSql)) {
                    $reviewSuccess = 'Thanks! Your review was submitted.';
                } else {
                    $reviewErrors[] = 'Could not submit your review. Please try again.';
                }
            }
        }
    }
}

$q = "SELECT * FROM movie_listings WHERE movie_id = '$movie_id'";
$r = mysqli_query($link, $q);

if ($r && mysqli_num_rows($r) == 1) {
    $row = mysqli_fetch_array($r, MYSQLI_ASSOC);
    list($ageRatingImage, $ageRatingLabel) = resolveAgeRatingImage($row['age_rating'] ?? '');
    list($trailerEmbedUrl, $trailerWatchUrl) = resolveTrailerUrls($row['preview'] ?? '', $row['movie_title'] ?? '');


    $trailerHtml = '';
    if ($trailerEmbedUrl !== '') {
        $trailerHtml = '<iframe class="rounded h-auto w-100" src="' . htmlspecialchars($trailerEmbedUrl) . '" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>';
    } else {
        $poster = !empty($row['img']) ? $row['img'] : 'img/comingsoon.gif';
        $trailerHtml = '<img class="rounded h-auto w-100 mb-2" src="' . htmlspecialchars($poster) . '" alt="Movie poster">';
        $trailerHtml .= '<a class="btn btn-sm btn-outline-orange0" target="_blank" rel="noopener noreferrer" href="' . htmlspecialchars($trailerWatchUrl) . '">Watch trailer on YouTube</a>';
    }

    $today = new DateTime('today');
    $releaseDateObj = !empty($row['release']) ? DateTime::createFromFormat('Y-m-d', $row['release']) : false;
    $bookingControlsHtml = '';

    if ($releaseDateObj) {
        $bookingMinObj = clone $releaseDateObj;
        if ($bookingMinObj < $today) {
            $bookingMinObj = clone $today;
        }

        $bookingMaxObj = clone $releaseDateObj;
        $bookingMaxObj->modify('+2 months');

        if ($bookingMinObj <= $bookingMaxObj) {
            $bookingMin = $bookingMinObj->format('Y-m-d');
            $bookingMax = $bookingMaxObj->format('Y-m-d');
            $defaultBooking = $bookingMin;
            $movieIdEsc = urlencode($row['movie_id']);

            $bookingControlsHtml .= '<div class="mb-3">';
            $bookingControlsHtml .= '<label for="booking_date" class="form-label text-white">Choose booking date</label>';
            $bookingControlsHtml .= '<input type="date" id="booking_date" class="form-control form-control-sm" min="' . htmlspecialchars($bookingMin) . '" max="' . htmlspecialchars($bookingMax) . '" value="' . htmlspecialchars($defaultBooking) . '">';
            $bookingControlsHtml .= '<small class="text-muted">Available from ' . htmlspecialchars($bookingMinObj->format('d/m/Y')) . ' to ' . htmlspecialchars($bookingMaxObj->format('d/m/Y')) . '</small>';
            $bookingControlsHtml .= '</div>';

            $bookingControlsHtml .= '<div class="btn-group btn-group-sm" id="booking-buttons">';
            $bookingControlsHtml .= '<a data-show="show1.php" href="show1.php?movie_id=' . $movieIdEsc . '&booking_date=' . $defaultBooking . '" class="btn btn-sm btn-outline-orange0" role="button"> Book > ' . htmlspecialchars($row['show1']) . ' </a>';
            $bookingControlsHtml .= '<a data-show="show2.php" href="show2.php?movie_id=' . $movieIdEsc . '&booking_date=' . $defaultBooking . '" class="btn btn-sm btn-outline-orange0" role="button"> Book > ' . htmlspecialchars($row['show2']) . ' </a>';
            $bookingControlsHtml .= '<a data-show="show3.php" href="show3.php?movie_id=' . $movieIdEsc . '&booking_date=' . $defaultBooking . '" class="btn btn-sm btn-outline-orange0" role="button"> Book > ' . htmlspecialchars($row['show3']) . ' </a>';
            $bookingControlsHtml .= '</div>';
        } else {
            $bookingControlsHtml = '<div class="alert alert-warning">Booking is closed for this movie (older than 2 months from release).</div>';
        }
    } else {
        $bookingControlsHtml = '<div class="alert alert-warning">Release date unavailable for this movie.</div>';
    }

    echo '
<div class="container d-flex mt-5 sticky-top" style="width: fit-content;"><h1 class="text-center fw-bolder text-orange0">Movie</h1></div>
    <div class="container my-5 bg-black" data-bs-theme="dark">
        <div class="row py-3">
            <div class="col-md-6 text-center">' . $trailerHtml . '</div>
            <div class="col-md-6 text-white">
                <h1 class="display-4 fw-bold text-orange0">' . htmlspecialchars($row['movie_title']) . '</h1>
                <p class="lead">Release Date: ' . date('d/m/Y', strtotime($row['release'])) . '</p>
                <p>Genre: ' . htmlspecialchars($row['genre']) . '</p>
            </div>
            <div class="col-md-6 text-white">
                <img src="' . htmlspecialchars($ageRatingImage) . '" alt="Age rating ' . htmlspecialchars($ageRatingLabel) . '" width="50px">
                <p>' . htmlspecialchars($row['further_info']) . '</p>
            </div>
            <div class="col-md-6">
                <h4 class="text-orange0">Show Times</h4>
                <hr>
                <div class="card bg-black">
                    <div class="card-body btn-group btn-group-sm">
                        <h5 class="card-title">' . htmlspecialchars($row['theatre']) . '</h5>
                    </div>
                    ' . $bookingControlsHtml . '
                </div>
                <hr>
            </div>
        </div>
    </div>';

    echo '<div class="container mb-4" data-bs-theme="dark">';
    echo '<div class="card bg-black text-white"><div class="card-body">';
    echo '<h4 class="text-orange0">User Reviews</h4>';

    if (!empty($reviewErrors)) {
        echo '<div class="alert alert-danger">';
        foreach ($reviewErrors as $error) {
            echo '<div>' . htmlspecialchars($error) . '</div>';
        }
        echo '</div>';
    }

    if ($reviewSuccess !== '') {
        echo '<div class="alert alert-success">' . htmlspecialchars($reviewSuccess) . '</div>';
    }

    if (isset($_SESSION['id'])) {
        echo '
        <form method="post" action="movie.php?movie_id=' . urlencode($movie_id) . '">
            <input type="hidden" name="movie_id" value="' . htmlspecialchars($movie_id) . '">
            <div class="mb-2">
                <label for="rating" class="form-label">Your rating</label>
                <select class="form-select" name="rating" id="rating" required>
                    <option value="5">5 - Excellent</option>
                    <option value="4">4 - Very good</option>
                    <option value="3">3 - Good</option>
                    <option value="2">2 - Fair</option>
                    <option value="1">1 - Poor</option>
                </select>
            </div>
            <div class="mb-2">
                <label for="review_text" class="form-label">Your review</label>
                <textarea class="form-control" id="review_text" name="review_text" rows="3" required></textarea>
            </div>
            <button type="submit" name="review_submit" class="btn btn-sm btn-outline-orange0">Submit Review</button>
        </form>
        <hr>';
    } else {
        echo '<p><a href="login.php" class="link-orange0">Log in</a> to write a review.</p><hr>';
    }

    $reviewsSql = "SELECT r.id, r.rating, r.review_text, r.review_date, u.username
                   FROM reviews r
                   LEFT JOIN new_users u ON u.id = r.user_id
                   WHERE r.movie_id = '$movie_id'
                   ORDER BY r.review_date DESC";
    $reviewsRes = mysqli_query($link, $reviewsSql);

    if ($reviewsRes && mysqli_num_rows($reviewsRes) > 0) {
        while ($review = mysqli_fetch_assoc($reviewsRes)) {
            $stars = str_repeat('★', (int)$review['rating']) . str_repeat('☆', 5 - (int)$review['rating']);
            echo '<div class="mb-3 p-3 border rounded">';
            echo '<div class="d-flex justify-content-between">';
            echo '<strong>' . htmlspecialchars($review['username'] ?? 'Anonymous') . '</strong>';
            echo '<small>' . htmlspecialchars(date('d/m/Y H:i', strtotime($review['review_date']))) . '</small>';
            echo '</div>';
            echo '<div class="text-warning">' . $stars . '</div>';
            echo '<div>' . nl2br(htmlspecialchars($review['review_text'])) . '</div>';
            echo '</div>';
        }
    } else {
        echo '<p class="text-muted">No reviews yet. Be the first to review this movie.</p>';
    }

    echo '</div></div></div>';
} else {
    echo '<p>No movie found with the given ID.</p>';
}

mysqli_close($link);
?>
<script>
(function () {
    var bookingDate = document.getElementById('booking_date');
    var bookingButtons = document.getElementById('booking-buttons');
    if (!bookingDate || !bookingButtons) return;

    function updateBookingLinks() {
        var date = encodeURIComponent(bookingDate.value);
        var links = bookingButtons.querySelectorAll('a[data-show]');
        links.forEach(function (a) {
            var href = a.getAttribute('href') || '';
            if (href.indexOf('booking_date=') !== -1) {
                href = href.replace(/([?&])booking_date=[^&]*/i, '$1booking_date=' + date);
            } else {
                href += (href.indexOf('?') === -1 ? '?' : '&') + 'booking_date=' + date;
            }
            a.setAttribute('href', href);
        });
    }

    bookingDate.addEventListener('change', updateBookingLinks);
    updateBookingLinks();
})();
</script>
<?php include 'includes/footer_element.php'; ?>






