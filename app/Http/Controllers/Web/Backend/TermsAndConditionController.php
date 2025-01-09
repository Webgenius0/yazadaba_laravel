<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TermsAndCondition;
use App\Models\PrivacyPolicy;
use Exception;

class TermsAndConditionController extends Controller
{
    public function index()
    {
        $termsAndCondition = TermsAndCondition::first();
        return view('backend.layout.terms&condition.index', compact('termsAndCondition'));
    }

    public function update(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'terms' => 'nullable|string',
            'conditions'=> 'nullable|string',
        ]);

        $termsAndCondition = TermsAndCondition::firstOrNew();
        $termsAndCondition->terms = $request->terms;
        $termsAndCondition->conditions = $request->conditions;
        try {
            $termsAndCondition->save();
            return back()->with('t-success', 'Updated successfully');

        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update');
        }
   

        return redirect()->back()->with('success', 'Terms and condition updated successfully');
    }


    public function updatePrivecyPolicy(Request $request): ?\Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'privacy_policy' => 'nullable|string',
            'policy'=> 'nullable|string',
        ]);

        $privecyPolicy = PrivacyPolicy::firstOrNew();
        $privecyPolicy->privacy_policy = $request->privacy_policy;
        $privecyPolicy->policy = $request->policy;
        try {
            $privecyPolicy->save();
            return back()->with('t-success', 'Updated successfully');

        } catch (Exception $e) {
            return back()->with('t-error', 'Failed to update');
        }
        return redirect()->back()->with('success', 'Terms and condition updated successfully');
    }
}