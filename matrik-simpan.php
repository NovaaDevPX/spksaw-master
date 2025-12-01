<?php
require "include/conn.php";

$id_alternative = $_POST['id_alternative'];
$id_criteria    = $_POST['id_criteria'];
$value          = $_POST['value'];
$period         = $_POST['period']; // <-- tambahkan period

// Validasi nilai
if ($value < 0 || $value > 5) {
  header("Location: matrik.php?msg=Nilai harus di antara 0 sampai 5!&type=warning");
  exit;
}

// Validasi period (harus format YYYY-MM)
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
  header("Location: matrik.php?msg=Periode tidak valid!&type=error");
  exit;
}

// CEK DUPLIKAT BERDASARKAN:
// 1. id_alternative
// 2. id_criteria
// 3. period
$checkQuery = "
  SELECT * 
  FROM saw_evaluations 
  WHERE id_alternative = '$id_alternative' 
    AND id_criteria = '$id_criteria'
    AND period = '$period'
";
$checkResult = $db->query($checkQuery);

if ($checkResult->num_rows > 0) {
  header("Location: matrik.php?msg=Data untuk bulan ini sudah ada!&type=warning");
  exit;
}

// SIMPAN DATA
$sql = "
  INSERT INTO saw_evaluations 
    (id_alternative, id_criteria, value, period)
  VALUES 
    ('$id_alternative', '$id_criteria', '$value', '$period')
";

$result = $db->query($sql);

if ($result === true) {
  header("Location: matrik.php?msg=Data berhasil disimpan!&type=success");
} else {
  header("Location: matrik.php?msg=Terjadi kesalahan pada server!&type=error");
}
exit;
