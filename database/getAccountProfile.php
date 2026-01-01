<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();

$post = getPostData();
$uesrId = $post['uesrId'] ?? '';

$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn0->prepare("SELECT username, id FROM users WHERE id = ?");
$stmt->bind_param("i", $uesrId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();

    $stmt2 = $conn1->prepare("SELECT save_data FROM userdata WHERE id = ?");
    $stmt2->bind_param("i", $row['id']);
    $stmt2->execute();
    $result2 = $stmt2->get_result();
    $row2 = $result2->fetch_assoc();

    $savedata = json_decode($row2['save_data'], true);
    $custom = null;
    if (isset($savedata['bird']['customIcon']['selected'])) {
        $selected = $savedata['bird']['customIcon']['selected'];
        foreach ($savedata['bird']['customIcon']['data'] as $entry) {
            if (isset($entry['uuid']) && $entry['uuid'] === $selected) {
                $custom = $entry['data'];
                break;
            }
        }
    }
    echo encrypt(json_encode([
        "success" => true,
        "totalNormalBerries" => $savedata['gameStore']['totalNormalBerries'] ?? 0,
        "totalPoisonBerries" => $savedata['gameStore']['totalPoisonBerries'] ?? 0,
        "totalSlowBerries" => $savedata['gameStore']['totalSlowBerries'] ?? 0,
        "totalUltraBerries" => $savedata['gameStore']['totalUltraBerries'] ?? 0,
        "totalSpeedyBerries" => $savedata['gameStore']['totalSpeedyBerries'] ?? 0,
        "totalCoinBerries" => $savedata['gameStore']['totalCoinBerries'] ?? 0,
        "totalRandomBerries" => $savedata['gameStore']['totalRandomBerries'] ?? 0,
        "totalAntiBerries" => $savedata['gameStore']['totalAntiBerries'] ?? 0,
        "coins" => $savedata['bird']['customIcon']['balance'] ?? 0,
        "name" => $row['username'],
        "icon" => $savedata['bird']['icon'] ?? 1,
        "overlay" => $savedata['bird']['overlay'] ?? 0,
        "customIcon" => $custom,
        "playerIconColor" => $savedata['settings']['colors']['icon'] ?? [255,255,255],
        "playerOverlayColor" => $savedata['settings']['colors']['overlay'] ?? [255,255,255]
    ]));
} else {
    echo encrypt(json_encode(["success" => false]));
}

$stmt->close();
$conn0->close();
$conn1->close();