<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FieldVisit;
use App\Models\User;
use App\Models\VisitPurpose;
use Carbon\Carbon;

class FieldStaffVisitController extends Controller
{
    /**
     * Display a listing of the field staff visits.
     */
    public function index(Request $request)
    {
        $query = FieldVisit::with(['user.fieldStaff.salesManager.user', 'purpose'])->orderBy('start_at', 'desc');

        // Filtering by Manager
        if ($request->filled('manager_id')) {
            $query->whereHas('user.fieldStaff.salesManager', function($q) use ($request) {
                $q->where('user_id', $request->manager_id);
            });
        }

        // Filtering by Staff
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filtering by Party Type
        if ($request->filled('party_type')) {
            $query->where('party_type', $request->party_type);
        }

        // Filtering by Purpose
        if ($request->filled('purpose_id')) {
            $query->where('purpose_id', $request->purpose_id);
        }

        // Filtering by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filtering by Repeat Visit
        if ($request->filled('is_repeat')) {
            $isRepeat = $request->is_repeat == 1;
            $operator = $isRepeat ? '> 1' : '= 1';
            
            $query->whereRaw("(
                SELECT COUNT(*) 
                FROM field_visits AS fv2 
                WHERE fv2.user_id = field_visits.user_id 
                AND fv2.party_type = field_visits.party_type 
                AND fv2.party_id = field_visits.party_id 
                AND DATE(fv2.start_at) = DATE(field_visits.start_at) 
                AND fv2.deleted_at IS NULL
            ) " . $operator);
        }

        // Filtering by Date Range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->whereBetween('start_at', [$startDate, $endDate]);
        } elseif ($request->filled('start_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $query->where('start_at', '>=', $startDate);
        } elseif ($request->filled('end_date')) {
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $query->where('start_at', '<=', $endDate);
        }

        if ($request->filled('export')) {
            $exportType = $request->export;
            if ($exportType === 'excel') return $this->exportExcel($query, $request);
            if ($exportType === 'pdf') return $this->exportPdf($query, $request);
            if ($exportType === 'print') return $this->printView($query, $request);
            return $this->exportCsv($query, $request);
        }

        if ($request->ajax()) {
            return \Yajra\DataTables\Facades\DataTables::of($query)
                ->addColumn('staff_member', function ($visit) {
                    $name = optional($visit->user)->name ?? 'Unknown Staff';
                    return '<div class="text-dark">' . $name . '</div>';
                })
                ->addColumn('manager', function ($visit) {
                    $managerName = optional(optional(optional($visit->user)->fieldStaff)->salesManager)->user->name ?? 'N/A';
                    return '<div class="text-dark">' . $managerName . '</div>';
                })
                  ->addColumn('party_type', function ($visit) {
                      return '<div class="text-dark">' . ucfirst($visit->party_type) . '</div>';
                  })
                  ->addColumn('party_details', function ($visit) {
                      return '<div class="text-dark">' . $visit->party_name . '</div>';
                  })
                  ->addColumn('purpose', function ($visit) {
                    $html = '<div class="text-dark">' . (optional($visit->purpose)->name ?? 'N/A') . '</div>';
                    if ($visit->is_repeat) {
                        $html .= '<div class="mt-1"><span class="badge bg-warning text-dark"><i class="fa fa-redo-alt"></i> Repeat Visit</span></div>';
                    }
                    return $html;
                })
                ->addColumn('duration', function ($visit) {
                    $durationHtml = '-';
                    if ($visit->start_at && $visit->end_at) {
                        $duration = $visit->start_at->diffInMinutes($visit->end_at);
                        $hours = floor($duration / 60);
                        $mins = $duration % 60;
                        $durationHtml = ($hours > 0 ? $hours . 'h ' : '') . $mins . 'm';
                    }
                    return '<div class="text-dark">' . $durationHtml . '</div>';
                })
                ->addColumn('date', function ($visit) {
                    return $visit->start_at ? '<div style="color: #004085;">' . $visit->start_at->format('d M, Y') . '</div>' : 'N/A';
                })
                ->addColumn('start_time', function ($visit) {
                    return $visit->start_at ? '<div class="text-dark">' . $visit->start_at->format('h:i A') . '</div>' : 'N/A';
                })
                ->addColumn('end_time', function ($visit) {
                    if ($visit->end_at) {
                        return '<div class="text-dark">' . $visit->end_at->format('h:i A') . '</div>';
                    } elseif ($visit->status == 'ongoing') {
                        return '<span class="badge bg-warning text-dark ms-1">Ongoing</span>';
                    }
                    return '<div class="text-dark">-</div>';
                })
                ->editColumn('remarks', function ($visit) {
                    return '<div class="text-dark" style="max-width: 220px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; text-overflow: ellipsis; white-space: normal; line-height: 1.4;" title="' . htmlspecialchars($visit->remarks ?? '') . '">' . htmlspecialchars($visit->remarks ?: '-') . '</div>';
                })
                ->addColumn('action', function ($visit) {
                    $durationHtml = '-';
                    if ($visit->start_at && $visit->end_at) {
                        $duration = $visit->start_at->diffInMinutes($visit->end_at);
                        $hours = floor($duration / 60);
                        $mins = $duration % 60;
                        $durationHtml = ($hours > 0 ? $hours . 'h ' : '') . $mins . 'm';
                    }
                    return '
                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <button class="btn btn-sm btn-light border shadow-sm view-visit-btn" data-visit=\'' . htmlspecialchars(json_encode([
                                'staff_name' => $visit->user->name ?? 'N/A',
                                'manager_name' => optional(optional(optional($visit->user)->fieldStaff)->salesManager)->user->name ?? 'None',
                                'date' => $visit->start_at ? $visit->start_at->format('d M, Y') : 'N/A',
                                'start_time' => $visit->start_at ? $visit->start_at->format('h:i A') : 'N/A',
                                'end_time' => $visit->end_at ? $visit->end_at->format('h:i A') : 'Ongoing',
                                'duration' => $durationHtml,
                                'party_name' => $visit->party_name ?: 'N/A',
                                'party_type' => ucfirst($visit->party_type),
                                'purpose' => $visit->purpose->name ?? 'N/A',
                                'status' => ucfirst($visit->status),
                                'remarks' => $visit->remarks ?: 'No remarks provided.'
                            ]), ENT_QUOTES, 'UTF-8') . '\' title="View Details">
                                <i class="fa fa-eye text-primary"></i>
                            </button>
                        </div>';
                })
                ->rawColumns(['staff_member', 'manager', 'party_type', 'party_details', 'purpose', 'duration', 'date', 'start_time', 'end_time', 'remarks', 'action'])
                ->make(true);
        }

        // Get dropdown data for filters
        $managers = User::role('salesmanager')->where('status', 'active')->orderBy('name', 'asc')->get();
        $staffUsers = User::role('fieldstaff')->where('status', 'active')->with('fieldStaff')->orderBy('name', 'asc')->get();
        $purposes = VisitPurpose::orderBy('name', 'asc')->get();

        return view('admin.field_staff.visits', compact('managers', 'staffUsers', 'purposes'));
    }

    private function getReportTitle($request)
    {
        $fromDate = $request->from_date ?? $request->start_date;
        $toDate = $request->to_date ?? $request->end_date;

        if ($fromDate && $toDate) {
            try {
                $from = \Carbon\Carbon::parse($fromDate);
                $to = \Carbon\Carbon::parse($toDate);
                
                if ($from->isSameMonth($to) && $from->day === 1 && $to->day === $to->daysInMonth) {
                    return $from->format('F Y');
                }
                
                return $from->format('d M Y') . ' to ' . $to->format('d M Y');
            } catch (\Exception $e) {
                // Ignore parse errors
            }
        }
        return 'All Time';
    }

    /**
     * Export visits query to CSV
     */
    private function exportCsv($query, $request)
    {
        $visits = $query->get();
        $reportTitle = $this->getReportTitle($request);
        
        $fileName = 'Staff_Visits_' . str_replace(' ', '_', $reportTitle) . '.csv';

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = [
            'Date', 'Start Time', 'End Time', 'Staff Name', 'Manager Name', 'Party Type', 'Party Name', 
            'Purpose', 'Status', 'Duration (Mins)', 'Repeat Visit', 'Remarks'
        ];

        $callback = function() use($visits, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($visits as $visit) {
                $partyType = ucfirst($visit->party_type);
                $partyName = '';
                if ($visit->party_type === 'retailer' || $visit->party_type === 'distributor') {
                    $partyName = optional($visit->party)->shop_name ?? optional($visit->party)->name ?? 'Unknown';
                } else {
                    $partyName = 'Party ID: ' . $visit->party_id;
                }
                
                $purpose = optional($visit->purpose)->name;
                $status = ucfirst($visit->status);
                
                $duration = '';
                if ($visit->start_at && $visit->end_at) {
                    $duration = $visit->start_at->diffInMinutes($visit->end_at);
                }
                
                $row = [
                    $visit->start_at ? $visit->start_at->format('Y-m-d') : '',
                    $visit->start_at ? $visit->start_at->format('h:i A') : '',
                    $visit->end_at ? $visit->end_at->format('h:i A') : '',
                    optional($visit->user)->name,
                    optional(optional(optional($visit->user)->fieldStaff)->salesManager)->user->name ?? 'N/A',
                    $partyType,
                    $partyName,
                    $purpose,
                    $status,
                    $duration,
                    $visit->is_repeat ? 'Yes' : 'No',
                    $visit->remarks
                ];
                
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }

    /**
     * Export visits query to Excel
     */
    private function exportExcel($query, $request)
    {
        $visits = $query->get();
        $reportTitle = $this->getReportTitle($request);
        $fileName = 'Staff_Visits_' . str_replace(' ', '_', $reportTitle) . '.xls';
        return response(view('admin.field_staff.exports.excel', compact('visits', 'reportTitle')))
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    /**
     * Export visits query to PDF
     */
    private function exportPdf($query, $request)
    {
        $visits = $query->get();
        $reportTitle = $this->getReportTitle($request);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.field_staff.exports.pdf', compact('visits', 'reportTitle'))->setPaper('a4', 'landscape');
        return $pdf->download('Staff_Visits_' . str_replace(' ', '_', $reportTitle) . '.pdf');
    }

    /**
     * Open Print View for visits query
     */
    private function printView($query, $request)
    {
        $visits = $query->get();
        $reportTitle = $this->getReportTitle($request);
        return view('admin.field_staff.exports.print', compact('visits', 'reportTitle'));
    }

    private function exportDailyVisitReport($query, $request) {
        $visits = $query->get()->groupBy(function($visit) {
            return $visit->start_at ? $visit->start_at->format('Y-m-d') : 'Unknown Date';
        });
        
        $fileName = 'Daily_Visit_Report_' . date('Y-m-d') . '.csv';
        $headers = ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0"];
        
        $callback = function() use($visits) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Staff Name', 'Total Visits', 'Completed Visits', 'Ongoing Visits']);
            
            foreach ($visits as $date => $dayVisits) {
                $staffGrouped = $dayVisits->groupBy('user_id');
                foreach ($staffGrouped as $userId => $staffVisits) {
                    $staffName = optional($staffVisits->first()->user)->name ?? 'Unknown';
                    $total = $staffVisits->count();
                    $completed = $staffVisits->where('status', 'completed')->count();
                    $ongoing = $staffVisits->where('status', 'ongoing')->count();
                    
                    fputcsv($file, [$date, $staffName, $total, $completed, $ongoing]);
                }
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
    
    private function exportMonthlyVisitReport($query, $request) {
        $visits = $query->get()->groupBy(function($visit) {
            return $visit->start_at ? $visit->start_at->format('Y-m') : 'Unknown Month';
        });
        
        $fileName = 'Monthly_Visit_Report_' . date('Y-m-d') . '.csv';
        $headers = ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0"];
        
        $callback = function() use($visits) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Month', 'Staff Name', 'Total Visits', 'Completed Visits', 'Ongoing Visits']);
            
            foreach ($visits as $month => $monthVisits) {
                $staffGrouped = $monthVisits->groupBy('user_id');
                foreach ($staffGrouped as $userId => $staffVisits) {
                    $staffName = optional($staffVisits->first()->user)->name ?? 'Unknown';
                    $total = $staffVisits->count();
                    $completed = $staffVisits->where('status', 'completed')->count();
                    $ongoing = $staffVisits->where('status', 'ongoing')->count();
                    
                    fputcsv($file, [$month, $staffName, $total, $completed, $ongoing]);
                }
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
    
    private function exportPartyWiseVisitReport($query, $request) {
        $visits = $query->get()->groupBy(function($visit) {
            return $visit->party_type . '_' . $visit->party_id;
        });
        
        $fileName = 'Party_Wise_Visit_Report_' . date('Y-m-d') . '.csv';
        $headers = ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0"];
        
        $callback = function() use($visits) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Party Type', 'Party Name', 'Total Visits', 'Last Visited']);
            
            foreach ($visits as $key => $partyVisits) {
                $firstVisit = $partyVisits->first();
                $partyType = ucfirst($firstVisit->party_type);
                $partyName = 'Unknown';
                if ($firstVisit->party_type === 'retailer' || $firstVisit->party_type === 'distributor') {
                    $partyName = optional($firstVisit->party)->shop_name ?? optional($firstVisit->party)->name ?? 'Unknown';
                } else {
                    $partyName = 'Party ID: ' . $firstVisit->party_id;
                }
                
                $total = $partyVisits->count();
                $lastVisited = $partyVisits->sortByDesc('start_at')->first()->start_at;
                $lastVisitedStr = $lastVisited ? $lastVisited->format('Y-m-d h:i A') : 'N/A';
                
                fputcsv($file, [$partyType, $partyName, $total, $lastVisitedStr]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
    
    private function exportRepeatVisitReport($query, $request) {
        $visits = $query->get()->groupBy(function($visit) {
            return $visit->party_type . '_' . $visit->party_id;
        })->filter(function($partyVisits) {
            return $partyVisits->count() > 1; // Only repeat visits
        });
        
        $fileName = 'Repeat_Visit_Report_' . date('Y-m-d') . '.csv';
        $headers = ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0"];
        
        $callback = function() use($visits) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Party Type', 'Party Name', 'Number of Visits', 'First Visited', 'Latest Visited']);
            
            foreach ($visits as $key => $partyVisits) {
                $firstVisit = $partyVisits->first();
                $partyType = ucfirst($firstVisit->party_type);
                $partyName = 'Unknown';
                if ($firstVisit->party_type === 'retailer' || $firstVisit->party_type === 'distributor') {
                    $partyName = optional($firstVisit->party)->shop_name ?? optional($firstVisit->party)->name ?? 'Unknown';
                } else {
                    $partyName = 'Party ID: ' . $firstVisit->party_id;
                }
                
                $total = $partyVisits->count();
                $sorted = $partyVisits->sortBy('start_at');
                $firstVisitedStr = optional($sorted->first()->start_at)->format('Y-m-d h:i A') ?? 'N/A';
                $latestVisitedStr = optional($sorted->last()->start_at)->format('Y-m-d h:i A') ?? 'N/A';
                
                fputcsv($file, [$partyType, $partyName, $total, $firstVisitedStr, $latestVisitedStr]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
    
    private function exportDailyKilometerReport($query, $request) {
        // Calculate distance from LocationLogs matching the query filters (dates, user)
        $logQuery = \App\Models\LocationLog::query();
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = Carbon::parse($request->start_date)->startOfDay();
            $endDate = Carbon::parse($request->end_date)->endOfDay();
            $logQuery->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        if ($request->filled('user_id')) {
            $logQuery->where('user_id', $request->user_id);
        }
        
        $logs = $logQuery->orderBy('created_at', 'asc')->get()->groupBy(function($log) {
            return $log->created_at->format('Y-m-d');
        });
        
        $fileName = 'Daily_Kilometer_Report_' . date('Y-m-d') . '.csv';
        $headers = ["Content-type" => "text/csv", "Content-Disposition" => "attachment; filename=$fileName", "Pragma" => "no-cache", "Cache-Control" => "must-revalidate, post-check=0, pre-check=0", "Expires" => "0"];
        
        $callback = function() use($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Staff Name', 'Total Kilometers (Estimated)']);
            
            foreach ($logs as $date => $dayLogs) {
                $staffGrouped = $dayLogs->groupBy('user_id');
                foreach ($staffGrouped as $userId => $staffLogs) {
                    $staffName = optional($staffLogs->first()->user)->name ?? 'Unknown';
                    $totalKm = 0;
                    
                    $prevLog = null;
                    foreach ($staffLogs as $log) {
                        if ($prevLog && $log->latitude && $log->longitude && $prevLog->latitude && $prevLog->longitude) {
                            $totalKm += self::calculateHaversineDistance($prevLog->latitude, $prevLog->longitude, $log->latitude, $log->longitude);
                        }
                        $prevLog = $log;
                    }
                    
                    fputcsv($file, [$date, $staffName, number_format($totalKm, 2) . ' km']);
                }
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }
    
    public static function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2) {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earthRadius * $c;
    }
}
