<?php
require "include/conn.php";
require "include/notification-helper.php";

if (isset($_GET['id'])) {
  $id = intval($_GET['id']);

  // Ambil nama alternatif dulu
  $stmt = $db->prepare("SELECT name FROM saw_alternatives WHERE id_alternative = ?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $result = $stmt->get_result();
  $data = $result->fetch_assoc();

  if ($data) {
    $name = $data['name'];

    // Hapus alternatif
    $stmtDelete = $db->prepare("DELETE FROM saw_alternatives WHERE id_alternative = ?");
    $stmtDelete->bind_param("i", $id);

    if ($stmtDelete->execute()) {

      // =============================
      // NOTIFIKASI
      // =============================
      $title = "Alternatif Dihapus";
      $message = "Alternatif dengan nama \"$name\" telah dihapus.";

      // kirim ke admin
      createNotification(
        $db,
        $title,
        $message,
        "admin",
        null
      );

      // kirim ke quality_control
      createNotification(
        $db,
        $title,
        $message,
        "quality_control",
        null
      );

      header("Location: ./alternatif.php?success=deleted");
      exit;
    } else {
      header("Location: ./alternatif.php?error=failed");
      exit;
    }
  } else {
    header("Location: ./alternatif.php?error=notfound");
    exit;
  }
} else {
  header("Location: ./alternatif.php");
  exit;
}
