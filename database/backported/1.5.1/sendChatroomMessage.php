<?php
$post = getPostData();
$request_content = $post['content'] ?? '';
$token = $post['gameSession'] ?? '';

if (!preg_match('/^[ a-zA-Z0-9!@#\$%\^&\*\(\)_\+\-=\[\]\{\};\':",\.<>\/\?\\\\|`~]+$/', $request_content)) {
    exitWithMessage("-1");
}

$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn0->prepare("SELECT id FROM users WHERE token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$row = $result->fetch_assoc();
if (!$row) exitWithMessage("-1");

$id = $row["id"];
$content = base64_encode($request_content);
$time = time();

$stmt = $conn1->prepare("INSERT INTO chats (userId, content, timestamp) VALUES (?, ?, ?)");
$stmt->bind_param("isi", $id, $content, $time);
$stmt->execute();
$stmt->close();

echo encrypt("1");

$conn0->close();
$conn1->close();