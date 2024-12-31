<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\SocalMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SocalmediaContoller extends Controller
{
    public function index() {
        $social_link = SocalMedia::latest('id')->get();
        return view('web.backend.layout.system_setting.socialmedia', compact('social_link'));
    }

 
    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'social_media.*'    => 'required|string',
            'profile_link.*'    => 'required|url',
            'social_media_id.*' => 'sometimes|nullable|integer',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try {
            $idsToUpdate = collect($request->social_media_id)->filter()->all();

            // Update existing entries and collect their IDs
            foreach ($request->social_media as $index => $media) {
                $profileLink   = $request->profile_link[$index] ?? null;
                $socialMediaId = $request->social_media_id[$index] ?? null;

                if ($media && $profileLink) {
                    $socialMedia               = $socialMediaId ? Socalmedia::find($socialMediaId) : new Socalmedia();
                    $socialMedia->social_media = $media;
                    $socialMedia->profile_link = $profileLink;
                    $socialMedia->save();

                    // If updating, remove this ID from the $idsToUpdate array
                    if (($key = array_search($socialMediaId, $idsToUpdate)) !== false) {
                        unset($idsToUpdate[$key]);
                    }
                }
            }

            Socalmedia::whereIn('id', $idsToUpdate)->delete();

            return back()->with('t-success', 'Social media links updated successfully.');
        } catch (Exception) {
            return back()->with('t-error', 'Social media links failed update.');
        }
    }


    public function destroy(int $id) {
        try {
            // Correctly delete the SocialMedia record by ID
            Socalmedia::destroy($id);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Social media link deleted successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete social media link.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
