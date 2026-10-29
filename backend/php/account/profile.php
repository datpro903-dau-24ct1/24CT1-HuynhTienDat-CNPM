<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
  http_response_code(200);
  exit();
}

require_once "../config/database.php";

$userId = intval($_GET["user_id"] ?? 0);

if (!$userId) {
  http_response_code(400);
  echo json_encode(["message" => "Thiếu user_id!"]);
  $conn->close();
  exit();
}

$stmt = $conn->prepare("
  SELECT 
    id, 
    name, 
    email, 
    student_code, 
    class, 
    date_of_birth, 
    avatar, 
    role, 
    created_at,
    (SELECT COUNT(*) FROM posts WHERE posts.user_id = users.id) AS post_count
  FROM users 
  WHERE id = ? 
  LIMIT 1
");

$stmt->bind_param("i", $userId);
$stmt->execute();
$res = $stmt->get_result();
$profileUser = $res->fetch_assoc();
$stmt->close();

if (!$profileUser) {
  http_response_code(404);
  echo json_encode(["message" => "Không tìm thấy người dùng!"]);
  $conn->close();
  exit();
}

$profileUser["id"] = intval($profileUser["id"]);
$profileUser["post_count"] = intval($profileUser["post_count"] ?? 0);

echo json_encode($profileUser);
$conn->close();

