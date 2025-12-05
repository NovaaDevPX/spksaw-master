<?php
// user-management/add-user.php
require "../include/conn.php";

// Proses hanya jika POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Ambil & sanitasi input dasar
  $username = isset($_POST['username']) ? trim($_POST['username']) : '';
  $rawPassword = isset($_POST['password']) ? $_POST['password'] : '';
  $role = isset($_POST['role']) ? trim($_POST['role']) : '';

  // Daftar role yang diizinkan 
  $allowedRoles = ['admin', 'manager', 'mitra', 'quality_control'];

  // Validasi sederhana
  $errors = [];
  if ($username === '') $errors[] = "Username wajib diisi.";
  if ($rawPassword === '') $errors[] = "Password wajib diisi.";
  if (!in_array($role, $allowedRoles, true)) $errors[] = "Role tidak valid.";

  if (empty($errors)) {
    // Cek apakah username sudah ada
    $stmt = $db->prepare("SELECT id_user FROM saw_users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res && $res->num_rows > 0) {
      $errors[] = "Username sudah digunakan, pilih username lain.";
      $stmt->close();
    } else {
      $stmt->close();

      // hashing password
      $passwordHashed = md5($rawPassword);

      // Simpan user
      $ins = $db->prepare("INSERT INTO saw_users (username, password, role) VALUES (?, ?, ?)");
      $ins->bind_param("sss", $username, $passwordHashed, $role);

      if ($ins->execute()) {
        $ins->close();
        // redirect ke list dengan pesan sukses (header redirect lebih baik)
        header("Location: list-user.php?msg=" . urlencode("User berhasil dibuat"));
        exit;
      } else {
        $errors[] = "Gagal menyimpan user (DB error).";
        $ins->close();
      }
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
          <form method="POST" novalidate>
            <div class="mb-3">
              <label class="form-label">Username</label>
              <input type="text" name="username" class="form-control" required value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
            </div>

            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
              <div class="form-text">Password akan disimpan aman (hashed).</div>
            </div>

            <div class="mb-3">
              <label class="form-label">Role</label>
              <select name="role" class="form-control" required>
                <option value="" selected>-- pilih role --</option>
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