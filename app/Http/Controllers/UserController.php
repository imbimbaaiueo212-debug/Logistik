<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;


class UserController extends Controller
{
    // ======================
    // LIST USER
    // ======================
    public function index()
{
    $users = User::where('id', Auth::id())->get();

    return view('users.index', compact('users'));
}

    // ======================
    // CREATE FORM
    // ======================
    public function create()
    {
        return view('users.create');
    }

    // ======================
    // STORE USER
    // ======================
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6'
        ]);

        User::create([
    'name' => $request->name,
    'email' => $request->email,
    'role' => $request->role,
    'password' => Hash::make($request->password),
]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dibuat');
    }

    // ======================
    // EDIT FORM
    // ======================
    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    // ======================
    // UPDATE USER
    // ======================
    public function update(Request $request, $id)
{
    $user = User::findOrFail($id);

    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users,email,' . $user->id,
    ]);

    $data = [
        'name' => $request->name,
        'email' => $request->email,
        'role' => $request->role,
    ];

    // 🔥 hanya update password kalau diisi
    if ($request->filled('password')) {
        $request->validate([
            'password' => 'min:6|confirmed'
        ]);

        $data['password'] = Hash::make($request->password);
    }

    $user->update($data);

    return redirect()->route('users.index')
        ->with('success', 'User berhasil diupdate');
}
    // ======================
    // DELETE USER
    // ======================
    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return back()->with('success', 'User berhasil dihapus');
    }

    // ======================
    // RESET PASSWORD (ADMIN)
    // ======================
    public function resetForm($id)
    {
        $user = User::findOrFail($id);
        return view('users.reset-password', compact('user'));
    }

    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::findOrFail($id);

        $user->password = Hash::make($request->password);
        $user->save();

        return redirect()->route('users.index')
            ->with('success', 'Password berhasil direset');
    }

    
}