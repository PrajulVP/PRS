<?php
$content = file_get_contents('c:/wamp64/www/prs/app/Http/Controllers/ReportController.php');
$content = str_replace(
    "'executive_reports', 'view'), 403);", 
    "'executive_reports', 'view') && !\$user->hasRole('salesmanager'), 403);", 
    $content
);
file_put_contents('c:/wamp64/www/prs/app/Http/Controllers/ReportController.php', $content);
echo "Replaced successfully.";
