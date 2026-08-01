<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Product;

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
        
        $brands = \App\Models\Brand::orderBy('id')->get();
        $product_brands = $brands->pluck('name')->implode(',');
        $returnable_brands = Setting::getValue('returnable_brands', '');
        $loyalty_brands = Setting::getValue('loyalty_brands', '');
        
        $slabs = \App\Models\LoyaltySlab::orderBy('min_points')->get();
        $loyalty_rules_array = [];
        foreach ($slabs as $slab) {
            $brand = $slab->type;
            if (!isset($loyalty_rules_array[$brand])) {
                $loyalty_rules_array[$brand] = [];
            }
            $loyalty_rules_array[$brand][] = [
                'threshold' => $slab->min_points,
                'reward' => $slab->gift_name,
                'description' => $slab->description,
                'image_url' => $slab->gift_image ? asset($slab->gift_image) : null,
                'image_path' => $slab->gift_image
            ];
        }
        $loyalty_rules = json_encode($loyalty_rules_array);

        $type_medical_title = Setting::getValue('type_medical_title', 'ATOMEDS');
        $type_medical_desc = Setting::getValue('type_medical_desc', 'Medicines');
        $type_ortho_title = Setting::getValue('type_ortho_title', 'ATOMSHIELD');
        $type_ortho_desc = Setting::getValue('type_ortho_desc', 'Surgical and Orthopedic Supports');
        $type_general_title = Setting::getValue('type_general_title', 'SUDHNEELGIRI');
        $type_general_desc = Setting::getValue('type_general_desc', 'Herbals');

        $leaveTypes = \App\Models\LeaveType::all();

        return view('admin.settings.general', compact(
            'value', 'cgst', 'sgst', 
            'geofence_radius', 'ta_rate_per_km', 'da_hq_rate', 'da_outstation_rate',
            'hq_radius_km', 'product_brands', 'returnable_brands',
            'type_medical_title', 'type_medical_desc',
            'type_ortho_title', 'type_ortho_desc',
            'type_general_title', 'type_general_desc',
            'brands', 'leaveTypes', 'loyalty_brands', 'loyalty_rules'
        ));
    }

    /**
     * Save setting via AJAX
     */
    public function save(Request $request)
    {
        $data = $request->validate([
            'slug' => 'required|string',
            'value' => 'nullable',
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
        } elseif ($data['slug'] === 'loyalty_brands') {
            $title = 'Loyalty Brands';
            $desc = 'Comma-separated list of brands with loyalty enabled.';
        } elseif ($data['slug'] === 'loyalty_rules') {
            $rulesData = json_decode($data['value'], true);
            $images = request()->file('images');
            
            \DB::transaction(function() use ($rulesData, $images) {
                $existingSlabs = \App\Models\LoyaltySlab::all();
                $incomingKeys = [];
                
                if (is_array($rulesData)) {
                    foreach ($rulesData as $brand => $rules) {
                        if (!is_array($rules)) continue;
                        foreach ($rules as $rule) {
                            $key = $brand . '_' . $rule['threshold'];
                            $incomingKeys[] = $key;
                            
                            $slab = \App\Models\LoyaltySlab::firstOrNew([
                                'type' => $brand,
                                'min_points' => $rule['threshold']
                            ]);
                            $slab->slab_name = $brand . ' - ₹' . $rule['threshold'];
                            $slab->gift_name = $rule['reward'];
                            $slab->description = $rule['description'] ?? null;
                            
                            if ($images && isset($images[$key])) {
                                $file = $images[$key];
                                $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                                $file->move(public_path('uploads/loyalty_gifts'), $filename);
                                $slab->gift_image = 'uploads/loyalty_gifts/' . $filename;
                            }
                            
                            $slab->save();
                        }
                    }
                }
                
                foreach ($existingSlabs as $slab) {
                    $key = $slab->type . '_' . $slab->min_points;
                    if (!in_array($key, $incomingKeys)) {
                        if ($slab->redemptions()->count() == 0) {
                            $slab->delete();
                        }
                    }
                }
            });
            return response()->json(['message' => 'Loyalty Slabs saved successfully.']);
        } elseif ($data['slug'] === 'type_medical_title') {
            $title = 'Medical Product Type Title';
            $desc = 'Main title for Medical Products tab in modal.';
        } elseif ($data['slug'] === 'type_medical_desc') {
            $title = 'Medical Product Type Description';
            $desc = 'Sub-description for Medical Products tab.';
        } elseif ($data['slug'] === 'type_ortho_title') {
            $title = 'Ortho Product Type Title';
            $desc = 'Main title for Orthopedic Products tab in modal.';
        } elseif ($data['slug'] === 'type_ortho_desc') {
            $title = 'Ortho Product Type Description';
            $desc = 'Sub-description for Orthopedic Products tab.';
        } elseif ($data['slug'] === 'type_general_title') {
            $title = 'General Product Type Title';
            $desc = 'Main title for Herbal/General Products tab.';
        } elseif ($data['slug'] === 'type_general_desc') {
            $title = 'General Product Type Description';
            $desc = 'Sub-description for Herbal/General Products tab.';
        }

        $setting = Setting::setValue(
            $data['slug'],
            $data['value'],
            $title,
            $desc
        );

        // Handle brand renames in products table
        if ($data['slug'] === 'product_brands' && $request->has('renamed_brands') && is_array($request->renamed_brands)) {
            foreach ($request->renamed_brands as $oldName => $newName) {
                Product::where('brand', $oldName)->update(['brand' => $newName]);
            }
        }

        return response()->json(['message' => 'Setting saved', 'setting' => $setting]);
    }

    public function saveBrand(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer|exists:brands,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'layout_type' => 'required|string|in:medical,ortho,general,custom',
            'custom_fields' => 'nullable|array',
        ]);

        $name = trim($request->name);
        $id = $request->id;

        // Check uniqueness manually to support edit case-insensitively
        $existing = \App\Models\Brand::where('name', $name)->first();
        if ($existing && $existing->id != $id) {
            return response()->json(['message' => 'Brand name already exists.'], 422);
        }

        $customFields = $request->layout_type === 'custom' ? $request->custom_fields : null;

        if ($id) {
            $brand = \App\Models\Brand::find($id);
            $oldName = $brand->name;
            $brand->update([
                'name' => $name,
                'description' => $request->description,
                'icon' => $request->icon ?: 'fa-tag',
                'layout_type' => $request->layout_type,
                'custom_fields' => $customFields,
            ]);

            // Sync product table if renamed
            if (strcasecmp($oldName, $name) !== 0) {
                Product::where('brand', $oldName)->update(['brand' => $name]);
            }
        } else {
            $brand = \App\Models\Brand::create([
                'name' => $name,
                'description' => $request->description,
                'icon' => $request->icon ?: 'fa-tag',
                'layout_type' => $request->layout_type,
                'custom_fields' => $customFields,
            ]);
        }

        // Sync legacy product_brands setting
        $names = \App\Models\Brand::pluck('name')->implode(',');
        Setting::setValue('product_brands', $names);

        return response()->json(['message' => 'Brand saved successfully', 'brand' => $brand]);
    }

    public function deleteBrand(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:brands,id',
        ]);

        $brand = \App\Models\Brand::find($request->id);
        if ($brand) {
            $brand->delete();
        }

        // Sync legacy product_brands setting
        $names = \App\Models\Brand::pluck('name')->implode(',');
        Setting::setValue('product_brands', $names);

        return response()->json(['message' => 'Brand deleted successfully']);
    }

    public function saveLeaveType(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer|exists:leave_types,id',
            'name' => 'required|string|max:255',
            'default_quota' => 'required|integer|min:0',
        ]);

        $name = trim($request->name);
        $id = $request->id;

        $existing = \App\Models\LeaveType::where('name', $name)->first();
        if ($existing && $existing->id != $id) {
            return response()->json(['message' => 'Leave Type already exists.'], 422);
        }

        if ($id) {
            $leaveType = \App\Models\LeaveType::find($id);
            $leaveType->update([
                'name' => $name,
                'default_quota' => $request->default_quota,
            ]);
        } else {
            $leaveType = \App\Models\LeaveType::create([
                'name' => $name,
                'default_quota' => $request->default_quota,
            ]);
        }

        // Auto-allocate to all field staffs and sales managers
        $users = \App\Models\User::whereHas('roles', function($q) {
            $q->whereIn('name', ['field_staff', 'sales_manager']);
        })->get();

        foreach ($users as $user) {
            \App\Models\UserLeaveBalance::updateOrCreate(
                ['user_id' => $user->id, 'leave_type_id' => $leaveType->id],
                ['balance' => $leaveType->default_quota]
            );
        }

        return response()->json(['message' => 'Leave Type saved and allocated successfully', 'leaveType' => $leaveType]);
    }

    public function deleteLeaveType(Request $request)
    {
        $request->validate([
            'id' => 'required|integer|exists:leave_types,id',
        ]);

        \App\Models\LeaveType::destroy($request->id);
        return response()->json(['message' => 'Leave Type deleted successfully']);
    }

    public function allocateLeaves(Request $request)
    {
        $leaveTypes = \App\Models\LeaveType::all();
        $users = \App\Models\User::whereHas('roles', function($q) {
            $q->whereIn('name', ['field_staff', 'sales_manager']);
        })->get();

        foreach ($users as $user) {
            foreach ($leaveTypes as $leaveType) {
                \App\Models\UserLeaveBalance::updateOrCreate(
                    ['user_id' => $user->id, 'leave_type_id' => $leaveType->id],
                    ['balance' => $leaveType->default_quota]
                );
            }
        }

        return response()->json(['message' => 'Leaves allocated successfully to all staff.']);
    }
}
