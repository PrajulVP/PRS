<?php
$host = '127.0.0.1';
$user = 'logiprompt_atomed';
$pass = '@*5dQ9Zx&W4i';
$db = 'logiprompt_atomed';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    echo "SUCCESS: Connected to $db\n";
    
    $res = $pdo->query("SHOW TABLE STATUS WHERE Name IN ('products', 'distributor_order_items', 'retailer_order_items')");
    while ($row = $res->fetch(PDO::FETCH_ASSOC)) {
        echo "Table: " . $row['Name'] . " | Engine: " . $row['Engine'] . "\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
