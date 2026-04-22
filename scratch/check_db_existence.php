<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';
$db = 'logiprompt_atomed';

try {
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $res = $pdo->query("SHOW DATABASES LIKE 'logiprompt_atomed'");
    if ($res->rowCount() > 0) {
        echo "Database logiprompt_atomed EXISTS\n";
    } else {
        echo "Database logiprompt_atomed DOES NOT EXIST in this MySQL instance\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
