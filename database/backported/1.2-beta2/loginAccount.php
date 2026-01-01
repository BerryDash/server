<?php
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$request_username = $_POST['username'];
$request_password = $_POST['password'];

$stmt = $conn0->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $request_username);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if (password_verify($request_password, $row["password"])) {
            $login_ip = getIPAddress();
            $login_time = time();
            $uid = $row['id'];

            $stmt2 = $conn1->prepare("SELECT * FROM userdata WHERE id = ?");
            $stmt2->bind_param("i", $uid);
            $stmt2->execute();
            $result2 = $stmt2->get_result();

            if ($result2->num_rows != 1) {
                echo '-1';
                exit;
            }

            $user2 = $result2->fetch_assoc();

            $username = $row['username'];
            $highscore = $user2['legacy_high_score'];
            $token = $user2['token'];
            $savedata = json_decode($user2['save_data'], true);
            $icon = $savedata['bird']['icon'] ?? 1;
            $overlay = $savedata['bird']['overlay'] ?? 0;

            $stmt = $conn0->prepare("UPDATE users SET latest_ip = ? WHERE id = ?");
            $stmt->bind_param("si", $login_ip, $uid);
            $stmt->execute();

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
            $stmt->close();
            $conn0->close();
            $conn1->close();
            exit("-2");
        }
    }
} else {
    $stmt->close();
    $conn0->close();
    $conn1->close();
    exit("-2");
}

$stmt->close();
$conn0->close();
$conn1->close();