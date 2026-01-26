<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
if (getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
    require __DIR__ . '/backported/1.5.1/getChatroomMessages.php';
    exit;
}
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn1->prepare("
    SELECT id, content, deleted_at, userId 
    FROM chats 
    WHERE deleted_at = 0 
    ORDER BY id DESC 
    LIMIT 50
");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$mapped = [];
$icons = [];
foreach ($result->fetch_all(mode: MYSQLI_ASSOC) as $row) {
    $userId = $row["userId"];
    $stmt = $conn1->prepare("SELECT legacy_high_score, save_data FROM userdata WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result2 = $stmt->get_result();
    $stmt->close();
    if ($result2->num_rows != 1) continue;
    $row2 = $result2->fetch_assoc();

    $stmt = $conn0->prepare("SELECT username FROM users WHERE id = ? LIMIT 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result3 = $stmt->get_result();
    $stmt->close();
    if ($result3->num_rows != 1) continue;
    $row3 = $result3->fetch_assoc();

    $savedata = json_decode($row2['save_data'], true);

    $customIcon = $savedata['bird']['customIcon']['selected'] ?? null;

    if ($customIcon != null && strlen($customIcon) == 36 && $icons[$customIcon] == null) {
        $stmt = $conn1->prepare("SELECT data FROM marketplaceicons WHERE id = ?");
        $stmt->bind_param("s", $customIcon);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        $rowData = $result->fetch_assoc();
        if ($rowData) {
            $icons[$customIcon] = $rowData["data"];
        }
    }

    $mapped[] = [
        'username' => $row3['username'],
        'userid' => $row['userId'],
        'content' => (int)$row['deleted_at'] == 0 ? $row['content'] : null,
        'deleted' => (int)$row['deleted_at'] != 0,
        'id' => $row['id'],
        'icon' => $savedata['bird']['icon'] ?? 1,
        'overlay' => $savedata['bird']['overlay'] ?? 0,
        'birdColor' => $savedata['settings']['colors']['icon'] ?? [255,255,255],
        'overlayColor' => $savedata['settings']['colors']['overlay'] ?? [255,255,255],
        'customIcon' => $customIcon,
    ];
}


echo encrypt(json_encode(getClientVersion() == "1.6" ? $mapped : ["messages" => array_reverse($mapped), "customIcons" => $icons == [] ? new stdClass() : $icons]));

$conn0->close();
$conn1->close();