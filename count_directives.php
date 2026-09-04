<?php
$content = file_get_contents('c:/wamp64/www/prs/resources/views/partials/dashboard_content.blade.php');
preg_match_all('/@(if|elseif|else|endif|forelse|endforelse|foreach|endforeach|php|endphp)\b/', $content, $matches);
print_r(array_count_values($matches[1]));
