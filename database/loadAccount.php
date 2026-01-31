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

$stmt = $conn0->prepare("SELECT id, username FROM users WHERE username = ? AND token = ?");
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

$stmt = $conn1->prepare("SELECT save_data FROM userdata WHERE id = ?");
$stmt->bind_param("i", $id);
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
$savedata['account']['session'] = $token;
if ($savedata['version'] !== '0') {
    $savedata['version'] = '0';
    if (isset($savedata["bird"]["customIcon"]["purchased"]))
    {
        $purchased = $savedata["bird"]["customIcon"]["purchased"];
        unset($savedata["bird"]["customIcon"]["purchased"]);
        $data = [];
        foreach ($purchased as $icon) {
            $stmt2 = $conn1->prepare("SELECT userId, data, price, name FROM marketplaceicons WHERE id = ?");
            $stmt2->bind_param("s", $icon);
            $stmt2->execute();
            $result3 = $stmt2->get_result();
            $stmt2->close();
            if ($result3->num_rows != 1) continue;
            $row3 = $result3->fetch_assoc();
            $uid = $row3['userId'];

            $stmt3 = $conn0->prepare("SELECT username FROM users WHERE id = ?");
            $stmt3->bind_param("i", $uid);
            $stmt3->execute();
            $result4 = $stmt3->get_result();
            $stmt3->close();
            if ($result4->num_rows != 1) continue;
            $row4 = $result4->fetch_assoc();

            $data[] = [
                "username" => $row4["username"],
                "userid" => $row3["userId"],
                "data" => $row3["data"],
                "uuid" => $icon,
                "price" => $row3["price"],
                "name" => base64_decode($row3["name"])
            ];
        }
        $savedata["bird"]["customIcon"]["data"] = $data;
    }
}
echo encrypt(json_encode([
    "success" => true,
    "data" => $savedata
]));

$conn0->close();
$conn1->close();