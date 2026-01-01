<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();

$post = getPostData();
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';
$price = (int)$post['price'] ?? 0;
$name = $post['name'] ?? '';
$name = base64_encode($name);
$filecontent = $post['filecontent'] ?? '';

if ($price < 10) exitWithMessage(json_encode(["success" => false, "message" => "Price cannot be be under 10 coins"]));
if (!preg_match('/^[a-zA-Z0-9 ]+$/', base64_decode($name))) exitWithMessage(json_encode(["success" => false, "message" => "Name is invalid"]));
if (!$filecontent) exitWithMessage(json_encode(["success" => false, "message" => "Invalid image uploaded"]));
$decoded = base64_decode($filecontent, true);
if (!$decoded) exitWithMessage(json_encode(["success" => false, "message" => "Invalid image uploaded"]));
if (strlen($decoded) > 1024 * 1024) exitWithMessage(json_encode(["success" => false, "message" => "File size exceeds 1 MB limit"]));
$info = getimagesizefromstring($decoded);
if (!$info) exitWithMessage(json_encode(["success" => false, "message" => "Invalid image uploaded"]));
if ($info[2] !== IMAGETYPE_PNG) exitWithMessage(json_encode(["success" => false, "message" => "Image must be a PNG"]));
if ($info[0] !== 128 || $info[1] !== 128) exitWithMessage(json_encode(["success" => false, "message" => "Image has to be 128x128"]));

$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn0->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) exitWithMessage(json_encode(["success" => false, "message" => "Invalid session token or username, please refresh login"]));
$stmt->close();
$id = $row["id"];

$stmt2 = $conn1->prepare("SELECT * FROM userdata WHERE token = ? AND id = ?");
$stmt2->bind_param("si", $token, $id);
$stmt2->execute();
$result2 = $stmt2->get_result();
$row2 = $result2->fetch_assoc();
if (!$row2) exitWithMessage(json_encode(["success" => false, "message" => "Invalid session token or username, please refresh login"]));
$stmt2->close();

$time = time();
$hash = hash('sha512', base64_decode($filecontent));

$stmt = $conn1->prepare("SELECT id FROM marketplaceicons WHERE hash = ?");
$stmt->bind_param("s", $hash);
$stmt->execute();
$result = $stmt->get_result();
if ($result->fetch_assoc()) {
    $stmt->close();
    exitWithMessage(json_encode(["success" => false, "message" => "This icon already exists in the marketplace"]));
}
$stmt->close();

$uuid = uuidv4();

$stmt = $conn1->prepare("INSERT INTO marketplaceicons (uuid, userId, data, hash, price, name, timestamp) VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sissisi", $uuid, $id, $filecontent, $hash, $price, $name, $time);
$stmt->execute();
$insertId = $conn1->insert_id;
$stmt->close();

echo encrypt(json_encode(["success" => true, "message" => "Icon uploaded successfully! It will be reviewed and accepted or denied soon"]));

$conn0->close();
$conn1->close();