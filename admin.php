<?php
require_once 'config.php';

// Check if user is logged in and is admin
if (!isset($_SESSION['id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit();
}

$link = getDBConnection();

// Get statistics
$stats = [];

// Total users
$result = mysqli_query($link, "SELECT COUNT(*) as count FROM new_users");
$stats['users'] = mysqli_fetch_assoc($result)['count'];

// Total movies
$result = mysqli_query($link, "SELECT COUNT(*) as count FROM movie_listings");
$stats['movies'] = mysqli_fetch_assoc($result)['count'];

// Total bookings
$result = mysqli_query($link, "SELECT COUNT(*) as count FROM movie_booking");
$stats['bookings'] = mysqli_fetch_assoc($result)['count'];

// Total revenue
$result = mysqli_query($link, "SELECT SUM(total) as total FROM movie_booking");
$stats['revenue'] = mysqli_fetch_assoc($result)['total'] ?? 0;

// Recent bookings
$recentBookings = mysqli_query($link, "SELECT mb.booking_id, mb.total, mb.booking_date, nu.username, nu.email 
    FROM movie_booking mb 
    JOIN new_users nu ON mb.id = nu.id 
    ORDER BY mb.booking_date DESC LIMIT 5");

mysqli_close($link);

include 'includes/header.php';
include 'includes/navbar.php';
?>

<div class="container mt-5">
    <h1 class="text-orange0 mb-4">Admin Dashboard</h1>
    
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-black border-orange0">
                <div class="card-body text-center">
                    <h3 class="text-orange0"><?php echo $stats['users']; ?></h3>
                    <p class="text-white mb-0">Total Users</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-black border-orange0">
                <div class="card-body text-center">
                    <h3 class="text-orange0"><?php echo $stats['movies']; ?></h3>
                    <p class="text-white mb-0">Total Movies</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-black border-orange0">
                <div class="card-body text-center">
                    <h3 class="text-orange0"><?php echo $stats['bookings']; ?></h3>
                    <p class="text-white mb-0">Total Bookings</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-black border-orange0">
                <div class="card-body text-center">
                    <h3 class="text-orange0">£<?php echo number_format($stats['revenue'], 2); ?></h3>
                    <p class="text-white mb-0">Total Revenue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card bg-black border-orange0">
                <div class="card-header bg-orange0 text-black">
                    <h4 class="mb-0">Quick Actions</h4>
                </div>
                <div class="card-body">
                    <a href="admin_movies.php" class="btn btn-outline-orange0 me-2">Manage Movies</a>
                    <a href="admin_bookings.php" class="btn btn-outline-orange0 me-2">View All Bookings</a>
                    <a href="admin_users.php" class="btn btn-outline-orange0 me-2">Manage Users</a>
                    <a href="sql/movie_listings.php" class="btn btn-outline-yellow-1" target="_blank">Import Movies from OMDB</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Bookings -->
    <div class="row">
        <div class="col-md-12">
            <div class="card bg-black border-orange0">
                <div class="card-header bg-orange0 text-black">
                    <h4 class="mb-0">Recent Bookings</h4>
                </div>
                <div class="card-body">
                    <table class="table table-dark table-striped">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>User</th>
                                <th>Email</th>
                                <th>Total</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($booking = mysqli_fetch_assoc($recentBookings)): ?>
                            <tr>
                                <td>#EC1000<?php echo $booking['booking_id']; ?></td>
                                <td><?php echo e($booking['username']); ?></td>
                                <td><?php echo e($booking['email']); ?></td>
                                <td>£<?php echo number_format($booking['total'], 2); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($booking['booking_date'])); ?></td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer_element.php'; ?>
