<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Course;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = Category::orderBy('created_at', 'desc')->get();
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('icon', function ($data) {
                        $url = asset($data->icon);
                        return '<img src="' . $url . '" alt="image" class="img-fluid">';
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
                    <a href="' . route('admin.category.edit', $data->id) . '" class="btn btn-primary text-white" title="Edit">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <a href="#" onclick="deleteAlert(' . $data->id . ')" class="btn btn-danger text-white" title="Delete">
                        <i class="fa fa-times"></i>
                    </a>
                </div>';
                    })
                    ->rawColumns(['action', 'icon', 'status'])
                    ->make(true);
            }
            return view('backend.layout.category.index');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', 'Something went wrong! Please try again.');
        }
    }

    public function create(){
        return view('backend.layout.category.create');
    }

    public function store(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'icon'=> 'required|image|mimes:jpeg,png,jpg,gif',
        ]);

        $iconUrl = '';
        if ($request->hasFile('icon')) {
            $file = $request->file('icon');
            $randomString = (string)Str::uuid();
            $iconUrl = Helper::fileUpload($file, 'category', $randomString);
        }
        $course = Category::create([
            'name' => $request->name,
            'icon' => $iconUrl,
        ]);
       return redirect()->route('admin.category.index')->with('t-success', 'Category created successfully.');

    }
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('backend.layout.category.edit', compact('category'));
    }
    public function update(Request $request, $id): \Illuminate\Http\RedirectResponse
    {
        $category = Category::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,gif',
        ]);

        // Handle the new icon file if uploaded
        if ($request->hasFile('icon')) {
            // Delete the old icon file if it exists
            if ($category->icon) {
                Helper::fileDelete($category->icon);
            }

            $file = $request->file('icon');
            $randomString = (string)Str::uuid();
            $iconUrl = Helper::fileUpload($file, 'category', $randomString);
            $category->icon = $iconUrl;
        }

        // Update category details
        $category->name = $request->name;
        $category->save();

        return redirect()->route('admin.category.index')->with('t-success', 'Category updated successfully.');
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        $category = Category::findOrFail($id);
        if ($category->icon) {
            Helper::fileDelete($category->icon);
        }
        $category->delete();
        return response()->json(['t-success' => true, 'message' => 'Deleted successfully.']);
    }

    public function status(Request $request, $courseId): ?\Illuminate\Http\JsonResponse
    {
        $data = Category::find($courseId);
        if (!$data) {
            return response()->json(['success' => false, 'message' => 'Category not found']);
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
