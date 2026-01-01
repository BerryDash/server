<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
$conn = newConnection();

$post = getPostData();
$targetId = (int)$post['targetId'] ?? 0;

$stmt = $conn->prepare("
    SELECT id, content, timestamp, likes, userId 
    FROM userposts 
    WHERE userId = ? AND deleted_at = 0 
    ORDER BY id DESC
");
$stmt->bind_param("i", $targetId);
$stmt->execute();
$result = $stmt->get_result();

echo encrypt(json_encode(array_map(
    fn($row) => [
        'id' => $row['id'],
        'userId' => $row['userId'],
        'content' => $row['content'],
        'timestamp' => genTimestamp($row['timestamp']) . " ago",
        'likes' => $row['likes']
    ],
    $result->fetch_all(MYSQLI_ASSOC)
)));

$conn->close();