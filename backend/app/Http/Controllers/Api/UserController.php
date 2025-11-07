<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Menampilkan daftar user.
     */
    public function index()
    {
        $users = User::all();
        return view('crud.user.index', compact('users'));
    }

    /**
     * Form edit user.
     */
    public function edit(User $user)
    {
        return view('crud.user.edit', compact('user'));
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'role' => 'required|in:admin,penjual,user',
        ]);

        $user->update([
            'role' => $request->role,
        ]);

        return redirect()->route('dashboard.manage.user.index')->with('success', 'Role user berhasil diubah!');
    }

    /**
     * Hapus user.
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus!');
    }
}
