<?php

$dir = 'c:\\wamp64\\www\\prs\\resources\\views';
$iter = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$count = 0;

$replacement = "buttons: {
                    dom: {
                        button: {
                            className: 'btn btn-sm btn-icon'
                        }
                    },
                    buttons: [{
                        extend: 'copy',
                        className: 'btn btn-secondary btn-sm',
                        text: '<i class=\"fa fa-copy\"></i> Copy'
                    },
                    {
                        extend: 'csv',
                        className: 'btn btn-info btn-sm text-white',
                        text: '<i class=\"fa fa-file-csv\"></i> CSV'
                    },
                    {
                        extend: 'excel',
                        className: 'btn btn-success btn-sm',
                        text: '<i class=\"fa fa-file-excel\"></i> Excel'
                    },
                    {
                        extend: 'pdf',
                        className: 'btn btn-danger btn-sm',
                        text: '<i class=\"fa fa-file-pdf\"></i> PDF'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-dark btn-sm',
                        text: '<i class=\"fa fa-print\"></i> Print'
                    }
                    ]
                }";


foreach ($iter as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());

        // This regex looks for buttons: { dom: { ... }, buttons: [ { extend: 'copy' ... } ] }
        $pattern1 = '/buttons:\s*\{\s*dom:\s*\{\s*button:\s*\{\s*className:\s*\'[^\']*\'\s*\}\s*\}\s*,\s*buttons:\s*\[\s*\{\s*extend:\s*\'copy\'[^\]]*\]\s*\}/ms';

        // This regex looks for buttons: [ { extend: 'copy' ... } ]
        $pattern2 = '/buttons:\s*\[\s*\{\s*extend:\s*\'copy\'\s*,[^\]]*\]/ms';

        $replaced = false;

        if (preg_match($pattern1, $content)) {
            $content = preg_replace($pattern1, $replacement, $content);
            $replaced = true;
        } elseif (preg_match($pattern2, $content)) {
            $content = preg_replace($pattern2, $replacement, $content);
            $replaced = true;
        }

        if ($replaced) {
            file_put_contents($file->getPathname(), $content);
            echo "Updated: " . $file->getPathname() . "\n";
            $count++;
        }
    }
}
echo "Total files updated: $count\n";
