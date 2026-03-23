<!-- login_modal.php -->
<div class="modal fade center" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true"
     data-bs-theme="dark">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-black text-yellow0">
            <div class="modal-header text-orange0">
                <h3 class="modal-title" id="loginModalLabel">Login</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="login_action.php" method="post">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" class="btn btn-outline-orange0">Login</button>
                </form>
                <p class="mt-3 text-bg-orange0">Don't have an account? <a class="text-black" href="#" data-bs-toggle="offcanvas"
                                                          data-bs-target="#offcanvasWithBothOptions"
                                                          aria-controls="offcanvasWithBothOptions">Register here</a></p>
            </div>
        </div>
    </div>
</div>