<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\LeaveRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FieldStaffManagementController extends Controller
{
    /**
     * Display a listing of expenses for approval.
     */
    public function expensesIndex(Request $request)
    {
        $query = Expense::with(['user', 'manager', 'admin']);

        // Filter by role
        if (Auth::user()->hasRole('salesmanager')) {
            $staffIds = User::whereHas('fieldStaff', function($q) {
                $q->where('sales_manager_id', Auth::user()->salesManager->id);
            })->pluck('id');
            $query->whereIn('user_id', $staffIds);
            
            // Managers primarily see pending
            if (!$request->has('status') || $request->status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        } elseif (Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
            // Admins primarily see manager_approved
            if (!$request->has('status') || $request->status === 'manager_approved') {
                $query->where('status', 'manager_approved');
            } elseif ($request->status === 'pending') {
                $query->where('status', 'pending'); // Can also see direct pending
            } elseif ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        }

        $expenses = $query->latest('expense_date')->paginate(20);

        return view('admin.fieldstaff_management.expenses.index', compact('expenses'));
    }

    /**
     * Approve or Reject an expense.
     */
    public function updateExpenseStatus(Request $request, Expense $expense)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'reason' => 'nullable|string|required_if:status,rejected',
        ]);

        $status = $request->status;
        $updateData = [
            'rejection_reason' => $request->reason,
        ];

        if (Auth::user()->hasRole('salesmanager')) {
            if ($status === 'approved') {
                $updateData['status'] = 'manager_approved';
                $updateData['manager_id'] = Auth::id();
            } else {
                $updateData['status'] = 'rejected';
            }
        } else {
            // Admin final approval
            $updateData['status'] = $status;
            $updateData['admin_id'] = Auth::id();
        }

        $expense->update($updateData);

        return back()->with('success', 'Expense status updated successfully.');
    }

    /**
     * Display a listing of leaves for approval.
     */
    public function leavesIndex(Request $request)
    {
        $query = LeaveRequest::with(['user', 'manager', 'admin']);

        if (Auth::user()->hasRole('salesmanager')) {
            $staffIds = User::whereHas('fieldStaff', function($q) {
                $q->where('sales_manager_id', Auth::user()->salesManager->id);
            })->pluck('id');
            $query->whereIn('user_id', $staffIds);

            if (!$request->has('status') || $request->status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        } elseif (Auth::user()->hasAnyRole(['admin', 'superadmin'])) {
            if (!$request->has('status') || $request->status === 'manager_approved') {
                $query->where('status', 'manager_approved');
            } elseif ($request->status === 'pending') {
                $query->where('status', 'pending');
            } elseif ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        }

        $leaves = $query->latest('start_date')->paginate(20);

        return view('admin.fieldstaff_management.leaves.index', compact('leaves'));
    }

    /**
     * Update leave status.
     */
    public function updateLeaveStatus(Request $request, LeaveRequest $leave)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $status = $request->status;
        $updateData = [];

        if (Auth::user()->hasRole('salesmanager')) {
            if ($status === 'approved') {
                $updateData['status'] = 'manager_approved';
                $updateData['manager_id'] = Auth::id();
            } else {
                $updateData['status'] = 'rejected';
            }
        } else {
            $updateData['status'] = $status;
            $updateData['admin_id'] = Auth::id();
            $updateData['approved_by'] = Auth::id(); // Backwards compatibility

            if ($updateData['status'] === 'approved' && $leave->status !== 'approved') {
                $type = $leave->type;
                $start = \Carbon\Carbon::parse($leave->start_date);
                $end = $leave->end_date ? \Carbon\Carbon::parse($leave->end_date) : $start->copy();
                
                $days = ($leave->duration_type === 'full_day' || empty($leave->duration_type)) 
                        ? ($start->diffInDays($end) + 1) 
                        : 0.5;
                
                $requester = $leave->user;
                if ($requester) {
                    $leaveType = \App\Models\LeaveType::where('name', $type)->first();
                    if ($leaveType) {
                        $userBalance = \App\Models\UserLeaveBalance::where('user_id', $requester->id)
                            ->where('leave_type_id', $leaveType->id)
                            ->first();
                        if ($userBalance) {
                            $userBalance->balance = max(0, $userBalance->balance - $days);
                            $userBalance->save();
                        }
                    }
                }
            }
        }

        $leave->update($updateData);
        
        // Notify the user
        if ($leave->user) {
            $leave->user->notify(new \App\Notifications\LeaveStatusUpdatedNotification($leave, $updateData['status'], Auth::user()));
        }

        return back()->with('success', 'Leave status updated successfully.');
    }
}
