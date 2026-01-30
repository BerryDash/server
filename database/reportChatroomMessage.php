<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();

$post = getPostData();
$id = $post['id'] ?? '';
$reason = $post['reason'] ?? '';
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

if (!preg_match('/^[ a-zA-Z0-9!@#\$%\^&\*\(\)_\+\-=\[\]\{\};\':",\.<>\/\?\\\\|`~]+$/', $reason)) exit;

$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn0->prepare("SELECT * FROM users WHERE username = ? AND token = ? LIMIT 1");
$stmt->bind_param("ss", $username, $token);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($result->num_rows != 1) exit;
$stmt->close();

$user_id = $row["id"];

$stmt2 = $conn1->prepare("SELECT * FROM userdata WHERE id = ? LIMIT 1");
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$row2 = $result2->fetch_assoc();
if ($result2->num_rows != 1) exit;
$stmt2->close();

$stmt = $conn1->prepare("SELECT id FROM chats WHERE userId != ? AND deleted_at = 0 AND id = ? LIMIT 1");
$stmt->bind_param("ii", $user_id, $id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

if ($res->num_rows < 0) exit;

$stmt = $conn1->prepare("SELECT id FROM chatroom_reports WHERE chatId = ? AND userId = ? LIMIT 1");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

if ($res->num_rows > 0) exit;

$time = time();
$reason = base64_encode($reason);

$stmt = $conn1->prepare("INSERT INTO chatroom_reports (chatid, userId, reason, timestamp) VALUES (?, ?, ?, ?)");
$stmt->bind_param("iisi", $id, $user_id, $reason, $time);
$stmt->execute();
$stmt->close();

$conn0->close();
$conn1->close();