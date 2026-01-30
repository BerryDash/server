<?php
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$request_username = $_POST['username'];
$request_password = $_POST['password'];

$stmt = $conn0->prepare("SELECT id, username, password, token FROM users WHERE username = ?");
$stmt->bind_param("s", $request_username);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    if (password_verify($request_password, $row['password'])) {
        $login_ip = getIPAddress();
        $login_time = time();
        $uid = $row['id'];

        $stmt = $conn1->prepare("SELECT legacy_high_score, save_data FROM userdata WHERE id = ? LIMIT 1");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $result2 = $stmt->get_result();
        $stmt->close();
        if ($result2->num_rows != 1) exitWithMessage("-1", false);
        $row2 = $result2->fetch_assoc();

        $username = $row['username'];
        $highscore = $row2['legacy_high_score'];
        $token = $row['token'];
        $savedata = json_decode($row2['save_data'], true);
        $icon = $savedata['bird']['icon'] ?? 1;
        $overlay = $savedata['bird']['overlay'] ?? 0;

        $stmt = $conn0->prepare("UPDATE users SET latest_ip = ? WHERE id = ?");
        $stmt->bind_param("si", $login_ip, $uid);
        $stmt->execute();
        $stmt->close();

        if (
            getClientVersion() == "1.2-beta2" ||
            getClientVersion() == "1.2" ||
            getClientVersion() == "1.21" ||
            getClientVersion() == "1.3-beta1"
        ) {
            echo "$token:$uid:$highscore";
        } else if (getClientVersion() == "1.3-beta2" || getClientVersion() == "1.3" || getClientVersion() == "1.33") {
            echo "$token:$uid:$highscore:$icon:$overlay";
        } else if (getClientVersion() == "1.4.0-beta1" || getClientVersion() == "1.4.0" || getClientVersion() == "1.4.1") {
            echo "1:$token:$username:$uid:$highscore:$icon:$overlay";
        } 
    } else {
        $conn0->close();
        $conn1->close();
        exitWithMessage("-2", false);
    }
} else {
    $conn0->close();
    $conn1->close();
    exitWithMessage("-2", false);
}

$conn0->close();
$conn1->close();