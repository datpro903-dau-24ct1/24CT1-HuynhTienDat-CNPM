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

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["message" => "Phương thức không được hỗ trợ!"]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$userId = intval($data["user_id"] ?? 0);
$currentPassword = $data["current_password"] ?? "";
$newPassword = $data["new_password"] ?? "";
$confirmPassword = $data["confirm_password"] ?? "";

/* KIỂM TRA DỮ LIỆU ĐẦU VÀO */

if (!$userId || empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
    http_response_code(400);
    echo json_encode(["message" => "Vui lòng nhập đầy đủ các trường!"]);
    exit();
}

if (strlen($newPassword) < 6) {
    http_response_code(400);
    echo json_encode(["message" => "Mật khẩu mới phải có ít nhất 6 ký tự!"]);
    exit();
}

if ($newPassword !== $confirmPassword) {
    http_response_code(400);
    echo json_encode(["message" => "Mật khẩu xác nhận không khớp!"]);
    exit();
}

/* LẤY MẬT KHẨU HIỆN TẠI TỪ DATABASE */

$sql = "SELECT password FROM users WHERE id = ? LIMIT 1";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(["message" => "Người dùng không tồn tại!"]);
    $stmt->close();
    $conn->close();
    exit();
}

$user = $result->fetch_assoc();
$stmt->close();

/* KIỂM TRA MẬT KHẨU CŨ */

if (!password_verify($currentPassword, $user["password"])) {
    http_response_code(400);
    echo json_encode(["message" => "Mật khẩu hiện tại không chính xác!"]);
    $conn->close();
    exit();
}

/* KHÔNG CHO ĐẶT TRÙNG MẬT KHẨU CŨ */

if ($currentPassword === $newPassword) {
    http_response_code(400);
    echo json_encode(["message" => "Mật khẩu mới không được trùng với mật khẩu hiện tại!"]);
    $conn->close();
    exit();
}

/* MÃ HÓA VÀ CẬP NHẬT MẬT KHẨU MỚI */

$hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

$updateSql = "UPDATE users SET password = ? WHERE id = ?";
$updateStmt = $conn->prepare($updateSql);
$updateStmt->bind_param("si", $hashedPassword, $userId);

if ($updateStmt->execute()) {
    http_response_code(200);
    echo json_encode(["message" => "Đổi mật khẩu thành công!"]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Có lỗi xảy ra khi cập nhật mật khẩu!"]);
}

$updateStmt->close();
$conn->close();

