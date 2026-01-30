<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();

$post = getPostData();
$request_content = $post['content'] ?? '';
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

if (!preg_match('/^[ a-zA-Z0-9!@#\$%\^&\*\(\)_\+\-=\[\]\{\};\':",\.<>\/\?\\\\|`~]+$/', $request_content)) {
    exitWithMessage(json_encode(["success" => false]));
}

$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn0->prepare("SELECT * FROM users WHERE username = ? AND token = ?");
$stmt->bind_param("ss", $username, $token);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
if ($result->num_rows != 1) exitWithMessage(json_encode(["success" => false]));
$row = $result->fetch_assoc();
$id = $row["id"];

$stmt = $conn1->prepare("SELECT * FROM userdata WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result2 = $stmt->get_result();
$stmt->close();
if ($result2->num_rows != 1) exitWithMessage(json_encode(["success" => false]));
$row2 = $result2->fetch_assoc();

$content = base64_encode($request_content);
$time = time();

$stmt = $conn1->prepare("INSERT INTO userposts (userId, content, timestamp) VALUES (?, ?, ?)");
$stmt->bind_param("isi", $id, $content, $time);
$stmt->execute();
$stmt->close();

echo encrypt(json_encode(["success" => true]));

$conn0->close();
$conn1->close();