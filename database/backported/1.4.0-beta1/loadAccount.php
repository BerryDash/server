<?php
if (getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
    $post = getPostData();
    $token = $post['gameSession'] ?? '';
    $username = $post['userName'] ?? '';
} else {
    $token = $_POST['gameSession'] ?? '';
    $username = $_POST['userName'] ?? '';
}

$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn0->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows != 1) {
    echo (getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") ? encrypt("-1") : "-1";
    $conn0->close();
    $conn1->close();
    exit;
}

$row = $result->fetch_assoc();
$id = $row["id"];

$stmt = $conn1->prepare("SELECT save_data, legacy_high_score FROM userdata WHERE id = ? AND token = ? LIMIT 1");
$stmt->bind_param("is", $id, $token);
$stmt->execute();
$result2 = $stmt->get_result();
$stmt->close();

if ($result2->num_rows != 1) {
    echo (getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") ? encrypt("-1") : "-1";
    $conn0->close();
    $conn1->close();
    exit;
}

$user2 = $result2->fetch_assoc();

$savedata = json_decode($user2['save_data'], true);
$icon = $savedata['bird']['icon'] ?? 1; 
$overlay = $savedata['bird']['overlay'] ?? 0;   
if (getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
    $birdColor = $savedata['settings']['colors']['icon'] ?? [255,255,255];
    $msg = "1:" . $user2['legacy_high_score'] . ":" . $icon . ":" . $overlay . ":0:0:0:0:0:0:" . ":" . $birdColor[0] . ":" . $birdColor[1] . ":" . $birdColor[2];
    if (getClientVersion() == "1.5.2") {
        $overlayColor = $savedata['settings']['colors']['overlay'] ?? [255,255,255];
        $msg .= ":" . $overlayColor[0] . ":" . $overlayColor[1] . ":" . $overlayColor[2];
    }
    echo encrypt($msg);
} else {
    echo "1:" . $user2['legacy_high_score'] . ":" . $icon . ":" . $overlay;
}

$conn0->close();
$conn1->close();