<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $settings = Setting::getSettings();
        return view('settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'receipt_title' => 'required|string|max:255',
            'app_title' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'receipt_address' => 'nullable|string|max:500',
            'receipt_phone' => 'nullable|string|max:20',
        ]);

        $settings = Setting::getSettings();

        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = 'logo_' . time() . '_' . Str::random(10) . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('images'), $logoName);
            
            // Delete old logo if exists
            if ($settings->logo_path && file_exists(public_path($settings->logo_path))) {
                unlink(public_path($settings->logo_path));
            }
            
            $validated['logo_path'] = 'images/' . $logoName;
        }

        // Remove logo from validated if not uploaded
        unset($validated['logo']);

        $settings->update($validated);

        return redirect()->route('settings.edit')
            ->with('success', 'Settings updated successfully!');
    }
}