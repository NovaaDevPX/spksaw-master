<?php
// include/notification-helper.php

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

/**
 * ============================
 * TAMBAH NOTIFIKASI
 * ============================
 */
function createNotification(
  $conn,
  $title,
  $message,
  $targetRole = null,
  $targetUserId = null,
  $isEvaluation = 0
) {

  if (!isset($_SESSION['id_user']) || !isset($_SESSION['username'])) {
    return false;
  }

  $createdBy   = (int) $_SESSION['id_user'];
  $creatorName = $_SESSION['username'];

  $fullMessage = $message . "\nDibuat oleh: " . $creatorName;

  $stmt = $conn->prepare("
        INSERT INTO saw_notifications 
        (title, message, is_evaluation, created_by, target_role, target_user_id, created_at)
        VALUES (?, ?, ?, ?, ?, ?, NOW())
    ");

  if (!$stmt) {
    die("Prepare failed: " . $conn->error);
  }

  $stmt->bind_param(
    "ssiisi",
    $title,
    $fullMessage,
    $isEvaluation,
    $createdBy,
    $targetRole,
    $targetUserId
  );

  return $stmt->execute();
}


/**
 * ==========================================================
 * BASE QUERY (dipakai bersama biar tidak double logic)
 * ==========================================================
 */
function baseNotificationQuery($isEvaluation)
{
  return "
        FROM saw_notifications n
        JOIN saw_users u 
            ON u.id_user = n.created_by
        WHERE
            n.is_evaluation = ?
        AND
        (
            n.target_user_id = ?
            OR
            (
                n.target_user_id IS NULL 
                AND n.target_role = ?
            )
            OR
            (
                n.target_user_id IS NULL 
                AND n.target_role IS NULL
            )
        )
    ";
}


/**
 * ============================
 * NOTIFIKASI BIASA (is_evaluation = 0)
 * ============================
 */
function getUserNotifications($conn, $userId, $userRole, $limit = 5)
{
  $sql = "
        SELECT 
            n.id_notification,
            n.title,
            n.message,
            n.created_at,
            u.username AS created_by_name
        "
    . baseNotificationQuery(0) .
    " ORDER BY n.created_at DESC
          LIMIT ?";

  $stmt = $conn->prepare($sql);
  $isEvaluation = 0;

  $stmt->bind_param("iisi", $isEvaluation, $userId, $userRole, $limit);
  $stmt->execute();

  return $stmt->get_result();
}


/**
 * ============================
 * NOTIFIKASI EVALUASI (is_evaluation = 1)
 * ============================
 */
function getEvaluationNotifications($conn, $userId, $userRole, $limit = 5)
{
  $sql = "
        SELECT 
            n.id_notification,
            n.title,
            n.message,
            n.created_at,
            u.username AS created_by_name
        "
    . baseNotificationQuery(1) .
    " ORDER BY n.created_at DESC
          LIMIT ?";

  $stmt = $conn->prepare($sql);
  $isEvaluation = 1;

  $stmt->bind_param("iisi", $isEvaluation, $userId, $userRole, $limit);
  $stmt->execute();

  return $stmt->get_result();
}


/**
 * ============================
 * HITUNG NOTIFIKASI BIASA
 * ============================
 */
function countNotifications($conn, $userId, $userRole)
{
  $sql = "
        SELECT COUNT(*) AS total
        "
    . baseNotificationQuery(0);

  $stmt = $conn->prepare($sql);
  $isEvaluation = 0;

  $stmt->bind_param("iis", $isEvaluation, $userId, $userRole);
  $stmt->execute();

  $result = $stmt->get_result()->fetch_assoc();
  return $result['total'];
}


/**
 * ============================
 * HITUNG NOTIFIKASI EVALUASI
 * ============================
 */
function countEvaluationNotifications($conn, $userId, $userRole)
{
  $sql = "
        SELECT COUNT(*) AS total
        "
    . baseNotificationQuery(1);

  $stmt = $conn->prepare($sql);
  $isEvaluation = 1;

  $stmt->bind_param("iis", $isEvaluation, $userId, $userRole);
  $stmt->execute();

  $result = $stmt->get_result()->fetch_assoc();
  return $result['total'];
}
