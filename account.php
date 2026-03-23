<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id'])) {
    require 'login_tools.php';
    load();
    exit;
}

include 'includes/navbar.php';
require 'connect_db.php';
global $link;

function defaultProfilePlaceholder() {
    $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='200' height='200' viewBox='0 0 200 200'><rect width='200' height='200' fill='%231f1f1f'/><circle cx='100' cy='75' r='35' fill='%23f39c12'/><rect x='50' y='120' width='100' height='50' rx='25' fill='%23f39c12'/></svg>";
    return 'data:image/svg+xml;utf8,' . $svg;
}

function resolveProfilePicture($value) {
    $value = trim((string)$value);
    if ($value === '') {
        return defaultProfilePlaceholder();
    }

    if (preg_match('#^https?://#i', $value)) {
        return $value;
    }

    $safePath = str_replace('\\', '/', $value);
    if (file_exists(__DIR__ . '/' . ltrim($safePath, '/'))) {
        return $safePath;
    }

    return defaultProfilePlaceholder();
}

$user_id = (int) $_SESSION['id'];
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $profile_picture = $_FILES['profile_picture'];

    if ($profile_picture['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Please choose an image to upload.';
    } else {
        $allowedMime = ['image/jpeg', 'image/png', 'image/gif'];
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $profile_picture['tmp_name']);
        finfo_close($finfo);

        $ext = strtolower(pathinfo($profile_picture['name'], PATHINFO_EXTENSION));

        if (!in_array($mimeType, $allowedMime, true) || !in_array($ext, $allowedExtensions, true)) {
            $errors[] = 'Invalid file type. Please upload JPG, PNG, or GIF.';
        } elseif ($profile_picture['size'] > 2 * 1024 * 1024) {
            $errors[] = 'File is too large. Maximum size is 2MB.';
        } else {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            $newName = uniqid('avatar_', true) . '.' . $ext;
            $upload_file = $upload_dir . $newName;

            if (move_uploaded_file($profile_picture['tmp_name'], $upload_file)) {
                $query = "UPDATE new_users SET profile_picture = ? WHERE id = ?";
                $stmt = mysqli_prepare($link, $query);

                if ($stmt) {
                    mysqli_stmt_bind_param($stmt, 'si', $upload_file, $user_id);
                    if (mysqli_stmt_execute($stmt)) {
                        $success = 'Profile picture updated successfully.';
                    } else {
                        $errors[] = 'Could not save profile picture.';
                    }
                    mysqli_stmt_close($stmt);
                } else {
                    $errors[] = 'Database error while updating your profile.';
                }
            } else {
                $errors[] = 'Error uploading file. Please try again.';
            }
        }
    }
}

$query = "SELECT * FROM new_users WHERE id = ?";
$stmt = mysqli_prepare($link, $query);

if (!$stmt) {
    die('<p>System error. Please try again later.</p>');
}

mysqli_stmt_bind_param($stmt, 'i', $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result || mysqli_num_rows($result) === 0) {
    mysqli_stmt_close($stmt);
    die('<p>No user found with the given ID.</p>');
}

$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
mysqli_close($link);

$profile_picture = resolveProfilePicture($user['profile_picture'] ?? '');
?>

<div class="container my-5">
    <div class="container d-flex mb-5 justify-content-center align-content-center rounded" data-bs-theme="dark" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
        <div class="card mb-5" style="max-width: 540px;">
            <div class="row">
                <div class="col-md-4 bg-black justify-content-center align-content-center">
                    <img src="<?php echo htmlspecialchars($profile_picture, ENT_QUOTES, 'UTF-8'); ?>" class="img-fluid rounded-2" alt="Profile Picture">
                </div>
                <div class="col-md-8 bg-black">
                    <div class="card-body">
                        <h3 class="card-title text-orange0">Account Information</h3>

                        <?php if (!empty($errors)): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php foreach ($errors as $error): ?>
                                    <p class="mb-1"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
                                <?php endforeach; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?php echo htmlspecialchars($success, ENT_QUOTES, 'UTF-8'); ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <p class="card-text"><strong>Username:</strong> <?php echo htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="card-text"><strong>Email:</strong> <?php echo htmlspecialchars($user['email'], ENT_QUOTES, 'UTF-8'); ?></p>
                        <p class="card-text"><strong>Member Since:</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($user['created_at'])), ENT_QUOTES, 'UTF-8'); ?></p>

                        <form action="account.php" method="post" enctype="multipart/form-data" class="mb-3">
                            <div class="mb-2">
                                <label for="profile_picture" class="form-label text-white">Update Profile Picture:</label>
                                <input type="file" class="form-control form-control-sm" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/gif" required>
                                <small class="text-muted">Max 2MB (JPG, PNG, GIF)</small>
                            </div>
                            <button type="submit" class="btn btn-sm btn-outline-orange0">Upload</button>
                        </form>

                        <div class="form-group d-flex justify-content-between mb-2">
                            <button id="toggleResetPassword" class="btn btn-sm btn-outline-yellow-1">Reset Password</button>
                            <a href="includes/delete_account.php" class="btn btn-sm btn-outline-red0" onclick="return confirm('Are you sure you want to delete your account?')">Delete Account</a>
                        </div>

                        <div id="resetPasswordForm" style="display: none;">
                            <form id="resetPassword" action="includes/reset_password.php" method="post">
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text" id="inputGroup-old-password">Old Password</span>
                                    <input type="password" class="form-control" name="old_password" id="old_password" aria-describedby="inputGroup-old-password" required>
                                </div>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text" id="inputGroup-new-password">New Password</span>
                                    <input type="password" class="form-control" name="new_password" id="new_password" aria-describedby="inputGroup-new-password" required>
                                </div>
                                <div class="input-group input-group-sm mb-3">
                                    <span class="input-group-text" id="inputGroup-confirm-password">Confirm New Password</span>
                                    <input type="password" class="form-control" name="confirm_password" id="confirm_password" aria-describedby="inputGroup-confirm-password" required>
                                </div>
                                <button type="submit" class="btn btn-sm btn-outline-orange0">Reset Password</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('toggleResetPassword').addEventListener('click', function () {
        var form = document.getElementById('resetPasswordForm');
        form.style.display = (form.style.display === 'none') ? 'block' : 'none';
    });

    document.getElementById('resetPassword').addEventListener('submit', function (event) {
        event.preventDefault();
        var formData = new FormData(this);

        fetch('includes/reset_password.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.text())
            .then(data => {
                alert(data);
                document.getElementById('resetPasswordForm').style.display = 'none';
            })
            .catch(error => console.error('Error:', error));
    });
</script>
<?php include 'includes/footer_element.php'; ?>
