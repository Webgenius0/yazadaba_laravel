<?php

namespace App\Http\Controllers\Web\Backend;

use Exception;
use App\Models\User;
use App\Models\Course;
use App\Models\RejectReason;
use Illuminate\Http\Request;
use App\Models\WithdrawRequest;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use TijsVerkoyen\CssToInlineStyles\Css\Rule\Rule;
use Yajra\DataTables\Facades\DataTables;

class WithdrawRequestController extends Controller
{


    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = WithdrawRequest::orderBy('created_at', 'desc')
                    ->where('status', 'pending')
                    ->get();

                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('user_name', function ($row) {
                        return $row->user->name ?? 'N/A';
                    })
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at ?? 'N/A';
                    })
                    ->addColumn('status', function ($data) {
                        return '
                <div class="dropdown">
                    <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton' . $data->id . '" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 12px; padding: 5px 10px;">
                        ' . ucfirst($data->status) . '  <!-- Display status as the button text -->
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton' . $data->id . '" style="font-size: 12px;">
                        <li><a class="dropdown-item" href="#" onclick="showStatusChangeAlert(' . $data->id . ', \'complete\')" style="padding: 5px 10px;">Complete</a></li>
                        <li><a class="dropdown-item" href="#" onclick="showStatusChangeAlert(' . $data->id . ', \'pending\')" style="padding: 5px 10px;">Pending</a></li>
                        <li><a class="dropdown-item" href="#" onclick="openRejectModal(' . $data->id . ')" style="padding: 5px 10px;">Rejected</a></li>
                    </ul>
                </div>
                <!-- Modal for rejection reason -->
                <div class="modal fade" id="rejectModal' . $data->id . '" tabindex="-1" aria-labelledby="rejectModalLabel' . $data->id . '" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="rejectModalLabel' . $data->id . '">Provide Rejection Reason</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                              <textarea id="rejectReason" class="form-control" rows="3" placeholder="Enter rejection reason"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-danger" onclick="submitRejectionReason(' . $data->id . ' ,'.$data->user_id.')">Reject</button>
                            </div>

                        </div>
                    </div>
                    </div>';
                    })
                    ->addColumn('action', function ($data) {
                        return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                        <a href="' . route('admin.withdraw.request.show', $data->id) . '" class="btn btn-primary text-white" title="View">
                            <i class="bi bi-eye"></i>
                        </a>
                        <a href="#" onclick="deleteAlert(' . $data->id . ')" class="btn btn-danger text-white" title="Delete">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>';
                    })
                    ->rawColumns(['action', 'status', 'created_at'])
                    ->make(true);
            }

            return view('backend.layout.withdraw_request.index');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', 'Something went wrong! Please try again.');
        }
    }

    public function show($id)
    {
        $user = User::find($id);
        $bank_info = WithdrawRequest::find($id);
        $courses = Course::where('user_id', $id)->get();
        return view('backend.layout.withdraw_request.show', compact('user', 'courses', 'bank_info'));
    }

    public function status(Request $request, $courseId)
    {
        // Find the withdraw request by ID
        $data = WithdrawRequest::find($courseId);

        // If the record doesn't exist, return an error message
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Withdraw Request not found',
            ]);
        }

        // Fetch the new status from the AJAX request
        $newStatus = $request->input('status');

        // Check if the provided status is valid
        if (in_array($newStatus, ['pending', 'rejected', 'complete'])) {
            // Update the status of the withdraw request
            $data->status = $newStatus;
            $data->save();

            // Return a success response with a message
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully.',
            ]);
        } else {
            // If the status is invalid, return an error response
            return response()->json([
                'success' => false,
                'message' => 'Invalid status provided.',
            ]);
        }
    }

    public function submitRejectionReason(Request $request, $id, $userId)
    {
        // Validate the incoming request
        $request->validate([
            'rejection_reason' => 'required|string|max:255',
        ]);

        // Find the withdrawal request by ID
        $withdrawRequest = WithdrawRequest::findOrFail($id);

        if ($withdrawRequest->user_id != $userId) {
            return response()->json(['error' => 'User mismatch for this withdrawal request'], 403);
        }
        // Update the status and rejection reason
        $withdrawRequest->status = 'rejected';
        $withdrawRequest->rejection_reason = $request->input('rejection_reason');
        $withdrawRequest->save();

        // Return a JSON response
        return response()->json(['message' => 'Withdrawal request rejected successfully.']);
    }

}
