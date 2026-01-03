<?php
require __DIR__ . '/../incl/util.php';
setPlainHeader();
$conn0 = newConnection(0);
$conn1 = newConnection(1);

$post = getPostData();
$userId = (int) $post['userId'] ?? 0;
$sortBy = (int) $post['sortBy'] ?? 2;
$priceRangeEnabled = isset($post['priceRangeEnabled']) ? (string) $post['priceRangeEnabled'] == 'False' ? false : true : false;
$priceRangeMin = (int) $post['priceRangeMin'] ?? 10;
$priceRangeMax = (int) $post['priceRangeMax'] ?? 250;
$searchForEnabled = isset($post['searchForEnabled']) ? (string) $post['searchForEnabled'] == 'False' ? false : true : false;
$searchForValue = (string) $post['searchForValue'] ?? '';
$onlyShowEnabled = isset($post['onlyShowEnabled']) ? (string) $post['onlyShowEnabled'] == 'False' ? false : true : false;
$onlyShowValue = (string) $post['onlyShowValue'] ?? '';
$currentIcons = json_decode(base64_decode((string) ($post['currentIcons'] ?? 'W10K')));

$where = ["(state = 1 OR state = 2)"];
$params = [];
$types = "";
$order = match ($sortBy) {
    1 => "ORDER BY price ASC",
    2 => "ORDER BY id ASC",
    3 => "ORDER BY id DESC",
    default => "ORDER BY price DESC",
};

if ($priceRangeEnabled) {
    $where[] = "price BETWEEN ? AND ?";
    $params[] = $priceRangeMin;
    $params[] = $priceRangeMax;
    $types .= "ii";
}

if ($searchForEnabled && $searchForValue !== '') {
    $where[] = "FROM_BASE64(name) LIKE ?";
    $params[] = "%$searchForValue%";
    $types .= "s";
}

if ($onlyShowEnabled) {
    if ($onlyShowValue === '0') {
        $where[] = "userId = ?";
        $params[] = $userId;
        $types .= "i";
    } elseif ($onlyShowValue === '1') {
        $where[] = "userId != ?";
        $params[] = $userId;
        $types .= "i";
    } elseif ($onlyShowValue === '2') {
        $placeholders = implode(',', array_fill(0, count($currentIcons), '?'));
        $where[] = "uuid IN ($placeholders)";
        $params = array_merge($params, $currentIcons);
        $types .= str_repeat('s', count($currentIcons));
    } elseif ($onlyShowValue === '3') {
        $placeholders = implode(',', array_fill(0, count($currentIcons), '?'));
        $where[] = "uuid NOT IN ($placeholders)";
        $params = array_merge($params, $currentIcons);
        $types .= str_repeat('s', count($currentIcons));
    }
}

$sql = "
    SELECT data, price, name, uuid, state, userId
    FROM marketplaceicons 
    WHERE " . implode(" AND ", $where) . "
    $order
";

$stmt = $conn1->prepare($sql);
if (!empty($params)) $stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

echo encrypt(json_encode(array_map(
    function ($row) {
        global $conn0;

        $stmt = $conn0->prepare("SELECT username FROM users WHERE id = ?");
        $stmt->bind_param("i", $row['userId']);
        $stmt->execute();
        $result2 = $stmt->get_result();
        $stmt->close();
        $row2 = $result2->fetch_assoc();

        return [
            'username' => $row2['username'] ?? 'Unknown',
            'userid' => $row['userId'],
            'data' => $row['data'],
            'uuid' => $row['uuid'],
            'price' => (int) $row['state'] == 2 ? 100000000 : $row['price'],
            'name' => base64_decode($row['name'])
        ];
    },
    $result->fetch_all(MYSQLI_ASSOC)
)));

$conn0->close();
$conn1->close();