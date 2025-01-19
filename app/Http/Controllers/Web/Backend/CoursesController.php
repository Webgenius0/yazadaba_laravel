<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Course;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use function Pest\Laravel\delete;

class CoursesController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = Course::all();
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('cover_image', function ($data) {
                        $url = asset($data->cover_image);
                        return '<img src="' . $url . '" alt="image" class="img-fluid" style="width: 40px; height: 40px;">';
                    })
                    ->addColumn('user_name', function ($data) {
                        return $data->user->name;

                    })
                    ->addColumn('category_name', function ($data) {
                        return $data->category->name;
                    })
                    ->addColumn('grade_name', function ($data) {
                        return $data->gradeLevel->name;
                    })
                    ->addColumn('status', function ($data) {
                        $status = ' <div class="form-check form-switch" style="margin-left:40px;">';
                        $status .= ' <input onclick="showStatusChangeAlert(' . $data->id . ')" type="checkbox" class="form-check-input" id="customSwitch' . $data->id . '" getAreaid="' . $data->id . '" name="status"';
                        if ($data->status === 'active') {
                            $status .= "checked";
                        }
                        $status .= '><label for="customSwitch' . $data->id . '" class="form-check-label" for="customSwitch"></label></div>';

                        return $status;
                    })
                    ->addColumn('action', function ($data) {
                        return '<div class="btn-group btn-group-sm" role="group" aria-label="Basic example">
                        <a href="' . route('admin.course.show', $data->id) . '" class="btn btn-primary text-white" title="View">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="#" onclick="deleteAlert(' . $data->id . ')" class="btn btn-danger text-white" title="Delete">
                            <i class="fa fa-times"></i>
                        </a>
                    </div>';
                    })
                    ->rawColumns(['cover_image', 'user_name', 'grade_name', 'category_name', 'action', 'status'])
                    ->make(true);
            }
            return view('backend.layout.courses.index');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', 'Something went wrong! Please try again.');
        }
    }

    public function status(Request $request, $courseId): ?\Illuminate\Http\JsonResponse
    {
        $data = Course::find($courseId);
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Course not found']);
        }
        if ($data->status === 'active') {
            $data->status = 'inactive';
            $data->save();
            return response()->json([
                'success' => false,
                'message' => 'Unpublished Successfully.',
                'data' => $data,
            ]);
        } else {
            $data->status = 'active';
            $data->save();
            return response()->json([
                'success' => true,
                'message' => 'Published Successfully.',
                'data' => $data,
            ]);
        }
    }

    public function destroy(Request $request, $courseId): \Illuminate\Http\JsonResponse
    {
        $data = Course::find($courseId);
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Course not found']);
        }
        if ($data->cover_image) {
            Helper::fileDelete($data->cover_image);
        }
        if ($data->cover_image) {
            Helper::fileDelete($data->cover_image);
        }
        $data->delete();
        return response()->json(['t-success' => true, 'message' => 'Deleted successfully.']);
    }

    public function show(Request $request, $courseId): \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View|\Illuminate\Foundation\Application|\Illuminate\View\View
    {
        $data = Course::with('courseModules')->find($courseId);

        $totalDurationInSeconds = DB::table('course_modules')
            ->where('course_id', $courseId)
            ->sum(DB::raw('TIME_TO_SEC(module_video_duration)'));

        $minutes = floor($totalDurationInSeconds / 60);
        $seconds = $totalDurationInSeconds % 60;

        $formattedCourseDuration = sprintf('%02d:%02d min', $minutes, $seconds);

        return view('backend.layout.courses.show', compact('data', 'formattedCourseDuration'));
    }


}
