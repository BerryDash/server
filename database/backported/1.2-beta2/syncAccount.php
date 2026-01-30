<?php
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$user_id = $_POST['userID'] ?? 0;
$token = $_POST['gameSession'] ?? '';
$high_score = $_POST['highScore'] ?? 0;

$stmt = $conn0->prepare("SELECT * FROM users WHERE id = ? AND token = ?");
$stmt->bind_param("ss", $user_id, $token);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows != 1) {
    echo (getClientVersion() == "1.3-beta2" || getClientVersion() == "1.3" || getClientVersion() == "1.33") ? "-2" : "-3";
    $conn0->close();
    $conn1->close();
    exit;
}
$stmt->close();
$user_id = $result->fetch_assoc()["id"];

$stmt = $conn1->prepare("SELECT * FROM userdata WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result2 = $stmt->get_result();
$stmt->close();
if ($result2->num_rows != 1) {
    echo (getClientVersion() == "1.3-beta2" || getClientVersion() == "1.3" || getClientVersion() == "1.33") ? "-2" : "-3";
    $conn0->close();
    $conn1->close();
    exit;
}

$updateStmt = $conn1->prepare("UPDATE userdata SET legacy_high_score = ? WHERE id = ?");
$updateStmt->bind_param("ii", $high_score, $user_id);
$updateStmt->execute();
$updateStmt->close();

echo "1";