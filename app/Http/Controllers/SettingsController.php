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
        
        $geofence_radius = Setting::getValue('geofence_radius', '20');
        $ta_rate_per_km = Setting::getValue('ta_rate_per_km', '10');
        $da_hq_rate = Setting::getValue('da_hq_rate', '250');
        $da_outstation_rate = Setting::getValue('da_outstation_rate', '500');
        $hq_radius_km = Setting::getValue('hq_radius_km', '15');
        $product_brands = Setting::getValue('product_brands', 'Atomets,Atomshield,Sudhneelgiri');
        $returnable_brands = Setting::getValue('returnable_brands', '');

        return view('admin.settings.general', compact(
            'value', 'cgst', 'sgst', 
            'geofence_radius', 'ta_rate_per_km', 'da_hq_rate', 'da_outstation_rate',
            'hq_radius_km', 'product_brands', 'returnable_brands'
        ));
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
        if (in_array($data['slug'], ['loyalty_point_inr', 'cgst', 'sgst', 'geofence_radius', 'ta_rate_per_km', 'da_hq_rate', 'da_outstation_rate', 'hq_radius_km'])) {
            if (!is_numeric($data['value']) || (float) $data['value'] < 0) {
                return response()->json(['message' => 'Invalid value.'], 422);
            }
        }
        
        if ($data['slug'] === 'product_brands' && empty($data['value'])) {
             return response()->json(['message' => 'Brands list cannot be empty.'], 422);
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
        } elseif ($data['slug'] === 'geofence_radius') {
            $title = 'Geo-fencing Radius';
            $desc = 'Maximum allowed radius from customer location for punching logs (in meters).';
        } elseif ($data['slug'] === 'ta_rate_per_km') {
            $title = 'TA Rate per KM';
            $desc = 'Travel Allowance rate per kilometer travelled.';
        } elseif ($data['slug'] === 'da_hq_rate') {
            $title = 'DA HQ Rate';
            $desc = 'Daily Allowance rate for HQ visits.';
        } elseif ($data['slug'] === 'da_outstation_rate') {
            $title = 'DA Outstation Rate';
            $desc = 'Daily Allowance rate for Outstation visits.';
        } elseif ($data['slug'] === 'hq_radius_km') {
            $title = 'HQ Radius Threshold';
            $desc = 'Maximum distance (in KM) considered as Headquarter area.';
        } elseif ($data['slug'] === 'product_brands') {
            $title = 'Product Brands';
            $desc = 'Comma-separated list of available product brands.';
        } elseif ($data['slug'] === 'returnable_brands') {
            $title = 'Returnable Brands';
            $desc = 'Comma-separated list of brands eligible for returns.';
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
