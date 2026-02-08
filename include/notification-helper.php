<?php
// include/notification_helper.php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/**
 * Tambah notifikasi
 */
function createNotification($conn, $title, $message, $targetRole = null, $targetUserId = null)
{
  // pastikan user login
  if (!isset($_SESSION['id_user']) || !isset($_SESSION['username'])) {
    return false;
  }

  $createdBy = $_SESSION['id_user'];
  $creatorName = $_SESSION['username'];

  // tambahkan nama pembuat ke pesan
  $message = $message . "\nDibuat oleh: " . $creatorName;

  $stmt = $conn->prepare("
        INSERT INTO saw_notifications 
        (title, message, created_by, target_role, target_user_id)
        VALUES (?, ?, ?, ?, ?)
    ");

  $stmt->bind_param(
    "ssisi",
    $title,
    $message,
    $createdBy,
    $targetRole,
    $targetUserId
  );

  return $stmt->execute();
}

/**
 * Ambil notifikasi user
 */
function getUserNotifications($conn, $userId, $userRole)
{
  $stmt = $conn->prepare("
        SELECT 
            n.*,
            u.username AS created_by_name,
            IF(r.is_read IS NULL, 0, r.is_read) AS is_read
        FROM saw_notifications n
        JOIN saw_users u 
            ON u.id_user = n.created_by
        LEFT JOIN saw_notification_reads r 
            ON n.id_notification = r.id_notification
            AND r.id_user = ?
        WHERE
            n.target_user_id = ?
            OR n.target_user_id IS NULL
            OR n.target_role = ?
        ORDER BY n.created_at DESC
    ");

  $stmt->bind_param("iis", $userId, $userId, $userRole);
  $stmt->execute();

  return $stmt->get_result();
}


/**
 * Hitung notifikasi belum dibaca
 */
function countUnreadNotifications($conn, $userId, $userRole)
{
  $stmt = $conn->prepare("
        SELECT COUNT(*) as total
        FROM saw_notifications n
        LEFT JOIN saw_notification_reads r 
            ON n.id_notification = r.id_notification
            AND r.id_user = ?
        WHERE
            (
                n.target_user_id = ?
                OR n.target_user_id IS NULL
                OR n.target_role = ?
            )
            AND (r.is_read IS NULL OR r.is_read = 0)
    ");

  $stmt->bind_param("iis", $userId, $userId, $userRole);
  $stmt->execute();
  $result = $stmt->get_result()->fetch_assoc();

  return $result['total'];
}


/**
 * Tandai notifikasi sebagai sudah dibaca
 */
function markNotificationAsRead($conn, $notificationId, $userId)
{
  $stmt = $conn->prepare("
        INSERT INTO saw_notification_reads 
        (id_notification, id_user, is_read, read_at)
        VALUES (?, ?, 1, NOW())
        ON DUPLICATE KEY UPDATE
            is_read = 1,
            read_at = NOW()
    ");

  $stmt->bind_param("ii", $notificationId, $userId);
  return $stmt->execute();
}
