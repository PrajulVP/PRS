<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ManagerController extends Controller
{
    public function index()
    {
        $managers = User::role('manager')->latest()->get();
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
            'email' => 'required|email|unique:users',
            'password' => 'required|min:4',
        ]);

        $data['password'] = Hash::make($data['password']);
        $data['role'] = 'manager';
        $manager = User::create($data);
        $manager->assignRole('manager');

        return redirect()->route('managers.index')->with('success', 'Manager added successfully!');
    }

    public function edit(User $manager)
    {
        return view('admin.managers.edit', compact('manager'));
    }

    public function update(Request $request, User $manager)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,' . $manager->id,
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

    public function destroy(User $manager)
    {
        $manager->delete();
        return redirect()->route('managers.index')->with('success', 'Manager deleted successfully!');
    }
}