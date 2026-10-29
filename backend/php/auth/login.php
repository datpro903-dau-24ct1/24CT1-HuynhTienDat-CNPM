<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once "../config/database.php";

$data = json_decode(
    file_get_contents("php://input"),
    true
);

$email = trim($data["email"] ?? "");
$password = $data["password"] ?? "";

if (
    empty($email) ||
    empty($password)
) {
    http_response_code(400);

    echo json_encode([
        "message" => "Vui lòng nhập email và mật khẩu!"
    ]);

    exit;
}

/*
|--------------------------------------------------------------------------
| Tìm user
|--------------------------------------------------------------------------
*/

$colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'is_banned'");
if ($colCheck && $colCheck->num_rows === 0) {
    @$conn->query("ALTER TABLE users ADD is_banned TINYINT(1) NOT NULL DEFAULT 0");
}

$sql = "
    SELECT
        id,
        name,
        email,
        password,
        student_code,
        class,
        date_of_birth,
        avatar,
        role,
        is_banned,
        created_at
    FROM users
    WHERE email = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "s",
    $email
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {

    http_response_code(401);

    echo json_encode([
        "message" => "Email hoặc mật khẩu không đúng!"
    ]);

    $stmt->close();
    $conn->close();

    exit;
}

$user = $result->fetch_assoc();

/*
|--------------------------------------------------------------------------
| Kiểm tra mật khẩu
|--------------------------------------------------------------------------
*/

if (!password_verify($password, $user["password"])) {

    http_response_code(401);

    echo json_encode([
        "message" => "Email hoặc mật khẩu không đúng!"
    ]);

    $stmt->close();
    $conn->close();

    exit;
}

if (!empty($user["is_banned"])) {
    http_response_code(403);
    echo json_encode([
        "message" => "Tài khoản của bạn đã bị khóa do vi phạm tiêu chuẩn cộng đồng! Vui lòng liên hệ quản trị viên."
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

/*
|--------------------------------------------------------------------------
| Không trả password về frontend
|--------------------------------------------------------------------------
*/

unset($user["password"]);

/*
|--------------------------------------------------------------------------
| Trả thông tin user
|--------------------------------------------------------------------------
*/

http_response_code(200);

echo json_encode([
    "message" => "Đăng nhập thành công!",
    "user" => $user
]);

$stmt->close();
$conn->close();