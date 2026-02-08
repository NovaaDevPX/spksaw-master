<?php
session_start();
include 'include/conn.php';

// Cek metode harus POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: login.php");
  exit;
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if ($username === '' || $password === '') {
  header("Location: login.php?error=empty_fields");
  exit;
}

// Cek apakah username ada
$stmt = $db->prepare("SELECT id_user, username, password, role FROM saw_users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows === 1) {
  $data = $result->fetch_assoc();

  // Verifikasi password (gunakan MD5 seperti data yang ada)
  if (md5($password) === $data['password']) {
    // Set session
    $_SESSION['id_user'] = $data['id_user'];
    $_SESSION['username'] = $data['username'];
    $_SESSION['role'] = $data['role'];
    $_SESSION['status'] = "login";

    // Arahkan berdasarkan role
    switch ($data['role']) {
      case 'admin':
        header("Location: index.php");
        exit;
      case 'mitra':
        header("Location: index.php");
        exit;
      case 'quality_control':
        header("Location: index.php");
        exit;
      case 'manager':
        header("Location: index.php");
        exit;
      default:
        header("Location: login.php?error=role_not_found");
        exit;
    }
  } else {
    // Password salah
    header("Location: login.php?error=wrong_password");
    exit;
  }
} else {
  // Username tidak ditemukan
  header("Location: login.php?error=wrong_username");
  exit;
}
