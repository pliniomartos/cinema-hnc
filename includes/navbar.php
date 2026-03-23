<?php
include 'header.php';
?>
<body class="d-flex flex-column min-vh-100">
<nav class="navbar sticky-top bg-black navbar-expand-lg border-5 border-bottom rounded-bottom-4 border-yellow-1">
    <div class="container-fluid">
        <a class="d-none d-lg-flex navbar-brand text-orange0" href="/cinema/home.php">EcCinema</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list btn btn-outline-orange0"></i>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-yellow-1 text-orange0 <?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'active text-black bg-yellow-1 rounded-3' : ''; ?>"
                       aria-current="page" href="/cinema/home.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-yellow-1 text-orange0 <?php echo basename($_SERVER['PHP_SELF']) == 'movie_listing.php' ? 'active text-black bg-yellow-1 rounded-3' : ''; ?>"
                       href="/cinema/movie_listing.php">Listings</a>
                </li>
                <?php if (isset($_SESSION['username'])): ?>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-yellow-1 text-orange0 <?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'active text-black bg-yellow-1 rounded-3' : ''; ?>"
                       href="/cinema/bookings.php">Bookings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-yellow-1 text-orange0 <?php echo basename($_SERVER['PHP_SELF']) == 'account.php' ? 'active text-black bg-yellow-1 rounded-3' : ''; ?>"
                       href="/cinema/account.php">Account</a>
                </li>
                <?php if (!empty($_SESSION['is_admin']) && (int)$_SESSION['is_admin'] === 1): ?>
                <li class="nav-item">
                    <a class="nav-link btn btn-outline-yellow-1 text-orange0 <?php echo basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'active text-black bg-yellow-1 rounded-3' : ''; ?>"
                       href="/cinema/admin.php">Admin</a>
                </li>
                <?php endif; ?>
                <?php endif; ?>
            </ul>

            <?php if (isset($_SESSION['username'])): ?>
                <div class="d-flex">
                    <span class="navbar-text text-orange0 me-2">Logged in as <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <a href="logout.php" class="btn btn-outline-red0">Logout</a>
                </div>
            <?php else: ?>
                <form class="d-flex gap-1 col-lg-5 offset-lg-5" action="/cinema/login_action.php" method="post" data-bs-theme="dark">
                    <input class="form-control bg-transparent border-2 border-orange0 me-2" type="email" placeholder="Email" name="email" required>
                    <input class="form-control bg-transparent border-2 border-orange0 me-2" type="password" placeholder="Password" name="password" required>
                    <button class="btn btn-outline-orange0" type="submit">Login</button>
                    <button class="btn btn-outline-orange0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasWithBothOptions" aria-controls="offcanvasWithBothOptions">
                        Register
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</nav>

<?php include 'register_canvas.php'; ?>
