<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();

$post = getPostData();
$id = $post['id'] ?? '';
$content = $post['content'] ?? '';
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

if (!preg_match('/^[ a-zA-Z0-9!@#\$%\^&\*\(\)_\+\-=\[\]\{\};\':",\.<>\/\?\\\\|`~]+$/', $content)) exit;

$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn0->prepare("SELECT id FROM users WHERE username = ? AND token = ?");
$stmt->bind_param("ss", $username, $token);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$row = $result->fetch_assoc();
if (!$row) exit;

$user_id = $row["id"];

$stmt2 = $conn1->prepare("SELECT 1 FROM userdata WHERE id = ?");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$stmt->close();
$row2 = $result2->fetch_assoc();
if (!$row2) exit;

$content = base64_encode($content);

$stmt = $conn1->prepare("UPDATE chats SET content = ? WHERE userId = ? AND id = ?");
$stmt->bind_param("sii", $content, $user_id, $id);
$stmt->execute();
$stmt->close();

$conn0->close();
$conn1->close();