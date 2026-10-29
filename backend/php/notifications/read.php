<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once "../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

$notification_id = intval($_GET["id"] ?? $data["id"] ?? $_POST["id"] ?? 0);

if ($notification_id <= 0) {
    http_response_code(400);
    echo json_encode([
        "message" => "ID thông báo không hợp lệ!"
    ], JSON_UNESCAPED_UNICODE);
    $conn->close();
    exit;
}

$sql = "
    UPDATE notifications
    SET is_read = 1
    WHERE id = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $notification_id);

if ($stmt->execute()) {
    http_response_code(200);
    echo json_encode([
        "message" => "Đã đánh dấu thông báo đã đọc!"
    ], JSON_UNESCAPED_UNICODE);
} else {
    http_response_code(500);
    echo json_encode([
        "message" => "Không thể đánh dấu thông báo!"
    ], JSON_UNESCAPED_UNICODE);
}

$stmt->close();
$conn->close();
