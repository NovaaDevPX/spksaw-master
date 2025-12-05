<?php
require "../include/conn.php";

// Ambil ID dari URL
if (!isset($_GET['id'])) {
  echo "<script>alert('User tidak ditemukan'); window.location='list-user.php';</script>";
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
  echo "<script>alert('User tidak ditemukan'); window.location='list-user.php';</script>";
  exit;
}

// Proses update
if (isset($_POST['update'])) {
  $username = trim($_POST['username']);
  $role = trim($_POST['role']);
  $password = $_POST['password'];

  // Update jika password diubah
  if (!empty($password)) {
    $passwordEnc = md5($password); // gunakan MD5 agar cocok dengan login

    $stmt = $db->prepare("
      UPDATE saw_users 
      SET username = ?, role = ?, password = ?
      WHERE id_user = ?
    ");
    $stmt->bind_param("sssi", $username, $role, $passwordEnc, $id);
  } else {
    // Update tanpa ubah password
    $stmt = $db->prepare("
      UPDATE saw_users 
      SET username = ?, role = ?
      WHERE id_user = ?
    ");
    $stmt->bind_param("ssi", $username, $role, $id);
  }

  $stmt->execute();
  $stmt->close();

  echo "<script>alert('User berhasil diupdate'); window.location='list-user.php';</script>";
  exit;
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

      <div class="card p-4 mt-3" style="max-width: 500px;">
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