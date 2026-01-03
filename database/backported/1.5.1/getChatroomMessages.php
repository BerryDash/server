<?php
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn1  ->prepare("
    SELECT id, content, userId 
    FROM chats
    WHERE deleted_at = 0 
    ORDER BY id DESC LIMIT 50
");
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
while ($row = $result->fetch_assoc()) {
    $userId = $row["userId"];
    $stmt2 = $conn1->prepare("SELECT save_data FROM userdata WHERE id = ? LIMIT 1");
    $stmt2->bind_param("i", $userId);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    if ($result2->num_rows != 1) continue;
    $row2 = $result2->fetch_assoc();

    $stmt3 = $conn0->prepare("SELECT username FROM users WHERE id = ? LIMIT 1");
    $stmt3->bind_param("i", $userId);
    $stmt3->execute();
    $result3 = $stmt3->get_result();
    if ($result3->num_rows != 1) continue;
    $row3 = $result3->fetch_assoc();

    $savedata = json_decode($row2['save_data'], true);
    $icon = $savedata['bird']['icon'] ?? 1; 
    $overlay = $savedata['bird']['overlay'] ?? 0;   
    $birdColor = $savedata['settings']['colors']['icon'] ?? [255,255,255];
    $overlayColor = $savedata['settings']['colors']['overlay'] ?? [255,255,255];

    $rows[] = implode(";", [
        $row['id'],
        base64_encode($row3['username']),
        $row['content'],
        $icon,
        $overlay,
        $userId,
        $birdColor[0],
        $birdColor[1],
        $birdColor[2],
        $overlayColor[0],
        $overlayColor[1],
        $overlayColor[2]
    ]);
}

echo encrypt("1" . ":" . implode("|", array_reverse($rows)));

$conn0->close();
$conn1->close();