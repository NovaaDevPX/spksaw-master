<?php
session_start();

// Jika user sudah login, arahkan berdasarkan role
if (isset($_SESSION['status']) && $_SESSION['status'] === 'login') {
  if ($_SESSION['role'] === 'admin') {
    header("Location: index.php");
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - SAW</title>
  <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/bootstrap.css">
  <link rel="stylesheet" href="assets/vendors/bootstrap-icons/bootstrap-icons.css">
  <link rel="stylesheet" href="assets/css/app.css">
  <link rel="stylesheet" href="assets/css/pages/auth.css">
  <link rel="shortcut icon" href="assets/images/icon-tsel.webp" type="image/x-icon">
</head>

<body>
  <div id="auth">
    <div class="row h-100">
      <div class="col-lg-5 col-12">
        <div id="auth-left">
          <div class="text-center mb-4">
            <img alt="Telkomsel Logo" style="max-width: 200px;">
          </div>

          <?php if (isset($_GET['error'])): ?>
            <?php if ($_GET['error'] == 'wrong_username'): ?>
              <div class="alert alert-danger">Username tidak ditemukan!</div>
            <?php elseif ($_GET['error'] == 'wrong_password'): ?>
              <div class="alert alert-danger">Password salah!</div>
            <?php elseif ($_GET['error'] == 'empty_fields'): ?>
              <div class="alert alert-warning">Harap isi semua field!</div>
            <?php elseif ($_GET['error'] == 'role_not_found'): ?>
              <div class="alert alert-danger">Role pengguna tidak dikenali!</div>
            <?php endif; ?>
          <?php endif; ?>

          <form action="login-act.php" method="post">
            <div class="form-group position-relative has-icon-left mb-4">
              <input type="text" class="form-control form-control-xl" placeholder="Username" name="username" required>
              <div class="form-control-icon"><i class="bi bi-person"></i></div>
            </div>
            <div class="form-group position-relative has-icon-left mb-4">
              <input type="password" class="form-control form-control-xl" placeholder="Password" name="password" required>
              <div class="form-control-icon"><i class="bi bi-shield-lock"></i></div>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg shadow-lg mt-5">Log in</button>
          </form>
        </div>
      </div>
      <div class="col-lg-7 d-none d-lg-block">
        <img src="assets/images/tlkm2.jpg" alt="Foto" class="img-fluid w-90 h-80" style="object-fit: cover;">
      </div>
    </div>
  </div>
</body>

</html>