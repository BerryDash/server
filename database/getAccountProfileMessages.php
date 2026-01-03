<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
$conn = newConnection();

$post = getPostData();
$targetId = (int)$post['targetId'] ?? 0;

$stmt = $conn->prepare("
    SELECT id, content, timestamp, votes, userId 
    FROM userposts 
    WHERE userId = ? AND deleted_at = 0 
    ORDER BY id DESC
");
$stmt->bind_param("i", $targetId);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

echo encrypt(json_encode(array_map(
    function ($row) {
        $votes = json_decode($row['votes'], true);
        $likes = is_array($votes) ? array_sum(array_map(fn($v) => $v === true ? 1 : -1, $votes)) : 0;

        return [
            'id' => $row['id'],
            'userId' => $row['userId'],
            'content' => $row['content'],
            'timestamp' => genTimestamp($row['timestamp']) . " ago",
            'likes' => $likes
        ];
    },
    $result->fetch_all(MYSQLI_ASSOC)
)));

$conn->close();