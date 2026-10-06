<?php

// SỬA / XÓA BÀI VIẾT
// - Sửa: chỉ chủ bài viết
// - Xóa: chủ bài viết hoặc admin

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Access-Control-Allow-Methods: POST, OPTIONS");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
  http_response_code(200);
  exit();
}

require_once "../config/database.php";

function respond($code, $message, $extra = []) {
  http_response_code($code);
  echo json_encode(array_merge(["message" => $message], $extra));
  exit();
}

function fetchOne($conn, $sql, $id) {
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $row = $stmt->get_result()->fetch_assoc();
  $stmt->close();
  return $row;
}

function runById($conn, $sql, $id) {
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $id);
  $ok = $stmt->execute();
  $stmt->close();
  return $ok;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
  respond(405, "Method không được hỗ trợ!");
}

$data = json_decode(file_get_contents("php://input"), true);

$action = $data["action"] ?? "";
$userId = intval($data["user_id"] ?? 0);
$postId = intval($data["post_id"] ?? 0);

if (!$userId || !$postId) {
  respond(400, "Thiếu thông tin!");
}

$post = fetchOne($conn, "SELECT user_id, image, file FROM posts WHERE id = ? LIMIT 1", $postId);

if (!$post) {
  respond(404, "Bài viết không tồn tại!");
}

$isOwner = intval($post["user_id"]) === $userId;

$me = fetchOne($conn, "SELECT role FROM users WHERE id = ? LIMIT 1", $userId);
$isAdmin = strtolower(trim($me["role"] ?? "")) === "admin";

// SỬA BÀI

if ($action === "update") {
  if (!$isOwner) {
    respond(403, "Bạn không có quyền sửa bài viết này!");
  }

  $content = trim($data["content"] ?? "");

  if ($content === "" && !$post["image"] && !$post["file"]) {
    respond(400, "Nội dung bài viết không được để trống!");
  }

  $stmt = $conn->prepare("UPDATE posts SET content = ? WHERE id = ?");
  $stmt->bind_param("si", $content, $postId);

  if (!$stmt->execute()) {
    respond(500, "Không thể cập nhật bài viết!");
  }

  respond(200, "Đã cập nhật bài viết!", ["content" => $content]);
}

// XÓA BÀI

if ($action === "delete") {
  if (!$isOwner && !$isAdmin) {
    respond(403, "Bạn không có quyền xóa bài viết này!");
  }

  try {
    $conn->begin_transaction();

    $ok =
      runById($conn, "DELETE FROM post_likes WHERE post_id = ?", $postId) &&
      runById($conn, "DELETE FROM comments WHERE post_id = ?", $postId) &&
      runById($conn, "DELETE FROM posts WHERE id = ?", $postId);

    if (!$ok) {
      throw new Exception("delete failed");
    }

    $conn->commit();
  } catch (Throwable $e) {
    $conn->rollback();
    respond(500, "Không thể xóa bài viết!");
  }

  // XÓA FILE ĐÍNH KÈM TRÊN SERVER

  foreach ([$post["image"], $post["file"]] as $path) {
    if ($path && strpos($path, "uploads/posts/") === 0 && strpos($path, "..") === false) {
      $fullPath = __DIR__ . "/../" . $path;

      if (is_file($fullPath)) {
        unlink($fullPath);
      }
    }
  }

  respond(200, "Đã xóa bài viết!");
}

respond(400, "Hành động không hợp lệ!");

$conn->close();

?>
