<?php 
require __DIR__ . '/../incl/util.php';
setPlainHeader();

$post = getPostData();
$id = $post['id'] ?? '';
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn0->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if ($result->num_rows != 1) exit;
$stmt->close();

$user_id = $row["id"];

$stmt = $conn1->prepare("SELECT 1 FROM userdata WHERE token = ? AND id = ? LIMIT 1");
$stmt->bind_param("si", $token, $user_id);
$stmt->execute();
$result2 = $stmt->get_result();
if ($result2->num_rows != 1) exit;
$stmt->close();

$time = time();

$stmt = $conn1->prepare("UPDATE chats SET deleted_at = ? WHERE userId = ? AND id = ?");
$stmt->bind_param("iii", $time, $user_id, $id);
$stmt->execute();
$stmt->close();

$conn0->close();
$conn1->close();