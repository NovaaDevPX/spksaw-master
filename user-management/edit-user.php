<?php
session_start();

require "../include/conn.php";
require "../include/notification-helper.php";

$errors = [];

// Proteksi login
if (!isset($_SESSION['id_user'])) {
  header("Location: ../auth/login.php");
  exit;
}

// Ambil ID dari URL
if (!isset($_GET['id'])) {
  header("Location: list-user.php?error=user_not_found");
  exit;
}

$id = intval($_GET['id']);

// Ambil data user
$stmt = $db->prepare("SELECT * FROM saw_users WHERE id_user = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
  header("Location: list-user.php?error=user_not_found");
  exit;
}

// Simpan data lama untuk notifikasi
$oldUsername = $user['username'];
$oldRole = $user['role'];

// Proses update
if (isset($_POST['update'])) {
  $username = trim($_POST['username']);
  $role = trim($_POST['role']);
  $password = $_POST['password'];

  $allowedRoles = ['admin', 'manager', 'mitra', 'quality_control'];

  // Validasi username
  if ($username === '') {
    $errors[] = "Username wajib diisi.";
  } elseif (strlen($username) < 4) {
    $errors[] = "Username minimal 4 karakter.";
  } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = "Username hanya boleh huruf, angka, dan underscore.";
  }

  // Validasi role
  if (!in_array($role, $allowedRoles, true)) {
    $errors[] = "Role tidak valid.";
  }

  // Validasi password jika diisi
  if (!empty($password)) {
    if (strlen($password) < 5) {
      $errors[] = "Password minimal 5 karakter.";
    } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:"\\|,.<>\/?]/', $password)) {
      $errors[] = "Password harus mengandung karakter spesial.";
    }
  }

  if (empty($errors)) {

    if (!empty($password)) {
      $passwordEnc = md5($password);

      $stmt = $db->prepare("
        UPDATE saw_users 
        SET username = ?, role = ?, password = ?
        WHERE id_user = ?
      ");
      $stmt->bind_param("sssi", $username, $role, $passwordEnc, $id);
    } else {
      $stmt = $db->prepare("
        UPDATE saw_users 
        SET username = ?, role = ?
        WHERE id_user = ?
      ");
      $stmt->bind_param("ssi", $username, $role, $id);
    }

    if ($stmt->execute()) {

      /* ===========================
         NOTIFIKASI
      =========================== */
      $title = "User Diupdate";

      $message = "User <b>$oldUsername</b> telah diupdate:
      - Username baru: <b>$username</b>
      - Role baru: <b>$role</b> ";

      createNotification(
        $db,
        $title,
        $message,
        'admin', // target semua admin
        null
      );

      $stmt->close();
      header("Location: list-user.php?success=updated");
      exit;
    } else {
      $errors[] = "Gagal mengupdate user.";
    }

    $stmt->close();
  }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require "../layout/head.php"; ?>

<body>
  <div id="app">
    <?php require "../layout/sidebar.php"; ?>

    <div id="main" class="py-4">
      <h3>Edit User</h3>

      <div class="card p-4 mt-3">

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <form method="POST">

          <div class="mb-3">
            <label class="form-label">Username</label>
            <input
              type="text"
              class="form-control"
              name="username"
              value="<?= htmlspecialchars($user['username']) ?>"
              required>
          </div>

          <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-control" required>
              <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
              <option value="manager" <?= $user['role'] == 'manager' ? 'selected' : '' ?>>Manager</option>
              <option value="mitra" <?= $user['role'] == 'mitra' ? 'selected' : '' ?>>Mitra</option>
              <option value="quality_control" <?= $user['role'] == 'quality_control' ? 'selected' : '' ?>>Quality Control</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Password Baru (opsional)</label>
            <input
              type="password"
              class="form-control"
              name="password"
              placeholder="Kosongkan jika tidak ingin mengubah">
            <div class="form-text">
              Minimal 5 karakter dan harus mengandung karakter spesial.
            </div>
          </div>

          <button type="submit" name="update" class="btn btn-primary">Simpan Perubahan</button>
          <a href="list-user.php" class="btn btn-secondary">Kembali</a>

        </form>
      </div>

      <?php require "../layout/footer.php"; ?>
    </div>
  </div>

  <?php require "../layout/js.php"; ?>
</body>

</html>