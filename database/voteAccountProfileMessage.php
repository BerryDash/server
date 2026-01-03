<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$post = getPostData();
$targetId = (int)$post['targetId'] ?? 0;
$liked = (int)$post['liked'] ?? -1;
$token = $post['token'] ?? '';
$username = $post['username'] ?? '';
if ($liked !== 0 && $liked !== 1) {
    echo encrypt(json_encode(["success" => false, "message" => 'Invalid type']));
    exit;
}

$stmt = $conn0->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) {
    echo encrypt(json_encode(["success" => false, "message" => 'User info not found']));
    exit;
}
$stmt->close();

$user_id = $row["id"];

$stmt = $conn1->prepare("SELECT votes, likes FROM userposts WHERE id = ?");
$stmt->bind_param("i", $targetId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
if (!$row) {
    echo encrypt(json_encode(["success" => false, "message" => 'Post info not found']));
    exit;
}
$stmt->close();

$votes = json_decode($row["votes"], true) ?? [];
$likes = (int)$row["likes"];
if (isset($votes[$user_id])) {
    echo encrypt(json_encode(["success" => false, "message" => 'You have already voted']));
    exit;
}

$votes[$user_id] = $liked === 0 ? false : true;
$likes += $liked ? 1 : -1;
$votes = json_encode($votes);

$stmt = $conn1->prepare("UPDATE userposts SET likes = ?, votes = ? WHERE id = ?");
$stmt->bind_param("isi", $likes, $votes, $targetId);
$stmt->execute();
$stmt->close();

echo encrypt(json_encode(["success" => true, "likes" => $likes]));

$conn0->close();
$conn1->close();