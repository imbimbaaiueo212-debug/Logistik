<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Module;
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
        $users = User::orderBy('name')->get();

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
    $user    = User::findOrFail($id);
    $modules = Module::all();

    return view('users.edit', compact('user', 'modules'));
}

    // ======================
    // UPDATE USER
    // ======================
    public function update(Request $request, $id)
{
    $user = User::findOrFail($id);

    $request->validate([
        'name'      => 'required',
        'email'     => 'required|email|unique:users,email,' . $user->id,
        'role'      => 'required|in:admin,customer,pic,pic_khusus,gudang',
        'modules'   => 'nullable|array',
        'modules.*' => 'exists:modules,id',
    ]);

    $modules = $request->modules ?? [];

    if (in_array($request->role, ['customer', 'pic']) && count($modules) > 1) {
        return back()->withErrors([
            'modules' => 'Role ' . ($request->role === 'pic' ? 'PIC' : 'Customer') . ' hanya boleh diberi 1 akses.',
        ])->withInput();
    }

    $data = [
        'name'  => $request->name,
        'email' => $request->email,
        'role'  => $request->role,
    ];

    if ($request->filled('password')) {
        $request->validate([
            'password' => 'min:6|confirmed'
        ]);
        $data['password'] = Hash::make($request->password);
    }

    $user->update($data);

    // Modul gudang tetap (paket otomatis), admin tidak butuh pivot, sisanya sesuai pilihan admin
    $gudangModuleKeys = [
        'gudang', 'suplier', 'produk', 'produk_supplier',
        'quotation', 'purchase_order', 'stok_masuk', 'transfer',
        'distribusi', 'distribusi_retur',
    ];

    if ($request->role === 'admin') {
        $user->modules()->sync([]);
    } elseif ($request->role === 'gudang') {
        $user->modules()->sync(Module::whereIn('key', $gudangModuleKeys)->pluck('id'));
    } else {
        $user->modules()->sync($modules);
    }

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

    public function accessForm($id)
{
    $user    = User::findOrFail($id);
    $modules = Module::all();

    return view('users.access', compact('user', 'modules'));
}

public function updateAccess(Request $request, $id)
{
    $user = User::findOrFail($id);

    $request->validate([
        'role'        => 'required|in:admin,customer,pic,pic_khusus',
        'is_approved' => 'nullable|boolean',
        'modules'     => 'nullable|array',
        'modules.*'   => 'exists:modules,id',
    ]);

    $modules = $request->modules ?? [];

    if (in_array($request->role, ['customer', 'pic']) && count($modules) > 1) {
        return back()->withErrors([
            'modules' => 'Role ' . ($request->role === 'pic' ? 'PIC' : 'Customer') . ' hanya boleh diberi 1 akses.',
        ])->withInput();
    }

    $user->update([
        'role'        => $request->role,
        'is_approved' => $request->boolean('is_approved'),
    ]);

    $user->modules()->sync($request->role === 'admin' ? [] : $modules);

    return redirect()->route('users.index')->with('success', 'Hak akses user berhasil diperbarui');
}
}