<?php
require "include/conn.php";
require "include/notification-helper.php";

$id_alternative = $_POST['id_alternative'];
$id_criteria    = $_POST['id_criteria'];
$value          = $_POST['value'];
$period         = $_POST['period']; // YYYY-MM

// Ambil tahun dari period
$year = substr($period, 0, 4);

// Validasi nilai
if ($value < 0 || $value > 5) {
  header("Location: matrik.php?year=$year&period=$period&error=invalid_data");
  exit;
}

// Validasi period
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
  header("Location: matrik.php?error=invalid_period");
  exit;
}

// CEK DUPLIKAT
$checkQuery = "
  SELECT * 
  FROM saw_evaluations 
  WHERE id_alternative = '$id_alternative' 
    AND id_criteria = '$id_criteria'
    AND period = '$period'
";

$checkResult = $db->query($checkQuery);

if ($checkResult->num_rows > 0) {
  header("Location: matrik.php?year=$year&period=$period&error=data_exists");
  exit;
}

// SIMPAN
$sql = "
  INSERT INTO saw_evaluations 
    (id_alternative, id_criteria, value, period)
  VALUES 
    ('$id_alternative', '$id_criteria', '$value', '$period')
";

$result = $db->query($sql);

if ($result === true) {

  /* ===========================
     NOTIFIKASI
  =========================== */
  $title = "Penilaian Baru Ditambahkan";
  $message = "Nilai alternatif ID $id_alternative untuk kriteria ID $id_criteria pada periode $period telah ditambahkan.";

  // ke admin
  createNotification($db, $title, $message, "admin", null);

  // ke quality control
  createNotification($db, $title, $message, "quality_control", null);

  header("Location: matrik.php?year=$year&period=$period&success=added");
} else {
  header("Location: matrik.php?year=$year&period=$period&error=failed");
}

exit;
