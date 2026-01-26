<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$post = getPostData();
$targetId = (int)$post['targetId'] ?? 0;
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

$stmt = $conn0->prepare("SELECT id FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
if ($result->num_rows != 1) exitWithMessage(json_encode(["success" => false, "message" => 'User info not found']));
$user_id = $result->fetch_assoc()["id"];

$stmt = $conn1->prepare("SELECT 1 FROM userdata WHERE token = ? AND id = ?");
$stmt->bind_param("si", $token, $user_id);
$stmt->execute();
$result2 = $stmt->get_result();
$stmt->close();
if ($result2->num_rows != 1) exitWithMessage(json_encode(["success" => false, "message" => 'User info not found']));

$time = time();

$stmt = $conn1->prepare("UPDATE userposts SET deleted_at = ? WHERE id = ? AND userId = ? AND deleted_at = 0");
$stmt->bind_param("iii", $time, $targetId, $user_id);
$stmt->execute();
$stmt->close();

echo encrypt(json_encode(["success" => $stmt->affected_rows > 0]));

$conn0->close();
$conn1->close();