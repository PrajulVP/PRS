<?php
$lines = file('c:/wamp64/www/prs/old_dashboard.blade.php');
foreach ($lines as $i => $line) {
    if (preg_match('/@(if|endif|else|elseif)\b/', $line, $match)) {
        echo ($i + 1) . ": " . trim($line) . "\n";
    }
}
