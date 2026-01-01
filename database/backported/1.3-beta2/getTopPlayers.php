<?php
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$stmt = $conn0->prepare("SELECT username, id FROM users WHERE leaderboards_banned = 0");
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $topPlayers = [];
    
    while ($row = $result->fetch_assoc()) {
        $id = $row["id"];
        $stmt2 = $conn1->prepare("SELECT legacy_high_score, save_data FROM userdata WHERE id = ? AND legacy_high_score > 0 ORDER BY legacy_high_score DESC LIMIT 1");
        $stmt2->bind_param("i", $id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();

        if ($result2->num_rows != 1) {
            continue;
        }

        $user2 = $result2->fetch_assoc();

        $savedata = json_decode($user2['save_data'], true);
        $icon = $savedata['bird']['icon'] ?? 1;
        $overlay = $savedata['bird']['overlay'] ?? 0;
        if (getClientVersion() == "1.3-beta2" || getClientVersion() == "1.3" || getClientVersion() == "1.33") {
            $topPlayers[] = $row["username"] . ":" . $user2["legacy_high_score"] . ":" . $icon . ":" . $overlay . ":" . $id;
        } else if (getClientVersion() == "0") {
            $topPlayers[] = base64_encode($row["username"]) . ":" . $user2["legacy_high_score"] . ":" . $icon . ":" . $overlay . ":" . $id;
        } else if (getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
            $birdColor = $savedata['settings']['colors']['icon'] ?? [255,255,255];
            $overlayColor = $savedata['settings']['colors']['overlay'] ?? [255,255,255];
            $topPlayers[] = base64_encode($row["username"]) . ":" . $user2["legacy_high_score"] . ":" . $icon . ":" . $overlay . ":" . $id . ":" . $birdColor[0] . ":" . $birdColor[1] . ":" . $birdColor[2] . ":" . $overlayColor[0] . ":" . $overlayColor[1] . ":" . $overlayColor[2];
        }
    }

    if (getClientVersion() == "1.3-beta2" || getClientVersion() == "1.3" || getClientVersion() == "1.33") {
        echo implode("::", $topPlayers);
    } else if (getClientVersion() == "0") {
        echo implode(";", $topPlayers);
    } else if (getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
        echo encrypt(implode(";", $topPlayers));
    }
} else {
    if (getClientVersion() == "1.3-beta2" || getClientVersion() == "1.3" || getClientVersion() == "1.33") {
        echo "-2";
    } else if (getClientVersion() == "0") {
        echo "-1";
    } else if (getClientVersion() == "1.5.0" || getClientVersion() == "1.5.1" || getClientVersion() == "1.5.2") {
        echo encrypt("-1");
    }
}

$conn0->close();
$conn1->close();