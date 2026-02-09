<?php
session_start();

require "../include/conn.php";
require "../include/notification-helper.php";

// Proteksi login
if (!isset($_SESSION['id_user'])) {
  header("Location: ../auth/login.php");
  exit;
}

// Hapus user
if (isset($_GET["delete"])) {
  $id = intval($_GET["delete"]);

  // Ambil data user sebelum dihapus
  $stmt = $db->prepare("SELECT username, role FROM saw_users WHERE id_user = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $user = $result->fetch_assoc();
  $stmt->close();

  if ($user) {
    $username = $user['username'];
    $role = $user['role'];

    // Proses hapus
    if ($db->query("DELETE FROM saw_users WHERE id_user = $id")) {

      /* ===========================
         NOTIFIKASI
      =========================== */
      $title = "User Dihapus";
      $message = "
User <b>$username</b> dengan role <b>$role</b> telah dihapus.
      ";

      createNotification(
        $db,
        $title,
        $message,
        'admin', // notifikasi ke semua admin
        null
      );

      header("Location: list-user.php?success=deleted");
    } else {
      header("Location: list-user.php?error=failed");
    }
  } else {
    header("Location: list-user.php?error=user_not_found");
  }

  exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<?php require "../layout/head.php"; ?>

<body>
  <div id="app">
    <?php include "../include/notification.php"; ?>
    <?php require "../layout/sidebar.php"; ?>

    <div id="main" class="py-4">
      <div class="container">

        <?php if (isset($_GET['msg'])): ?>
          <div class="alert alert-<?= $_GET['type'] == 'success' ? 'success' : 'danger' ?>">
            <?= htmlspecialchars($_GET['msg']) ?>
          </div>
        <?php endif; ?>

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
                    <a href="edit-user.php?id=<?= $row['id_user'] ?>" class="btn btn-warning btn-sm">
                      <i class="bi bi-pencil-square"></i>
                    </a>

                    <a href="list-user.php?delete=<?= $row['id_user'] ?>"
                      class="btn btn-danger btn-sm"
                      onclick="return confirm('Hapus user ini?');">
                      <i class="bi bi-trash"></i>
                    </a>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>

      </div>
      <?php require "../layout/footer.php"; ?>
    </div>
  </div>

  <?php require "../layout/js.php"; ?>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

</body>

</html>