<?php

// XÓA BÌNH LUẬN (chỉ chủ bình luận)

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
  http_response_code(200);
  exit();
}

require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  http_response_code(405);
  echo json_encode(["message" => "Method không được hỗ trợ!"]);
  exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$userId = intval($data["user_id"] ?? 0);
$commentId = intval($data["comment_id"] ?? 0);

if (!$userId || !$commentId) {
  http_response_code(400);
  echo json_encode(["message" => "Thiếu thông tin!"]);
  exit();
}

$stmt = $conn->prepare("DELETE FROM comments WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $commentId, $userId);

if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(["message" => "Không thể xóa bình luận!"]);
  exit();
}

if ($stmt->affected_rows === 0) {
  http_response_code(403);
  echo json_encode(["message" => "Bình luận không tồn tại hoặc không phải của bạn!"]);
  exit();
}

echo json_encode(["message" => "Đã xóa bình luận!"]);

$stmt->close();
$conn->close();

?>
