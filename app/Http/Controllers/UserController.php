<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', User::class);

        $query = User::latest();

        // Admin can only see Cashiers (and themselves)
        if (auth()->user()->isAdmin() && !auth()->user()->isSuperAdmin()) {
            $query->where('role', 'cashier');
        }

        $users = $query->paginate(20);
        return view('users.index', compact('users'));
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);
        return view('users.show', compact('user'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $user = auth()->user();
        $allowedRoles = $user->isSuperAdmin() 
            ? ['admin', 'cashier'] 
            : ['cashier'];

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'role' => 'required|in:' . implode(',', $allowedRoles),
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function update(Request $request, User $user)
    {
        $this->authorize('update', $user);

        $authUser = auth()->user();
        $allowedRoles = $authUser->isSuperAdmin() 
            ? ['super_admin', 'admin', 'cashier'] 
            : ($authUser->isAdmin() ? ['cashier'] : []);

        // Prevent changing own role if not super admin
        if ($user->id === $authUser->id && !$authUser->isSuperAdmin()) {
            unset($allowedRoles); // Or restrict to current role
        }

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:20',
        ];

        if (!empty($allowedRoles)) {
            $rules['role'] = 'required|in:' . implode(',', $allowedRoles);
        }

        if ($request->has('password') && $request->password) {
            $rules['password'] = 'required|string|min:8';
        }

        $validated = $request->validate($rules);

        if ($request->has('password') && $request->password) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy(User $user)
    {
        $this->authorize('delete', $user);

        if ($user->getKey() === auth()->id()) {
            return redirect()->route('users.index')->with('error', 'You cannot delete your own account.');
        }
        
        $user->delete();
        
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}
