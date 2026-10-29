<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once "../config/database.php";

$adminId = intval($_GET["admin_id"] ?? 0);

if (!$adminId) {
    http_response_code(401);
    echo json_encode(["message" => "Yêu cầu đăng nhập quản trị viên!"]);
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
    echo json_encode(["message" => "Bạn không có quyền truy cập trang quản trị!"]);
    $conn->close();
    exit;
}

// Tự động thêm cột is_banned nếu DB chưa có
$colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'is_banned'");
if ($colCheck && $colCheck->num_rows === 0) {
    @$conn->query("ALTER TABLE users ADD is_banned TINYINT(1) NOT NULL DEFAULT 0");
}

$sql = "
    SELECT 
        u.id,
        u.name,
        u.email,
        u.student_code,
        u.class,
        u.role,
        u.avatar,
        u.created_at,
        COALESCE(u.is_banned, 0) AS is_banned,
        (SELECT COUNT(*) FROM posts WHERE posts.user_id = u.id) AS post_count,
        (SELECT COUNT(*) FROM comments WHERE comments.user_id = u.id) AS comment_count
    FROM users u
    ORDER BY u.id DESC
";

$result = $conn->query($sql);
$users = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row["id"] = intval($row["id"]);
        $row["is_banned"] = intval($row["is_banned"]);
        $row["post_count"] = intval($row["post_count"]);
        $row["comment_count"] = intval($row["comment_count"]);
        $users[] = $row;
    }
}

echo json_encode($users);
$conn->close();

