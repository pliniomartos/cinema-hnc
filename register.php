<!-- Registration Modal -->
<?php include 'includes/navbar.php'; ?>
<div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true" data-bs-theme="dark">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerModalLabel">Register</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="includes/register_action.php" class="was-validated form-floating" method="post">
                    <div class="form-floating">
                        <input type="text" name="username" class="form-control" id="floatingInput" placeholder="Username" required>
                        <label for="floatingInput">Username</label>
                    </div>
                    <label for="username">Username:</label><br>
                    <input type="text" name="username" placeholder="Username" class="form-control"
                           value="<?php if (isset($_POST['username'])) echo $_POST['username']; ?>" required>
                    <br><br>
                    <label for="email">Email:</label><br>
                    <input type="email" name="email" class="form-control" placeholder="email@email.com"
                           value="<?php if (isset($_POST['email'])) echo $_POST['email']; ?>" required>
                    <br>
                    <small id="emailHelp" class="form-text text-muted">We'll never share your email with anyone
                        else.</small>
                    <br><br>
                    <label for="password">Password:</label><br>
                    <input type="password" name="pass1" class="form-control" placeholder="Create New Password"
                           value="<?php if (isset($_POST['pass1'])) echo $_POST['pass1']; ?>" required>
                    <br><br>
                    <label for="password">Confirm Password:</label><br>
                    <input type="password" name="pass2" class="form-control" placeholder="Confirm Password"
                           value="<?php if (isset($_POST['pass2'])) echo $_POST['pass2']; ?>" required>
                    <br><br>
                    <input type="submit" value="Submit" class="btn btn-primary">
                </form>
            </div>
        </div>
    </div>
</div>
<?php include 'includes/footer_element.php'; ?>