<?php
require "../include/conn.php";

// Ambil ID dari URL
if (!isset($_GET['id'])) {
  echo "<script>alert('User tidak ditemukan'); window.location='list-user.php';</script>";
  exit;
}

$id = intval($_GET['id']);

// Ambil data user
$user = $db->query("SELECT * FROM saw_users WHERE id_user = $id")->fetch_assoc();

if (!$user) {
  echo "<script>alert('User tidak ditemukan'); window.location='list-user.php';</script>";
  exit;
}

// Proses update
if (isset($_POST['update'])) {
  $username = $_POST['username'];
  $role = $_POST['role'];
  $password = $_POST['password'];

  // Jika password diisi → update password juga
  if (!empty($password)) {
    $passwordEnc = password_hash($password, PASSWORD_DEFAULT);
    $db->query("
      UPDATE saw_users SET 
        username = '$username',
        role = '$role',
        password = '$passwordEnc'
      WHERE id_user = $id
    ");
  } else {
    // Tanpa ubah password
    $db->query("
      UPDATE saw_users SET 
        username = '$username',
        role = '$role'
      WHERE id_user = $id
    ");
  }

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
            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-control" required>
              <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
              <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Password Baru (opsional)</label>
            <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengubah">
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