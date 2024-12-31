<?php

namespace App\Http\Controllers\Web\Backend;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class SystemSettingController extends Controller
{
    public function index()
    {
        $setting = SystemSetting::latest('id')->first();
        return view('backend.layout.system_setting.index', compact('setting'));
    }

    public function update(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        // Validate the uploaded files
        $validator = Validator::make($request->all(), [
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|mimes:jpeg,png,jpg,gif,svg,ico|max:2048',
        ]);

        // If validation fails, redirect back with errors
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Fetch the existing system setting record (if any)
        $setting = SystemSetting::firstOrNew();

        // Handle the logo file upload, if a new logo was uploaded
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $randomString = (string)Str::uuid();
            $logoUrl = Helper::fileUpload($file, 'system/logo', $randomString);

            // Update the logo field with the new logo URL
            $setting->logo = $logoUrl;
        }

        // Handle the favicon file upload, if a new favicon was uploaded
        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $randomString = (string)Str::uuid();
            $faviconUrl = Helper::fileUpload($file, 'system/favicon', $randomString);

            // Update the favicon field with the new favicon URL
            $setting->favicon = $faviconUrl;
        }

        try {
            // Save the setting (logo and/or favicon) to the database
            $setting->save();

            // Return a success message
            return back()->with('t-success', 'Updated successfully');
        } catch (Exception $e) {
            // Return an error message if something went wrong
            return back()->with('t-error', 'Failed to update');
        }
    }

    public function mailSetting()
    {
        return view('backend.layout.system_setting.mail-setting');
    }

    public function mailSettingUpdate(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'mail_mailer' => 'required|string',
            'mail_host' => 'required|string',
            'mail_port' => 'required|string',
            'mail_username' => 'nullable|string',
            'mail_password' => 'nullable|string',
            'mail_encryption' => 'nullable|string',
            'mail_from_address' => 'required|string',
        ]);
        try {
            $envContent = File::get(base_path('.env'));
            $lineBreak = "\n";
            $envContent = preg_replace([
                '/MAIL_MAILER=(.*)\s/',
                '/MAIL_HOST=(.*)\s/',
                '/MAIL_PORT=(.*)\s/',
                '/MAIL_USERNAME=(.*)\s/',
                '/MAIL_PASSWORD=(.*)\s/',
                '/MAIL_ENCRYPTION=(.*)\s/',
                '/MAIL_FROM_ADDRESS=(.*)\s/',
            ], [
                'MAIL_MAILER=' . $request->mail_mailer . $lineBreak,
                'MAIL_HOST=' . $request->mail_host . $lineBreak,
                'MAIL_PORT=' . $request->mail_port . $lineBreak,
                'MAIL_USERNAME=' . $request->mail_username . $lineBreak,
                'MAIL_PASSWORD=' . $request->mail_password . $lineBreak,
                'MAIL_ENCRYPTION=' . $request->mail_encryption . $lineBreak,
                'MAIL_FROM_ADDRESS=' . '"' . $request->mail_from_address . '"' . $lineBreak,
            ], $envContent);

            if ($envContent !== null) {
                File::put(base_path('.env'), $envContent);
            }
            return back()->with('t-success', 'Updated successfully');
        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update');
        }

    }

    public function profileIndex()
    {
        return view('backend.layout.system_setting.profile_setting');
    }

    public function profileUpdate(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'fname'=>'required|string|max:255',
            'lname'=>'required|string|max:255',
            'email'=>'required|string|unique:users,email,'.Auth::user()->id,
        ]);
        $avatarUrl = '';
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $randomString = (string)Str::uuid();
            $avatarUrl = Helper::fileUpload($file, 'system/avatar', $randomString);
        }
        $user = User::find(Auth::user()->id);
        $user->fname = $request->fname;
        $user->lname = $request->lname;
        $user->email = $request->email;
        $user->save();

        return redirect()->back()->with('t-success','Profile Update Successfully!');
    }


    public function passwordUpdate(Request $request): \Illuminate\Http\RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required','confirmed'
            ],
        ]);

        // Update the user's password
        $request->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Redirect back with a success message
        return redirect()->back()->with('t-success', 'Password updated successfully!');
    }

}
