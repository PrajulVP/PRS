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
        $cgst = Setting::getValue('cgst', '9');
        $sgst = Setting::getValue('sgst', '9');
        return view('admin.settings.general', compact('value', 'cgst', 'sgst'));
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

        // Basic numeric validation
        if (in_array($data['slug'], ['loyalty_point_inr', 'cgst', 'sgst'])) {
            if (!is_numeric($data['value']) || (float) $data['value'] < 0) {
                return response()->json(['message' => 'Invalid value.'], 422);
            }
        }

        $title = $data['slug'];
        $desc = '';

        if ($data['slug'] === 'loyalty_point_inr') {
            $title = 'Loyalty point INR';
            $desc = 'INR value of 1 loyalty point';
        } elseif ($data['slug'] === 'cgst') {
            $title = 'CGST Percentage';
            $desc = 'Central Goods and Services Tax Percentage';
        } elseif ($data['slug'] === 'sgst') {
            $title = 'SGST Percentage';
            $desc = 'State Goods and Services Tax Percentage';
        }

        $setting = Setting::setValue(
            $data['slug'],
            $data['value'],
            $title,
            $desc
        );

        return response()->json(['message' => 'Setting saved', 'setting' => $setting]);
    }
}
