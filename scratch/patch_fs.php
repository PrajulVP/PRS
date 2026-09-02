<?php
$file = 'c:\wamp64\www\prs\app\Http\Controllers\Api\FieldStaffDashboardApiController.php';
$content = file_get_contents($file);

// Wrap index in try catch
$oldStart = '    public function index(Request $request)
    {
        /** @var \App\Models\User $user */';
$newStart = '    public function index(Request $request)
    {
        try {
        /** @var \App\Models\User $user */';
$content = str_replace($oldStart, $newStart, $content);

$oldEnd = '                \'recent_visits\' => $recentVisits,
            ]);
    }';
$newEnd = '                \'recent_visits\' => $recentVisits,
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
echo "Patched FieldStaffDashboardApiController.php\n";
