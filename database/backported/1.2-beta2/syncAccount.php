<?php
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$request_uid = $_POST['userID'] ?? 0;
$request_session = $_POST['gameSession'] ?? '';
$request_score = $_POST['highScore'] ?? 0;

$stmt = $conn0->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("s", $request_uid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows != 1) {
    echo (getClientVersion() == "1.3-beta2" || getClientVersion() == "1.3" || getClientVersion() == "1.33") ? "-2" : "-3";
    $conn0->close();
    $conn1->close();
    exit;
}

$request_uid = $result->fetch_assoc()["id"];

$stmt2 = $conn1->prepare("SELECT * FROM userdata WHERE token = ? AND id = ?");
$stmt2->bind_param("si", $request_session, $request_uid);
$stmt2->execute();
$result2 = $stmt2->get_result();

if ($result2->num_rows != 1) {
    echo (getClientVersion() == "1.3-beta2" || getClientVersion() == "1.3" || getClientVersion() == "1.33") ? "-2" : "-3";
    $conn0->close();
    $conn1->close();
    exit;
}

$updateStmt = $conn1->prepare("UPDATE userdata SET legacy_high_score = ? WHERE token = ? AND id = ?");
$updateStmt->bind_param("isi", $request_score, $request_session, $request_uid);
$updateStmt->execute();
$updateStmt->close();

echo "1";