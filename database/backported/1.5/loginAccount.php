<?php
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$post = getPostData();
$username = $post['username'];
$password = $post['password'];
$currentHighScore = $post['currentHighScore'] ?? 0;
$loginType = $post['loginType'] ?? '0';

$stmt = $conn0->prepare("SELECT id, username, password FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
if ($result->num_rows != 1) exitWithMessage("-1");
$row = $result->fetch_assoc();

if (!password_verify($password, $row["password"])) exitWithMessage("-1");

$id = $row['id'];
$stmt = $conn1->prepare("SELECT token, legacy_high_score FROM userdata WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result2 = $stmt->get_result();
$stmt->close();
if ($result2->num_rows != 1) exitWithMessage("-1");
$row2 = $result2->fetch_assoc();

$token = $row2['token'];
$ip = getIPAddress();

$stmt = $conn0->prepare("UPDATE users SET latest_ip = ? WHERE id = ?");
$stmt->bind_param("si", $ip, $id);
$stmt->execute();
$stmt->close();
$stmt = $conn1->prepare("UPDATE userdata SET token = ? WHERE id = ?");
$stmt->bind_param("si", $token, $id);
$stmt->execute();
$stmt->close();

if ($currentHighScore > $row2['legacy_high_score']) {
    $stmt = $conn1->prepare("UPDATE userdata SET legacy_high_score = ? WHERE id = ?");
    $stmt->bind_param("ii", $currentHighScore, $id);
    $stmt->execute();
    $stmt->close();
    $row2['legacy_high_score'] = $currentHighScore;
}

$savedata = json_decode($row['save_data'], true);
$birdColor = $savedata['settings']['colors']['icon'] ?? [255,255,255];
$overlayColor = $savedata['settings']['colors']['overlay'] ?? [255,255,255];

if ($loginType === "0") {
    echo encrypt("1" . ":" . $token . ":" . $row['username'] . ":" . $id . ":" . $row2['legacy_high_score'] . ":" . ($savedata['bird']['icon'] ?? 1) . ":" . ($savedata['bird']['overlay'] ?? 0) . ":0:0:0:0:0:" . ":" . $birdColor[0] . ":" . $birdColor[1] . ":" . $birdColor[2] . ":" . $overlayColor[0] . ":" . $overlayColor[1] . ":" . $overlayColor[2]);
} elseif ($loginType === "1") {
    echo encrypt("1" . ":" . $token . ":" . $row['username'] . ":" . $id);
}
$conn0->close();
$conn1->close();