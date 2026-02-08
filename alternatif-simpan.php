<?php
require "include/conn.php";
require "include/notification-helper.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name']);

  if (!empty($name)) {

    // Reset auto_increment
    $db->query("ALTER TABLE saw_alternatives AUTO_INCREMENT = 1");

    // Insert alternatif
    $stmt = $db->prepare("INSERT INTO saw_alternatives (name) VALUES (?)");
    $stmt->bind_param("s", $name);

    if ($stmt->execute()) {

      // =============================
      // NOTIFIKASI
      // =============================
      $title = "Alternatif Baru Ditambahkan";
      $message = "Alternatif baru dengan nama \"$name\" telah ditambahkan.";

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

      header("Location: ./alternatif.php?status=success");
      exit;
    } else {
      header("Location: ./alternatif.php?status=error");
      exit;
    }
  } else {
    header("Location: ./alternatif.php?status=empty");
    exit;
  }
}
