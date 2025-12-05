<?php
require "../include/conn.php";

// Hapus user
if (isset($_GET["delete"])) {
  $id = intval($_GET["delete"]);

  // Cegah penghapusan user role admin
  $cek = $db->query("SELECT role FROM saw_users WHERE id_user = $id")->fetch_assoc();
  if ($cek && $cek['role'] === 'admin') {
    echo "<script>alert('User dengan role admin tidak boleh dihapus!'); window.location='list-user.php';</script>";
    exit;
  }

  // Jika bukan admin → boleh hapus
  $db->query("DELETE FROM saw_users WHERE id_user = $id");
  echo "<script>alert('User berhasil dihapus'); window.location='list-user.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require "../layout/head.php"; ?>

<body>
  <div id="app">
    <?php require "../layout/sidebar.php"; ?>

    <div id="main" class="py-4">

      <div class="d-flex justify-content-between align-items-center">
        <h3>Daftar User</h3>
        <a href="add-user.php" class="btn btn-primary">+ Tambah User</a>
      </div>

      <div class="card p-3 mt-3">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Username</th>
              <th>Role</th>
              <th width="150">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            $result = $db->query("SELECT * FROM saw_users ORDER BY id_user DESC");
            $no = 1;
            while ($row = $result->fetch_assoc()) {
            ?>
              <tr>
                <td><?= $no++ ?></td>
                <td><?= htmlspecialchars($row["username"]) ?></td>
                <td><?= ucfirst($row["role"]) ?></td>
                <td>

                  <!-- Tombol edit SELALU ADA -->
                  <a href="edit-user.php?id=<?= $row['id_user'] ?>" class="btn btn-warning btn-sm">
                    <i class="bi bi-pencil-square"></i>
                  </a>

                  <!-- Tombol hapus HANYA tampil jika role BUKAN admin -->
                  <?php if ($row["role"] !== "admin"): ?>
                    <a href="list-user.php?delete=<?= $row['id_user'] ?>"
                      class="btn btn-danger btn-sm"
                      onclick="return confirm('Hapus user ini?');">
                      <i class="bi bi-trash"></i>
                    </a>
                  <?php endif; ?>

                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>

      <?php require "../layout/footer.php"; ?>
    </div>
  </div>

  <?php require "../layout/js.php"; ?>

  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

</body>

</html>