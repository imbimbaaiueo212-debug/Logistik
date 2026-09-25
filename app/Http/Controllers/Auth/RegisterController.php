<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Module;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        $modules = Module::all();
        return view('auth.register', compact('modules'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'role'     => 'required|in:admin,customer,pic,pic_khusus',
            'modules'     => 'nullable|array',
            'modules.*'   => 'exists:modules,id',
        ]);

        $modules = $request->modules ?? [];

        if (in_array($request->role, ['customer', 'pic']) && count($modules) > 1) {
            return back()->withErrors([
                'modules' => 'Role ' . ($request->role === 'pic' ? 'PIC' : 'Customer') . ' hanya boleh diberi 1 akses.',
            ])->withInput();
        }

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'is_approved' => true, // admin yang buat, langsung aktif
        ]);

        if ($request->role !== 'admin') {
            $user->modules()->sync($modules);
        }

        // TIDAK Auth::login($user) — supaya admin tetap login sebagai dirinya sendiri

        return redirect()->route('users.index')
            ->with('success', 'Akun berhasil dibuat dengan hak akses: ' . $user->role);
    }
}