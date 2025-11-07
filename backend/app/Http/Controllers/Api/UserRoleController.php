<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserRoleController extends Controller
{
    /**
     * User mengajukan permintaan menjadi penjual
     */
    public function requestSeller(Request $request)
    {
        $user = $request->user();
        $user->update(['role' => 'penjual_pending']);

        return back()->with('success', 'Permintaan menjadi penjual berhasil dikirim.');
    }

    /**
     * Admin melihat semua request penjual
     */
    public function index()
    {
        $sellerRequests = User::where('role', 'penjual_pending')->get();

        return view('crud.admin.seller-requests', compact('sellerRequests'));
    }

    /**
     * Admin menyetujui permintaan user menjadi penjual
     */
    public function approve($id)
    {
        $user = User::findOrFail($id);
        $user->update(['role' => 'penjual']);

        return back()->with('success', 'User berhasil disetujui menjadi penjual.');
    }
}
