<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit();
}

$link = getDBConnection();
$message = '';
$error = '';

/**
 * Fetch movie data from OMDB API
 */
function fetchMovieFromOMDB($imdb_id) {
    $api_key = OMDB_API_KEY;
    if (empty($api_key)) {
        return false;
    }

    $url = "https://www.omdbapi.com/?i=" . urlencode($imdb_id) . "&plot=full&apikey=" . urlencode($api_key);
    $response = false;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        curl_close($ch);
    }

    if ($response === false) {
        $context = stream_context_create([
            'http' => ['timeout' => 15]
        ]);
        $response = @file_get_contents($url, false, $context);
    }

    if ($response === false) {
        return false;
    }

    $data = json_decode($response, true);
    if (!$data || !isset($data['Response']) || $data['Response'] !== 'True') {
        return false;
    }

    return $data;
}

function getTrailerUrl($title) {
    $search_query = urlencode($title . ' official trailer');
    return 'https://www.youtube.com/results?search_query=' . $search_query;
}

/**
 * Try to fetch a proper YouTube trailer URL from TMDB using IMDb ID.
 * Falls back to YouTube search URL when not available.
 */
function fetchTrailerFromTMDB($imdb_id, $fallbackTitle = '') {
    $tmdbKey = defined('TMDB_API_KEY') ? TMDB_API_KEY : '';

    if (empty($tmdbKey) || empty($imdb_id)) {
        return $fallbackTitle ? getTrailerUrl($fallbackTitle) : false;
    }

    $findUrl = 'https://api.themoviedb.org/3/find/' . urlencode($imdb_id)
        . '?api_key=' . urlencode($tmdbKey)
        . '&external_source=imdb_id';

    $findResponse = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($findUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $findResponse = curl_exec($ch);
        curl_close($ch);
    }
    if ($findResponse === false) {
        $context = stream_context_create(['http' => ['timeout' => 15]]);
        $findResponse = @file_get_contents($findUrl, false, $context);
    }
    if ($findResponse === false) return '';

    $findData = json_decode($findResponse, true);
    if (!is_array($findData) || empty($findData['movie_results'][0]['id'])) return '';

    $tmdbMovieId = (int)$findData['movie_results'][0]['id'];
    $videosUrl = 'https://api.themoviedb.org/3/movie/' . $tmdbMovieId
        . '/videos?api_key=' . urlencode($tmdbKey) . '&language=en-US';

    $videosResponse = false;
    if (function_exists('curl_init')) {
        $ch = curl_init($videosUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $videosResponse = curl_exec($ch);
        curl_close($ch);
    }
    if ($videosResponse === false) {
        $context = stream_context_create(['http' => ['timeout' => 15]]);
        $videosResponse = @file_get_contents($videosUrl, false, $context);
    }
    if ($videosResponse === false) return '';

    $videosData = json_decode($videosResponse, true);
    if (!is_array($videosData) || empty($videosData['results']) || !is_array($videosData['results'])) return '';

    $yt = [];
    foreach ($videosData['results'] as $video) {
        if (($video['site'] ?? '') === 'YouTube' && !empty($video['key'])) {
            $yt[] = $video;
        }
    }
    if (empty($yt)) return '';

    usort($yt, function ($a, $b) {
        $score = function ($v) {
            $s = 0;
            $lang = strtolower((string)($v['iso_639_1'] ?? ''));
            $type = strtolower((string)($v['type'] ?? ''));
            $name = strtolower((string)($v['name'] ?? ''));
            if ($lang === 'en') $s += 100;
            if ($type === 'trailer') $s += 60;
            if (!empty($v['official'])) $s += 25;
            if (strpos($name, 'official') !== false) $s += 10;
            if (strpos($name, 'trailer') !== false) $s += 8;
            if (in_array($lang, ['hi','te','ta','ml','bn'], true)) $s -= 20;
            return $s;
        };
        return $score($b) <=> $score($a);
    });

    return 'https://www.youtube.com/watch?v=' . urlencode($yt[0]['key']);
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_movie']) && validateCsrfToken($_POST['csrf_token'] ?? '')) {
    $movie_id = mysqli_real_escape_string($link, $_POST['delete_movie']);
    $stmt = mysqli_prepare($link, "DELETE FROM movie_listings WHERE movie_id = ?");
    mysqli_stmt_bind_param($stmt, 's', $movie_id);
    if (mysqli_stmt_execute($stmt)) {
        $message = 'Movie deleted successfully.';
    } else {
        $error = 'Error deleting movie.';
    }
    mysqli_stmt_close($stmt);
}

// Handle add/edit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        die('Security validation failed.');
    }
    $movie_id = mysqli_real_escape_string($link, $_POST['movie_id'] ?? '');
    $show1 = mysqli_real_escape_string($link, $_POST['show1'] ?? '10:00');
    $show2 = mysqli_real_escape_string($link, $_POST['show2'] ?? '14:00');
    $show3 = mysqli_real_escape_string($link, $_POST['show3'] ?? '18:00');
    $mov_price = floatval($_POST['mov_price'] ?? 10.00);
    $theatre = mysqli_real_escape_string($link, $_POST['theatre'] ?? 'Main Theatre');

    if (empty($movie_id) || !preg_match('/^tt\d+$/i', $movie_id)) {
        $error = 'Invalid IMDb ID format. It should start with "tt" followed by numbers (e.g., tt1375666).';
    } else {
        $omdb_data = fetchMovieFromOMDB($movie_id);

        if (!$omdb_data) {
            $error = 'Could not fetch movie data from OMDB. Please check the IMDb ID and try again.';
        } else {
            $movie_title = mysqli_real_escape_string($link, $omdb_data['Title'] ?? '');
            $genre = mysqli_real_escape_string($link, $omdb_data['Genre'] ?? '');
            $age_rating = mysqli_real_escape_string($link, $omdb_data['Rated'] ?? '');
            $further_info = mysqli_real_escape_string($link, $omdb_data['Plot'] ?? '');
            $release_date = mysqli_real_escape_string($link, $omdb_data['Released'] ?? '');
            $img = mysqli_real_escape_string($link, $omdb_data['Poster'] ?? '');

            if (!empty($release_date) && $release_date !== 'N/A') {
                $date_obj = DateTime::createFromFormat('d M Y', $release_date);
                if ($date_obj) {
                    $release_date = $date_obj->format('Y-m-d');
                } else {
                    $release_date = '';
                }
            } else {
                $release_date = '';
            }

            $preview = mysqli_real_escape_string($link, fetchTrailerFromTMDB($movie_id, $movie_title));

            if (!empty($_POST['edit_mode'])) {
                $stmt = mysqli_prepare($link, "UPDATE movie_listings SET movie_title=?, genre=?, age_rating=?, show1=?, show2=?, show3=?, theatre=?, further_info=?, `release`=?, img=?, preview=?, mov_price=? WHERE movie_id=?");
                mysqli_stmt_bind_param($stmt, 'sssssssssssds', $movie_title, $genre, $age_rating, $show1, $show2, $show3, $theatre, $further_info, $release_date, $img, $preview, $mov_price, $movie_id);
            } else {
                $check_stmt = mysqli_prepare($link, "SELECT movie_id FROM movie_listings WHERE movie_id = ?");
                mysqli_stmt_bind_param($check_stmt, 's', $movie_id);
                mysqli_stmt_execute($check_stmt);
                mysqli_stmt_store_result($check_stmt);

                if (mysqli_stmt_num_rows($check_stmt) > 0) {
                    $error = 'A movie with this IMDb ID already exists.';
                    mysqli_stmt_close($check_stmt);
                } else {
                    mysqli_stmt_close($check_stmt);
                    $stmt = mysqli_prepare($link, "INSERT INTO movie_listings (movie_id, movie_title, genre, age_rating, show1, show2, show3, theatre, further_info, `release`, img, preview, mov_price) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    mysqli_stmt_bind_param($stmt, 'ssssssssssssd', $movie_id, $movie_title, $genre, $age_rating, $show1, $show2, $show3, $theatre, $further_info, $release_date, $img, $preview, $mov_price);
                }
            }

            if (empty($error) && isset($stmt)) {
                if (mysqli_stmt_execute($stmt)) {
                    $message = !empty($_POST['edit_mode']) ? 'Movie updated successfully.' : 'Movie added successfully.';
                } else {
                    $error = 'Error saving movie: ' . mysqli_stmt_error($stmt);
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

$result = mysqli_query($link, "SELECT * FROM movie_listings ORDER BY `release` DESC");
$movies = [];
while ($row = mysqli_fetch_assoc($result)) {
    $movies[] = $row;
}

$editMovie = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $movie_id = mysqli_real_escape_string($link, $_GET['edit']);
    $stmt = mysqli_prepare($link, "SELECT * FROM movie_listings WHERE movie_id = ?");
    mysqli_stmt_bind_param($stmt, 's', $movie_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $editMovie = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

mysqli_close($link);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5">
    <h1 class="text-orange0 mb-4">Manage Movies</h1>

    <?php if ($message): ?>
        <div class="alert alert-success"><?php echo e($message); ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo e($error); ?></div>
    <?php endif; ?>

    <div class="card bg-black border-orange0 mb-4">
        <div class="card-header bg-orange0 text-black">
            <h4 class="mb-0"><?php echo $editMovie ? 'Edit Movie' : 'Add New Movie'; ?></h4>
        </div>
        <div class="card-body">
            <form method="post" action="admin_movies.php" id="movieForm">
                <input type="hidden" name="csrf_token" value="<?php echo e(getCsrfToken()); ?>">
                <?php if ($editMovie): ?>
                    <input type="hidden" name="edit_mode" value="1">
                <?php endif; ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-white">IMDb ID <span class="text-muted">(e.g., tt1375666)</span></label>
                        <input type="text" name="movie_id" id="movie_id" class="form-control"
                               value="<?php echo e($editMovie['movie_id'] ?? ''); ?>"
                               <?php echo $editMovie ? 'readonly' : 'required'; ?>
                               placeholder="tt1375666" pattern="^tt\d+$"
                               title="IMDb ID should start with 'tt' followed by numbers">
                        <div class="form-text text-muted">Enter the IMDb ID and movie details will be fetched automatically.</div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-white">Price (£)</label>
                        <input type="number" step="0.01" name="mov_price" class="form-control"
                               value="<?php echo e($editMovie['mov_price'] ?? '10.00'); ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3"><label class="form-label text-white">Show 1</label><input type="time" name="show1" class="form-control" value="<?php echo e($editMovie['show1'] ?? '10:00'); ?>" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label text-white">Show 2</label><input type="time" name="show2" class="form-control" value="<?php echo e($editMovie['show2'] ?? '14:00'); ?>" required></div>
                    <div class="col-md-4 mb-3"><label class="form-label text-white">Show 3</label><input type="time" name="show3" class="form-control" value="<?php echo e($editMovie['show3'] ?? '18:00'); ?>" required></div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-white">Theatre</label>
                    <input type="text" name="theatre" class="form-control" value="<?php echo e($editMovie['theatre'] ?? 'Main Theatre'); ?>">
                </div>

                <button type="submit" class="btn btn-outline-orange0"><?php echo $editMovie ? 'Update Movie' : 'Add Movie'; ?></button>
                <?php if ($editMovie): ?><a href="admin_movies.php" class="btn btn-outline-secondary">Cancel</a><?php endif; ?>
            </form>
        </div>
    </div>

    <div class="card bg-black border-orange0">
        <div class="card-header bg-orange0 text-black"><h4 class="mb-0">All Movies (<?php echo count($movies); ?>)</h4></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-striped">
                    <thead><tr><th>ID</th><th>Title</th><th>Genre</th><th>Rating</th><th>Price</th><th>Shows</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($movies as $movie): ?>
                        <tr>
                            <td><?php echo e($movie['movie_id']); ?></td>
                            <td><?php echo e($movie['movie_title']); ?></td>
                            <td><?php echo e($movie['genre']); ?></td>
                            <td><?php echo e($movie['age_rating']); ?></td>
                            <td>£<?php echo e($movie['mov_price']); ?></td>
                            <td><?php echo e($movie['show1']) . ', ' . e($movie['show2']) . ', ' . e($movie['show3']); ?></td>
                            <td>
                                <a href="admin_movies.php?edit=<?php echo e($movie['movie_id']); ?>" class="btn btn-sm btn-outline-yellow-1">Edit</a>
                                <form method="POST" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this movie?')">
                                    <input type="hidden" name="csrf_token" value="<?php echo e(getCsrfToken()); ?>">
                                    <input type="hidden" name="delete_movie" value="<?php echo e($movie['movie_id']); ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-red0">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer_element.php'; ?>




