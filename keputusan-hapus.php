<?php
require "include/conn.php";
require "include/notification-helper.php";

// ============================
// VALIDASI PARAMETER
// ============================

if (!isset($_GET['id'])) {
  header("Location: matrik.php?error=invalid_id");
  exit;
}

$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($id === false || $id <= 0) {
  header("Location: matrik.php?error=invalid_id");
  exit;
}

if (!isset($_GET['period']) || trim($_GET['period']) === '') {
  header("Location: matrik.php?error=invalid_period");
  exit;
}

$period = trim($_GET['period']);

if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
  header("Location: matrik.php?error=invalid_period");
  exit;
}

$year = substr($period, 0, 4);

// ============================
// AMBIL DETAIL ALTERNATIF
// ============================

$altStmt = $db->prepare("
  SELECT name 
  FROM saw_alternatives 
  WHERE id_alternative = ?
");

if (!$altStmt) {
  header("Location: matrik.php?year=$year&period=$period&error=failed");
  exit;
}

$altStmt->bind_param("i", $id);
$altStmt->execute();
$altResult = $altStmt->get_result();
$alternative = $altResult->fetch_assoc();
$altStmt->close();

$alternativeName = $alternative['name'] ?? "ID $id";

// ============================
// HAPUS DATA
// ============================

$stmt = $db->prepare("
  DELETE FROM saw_evaluations 
  WHERE id_alternative = ? 
    AND period = ?
");

if (!$stmt) {
  header("Location: matrik.php?year=$year&period=$period&error=failed");
  exit;
}

$stmt->bind_param("is", $id, $period);
$stmt->execute();

$affected = $stmt->affected_rows;
$stmt->close();

// ============================
// JIKA BERHASIL DIHAPUS
// ============================

if ($affected > 0) {

  /* ============================
     NOTIFIKASI
  ============================ */

  $title = "Evaluasi Dihapus";

  $message = "
Evaluasi telah dihapus:
- Alternatif : <b>$alternativeName</b>
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

  header("Location: matrik.php?year=$year&period=$period&success=deleted");
  exit;
} else {

  header("Location: matrik.php?year=$year&period=$period&error=not_found");
  exit;
}
