<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\GradeLevel;
use App\Models\PublishRequest;
use App\Models\User;
use App\Models\WithdrawRequest;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;

class CoursePublicationController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = PublishRequest::orderBy('created_at', 'desc')->get();
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('user_name', function ($row) {
                        return $row->user->name ?? 'N/A';
                    })
                    ->addColumn('course_name', function ($row) {
                        return $row->course->name ?? 'N/A';
                    })
                    ->addColumn('created_at', function ($row) {
                        return $row->created_at ?? 'N/A';
                    })
                    ->addColumn('status', function ($data) {
                        // Assuming $data['status'] contains the status value
                        $status = $data['status'];

                        // Determine the button color based on the status
                        if ($status == 'complete') {
                            return '<button class="btn btn-success btn-sm">' . htmlspecialchars($status) . '</button>';
                        } elseif ($status == 'rejected') {
                            return '<button class="btn btn-danger btn-sm">' . htmlspecialchars($status) . '</button>';
                        } elseif ($status == 'pending') {
                            return '<button class="btn btn-warning btn-sm">' . htmlspecialchars($status) . '</button>';
                        } elseif ($status == 'published') {
                            return '<button class="btn btn-primary btn-sm">' . htmlspecialchars($status) . '</button>';
                        } else {
                            // Default button for any other status value
                            return '<button class="btn btn-secondary btn-sm">' . htmlspecialchars($status) . '</button>';
                        }
                    })
                    ->addColumn('action', function ($data) {
                        return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">

                    <a href="#" onclick="deleteAlert(' . $data->id . ')" class="btn btn-danger text-white" title="Delete">
                        <i class="fa fa-times"></i>
                    </a>
                </div>';
                    })
                    ->rawColumns(['action', 'created_at', 'status'])
                    ->make(true);
            }
            return view('backend.layout.course_publication.index');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', 'Something went wrong! Please try again.');
        }
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $data = PublishRequest::findOrFail($id);
        $data->delete();
        return response()->json(['t-success' => true, 'message' => 'Deleted successfully.']);
    }



}
