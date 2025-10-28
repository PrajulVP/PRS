<?php

namespace App\Http\Controllers;

use App\Models\Manager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManagerController extends Controller
{
    public function index()
    {
        $managers = Manager::latest()->get();
        return view('admin.managers.index', compact('managers'));
    }

    public function create()
    {
        return view('admin.managers.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:managers',
            'password' => 'required|min:4',
        ]);

        $data['password'] = Hash::make($data['password']);
        Manager::create($data);

        return redirect()->route('managers.index')->with('success', 'Manager added successfully!');
    }

    public function edit(Manager $manager)
    {
        return view('admin.managers.edit', compact('manager'));
    }

    public function update(Request $request, Manager $manager)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:managers,email,' . $manager->id,
            'password' => 'nullable|min:4',
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $manager->update($data);

        return redirect()->route('managers.index')->with('success', 'Manager updated successfully!');
    }

    public function destroy(Manager $manager)
    {
        $manager->delete();
        return redirect()->route('managers.index')->with('success', 'Manager deleted successfully!');
    }
}
