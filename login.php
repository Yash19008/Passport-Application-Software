<?php
session_start();
include 'inc/db.php';

if (isset($_SESSION['uid'])) {
  header("Location: index.php");
  exit;
}

function showAlertAndExit($message)
{
  echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
      Swal.fire({
        icon: 'error',
        title: 'Login Failed',
        text: '$message',
        confirmButtonColor: '#3085d6'
      }).then(() => {
        window.location.href = 'login.php'; // redirect back after alert
      });
    });
  </script>";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    showAlertAndExit("Invalid email format.");
  }

  $stmt = $conn->prepare("SELECT id, name, email, password, last_password_change, status FROM users WHERE email = ?");
  $stmt->bind_param("s", $email);
  $stmt->execute();

  $result = $stmt->get_result();
  if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if ($user['status'] != 1) {
      showAlertAndExit("Admin is inactive.");
    }

    if (password_verify($password, $user['password'])) {
      // Check if password change is older than 7 days
      $lastChange = new DateTime($user['last_password_change']);
      $now = new DateTime();
      $interval = $now->diff($lastChange);

      if ($interval->days >= 7) {
        $_SESSION['uid'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['email'] = $user['email'];
        // Optional: regenerate session ID for security
        session_regenerate_id(true);
        // Redirect to password change page
        header("Location: change_password.php?reason=expired");
        exit;
      }

      // Normal login
      $_SESSION['uid'] = $user['id'];
      $_SESSION['name'] = $user['name'];
      $_SESSION['email'] = $user['email'];
      session_regenerate_id(true);

      header("Location: index.php");
      exit;
    } else {
      showAlertAndExit("Incorrect password.");
    }
  } else {
    showAlertAndExit("No user found with that email.");
  }

  $stmt->close();
}
?>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin | Login</title>

  <!-- Google Font: Source Sans Pro -->
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="./plugins/fontawesome-free/css/all.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="./plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="./dist/css/adminlte.min.css">
</head>

<body class="hold-transition login-page">
  <div class="login-box">
    <!-- /.login-logo -->
    <div class="card card-outline card-primary">
      <div class="card-header text-center">
        <a href="#" class="h1">Login to Panel</a>
      </div>
      <div class="card-body">
        <p class="login-box-msg">Sign in to start your session</p>

        <form action="" method="post">
          <div class="input-group mb-3">
            <input type="email" name="email" class="form-control" placeholder="Email" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-envelope"></span>
              </div>
            </div>
          </div>
          <div class="input-group mb-3">
            <input type="password" name="password" class="form-control" placeholder="Password" required>
            <div class="input-group-append">
              <div class="input-group-text">
                <span class="fas fa-lock"></span>
              </div>
            </div>
          </div>
          <div class="row">
            <!-- /.col -->
            <div class="col-12">
              <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </div>
            <!-- /.col -->
          </div>
        </form>
      </div>
      <!-- /.card-body -->
    </div>
    <!-- /.card -->
  </div>
  <!-- /.login-box -->

  <!-- jQuery -->
  <script src="./plugins/jquery/jquery.min.js"></script>
  <!-- Bootstrap 4 -->
  <script src="./plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- AdminLTE App -->
  <script src="./dist/js/adminlte.min.js"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>