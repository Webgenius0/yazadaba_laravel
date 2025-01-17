<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Notifications\WithdrawRequestRejected;
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
                        <li>
                              <a class="dropdown-item" href="#" onclick="showStatusChangeAlert(event, ' . $data->id . ', \'complete\')" style="padding: 5px 10px;">Complete</a>
                            </li>
                             <li><a class="dropdown-item" href="#" onclick="showStatusChangeAlert(' . $data->id . ', \'pending\')" style="padding: 5px 10px;">Pending</a></li>
                        <li><a class="dropdown-item" href="#" onclick="openRejectModal(event,' . $data->id . ' , '. $data->user_id .',\'rejected\')" style="padding: 5px 10px;">Rejected</a></li>
                    </ul>
                </div>
              ';
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

    public function status(Request $request, $courseId): ?\Illuminate\Http\JsonResponse
    {
        // Find the withdraw request by ID
        $data = WithdrawRequest::find($courseId);

        // If the record doesn't exist, return an error message
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data not found',
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

        // Ensure that the withdrawal request belongs to the correct user
        if ($withdrawRequest->user_id != $userId) {
            return response()->json(['error' => 'User mismatch for this withdrawal request'], 403);
        }

        // Store the amount to be added back to the user's balance
        $amountToAddBack = $withdrawRequest->amount;

        // Update the status and rejection reason
        $withdrawRequest->status = 'rejected';
        $withdrawRequest->rejection_reason = $request->input('rejection_reason');
        $withdrawRequest->save();

        // Add the rejected amount back to the withdrawal request's remaining_balance
        $withdrawRequest->remaining_balance += $amountToAddBack;
        $withdrawRequest->save();

        // Notify the user about the rejection (optional)
        $user = $withdrawRequest->user;
        $user->notify(new WithdrawRequestRejected($request->input('rejection_reason')));

        if ($user->firebaseTokens) {
            $avatarPath = asset('backend/images/dashboard/img_1.jpg');
            $notifyData = [
                'title' => 'Withdrawal Request Rejected',
                'body' => "Your withdrawal request of {$withdrawRequest->amount} has been rejected.\nReason:{!!$withdrawRequest->rejection_reason!!}",
            ];
            foreach ($user->firebaseTokens as $firebaseToken) {
                Helper::sendNotifyMobile($firebaseToken->token, $notifyData);
            }
        }

        // Return a JSON response
        return response()->json(['message' => 'Withdrawal request rejected successfully.']);
    }
    public function destroy($id){
        try {
            $data = WithdrawRequest::where('status','pending')->find($id);
            if (!$data){
                return response()->json(['t-error' => true,'message' => 'Record not found.']);
            }
            $data->delete();
            return response()->json(['t-success' => true,'message' => 'Record deleted successfully.']);
        }catch (Exception $e){
            Log::error($e->getMessage());
            return response()->json(['t-error' => true,'message' => 'Failed to delete record.']);
        }
        }


}
