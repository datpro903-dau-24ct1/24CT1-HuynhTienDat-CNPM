<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

require_once "../config/database.php";

$postId = isset($_GET["post_id"])
    ? intval($_GET["post_id"])
    : 0;


// ======================================================
// GET - LẤY COMMENT
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    if (!$postId) {

        http_response_code(400);

        echo json_encode([
            "message" => "Thiếu post_id!"
        ]);

        exit;
    }

    $sql = "
        SELECT
            c.id,
            c.post_id,
            c.user_id,
            c.content,
            c.created_at,

            u.name,
            u.avatar

        FROM comments c

        JOIN users u
            ON c.user_id = u.id

        WHERE c.post_id = ?

        ORDER BY c.created_at ASC
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $postId
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $comments = [];

    while ($row = $result->fetch_assoc()) {
        $comments[] = $row;
    }

    echo json_encode($comments);

    $stmt->close();
    $conn->close();

    exit;
}


// ======================================================
// POST - THÊM COMMENT
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $data = json_decode(
        file_get_contents("php://input"),
        true
    );

    if (!$postId && isset($data["post_id"])) {
        $postId = intval($data["post_id"]);
    }

    if (!$postId) {

        http_response_code(400);

        echo json_encode([
            "message" => "Thiếu post_id!"
        ]);

        exit;
    }



    $userId = intval($data["user_id"] ?? 0);
    $content = trim($data["content"] ?? "");

    if (!$userId || $content === "") {

        http_response_code(400);

        echo json_encode([
            "message" => "Nội dung bình luận không hợp lệ!"
        ]);

        exit;
    }


    // =========================
    // THÊM COMMENT
    // =========================

    $sql = "
        INSERT INTO comments
        (
            post_id,
            user_id,
            content
        )
        VALUES (?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "iis",
        $postId,
        $userId,
        $content
    );

    if (!$stmt->execute()) {

        http_response_code(500);

        echo json_encode([
            "message" => "Không thể bình luận!"
        ]);

        exit;
    }

    $commentId = $stmt->insert_id;

    $stmt->close();


    // =========================
    // LẤY COMMENT VỪA TẠO
    // =========================

    $sql = "
        SELECT
            c.id,
            c.post_id,
            c.user_id,
            c.content,
            c.created_at,

            u.name,
            u.avatar

        FROM comments c

        JOIN users u
            ON c.user_id = u.id

        WHERE c.id = ?

        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "i",
        $commentId
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $comment = $result->fetch_assoc();
    $stmt->close();

    /*
    |--------------------------------------------------------------------------
    | TẠO THÔNG BÁO COMMENT CHO CHỦ BÀI VIẾT
    |--------------------------------------------------------------------------
    */
    $postSql = "SELECT user_id FROM posts WHERE id = ? LIMIT 1";
    $postStmt = $conn->prepare($postSql);
    $postStmt->bind_param("i", $postId);
    $postStmt->execute();
    $postRes = $postStmt->get_result();

    if ($postRow = $postRes->fetch_assoc()) {
        $postOwnerId = intval($postRow["user_id"]);

        // Chỉ tạo thông báo nếu người comment khác chủ bài viết
        if ($postOwnerId && $postOwnerId !== $userId) {
            $notifContent = "đã bình luận về bài viết của bạn.";
            $insSql = "INSERT INTO notifications (user_id, type, from_user_id, content, related_id) VALUES (?, 'comment', ?, ?, ?)";
            $insStmt = $conn->prepare($insSql);
            $insStmt->bind_param("iisi", $postOwnerId, $userId, $notifContent, $postId);
            $insStmt->execute();
            $insStmt->close();
        }
    }
    $postStmt->close();

    http_response_code(201);

    echo json_encode([
        "message" => "Bình luận thành công!",
        "comment" => $comment
    ]);

    $conn->close();

    exit;
}


// ======================================================
// METHOD KHÔNG HỖ TRỢ
// ======================================================

http_response_code(405);

echo json_encode([
    "message" => "Method không được hỗ trợ!"
]);

$conn->close();

?>