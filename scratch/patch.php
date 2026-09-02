<?php
$file = 'c:\wamp64\www\prs\app\Http\Controllers\Api\SalesManagerDashboardApiController.php';
$content = file_get_contents($file);

// Replace the brand lookup
$oldBrand = '$uniqueBrands = \App\Models\Product::select(\'brand\')->distinct()->pluck(\'brand\');';
$newBrand = '$uniqueBrands = \App\Models\Brand::pluck(\'name\');';
$content = str_replace($oldBrand, $newBrand, $content);

// Wrap index in try catch
$oldStart = '    public function index(Request $request)
    {
        /** @var \App\Models\User $user */';
$newStart = '    public function index(Request $request)
    {
        try {
        /** @var \App\Models\User $user */';
$content = str_replace($oldStart, $newStart, $content);

$oldEnd = '            \'top_fieldstaff\' => $topFieldStaff,
        ]);
    }';
$newEnd = '            \'top_fieldstaff\' => $topFieldStaff,
        ]);
        } catch (\Exception $e) {
            return response()->json([
                \'message\' => \'Exception Caught\',
                \'error\' => $e->getMessage(),
                \'file\' => $e->getFile(),
                \'line\' => $e->getLine()
            ], 500);
        }
    }';
$content = str_replace($oldEnd, $newEnd, $content);

file_put_contents($file, $content);
echo "Patched SalesManagerDashboardApiController.php\n";
