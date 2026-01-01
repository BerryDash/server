<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$post = getPostData();
$targetId = (int)$post['targetId'] ?? 0;
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

$stmt = $conn0->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows != 1) exitWithMessage(json_encode(["success" => false, "message" => 'User info not found']));
$row = $result->fetch_assoc();
$user_id = $row["id"];

$stmt2 = $conn1->prepare("SELECT * FROM userdata WHERE token = ? AND id = ?");
$stmt2->bind_param("si", $token, $user_id);
$stmt2->execute();
$result2 = $stmt2->get_result();
if ($result2->num_rows != 1) exitWithMessage(json_encode(["success" => false, "message" => 'User info not found']));
$row2 = $result2->fetch_assoc();

$time = time();

$stmt = $conn1->prepare("UPDATE userposts SET deleted_at = ? WHERE id = ? AND userId = ? AND deleted_at = 0");
$stmt->bind_param("iii", $time, $targetId, $user_id);
$stmt->execute();

echo encrypt(json_encode(["success" => $stmt->affected_rows > 0]));

$conn0->close();
$conn1->close();