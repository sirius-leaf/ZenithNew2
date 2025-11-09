<?php

namespace App\Http\Controllers;

use App\Models\Toko;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

class TokoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth::user();

        // 1. Cek apakah user adalah 'penjual'
        if ($user->role !== 'penjual') {
            return redirect()->route('dashboard')->with('error', 'Hanya penjual yang bisa membuat toko.');
        }

        // 2. Cek apakah user sudah punya toko
        if ($user->toko) {
            // Jika sudah, redirect ke halaman kelola toko
            return redirect()->route('dashboard.manage.toko.index')->with('info', 'Anda sudah memiliki toko.');
        }

        // 3. Jika lolos, tampilkan form
        return view('toko.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'toko_name' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:255',
        ]);

        // Ambil user yang sedang login
        $user = Auth::user();

        // Cek apakah user sudah punya toko
        if ($user->toko) {
            return redirect()->route('dashboard')->with('error', 'Kamu sudah memiliki toko.');
        }

        // Buat toko baru lewat relasi
        $user->toko()->create([
            'toko_name' => $request->toko_name,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('dashboard')->with('success', 'Toko berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $user = Auth::user();
        $toko = $user->toko; // Ambil toko milik user yang login

        // Jika user bukan penjual, kembalikan ke dashboard
        if ($user->role !== 'penjual') {
            return redirect()->route('dashboard')->with('error', 'Anda bukan penjual.');
        }

        // Jika penjual tapi belum punya toko, paksa ke halaman create
        if (!$toko) {
            return redirect()->route('dashboard.manage.toko.create')->with('info', 'Anda harus membuat toko terlebih dahulu.');
        }

        // Jika penjual dan sudah punya toko, tampilkan halaman kelola toko
        // Di sini Anda bisa menambahkan logika untuk mengambil produk milik toko, dll.
        return view('toko.show', compact('toko'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Toko $toko)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Toko $toko)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Toko $toko)
    {
        //
    }
}
