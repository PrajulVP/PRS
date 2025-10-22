<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DistrictController extends Controller
{
    // Show all districts
    public function index()
    {
        return District::all();  // for now - API style
    }

    // Store new district
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string',
            'state_id' => 'required|integer',
        ]);

        return District::create($request->all());
    }
}
