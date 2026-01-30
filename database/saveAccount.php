<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
if (
    getClientVersion() == "1.2-beta2" ||
    getClientVersion() == "1.2" ||
    getClientVersion() == "1.21" ||
    getClientVersion() == "1.3-beta1" ||
    getClientVersion() == "1.3-beta2" ||
    getClientVersion() == "1.3" ||
    getClientVersion() == "1.33"
) {
    require __DIR__ . '/backported/1.2-beta2/syncAccount.php';
    exit;
}
if (getClientVersion() == "1.4.0-beta1" || getClientVersion() == "1.4.0" || getClientVersion() == "1.4.1") {
    require __DIR__ . '/backported/1.4.0-beta1/saveAccount.php';
    exit;
}
if (getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
    require __DIR__ . '/backported/1.5/saveAccount.php';
    exit;
}

$post = getPostData();
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';
$savedata = $post['saveData'] ?? 'e30=';

try {
    $savedata = json_decode(base64_decode($savedata), true);
    $savedata['account']['id'] = null;
    $savedata['account']['name'] = null;
    $savedata['account']['session'] = null;
    $savedata = json_encode($savedata);
} catch (Exception $e) {
    echo encrypt(json_encode(["success" => false, "message" => "Couldn't parse save data"]));
}

$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn0->prepare("SELECT * FROM users WHERE username = ? AND token = ?");
$stmt->bind_param("ss", $username, $token);
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

$stmt = $conn1->prepare("SELECT id FROM userdata WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows != 1) {
    echo encrypt(json_encode(["success" => false, "message" => "Invalid session token or username, please refresh login"]));
    $conn0->close();
    $conn1->close();
    exit;
}

$stmt = $conn1->prepare("UPDATE userdata SET save_data = ? WHERE id = ?");
$stmt->bind_param("si", $savedata, $id);
$stmt->execute();
$stmt->close();
echo encrypt(json_encode(["success" => true]));

$conn0->close();
$conn1->close();