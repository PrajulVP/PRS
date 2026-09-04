<?php
$file = 'c:/wamp64/www/prs/resources/views/dashboard.blade.php';
$content = file_get_contents($file);

// Find where to split.
$startMarker = '<div class="row size-column">';
$startPos = strpos($content, $startMarker);

$endMarker = '@endsection';
$endPos = strrpos($content, $endMarker); // Find the last one

if ($startPos !== false && $endPos !== false) {
    $wrapperHeader = substr($content, 0, $startPos);
    $innerContent = substr($content, $startPos, $endPos - $startPos);
    
    // Create partials directory if it doesn't exist
    if (!is_dir(dirname($file) . '/partials')) {
        mkdir(dirname($file) . '/partials', 0777, true);
    }
    
    file_put_contents(dirname($file) . '/partials/dashboard_content.blade.php', $innerContent);
    
    $newWrapper = $wrapperHeader . "        <div id=\"dashboard-dynamic-content\">\n            @include('partials.dashboard_content')\n        </div>\n    </div>\n";
    
    // Add the ajax logic in a script block at the end of the wrapper
    $ajaxScript = <<<EOT

@push('scripts')
<script>
    function fetchDashboardMonth(month) {
        // Show loading state
        const container = document.getElementById('dashboard-dynamic-content');
        if(container) {
            container.style.opacity = '0.5';
        }
        
        // Update URL
        const newUrl = new URL(window.location.href);
        newUrl.searchParams.set('month', month);
        window.history.pushState({month: month}, '', newUrl);

        fetch(`?month=\${month}`, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.text())
        .then(html => {
            if(container) {
                // Using jQuery to replace HTML ensures script tags are evaluated
                $('#dashboard-dynamic-content').html(html);
                container.style.opacity = '1';
            }
        })
        .catch(err => {
            console.error('Error fetching dashboard:', err);
            if(container) container.style.opacity = '1';
        });
    }
</script>
@endpush
@endsection
EOT;
    
    $newWrapper .= $ajaxScript;
    
    file_put_contents($file, $newWrapper);
    echo "Splitting completed successfully.";
} else {
    echo "Markers not found.";
}
