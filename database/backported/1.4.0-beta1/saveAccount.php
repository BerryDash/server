<?php
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$request_userName = $_POST['userName'] ?? 0;
$request_gameSession = $_POST['gameSession'] ?? '';
$request_highScore = $_POST['highScore'] ?? 0;
$request_icon = $_POST['icon'] ?? 0;
$request_overlay = $_POST['overlay'] ?? 0;

$stmt = $conn0->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $request_userName);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    echo "-2";
    $conn0->close();
    $conn1->close();
    exit;
}

$request_uid = $result->fetch_assoc()["id"];

$stmt2 = $conn1->prepare("SELECT * FROM userdata WHERE token = ? AND id = ?");
$stmt2->bind_param("si", $request_gameSession, $request_uid);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($result2->num_rows != 1) {
    echo (getClientVersion() == "1.3-beta2" || getClientVersion() == "1.3" || getClientVersion() == "1.33") ? "-2" : "-3";
    $conn0->close();
    $conn1->close();
    exit;
}

$row2 = $result2->fetch_assoc();

$savedata = json_decode($row2['save_data'], true);
$savedata['bird']['icon'] = $request_icon;
$savedata['bird']['overlay'] = $request_overlay;
$savedata = json_encode($savedata);

$updateStmt = $conn1->prepare("UPDATE userdata SET legacy_high_score = ?, save_data = ? WHERE token = ? AND id = ?");
$updateStmt->bind_param("issi", $request_highScore, $savedata, $request_gameSession, $request_uid);
$updateStmt->execute();
$updateStmt->close();

echo "1";