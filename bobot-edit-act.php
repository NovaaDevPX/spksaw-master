<?php
require "include/conn.php";
require "include/notification-helper.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

  $id = intval($_POST['id_criteria']);
  $criteria = trim($_POST['criteria']);
  $weight = floatval($_POST['weight']);
  $attribute = trim($_POST['attribute']);

  if ($id <= 0 || $criteria === '') {
    header("Location: ./bobot.php?error=invalid_data");
    exit;
  }

  $stmt = $db->prepare("
    UPDATE saw_criterias
    SET criteria = ?, weight = ?, attribute = ?
    WHERE id_criteria = ?
  ");

  $stmt->bind_param("sdsi", $criteria, $weight, $attribute, $id);

  if ($stmt->execute()) {

    /* ===========================
       NOTIFIKASI
    =========================== */
    $title = "Bobot Kriteria Diperbarui";
    $message = "Kriteria \"$criteria\" telah diperbarui.";

    // kirim ke admin
    createNotification($db, $title, $message, "admin", null);

    // kirim ke quality_control
    createNotification($db, $title, $message, "quality_control", null);

    header("Location: ./bobot.php?success=updated");
    exit;
  } else {
    header("Location: ./bobot.php?error=failed");
    exit;
  }
}
