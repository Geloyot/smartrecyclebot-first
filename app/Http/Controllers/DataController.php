<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class DataController extends Controller
{
    public function userAdd(Request $request) {
        $DataCredentials = [
            'name' => 'required|unique:users,name',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:8',
            'role_id' => 'required|exists:roles,id',
        ];

        $validated = $request->validate($DataCredentials);

        // Hash the password
        // $validated['password'] = Hash::make($validated['password']);

        // Set default status and timestamp
        $validated['status'] = 'Active';
        $validated['last_status_updated'] = now();

        $user = User::create($validated);

        $notify = [
            'user_id'  => null,                      // global alert for admins
            'type'     => 'User Management',
            'title'    => 'New User Registered',
            'message'  => "A new account for {$user->name} has been created.",
            'level'    => 'info',
            'is_read'  => false,
        ];
        Notification::create($notify);

        return redirect()->intended('/admin/user-management')->with('success', 'User added successfully.');
    }

    public function userEdit(Request $request, $id) {
        $user = User::findOrFail($id);

        // Validate base fields
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:users,name,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'role_id' => 'required|integer|exists:roles,id',
        ]);

        // Optional password update
        if ($request->filled('password')) {
            $request->validate([
                'password' => 'required|string|min:8|confirmed',
            ]);
            $validatedData['password'] = Hash::make($request->password);
        }

        // Update the user with all applicable fields
        $user->update($validatedData);
        $notify = [
            'user_id'  => $user->id,           // personal alert
            'type'     => 'Account',
            'title'    => 'Your Profile Was Updated',
            'message'  => "Your account details were updated by " . Auth::user()->name,
            'level'    => 'info',
            'is_read'  => false,
        ];
        Notification::create($notify);

        return redirect()->intended('/admin/user-management')->with('success', 'User updated successfully.');
    }

    public function userDeactivate(Request $request, $id) {
        $admin = $request->user();
        $user = User::findOrFail($id);

        if ($user->status === 'Deactivated') {
            $user->setStatus('Inactive');

            Notification::create([
                'user_id' => null,
                'type' => 'User Management',
                'title' => 'User Reactivation',
                'message' => "Account for {$user->name} has been reactivated.",
                'level' => 'info',
                'is_read' => false,
            ]);
        } else {
            $user->setStatus('Deactivated');

            Notification::create([
                'user_id' => null,
                'type' => 'User Management',
                'title' => 'User Deactivated',
                'message' => "Account for {$user->name} has been deactivated.",
                'level' => 'warning',
                'is_read' => false,
            ]);
        }

        return back()->with('success', "User {$user->name} deactivated.");
    }
}
