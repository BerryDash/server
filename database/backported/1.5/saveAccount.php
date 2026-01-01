<?php
$post = getPostData();
$token = $post['gameSession'] ?? '';
$username = $post['userName'] ?? '';
$highScore = (int)$post['highScore'] ?? 0;
$icon = (int)$post['icon'] ?? 1;
$overlay = (int)$post['overlay'] ?? 0;
$birdR = (int)$post['birdR'] ?? 255;
$birdG = (int)$post['birdG'] ?? 255;
$birdB = (int)$post['birdB'] ?? 255;
if (getClientVersion() == "1.5.2") {
    $overlayR = (int)$post['overlayR'] ?? 255;
    $overlayG = (int)$post['overlayG'] ?? 255;
    $overlayB = (int)$post['overlayB'] ?? 255;
}
$birdColor = [$birdR, $birdG, $birdB];
$overlayColor = [$overlayR, $overlayG, $overlayB];

$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn0->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    echo encrypt("-1");
    $conn0->close();
    $conn1->close();
    exit;
}

$row = $result->fetch_assoc();
$id = $row["id"];

$stmt2 = $conn1->prepare("SELECT * FROM userdata WHERE id = ? AND token = ?");
$stmt2->bind_param("is", $id, $token);
$stmt2->execute();
$result2 = $stmt2->get_result();

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
$savedata['settings']['colors']['icon'] = $birdColor;
if (getClientVersion() == "1.5.2") $savedata['settings']['colors']['overlay'] = $overlayColor;
$savedata = json_encode($savedata);
$updateStmt = $conn1->prepare("UPDATE userdata SET legacy_high_score = ?, save_data = ? WHERE id = ? AND token = ?");
$updateStmt->bind_param("isis", 
    $highScore, 
    $savedata, 
    $id, 
    $token
);
$updateStmt->execute();
$updateStmt->close();
echo encrypt("1");

$conn0->close();
$conn1->close();