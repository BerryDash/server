<?php
$post = getPostData();
$token = $post['gameSession'] ?? '';
$username = $post['userName'] ?? '';
$highScore = (int)$post['highScore'] ?? 0;
$icon = (int)$post['icon'] ?? 1;
$overlay = (int)$post['overlay'] ?? 0;
if (getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
    $birdR = (int)$post['birdR'] ?? 255;
    $birdG = (int)$post['birdG'] ?? 255;
    $birdB = (int)$post['birdB'] ?? 255;
    $birdColor = [$birdR, $birdG, $birdB];
}
if (getClientVersion() == "1.5.2") {
    $overlayR = (int)$post['overlayR'] ?? 255;
    $overlayG = (int)$post['overlayG'] ?? 255;
    $overlayB = (int)$post['overlayB'] ?? 255;
    $overlayColor = [$overlayR, $overlayG, $overlayB];
}
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn0->prepare("SELECT id FROM users WHERE username = ? AND token = ?");
$stmt->bind_param("ss", $username, $token);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows != 1) {
    echo encrypt("-1");
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
    echo encrypt("-1");
    $conn0->close();
    $conn1->close();
    exit;
}

$row2 = $result2->fetch_assoc();
$savedata = json_decode($row2['save_data'], true);
$savedata['bird']['icon'] = $icon;
$savedata['bird']['overlay'] = $overlay;
if (getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") $savedata['settings']['colors']['icon'] = $birdColor;
if (getClientVersion() == "1.5.2") $savedata['settings']['colors']['overlay'] = $overlayColor;
$savedata = json_encode($savedata);
$stmt = $conn1->prepare("UPDATE userdata SET legacy_high_score = ?, save_data = ? WHERE id = ?");
$stmt->bind_param("isi", 
    $highScore, 
    $savedata, 
    $id
);
$stmt->execute();
$stmt->close();
echo encrypt("1");

$conn0->close();
$conn1->close();