<?php
$dbUrl = "postgres://u21c2avk054t0h:pa1234480f2ddbbe15b8d75113e8e8639e2191144e383fe8899592417fe8a8208@c57oa7dm3pc281.cluster-czrs8kj4isg7.us-east-1.rds.amazonaws.com:5432/dddepvkqjvui5b";
$db = parse_url($dbUrl);
$conn = new PDO(
    "pgsql:host={$db['host']};port={$db['port']};dbname=" . ltrim($db['path'], '/'),
    $db['user'],
    $db['pass']
);

$result = $conn->query("SELECT * FROM information_schema.columns WHERE table_name = 'members'");
foreach($result as $row) {
    print_r($row);
}
?>