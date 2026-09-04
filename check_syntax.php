<?php
$lines = file('c:/wamp64/www/prs/resources/views/partials/dashboard_content.blade.php');
foreach ($lines as $i => $line) {
    if (preg_match('/@(if|endif|else|elseif)\b/', $line, $match)) {
        echo ($i + 1) . ": " . trim($line) . "\n";
    }
}
