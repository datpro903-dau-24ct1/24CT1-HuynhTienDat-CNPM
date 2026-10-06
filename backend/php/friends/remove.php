<?php

// HỦY KẾT BẠN / HỦY LỜI MỜI ĐÃ GỬI

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
$friendId = intval($data["friend_id"] ?? 0);

if (!$userId || !$friendId || $userId === $friendId) {
  http_response_code(400);
  echo json_encode(["message" => "Thiếu thông tin!"]);
  exit();
}

// Xóa quan hệ bạn bè (2 chiều) hoặc lời mời do chính mình gửi đang chờ

$sql = "
    DELETE FROM friendships
    WHERE
        (
            status = 'accepted'
            AND (
                (user_id = ? AND friend_id = ?)
                OR (user_id = ? AND friend_id = ?)
            )
        )
        OR (
            status = 'pending'
            AND user_id = ?
            AND friend_id = ?
        )
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("iiiiii", $userId, $friendId, $friendId, $userId, $userId, $friendId);

if (!$stmt->execute()) {
  http_response_code(500);
  echo json_encode(["message" => "Không thể thực hiện thao tác!"]);
  exit();
}

if ($stmt->affected_rows === 0) {
  http_response_code(400);
  echo json_encode(["message" => "Không có quan hệ để hủy!"]);
  exit();
}

echo json_encode(["message" => "Đã hủy thành công!"]);

$stmt->close();
$conn->close();

?>
