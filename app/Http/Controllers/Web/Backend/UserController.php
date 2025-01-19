<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PublishRequest;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->ajax()) {
                $data = User::orderBy('created_at', 'desc')->get();
                return DataTables::of($data)
                    ->addIndexColumn()
                    ->addColumn('avatar', function ($data) {
                        $url = $data->avatar ? asset($data->avatar) : asset('backend/images/profile-avatar.png');
                        return '<img src="' . $url . '" alt="image" class="img-fluid" style="height:50px; width:50px">';
                    })
                    ->rawColumns(['avatar'])
                    ->make(true);
            }
            return view('backend.layout.user.index');
        } catch (Exception $e) {
            return redirect()->back()->with('t-error', 'Something went wrong! Please try again.');
        }
    }
}
