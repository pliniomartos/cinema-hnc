<!-- Register Offcanvas -->
<div class="offcanvas offcanvas-start text-white bg-black border-2 border-end border-orange0" data-bs-scroll="true" tabindex="-1" id="offcanvasWithBothOptions"
     aria-labelledby="offcanvasWithBothOptionsLabel" data-bs-theme="dark">
    <div class="offcanvas-header">
        <h3 class="offcanvas-title text-orange0" id="offcanvasWithBothOptionsLabel">Register</h3>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form action="/cinema/includes/register_action.php" class="was-validated" method="post" id="registerForm">
            <div class="mb-3">
                <label for="username" class="form-label">Username:</label>
                <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
                <div id="usernameFeedback" class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="email@email.com" required>
                <div id="emailFeedback" class="invalid-feedback"></div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="pass1" id="password" class="form-control" placeholder="Create New Password" required>
            </div>
            <div class="mb-3">
                <label for="confirmPassword" class="form-label">Confirm Password:</label>
                <input type="password" name="pass2" id="confirmPassword" class="form-control" placeholder="Confirm Password" required>
                <div id="confirmPasswordFeedback" class="invalid-feedback"></div>
            </div>
            <button type="submit" class="btn btn-outline-orange0" id="submitBtn" disabled>Submit</button>
        </form>
    </div>
</div>

<script>
document.getElementById('username').addEventListener('blur', function() {
    checkDuplicate('username', this.value);
});

document.getElementById('email').addEventListener('blur', function() {
    checkDuplicate('email', this.value);
});

document.getElementById('confirmPassword').addEventListener('input', function() {
    checkPasswordMatch();
});

document.getElementById('registerForm').addEventListener('input', function() {
    validateForm();
});

function checkDuplicate(field, value) {
    const xhr = new XMLHttpRequest();
    xhr.open('POST', '/cinema/includes/check_duplicates.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.onload = function() {
        if (this.status === 200) {
            const response = JSON.parse(this.responseText);
            const feedbackElement = document.getElementById(field + 'Feedback');
            if (response.status === 'error') {
                feedbackElement.textContent = response.message;
                feedbackElement.style.display = 'block';
                document.getElementById(field).classList.add('is-invalid');
            } else {
                feedbackElement.style.display = 'none';
                document.getElementById(field).classList.remove('is-invalid');
            }
            validateForm();
        }
    };
    xhr.send(field + '=' + encodeURIComponent(value));
}

function checkPasswordMatch() {
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    const feedbackElement = document.getElementById('confirmPasswordFeedback');
    if (password !== confirmPassword) {
        feedbackElement.textContent = 'Passwords do not match.';
        feedbackElement.style.display = 'block';
        document.getElementById('confirmPassword').classList.add('is-invalid');
    } else {
        feedbackElement.style.display = 'none';
        document.getElementById('confirmPassword').classList.remove('is-invalid');
    }
    validateForm();
}

function validateForm() {
    const form = document.getElementById('registerForm');
    const submitBtn = document.getElementById('submitBtn');
    if (form.checkValidity() && !document.querySelector('.is-invalid')) {
        submitBtn.disabled = false;
    } else {
        submitBtn.disabled = true;
    }
}
</script>
