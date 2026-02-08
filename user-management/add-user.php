<?php
require "../include/conn.php";

$errors = [];

// Proses hanya jika POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  $username = isset($_POST['username']) ? trim($_POST['username']) : '';
  $rawPassword = isset($_POST['password']) ? $_POST['password'] : '';
  $role = isset($_POST['role']) ? trim($_POST['role']) : '';

  $allowedRoles = ['admin', 'manager', 'mitra', 'quality_control'];

  // Validasi username
  if ($username === '') {
    $errors[] = "Username wajib diisi.";
  } elseif (strlen($username) < 4) {
    $errors[] = "Username minimal 4 karakter.";
  } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
    $errors[] = "Username hanya boleh huruf, angka, dan underscore.";
  }

  // Validasi password
  if ($rawPassword === '') {
    $errors[] = "Password wajib diisi.";
  } elseif (strlen($rawPassword) < 5) {
    $errors[] = "Password minimal 5 karakter.";
  } elseif (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:"\\|,.<>\/?]/', $rawPassword)) {
    $errors[] = "Password harus mengandung minimal 1 karakter spesial.";
  }

  // Validasi role
  if (!in_array($role, $allowedRoles, true)) {
    $errors[] = "Role tidak valid.";
  }

  if (empty($errors)) {
    $stmt = $db->prepare("SELECT id_user FROM saw_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
      $errors[] = "Username sudah digunakan.";
      $stmt->close();
    } else {
      $stmt->close();

      $passwordHashed = md5($rawPassword);

      $ins = $db->prepare("INSERT INTO saw_users (username, password, role) VALUES (?, ?, ?)");
      $ins->bind_param("sss", $username, $passwordHashed, $role);

      if ($ins->execute()) {
        $ins->close();
        header("Location: list-user.php?msg=User berhasil ditambahkan&type=success");
        exit;
      } else {
        $errors[] = "Gagal menyimpan user.";
      }
      $ins->close();
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">
<?php require "../layout/head.php"; ?>

<body>
  <div id="app">
    <?php require "../layout/sidebar.php"; ?>

    <div id="main" class="py-4">
      <div class="container">
        <h3>Tambah User</h3>

        <?php if (!empty($errors)): ?>
          <div class="alert alert-danger">
            <ul class="mb-0">
              <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e) ?></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>

        <div class="card p-4 mt-3">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control"
                value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
            </div>

            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control">
              <div class="form-text">
                Minimal 5 karakter dan harus mengandung karakter spesial.
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Role</label>
              <select name="role" class="form-control">
                <option value="">-- pilih role --</option>
                <option value="admin">Admin</option>
                <option value="quality_control">Quality Control</option>
                <option value="manager">Manager</option>
                <option value="mitra">Mitra</option>
              </select>
            </div>

            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="list-user.php" class="btn btn-secondary">Kembali</a>
          </form>
        </div>
      </div>

      <?php require "../layout/footer.php"; ?>
    </div>
  </div>

  <?php require "../layout/js.php"; ?>
</body>

</html>