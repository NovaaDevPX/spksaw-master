<?php
require "../include/conn.php";

// Jika form di-submit
if ($_SERVER["REQUEST_METHOD"] === "POST") {

  $username = $_POST["username"];
  $password = password_hash($_POST["password"], PASSWORD_DEFAULT);
  $role = $_POST["role"];

  $stmt = $db->prepare("INSERT INTO saw_users (username, password, role) VALUES (?, ?, ?)");
  $stmt->bind_param("sss", $username, $password, $role);

  if ($stmt->execute()) {
    echo "<script>alert('User berhasil dibuat'); window.location='list-user.php';</script>";
  } else {
    echo "<script>alert('Gagal membuat user');</script>";
  }

  $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require "../layout/head.php"; ?>

<body>
  <div id="app">
    <?php require "../layout/sidebar.php"; ?>

    <div id="main" class="py-4">
      <h3>Tambah User</h3>
      <div class="card p-4 mt-3">

        <form method="POST">
          <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
          </div>

          <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>

          <div class="mb-3">
            <label>Role</label>
            <select name="role" class="form-control" required>
              <option value="master">Master</option>
              <option value="admin">Admin</option>
              <option value="manager">Manager</option>
              <option value="mitra">Mitra</option>
            </select>
          </div>

          <button type="submit" class="btn btn-primary">Simpan</button>
          <a href="list-user.php" class="btn btn-secondary">Kembali</a>
        </form>

      </div>

      <?php require "../layout/footer.php"; ?>
    </div>
  </div>

  <?php require "../layout/js.php"; ?>
</body>

</html>