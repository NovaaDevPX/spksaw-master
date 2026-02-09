<?php
require "include/conn.php";
require "include/notification-helper.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id = intval($_POST['id_alternative']);
  $name = trim($_POST['name']);

  if ($id <= 0 || $name === '') {
    header("Location: ./alternatif.php?error=invalid_data");
    exit;
  }

  // Ambil nama alternatif lama
  $stmtOld = $db->prepare("SELECT name FROM saw_alternatives WHERE id_alternative = ?");
  $stmtOld->bind_param("i", $id);
  $stmtOld->execute();
  $resultOld = $stmtOld->get_result();
  $oldData = $resultOld->fetch_assoc();

  if (!$oldData) {
    header("Location: ./alternatif.php?error=notfound");
    exit;
  }

  $oldName = $oldData['name'];

  // Update data
  $stmtUpdate = $db->prepare("UPDATE saw_alternatives SET name = ? WHERE id_alternative = ?");
  $stmtUpdate->bind_param("si", $name, $id);

  if ($stmtUpdate->execute()) {

    // =============================
    // NOTIFIKASI
    // =============================
    $title = "Alternatif Diperbarui";
    $message = "Alternatif \"$oldName\" telah diperbarui menjadi \"$name\".";

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

    header("Location: ./alternatif.php?success=updated");
    exit;
  } else {
    header("Location: ./alternatif.php?error=failed");
    exit;
  }
}
