<?php
$dbUrl = "postgres://u21c2avk054t0h:pa1234480f2ddbbe15b8d75113e8e8639e2191144e383fe8899592417fe8a8208@c57oa7dm3pc281.cluster-czrs8kj4isg7.us-east-1.rds.amazonaws.com:5432/dddepvkqjvui5b";
$db = parse_url($dbUrl);

$pgsqlConfig = sprintf(
    "pgsql:host=%s;port=%s;dbname=%s",
    $db['host'],
    $db['port'],
    ltrim($db['path'], '/')
);

try {
    $conn = new PDO(
        $pgsqlConfig,
        $db['user'],
        $db['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get total count
    $result = $conn->query("SELECT COUNT(*) as count FROM members");
    $count = $result->fetch(PDO::FETCH_ASSOC);
    echo "Total members: " . $count['count'] . "\n\n";

    // Get sample of members
    $result = $conn->query("SELECT * FROM members ORDER BY id LIMIT 5");
    echo "Sample members:\n";
    print_r($result->fetchAll(PDO::FETCH_ASSOC));

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>