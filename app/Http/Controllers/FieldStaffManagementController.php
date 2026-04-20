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

        // Filter by role (Sales Manager only sees their staff)
        if (Auth::user()->hasRole('salesmanager')) {
            $staffIds = User::whereHas('fieldStaff', function($q) {
                $q->where('sales_manager_id', Auth::user()->salesManager->id);
            })->pluck('id');
            $query->whereIn('user_id', $staffIds);
        }

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
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

        $updateData = [
            'status' => $request->status,
            'rejection_reason' => $request->reason,
        ];

        if (Auth::user()->hasRole('salesmanager')) {
            $updateData['manager_id'] = Auth::id();
        } else {
            $updateData['admin_id'] = Auth::id();
            // If admin approves, it's final. (In this simplified flow)
        }

        $expense->update($updateData);

        return back()->with('success', 'Expense ' . $request->status . ' successfully.');
    }

    /**
     * Display a listing of leaves for approval.
     */
    public function leavesIndex(Request $request)
    {
        $query = LeaveRequest::with(['user', 'approvedBy']);

        if (Auth::user()->hasRole('salesmanager')) {
            $staffIds = User::whereHas('fieldStaff', function($q) {
                $q->where('sales_manager_id', Auth::user()->salesManager->id);
            })->pluck('id');
            $query->whereIn('user_id', $staffIds);
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

        $leave->update([
            'status' => $request->status,
            'approved_by' => Auth::id(),
        ]);

        return back()->with('success', 'Leave request ' . $request->status . ' successfully.');
    }
}
