<?php
require "include/conn.php";

$id_alternative = $_POST['id_alternative'];
$id_criteria    = $_POST['id_criteria'];
$value          = $_POST['value'];
$period         = $_POST['period']; // YYYY-MM

// Ambil tahun dari period
$year = substr($period, 0, 4);

// Validasi nilai
if ($value < 0 || $value > 5) {
  header("Location: matrik.php?year=$year&period=$period&msg=Nilai harus antara 0 sampai 5!&type=warning");
  exit;
}

// Validasi period
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
  header("Location: matrik.php?msg=Periode tidak valid!&type=error");
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
  header("Location: matrik.php?year=$year&period=$period&msg=Data sudah ada untuk periode ini!&type=warning");
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
  header("Location: matrik.php?year=$year&period=$period&msg=Data berhasil disimpan!&type=success");
} else {
  header("Location: matrik.php?year=$year&period=$period&msg=Terjadi kesalahan server!&type=error");
}

exit;
