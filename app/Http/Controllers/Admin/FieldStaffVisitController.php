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
            $query->whereHas('user.fieldStaff', function($q) use ($request) {
                $q->where('sales_manager_id', $request->manager_id);
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

        if ($request->export === 'csv') {
            return $this->exportCsv($query, $request);
        }

        $visits = $query->paginate(20)->withQueryString();

        // Get dropdown data for filters
        $managers = User::role('salesmanager')->orderBy('name', 'asc')->get();
        $staffUsers = User::role('fieldstaff')->with('fieldStaff')->orderBy('name', 'asc')->get();
        $purposes = VisitPurpose::orderBy('name', 'asc')->get();

        return view('admin.field_staff.visits', compact('visits', 'managers', 'staffUsers', 'purposes'));
    }

    /**
     * Export visits query to CSV
     */
    private function exportCsv($query, $request)
    {
        $visits = $query->get();
        
        $fileName = 'Staff_Visits';
        
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = Carbon::parse($request->start_date);
            $end = Carbon::parse($request->end_date);
            
            // Check if it's the exact first and last day of the same month
            if ($start->isSameMonth($end) && $start->day === 1 && $end->day === $end->daysInMonth) {
                $fileName .= '_' . $start->format('F_Y');
            } else {
                $fileName .= '_' . $start->format('Y-m-d') . '_to_' . $end->format('Y-m-d');
            }
        } else {
            $fileName .= '_' . date('Y-m-d');
        }
        
        $fileName .= '.csv';
        
        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];
        
        $columns = ['Date', 'Time', 'Staff Name', 'Manager Name', 'Party Type', 'Party Name', 'Purpose', 'Status', 'Duration (Mins)', 'Remarks'];
        
        $callback = function() use($visits, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            foreach ($visits as $visit) {
                $date = $visit->start_at ? $visit->start_at->format('Y-m-d') : '';
                $time = $visit->start_at ? $visit->start_at->format('h:i A') : '';
                $staffName = optional($visit->user)->name;
                $managerName = optional(optional(optional($visit->user)->fieldStaff)->salesManager)->user->name ?? 'N/A';
                
                $partyType = ucfirst($visit->party_type);
                $partyName = '';
                if ($visit->party_type === 'retailer' || $visit->party_type === 'distributor') {
                    $partyName = optional($visit->party)->name ?? 'Unknown';
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
                    $date,
                    $time,
                    $staffName,
                    $managerName,
                    $partyType,
                    $partyName,
                    $purpose,
                    $status,
                    $duration,
                    $visit->remarks
                ];
                
                fputcsv($file, $row);
            }
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
