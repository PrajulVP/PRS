<?php

$filePath = __DIR__ . '/app/Http/Controllers/ReportController.php';
$content = file_get_contents($filePath);

$methods = <<<PHP

    public function managerReports(Request \$request)
    {
        \$user = Auth::user();
        abort_if(!\$user->hasAnyRole(['admin', 'superadmin']), 403);

        if (\$request->ajax()) {
            \$today = now()->toDateString();
            \$query = SalesManager::with(['user'])
                ->selectRaw("sales_managers.*, (SELECT CASE WHEN type = 'punch_in' THEN 1 ELSE 0 END FROM attendance_logs WHERE attendance_logs.user_id = sales_managers.user_id AND DATE(attendance_logs.timestamp) = ? ORDER BY timestamp DESC LIMIT 1) as is_online", [\$today]);

            return DataTables::of(\$query)
                ->addColumn('name', function(\$sm) {
                    \$status = \$sm->is_online ? '<span class="live-dot" style="width:8px;height:8px;background:#2ecc71;border-radius:50%;display:inline-block;margin-right:5px;"></span>' : '<span style="width:8px;height:8px;background:#95a5a6;border-radius:50%;display:inline-block;margin-right:5px;"></span>';
                    return \$status . (\$sm->user->name ?? 'N/A');
                })
                ->addColumn('actions', function(\$sm) {
                    return '<a href="' . route('admin.manager.tracking-map', ['user_id' => \$sm->user_id]) . '" class="btn btn-sm btn-primary"><i class="fa fa-map-marker-alt me-1"></i>Track</a>';
                })
                ->rawColumns(['name', 'actions'])
                ->make(true);
        }

        \$salesManagers = SalesManager::with('user')->get();

        \$today = now()->toDateString();
        \$activeManagerCount = \App\Models\AttendanceLog::whereDate('timestamp', \$today)
            ->whereHas('user', function(\$q) {
                \$q->whereHas('roles', function(\$r) {
                    \$r->where('name', 'salesmanager');
                });
            })
            ->where('type', 'punch_in')
            ->distinct('user_id')
            ->count();

        \$todayVisitsCount = \App\Models\VisitLog::whereDate('check_in_at', \$today)
            ->whereHas('user', function(\$q) {
                \$q->whereHas('roles', function(\$r) {
                    \$r->where('name', 'salesmanager');
                });
            })->count();
        
        \$pulseStats = [
            'active' => \$activeManagerCount,
            'visits' => \$todayVisitsCount,
            'alerts' => 0,
        ];

        return view('admin.reports.managers', compact('salesManagers', 'pulseStats'));
    }

    public function managerTracking(Request \$request)
    {
        \$userId = \$request->user_id;
        \$date = \$request->date ?? now()->toDateString();
        
        \$user = \App\Models\User::with(['salesManager'])->findOrFail(\$userId);
        
        // Fetch logs for the day
        \$locations = \App\Models\LocationLog::where('user_id', \$userId)
            ->whereDate('timestamp', \$date)
            ->orderBy('timestamp', 'asc')
            ->get();
            
        \$punches = \App\Models\AttendanceLog::where('user_id', \$userId)
            ->whereDate('timestamp', \$date)
            ->orderBy('timestamp', 'asc')
            ->get();
            
        \$visits = \App\Models\VisitLog::where('user_id', \$userId)
            ->whereDate('check_in_at', \$date)
            ->get();
 
        // Calculate total distance coverd
        \$totalDistance = \App\Models\LocationLog::calculateDailyDistance(\$userId, \$date);
 
        \$mockGpsCount = \App\Models\LocationLog::where('user_id', \$userId)
            ->whereDate('timestamp', \$date)
            ->where('is_mock_location', true)
            ->count();
 
        \$lastPunch = \$punches->last();
        \$isOnline = \$lastPunch && \$lastPunch->type === 'punch_in';

        return view('admin.reports.manager_tracking', compact('user', 'locations', 'punches', 'visits', 'date', 'totalDistance', 'isOnline', 'mockGpsCount'));
    }
 
    public function managerTrackingExport(Request \$request)
    {
        \$userId = \$request->user_id;
        \$date = \$request->date ?? now()->toDateString();
        \$format = \$request->format ?? 'pdf';

        \$user = \App\Models\User::with('salesManager')->findOrFail(\$userId);
        
        \$locations = \App\Models\LocationLog::where('user_id', \$userId)
            ->whereDate('timestamp', \$date)
            ->orderBy('timestamp', 'asc')
            ->get();
            
        \$punches = \App\Models\AttendanceLog::where('user_id', \$userId)
            ->whereDate('timestamp', \$date)
            ->orderBy('timestamp', 'asc')
            ->get();
            
        \$visits = \App\Models\VisitLog::where('user_id', \$userId)
            ->whereDate('check_in_at', \$date)
            ->get();

        \$totalDistance = \App\Models\LocationLog::calculateDailyDistance(\$userId, \$date);

        if (\$format === 'csv') {
            \$headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="manager_tracking_'.\$date.'.csv"',
            ];

            \$callback = function() use (\$locations, \$punches, \$visits) {
                \$file = fopen('php://output', 'w');
                
                fputcsv(\$file, ['--- PUNCH LOGS ---']);
                fputcsv(\$file, ['Time', 'Type', 'Coordinates']);
                foreach (\$punches as \$p) {
                    fputcsv(\$file, [\$p->timestamp->format('H:i'), \$p->type, \$p->latitude . ',' . \$p->longitude]);
                }

                fputcsv(\$file, []);
                fputcsv(\$file, ['--- VISITS ---']);
                fputcsv(\$file, ['Time', 'Customer', 'Duration', 'Coordinates']);
                foreach (\$visits as \$v) {
                    fputcsv(\$file, [
                        \$v->check_in_at->format('H:i'), 
                        \$v->customer_name, 
                        \$v->duration_minutes . ' mins', 
                        \$v->latitude . ',' . \$v->longitude
                    ]);
                }

                fputcsv(\$file, []);
                fputcsv(\$file, ['--- ROUTE PATH ---']);
                fputcsv(\$file, ['Time', 'Coordinates']);
                foreach (\$locations as \$l) {
                    fputcsv(\$file, [\$l->timestamp->format('H:i:s'), \$l->latitude . ',' . \$l->longitude]);
                }

                fclose(\$file);
            };

            return response()->stream(\$callback, 200, \$headers);
        }

        // PDF Export
        \$pdf = Pdf::loadView('admin.reports.pdf.manager_tracking', compact('user', 'locations', 'punches', 'visits', 'date', 'totalDistance'));
        return \$pdf->download("manager_tracking_{\$date}.pdf");
    }
PHP;

$content = preg_replace('/}(?=\s*$)/', $methods . "\n}", $content);
file_put_contents($filePath, $content);

echo "Methods injected successfully.\n";
