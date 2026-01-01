<?php
session_start();
include 'inc/db.php';

if (!isset($_SESSION['uid'])) {
    header("Location: login.php");
    exit;
}

// Function to show alert and redirect
function showAlertAndRedirect($icon, $title, $message, $redirect)
{
    echo "<script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script><script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: '$icon',
        title: '$title',
        text: '$message',
        confirmButtonColor: '#3085d6'
      }).then(() => {
        window.location.href = '$redirect';
      });
    });
  </script>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (strlen($new_password) < 8) {
        showAlertAndRedirect('error', 'Weak Password', 'Password must be at least 8 characters.', 'change_password.php');
        exit;
    }

    if ($new_password !== $confirm_password) {
        showAlertAndRedirect('error', 'Mismatch', 'Passwords do not match.', 'change_password.php');
        exit;
    }

    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ?, last_password_change = NOW() WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $_SESSION['uid']);

    if ($stmt->execute()) {
        showAlertAndRedirect('success', 'Password Changed', 'Your password was updated successfully.', 'index.php');
    } else {
        showAlertAndRedirect('error', 'Error', 'Something went wrong. Please try again.', 'change_password.php');
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Change Password</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap -->
    <link rel="stylesheet" href="./plugins/bootstrap/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="./plugins/fontawesome-free/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="./dist/css/adminlte.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <a href="#" class="h1">Update Password</a>
            </div>
            <div class="card-body">
                <p class="login-box-msg">Please enter your new password</p>
                <form method="post" action="">
                    <div class="input-group mb-3">
                        <input type="password" name="new_password" class="form-control" placeholder="New Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-lock"></span></div>
                        </div>
                    </div>
                    <div class="input-group mb-3">
                        <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
                        <div class="input-group-append">
                            <div class="input-group-text"><span class="fas fa-lock"></span></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary btn-block">Update Password</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="./plugins/jquery/jquery.min.js"></script>
    <script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="./dist/js/adminlte.min.js"></script>
</body>

</html>