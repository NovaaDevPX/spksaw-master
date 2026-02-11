<?php
require "include/conn.php";
require "include/notification-helper.php";

if (!isset($_SESSION['id_user'])) {
  header("Location: login.php");
  exit;
}

$createdBy      = $_SESSION['id_user'];
$id_alternative = $_POST['id_alternative'] ?? null;
$id_criteria    = $_POST['id_criteria'] ?? null;
$value          = $_POST['value'] ?? null;
$period         = $_POST['period'] ?? null;

$year = substr($period, 0, 4);

/* ============================
   VALIDASI
============================ */
if (!$id_alternative || !$id_criteria || !$value || !$period) {
  header("Location: matrik.php?year=$year&period=$period&error=invalid_data");
  exit;
}

if (!in_array($value, ['1', '2', '3', '4', '5'])) {
  header("Location: matrik.php?year=$year&period=$period&error=invalid_value");
  exit;
}

if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
  header("Location: matrik.php?error=invalid_period");
  exit;
}

/* ============================
   CEK DUPLIKAT
============================ */
$checkStmt = $db->prepare("
  SELECT 1
  FROM saw_evaluations
  WHERE id_alternative = ?
    AND id_criteria = ?
    AND period = ?
");
$checkStmt->bind_param("iis", $id_alternative, $id_criteria, $period);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
  header("Location: matrik.php?year=$year&period=$period&error=data_exists");
  exit;
}

/* ============================
   SIMPAN EVALUASI
============================ */
$insertStmt = $db->prepare("
  INSERT INTO saw_evaluations
    (id_alternative, id_criteria, value, period, created_by)
  VALUES
    (?, ?, ?, ?, ?)
");

$insertStmt->bind_param(
  "iissi",
  $id_alternative,
  $id_criteria,
  $value,
  $period,
  $createdBy
);

if ($insertStmt->execute()) {

  /* ============================
     AMBIL NAMA ALTERNATIF & KRITERIA
  ============================ */

  $detailStmt = $db->prepare("
      SELECT 
          a.name AS alternative_name,
          c.criteria AS criteria_name
      FROM saw_alternatives a
      JOIN saw_criterias c
      WHERE a.id_alternative = ?
        AND c.id_criteria = ?
  ");
  $detailStmt->bind_param("ii", $id_alternative, $id_criteria);
  $detailStmt->execute();
  $detail = $detailStmt->get_result()->fetch_assoc();

  $alternativeName = $detail['alternative_name'];
  $criteriaName    = $detail['criteria_name'];

  /* ============================
     NOTIFIKASI EVALUASI
  ============================ */

  $title = "Evaluasi Baru Ditambahkan";

  $message = "
Evaluasi baru telah ditambahkan:
- Alternatif : <b>$alternativeName</b>
- Kriteria   : <b>$criteriaName</b>
- Nilai      : <b>$value</b>
- Periode    : <b>$period</b>
  ";

  // ke admin
  createNotification(
    $db,
    $title,
    trim($message),
    "admin",
    null,
    1
  );

  // ke quality control
  createNotification(
    $db,
    $title,
    trim($message),
    "quality_control",
    null,
    1
  );

  header("Location: matrik.php?year=$year&period=$period&success=added");
  exit;
} else {
  header("Location: matrik.php?year=$year&period=$period&error=failed");
  exit;
}
