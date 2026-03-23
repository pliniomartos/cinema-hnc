<footer id="mainFooter" class="footer mt-auto bg-red2 border-5 border-top border-red-2 mt-5 position-relative">
    <div class="container text-center">
        <ul class="list-inline">
            <li class="list-inline-item"><a href="#" class="text-bg-red2" data-bs-toggle="offcanvas" data-bs-target="#privacyPolicyCanvas">Privacy Policy</a></li>
            <li class="list-inline-item"><a href="#" class="text-bg-red2" data-bs-toggle="offcanvas" data-bs-target="#termsOfServiceCanvas">Terms of Service</a></li>
            <li class="list-inline-item"><a href="#" class="text-bg-red2" data-bs-toggle="offcanvas" data-bs-target="#contactUsCanvas">Contact Us</a></li>
        </ul>
        <span class="text-red-1">© 2023 Cinema Website. All rights reserved.</span>
    </div>

</footer>
    <!-- Sticky bottom navbar with icons -->
    <nav class="d-lg-none navbar navbar-dark sticky-bottom bottom-0 start-0 end-0 p-0">
        <div class="container-fluid justify-content-around p-0 bg-black" style="min-height: 2rem">
            <div class="container-fluid p-0 btn-group btn-group-lg" role="group" aria-label="Basic radio toggle button group">
                <input type="radio" class="btn-check" name="nav" id="home" autocomplete="off" <?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'checked' : ''; ?>>
                <label style="min-height: 3rem" class="btn border-2 border-black <?php echo basename($_SERVER['PHP_SELF']) == 'home.php' ? 'btn-black text-orange0' : 'btn-orange0'; ?>" for="home" onclick="location.href='/cinema/home.php'"><i class="bi bi-house-door-fill"></i></label>

                <input type="radio" class="btn-check" name="nav" id="listings" autocomplete="off" <?php echo basename($_SERVER['PHP_SELF']) == 'movie_listing.php' ? 'checked' : ''; ?>>
                <label style="min-height: 3rem" class="btn border-2 border-black <?php echo basename($_SERVER['PHP_SELF']) == 'movie_listing.php' ? 'btn-black text-orange0' : 'btn-orange0'; ?>" for="listings" onclick="location.href='/cinema/movie_listing.php'"><i class="bi bi-film"></i></label>

            <?php if (isset($_SESSION['username'])): ?>
                <input type="radio" class="btn-check" name="nav" id="bookings" autocomplete="off" <?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'checked' : ''; ?>>
                <label style="min-height: 3rem" class="btn border-2 border-black <?php echo basename($_SERVER['PHP_SELF']) == 'bookings.php' ? 'btn-black text-orange0' : 'btn-orange0'; ?>" for="bookings" onclick="location.href='/cinema/bookings.php'"><i class="bi bi-calendar-check-fill"></i></label>

                <input type="radio" class="btn-check" name="nav" id="account" autocomplete="off" <?php echo basename($_SERVER['PHP_SELF']) == 'account.php' ? 'checked' : ''; ?>>
                <label style="min-height: 3rem" class="btn border-2 border-black <?php echo basename($_SERVER['PHP_SELF']) == 'account.php' ? 'btn-black text-orange0' : 'btn-orange0'; ?>" for="account" onclick="location.href='/cinema/account.php'"><i class="bi bi-person-fill"></i></label>
            <?php endif; ?>
            </div>
        </div>
    </nav>
</body>
</html>
<?php
include 'privacy_policy_offcanvas.php';
include 'terms_or_service_offcanvas.php';
include 'contact_us_offcanvas.php';
include 'cookies_policy_modal.php';
