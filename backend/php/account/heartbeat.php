<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
  http_response_code(200);
  exit();
}

require_once "../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);
$userId = intval($data["user_id"] ?? $_POST["user_id"] ?? 0);
$action = trim($data["action"] ?? $_POST["action"] ?? "ping"); // "ping" or "offline"

if (!$userId) {
  http_response_code(400);
  echo json_encode(["message" => "Thiếu user_id!"]);
  $conn->close();
  exit();
}

// Tự động thêm cột last_active nếu DB chưa có
$colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'last_active'");
if ($colCheck && $colCheck->num_rows === 0) {
  @$conn->query("ALTER TABLE users ADD last_active DATETIME NULL DEFAULT NULL");
}

if ($action === "offline") {
  // Khi đăng xuất, set last_active về NULL hoặc thời điểm cũ
  $stmt = $conn->prepare("UPDATE users SET last_active = NULL WHERE id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $stmt->close();
  echo json_encode(["message" => "Đã chuyển trạng thái offline", "online" => false]);
} else {
  // Ping heartbeat khi đang hoạt động
  $stmt = $conn->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
  $stmt->bind_param("i", $userId);
  $stmt->execute();
  $stmt->close();
  echo json_encode(["message" => "Heartbeat thành công", "online" => true]);
}

$conn->close();

