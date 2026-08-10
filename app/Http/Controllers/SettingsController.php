<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        $settings = Setting::getSettings();
        return view('layout.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'company_name'    => 'required|string|max:255',
            'company_address' => 'nullable|string|max:255',
            'phone1'          => 'required|string|max:50',
            'phone2'          => 'nullable|string|max:50',
            'zaad'            => 'nullable|string|max:50',
            'edahab'          => 'nullable|string|max:50',
        ]);

        $settings = Setting::getSettings();
        $settings->update([
            'company_name'    => $request->company_name,
            'company_address' => $request->company_address,
            'phone1'          => $request->phone1,
            'phone2'          => $request->phone2,
            'zaad'            => $request->zaad,
            'edahab'          => $request->edahab,
        ]);

        return redirect()->back()->with('status', 'Header & Merchant settings updated successfully!');
    }
}
