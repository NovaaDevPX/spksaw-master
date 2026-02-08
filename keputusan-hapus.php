<?php
require "include/conn.php";
require "include/notification-helper.php";

// helper to redirect back to matrik with optional message
function back($msg = '', $type = '', $year = '', $period = '')
{
  $url = 'matrik.php';
  $params = [];
  if ($type !== '') $params['type'] = $type;
  if ($msg !== '') $params['msg'] = $msg;
  if ($year !== '') $params['year'] = $year;
  if ($period !== '') $params['period'] = $period;
  if (!empty($params)) $url .= '?' . http_build_query($params);
  header("Location: $url");
  exit;
}

// pastikan param id ada
if (!isset($_GET['id'])) {
  back("Parameter id tidak ditemukan.", "error");
}

// ambil dan validasi id
$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if ($id === false || $id <= 0) {
  back("ID alternatif tidak valid.", "error");
}

// pastikan period ada
if (!isset($_GET['period']) || trim($_GET['period']) === '') {
  back("Parameter periode tidak ditemukan.", "error");
}

$period = trim($_GET['period']);

// validasi format period (harus YYYY-MM)
if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $period)) {
  back("Periode tidak valid. Gunakan format YYYY-MM.", "error");
}

$year = substr($period, 0, 4);

// gunakan prepared statement untuk hapus
$stmt = $db->prepare("DELETE FROM saw_evaluations WHERE id_alternative = ? AND period = ?");
if ($stmt === false) {
  back("Gagal menyiapkan query hapus.", "error", $year, $period);
}

$stmt->bind_param("is", $id, $period);
$ok = $stmt->execute();
if ($ok === false) {
  $stmt->close();
  back("Terjadi kesalahan saat menghapus data.", "error", $year, $period);
}

$affected = $stmt->affected_rows;
$stmt->close();

if ($affected > 0) {

  /* ===========================
     NOTIFIKASI
  =========================== */
  $title = "Data Penilaian Dihapus";
  $message = "Data penilaian alternatif ID $id pada periode $period telah dihapus.";

  // kirim ke admin
  createNotification($db, $title, $message, "admin", null);

  // kirim ke quality control
  createNotification($db, $title, $message, "quality_control", null);

  back("Data berhasil dihapus untuk periode {$period}.", "success", $year, $period);
} else {
  back("Tidak ada data yang ditemukan untuk dihapus pada periode {$period}.", "warning", $year, $period);
}
