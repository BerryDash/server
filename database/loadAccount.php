<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
if (
    getClientVersion() == "1.4.0-beta1" ||
    getClientVersion() == "1.4.0" ||
    getClientVersion() == "1.4.1" ||
    getClientVersion() == "1.5.0" ||
    getClientVersion() == "1.5.1" ||
    getClientVersion() == "1.5.2"
) {
    require __DIR__ . '/backported/1.4.0-beta1/loadAccount.php';
    exit;
}

$post = getPostData();
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';

$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn0->prepare("SELECT id, username FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows != 1) {
    echo encrypt(json_encode(["success" => false, "message" => "Invalid session token or username, please refresh login"]));
    $conn0->close();
    $conn1->close();
    exit;
}

$row = $result->fetch_assoc();
$id = $row["id"];

$stmt = $conn1->prepare("SELECT save_data, token FROM userdata WHERE id = ? AND token = ?");
$stmt->bind_param("is", $id, $token);
$stmt->execute();
$result2 = $stmt->get_result();
$stmt->close();

if ($result2->num_rows != 1) {
    echo encrypt(json_encode(["success" => false, "message" => "Invalid session token or username, please refresh login"]));
    $conn0->close();
    $conn1->close();
    exit;
}

$row2 = $result2->fetch_assoc();

$savedata = json_decode($row2['save_data'], true);
$savedata['account']['id'] = $id;
$savedata['account']['name'] = $row['username'];
$savedata['account']['session'] = $row2['token'];
echo encrypt(json_encode([
    "success" => true,
    "data" => $savedata
]));

$conn0->close();
$conn1->close();