<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["message" => "Method không được hỗ trợ!"]);
    exit;
}

require_once "../config/database.php";

$data = json_decode(file_get_contents("php://input"), true);

$adminId = intval($data["admin_id"] ?? 0);
$targetUserId = intval($data["user_id"] ?? 0);
$newBannedStatus = isset($data["is_banned"]) ? (intval($data["is_banned"]) ? 1 : 0) : 1;

if (!$adminId || !$targetUserId) {
    http_response_code(400);
    echo json_encode(["message" => "Thiếu thông tin người dùng!"]);
    $conn->close();
    exit;
}

// Kiểm tra quyền admin
$checkAdmin = $conn->prepare("SELECT role FROM users WHERE id = ? LIMIT 1");
$checkAdmin->bind_param("i", $adminId);
$checkAdmin->execute();
$adminUser = $checkAdmin->get_result()->fetch_assoc();
$checkAdmin->close();

if (!$adminUser || strtolower(trim($adminUser["role"] ?? "")) !== "admin") {
    http_response_code(403);
    echo json_encode(["message" => "Bạn không có quyền thực hiện thao tác này!"]);
    $conn->close();
    exit;
}

// Kiểm tra user mục tiêu
$checkTarget = $conn->prepare("SELECT id, name, role FROM users WHERE id = ? LIMIT 1");
$checkTarget->bind_param("i", $targetUserId);
$checkTarget->execute();
$targetUser = $checkTarget->get_result()->fetch_assoc();
$checkTarget->close();

if (!$targetUser) {
    http_response_code(404);
    echo json_encode(["message" => "Không tìm thấy người dùng!"]);
    $conn->close();
    exit;
}

// Không cho phép khóa tài khoản admin khác hoặc chính mình
if (strtolower(trim($targetUser["role"] ?? "")) === "admin") {
    http_response_code(400);
    echo json_encode(["message" => "Không thể khóa tài khoản quản trị viên!"]);
    $conn->close();
    exit;
}

// Tự động thêm cột is_banned nếu DB chưa có
$colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'is_banned'");
if ($colCheck && $colCheck->num_rows === 0) {
    @$conn->query("ALTER TABLE users ADD is_banned TINYINT(1) NOT NULL DEFAULT 0");
}

$stmt = $conn->prepare("UPDATE users SET is_banned = ? WHERE id = ?");
$stmt->bind_param("ii", $newBannedStatus, $targetUserId);

if ($stmt->execute()) {
    $actionText = $newBannedStatus ? "Khóa tài khoản" : "Mở khóa tài khoản";
    http_response_code(200);
    echo json_encode([
        "message" => "Đã {$actionText} sinh viên {$targetUser['name']} thành công!",
        "user_id" => $targetUserId,
        "is_banned" => $newBannedStatus
    ]);
} else {
    http_response_code(500);
    echo json_encode(["message" => "Không thể cập nhật trạng thái người dùng!"]);
}

$stmt->close();
$conn->close();

