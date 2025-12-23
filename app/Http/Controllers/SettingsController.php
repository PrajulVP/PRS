<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    /**
     * Show general settings page
     */
    public function general()
    {
        $value = Setting::getValue('loyalty_point_inr', '0');
        return view('admin.settings.general', compact('value'));
    }

    /**
     * Save setting via AJAX
     */
    public function save(Request $request)
    {
        $data = $request->validate([
            'slug' => 'required|string',
            'value' => 'required',
        ]);

        // Basic numeric validation for this specific setting
        if ($data['slug'] === 'loyalty_point_inr') {
            if (!is_numeric($data['value']) || (float) $data['value'] < 0) {
                return response()->json(['message' => 'Invalid value.'], 422);
            }
        }

        $setting = Setting::setValue(
            $data['slug'],
            $data['value'],
            'Loyalty point INR',
            'INR value of 1 loyalty point'
        );

        return response()->json(['message' => 'Setting saved', 'setting' => $setting]);
    }
}
