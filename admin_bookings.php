<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit();
}

$link = getDBConnection();

// Get all bookings with user and movie details
$query = "SELECT mb.booking_id, mb.total, mb.booking_date, 
    nu.id as user_id, nu.username, nu.email,
    GROUP_CONCAT(DISTINCT ml.movie_title SEPARATOR ', ') as movies
    FROM movie_booking mb 
    JOIN new_users nu ON mb.id = nu.id 
    LEFT JOIN booking_content bc ON mb.booking_id = bc.booking_id
    LEFT JOIN movie_listings ml ON bc.movie_id = ml.movie_id
    GROUP BY mb.booking_id
    ORDER BY mb.booking_date DESC";

$result = mysqli_query($link, $query);
$bookings = [];
while ($row = mysqli_fetch_assoc($result)) {
    $bookings[] = $row;
}

mysqli_close($link);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5">
    <h1 class="text-orange0 mb-4">All Bookings</h1>
    
    <div class="card bg-black border-orange0">
        <div class="card-header bg-orange0 text-black">
            <h4 class="mb-0">Bookings (<?php echo count($bookings); ?>)</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-dark table-striped">
                    <thead>
                        <tr>
                            <th>Booking Ref</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Movies</th>
                            <th>Total</th>
                            <th>Booking For</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                        <tr>
                            <td>#EC1000<?php echo $booking['booking_id']; ?></td>
                            <td><?php echo e($booking['username']); ?></td>
                            <td><?php echo e($booking['email']); ?></td>
                            <td><?php echo e($booking['movies'] ?? 'N/A'); ?></td>
                            <td>£<?php echo number_format($booking['total'], 2); ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($booking['booking_date'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer_element.php'; ?>


