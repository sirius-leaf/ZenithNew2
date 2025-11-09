<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Toko; // Pastikan Anda import model Toko

class MyTokoController extends Controller
{
    /**
     * Tampilkan Halaman "Kelola Toko" (Dashboard Toko)
     * Ini juga akan menampilkan daftar produk yang terkait.
     */
    public function show()
    {
        $user = Auth::user();

        // 1. Pastikan dia penjual
        if ($user->role !== 'penjual') {
            return redirect()->route('dashboard')->with('error', 'Anda bukan penjual.');
        }

        // 2. Ambil toko milik user, DAN produk-produk di dalamnya (Eager Loading)
        $toko = $user->toko()->with('products')->first();

        // 3. Jika dia penjual tapi belum punya toko (mis. dihapus admin)
        if (!$toko) {
            // Arahkan ke halaman 'buat toko'
            // (Asumsi rute ini diurus oleh TokoController atau yg lain)
            return redirect()->route('dashboard.manage.toko.create')->with('info', 'Anda harus membuat toko terlebih dahulu.');
        }

        // 4. Jika semua aman, tampilkan view dashboard tokonya
        // Kita kirim data $toko (yang sudah berisi produk) ke view
        return view('toko.show', compact('toko'));
    }

    /*
    CATATAN:
    Nanti Anda bisa tambahkan method edit() dan update() di sini,
    tapi itu untuk MENGEDIT DATA TOKO (misal: nama toko, deskripsi toko),
    BUKAN untuk produk.

    public function edit()
    {
        $toko = Auth::user()->toko;
        return view('toko.edit', compact('toko'));
    }
    */
}
