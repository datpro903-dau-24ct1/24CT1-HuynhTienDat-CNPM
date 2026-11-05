<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once "../config/database.php";

$userId = isset($_GET["user_id"]) ? intval($_GET["user_id"]) : 0;

if (!$userId) {
    http_response_code(400);
    echo json_encode(["message" => "Thiếu user_id!"]);
    exit;
}

// Cập nhật last_active của chính mình
$colCheck = $conn->query("SHOW COLUMNS FROM users LIKE 'last_active'");
if ($colCheck && $colCheck->num_rows === 0) {
    @$conn->query("ALTER TABLE users ADD last_active DATETIME NULL DEFAULT NULL");
}
$updSelf = $conn->prepare("UPDATE users SET last_active = NOW() WHERE id = ?");
if ($updSelf) {
    $updSelf->bind_param("i", $userId);
    $updSelf->execute();
    $updSelf->close();
}

$sql = "
    SELECT
        u.id,
        u.name,
        u.email,
        u.student_code,
        u.class,
        u.avatar,
        u.last_active,
        CASE
            WHEN u.last_active IS NOT NULL AND TIMESTAMPDIFF(SECOND, u.last_active, NOW()) <= 120
            THEN 1
            ELSE 0
        END AS is_online

    FROM friendships f

    JOIN users u
        ON (
            CASE
                WHEN f.user_id = ? THEN f.friend_id
                ELSE f.user_id
            END
        ) = u.id

    WHERE
        (
            f.user_id = ?
            OR f.friend_id = ?
        )
        AND f.status = 'accepted'

    ORDER BY is_online DESC, u.name ASC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    http_response_code(500);
    echo json_encode(["message" => "Không thể lấy danh sách bạn bè!"]);
    exit;
}

$stmt->bind_param("iii", $userId, $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();
$friends = [];

while ($row = $result->fetch_assoc()) {
    $row["is_online"] = intval($row["is_online"]) === 1;
    $friends[] = $row;
}

echo json_encode($friends);

$stmt->close();
$conn->close();
