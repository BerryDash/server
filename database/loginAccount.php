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
    getClientVersion() == "1.33" ||
    getClientVersion() == "1.4.0-beta1" ||
    getClientVersion() == "1.4.0" ||
    getClientVersion() == "1.4.1"
) {
    require __DIR__ . '/backported/1.2-beta2/loginAccount.php';
    exit;
}
if (getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
    require __DIR__ . '/backported/1.5/loginAccount.php';
    exit;
}
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$post = getPostData();
$username = $post['username'];
$password = $post['password'];

$stmt = $conn0->prepare("SELECT id, username, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows != 1) exitWithMessage(json_encode(["success" => false, "message" => "Invalid username or password"]));
$row = $result->fetch_assoc();
if (!password_verify($password, $row["password"])) exitWithMessage(json_encode(["success" => false, "message" => "Invalid username or password"]));

$id = $row['id'];

$stmt = $conn1->prepare("SELECT token FROM userdata WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result2 = $stmt->get_result();
$stmt->close();
if ($result2->num_rows != 1) exitWithMessage(json_encode(["success" => false, "message" => "Invalid username or password"]));

$token = $result2->fetch_assoc()['token'];
$ip = getIPAddress();

$stmt = $conn0->prepare("UPDATE users SET latest_ip = ? WHERE id = ?");
$stmt->bind_param("si", $ip, $id);
$stmt->execute();
$stmt->close();
$stmt = $conn1->prepare("UPDATE userdata SET token = ? WHERE id = ?");
$stmt->bind_param("si", $token, $id);
$stmt->execute();
$stmt->close();

$data = ["session" => $token, "username" => $row['username'], "userid" => $id];

echo encrypt(json_encode(["success" => true, "data" => $data]));

$conn0->close();
$conn1->close();