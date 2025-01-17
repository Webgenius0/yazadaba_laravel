<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\User;
use App\Models\WithdrawRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Predis\Command\Redis\SAVE;

class WithdrawRejectController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = WithdrawRequest::orderBy('created_at', 'desc')->where('status', 'rejected')->get();
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('user_name', function ($row) {
                        return $row->user->name ?? 'N/A';
                    })
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at ?? 'N/A';
                    })
                    ->addColumn('status', function ($data) {
                        // Assuming $data['status'] contains the status value
                        $status = $data['status'];

                        // Determine the button color based on the status
                        if ($status == 'complete') {
                            return '<button class="btn btn-success btn-sm" >' . htmlspecialchars($status) . '</button>';
                        } elseif ($status == 'rejected') {
                            return '<button class="btn btn-danger btn-sm"  >' . htmlspecialchars($status) . '</button>';
                        } elseif ($status == 'pending') {
                            return '<button class="btn btn-warning btn-sm" >' . htmlspecialchars($status) . '</button>';
                        } else {
                            // Default button for any other status value
                            return '<button class="btn btn-secondary btn-sm">' . htmlspecialchars($status) . '</button>';
                        }
                    })
                    ->addColumn('action', function ($data) {
                        return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                    <a href="' . route('admin.withdraw.reject.show', $data->id) . '" class="btn btn-primary text-white" title="View">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="#" onclick="deleteAlert(' . $data->id . ')" class="btn btn-danger text-white" title="Delete">
                        <i class="fa fa-times"></i>
                    </a>
                </div>';
                    })
                    ->rawColumns(['action', 'created_at', 'status'])
                    ->make(true);
            }
            return view('backend.layout.withdraw_reject.index');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', 'Something went wrong! Please try again.');
        }
    }

    public function show($id)
    {
        $user = User::find($id);
        $courses = Course::where('user_id', $id)->get();
        $bank_info = WithdrawRequest::find($id);
        return view('backend.layout.withdraw_reject.show', compact('user', 'courses', 'bank_info'));
    }

    public function destroy($id)
    {
        try {
            $data = WithdrawRequest::where('status', 'rejected')->find($id);
            if (!$data) {
                return response()->json(['t-error' => true, 'message' => 'Record not found.']);
            }
            $data->delete();
            return response()->json(['t-success' => true, 'message' => 'Record deleted successfully.']);
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return response()->json(['t-error' => true, 'message' => 'Failed to delete record.']);
        }
    }
}
