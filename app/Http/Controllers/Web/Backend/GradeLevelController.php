<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GradeLevel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;


class GradeLevelController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = GradeLevel::orderBy('created_at', 'desc')->get();
                return DataTables::of($data)
                    ->addIndexColumn()

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
                    <a href="' . route('admin.grade-level.edit', $data->id) . '" class="btn btn-primary text-white" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a href="#" onclick="deleteAlert(' . $data->id . ')" class="btn btn-danger text-white" title="Delete">
                        <i class="fa fa-times"></i>
                    </a>
                </div>';
                    })
                    ->rawColumns(['action', 'status'])
                    ->make(true);
            }
            return view('backend.layout.grade_level.index');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', 'Something went wrong! Please try again.');
        }
    }

    public function create()
    {
        return view('backend.layout.grade_level.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);


        $course = GradeLevel::create([
            'name' => $request->name,
        ]);
        return redirect()->route('admin.grade-level.index')->with('t-success', 'Grade Level created successfully.');

    }

    public function edit($id)
    {
        $grade_level = GradeLevel::findOrFail($id);
        return view('backend.layout.grade_level.edit', compact('grade_level'));
    }

    public function update(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $grade_level = GradeLevel::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // Update category details
        $grade_level->name = $request->name;
        $grade_level->save();

        return redirect()->route('admin.grade-level.index')->with('t-success', 'Grade Level updated successfully.');
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $grade_level = GradeLevel::findOrFail($id);
        $grade_level->delete();
        return response()->json(['t-success' => true, 'message' => 'Deleted successfully.']);
    }

    public function status(Request $request, $courseId): ?\Illuminate\Http\JsonResponse
    {
        $data = GradeLevel::find($courseId);
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Grade Level not found']);
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
}
