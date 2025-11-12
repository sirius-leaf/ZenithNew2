<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PcBuild;
use App\Models\Product;
use App\Models\BuildDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PcBuildController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pcBuild = Auth::user()->pcBuild()->get();

        return view('crud.pcBuild.index', compact('pcBuild'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::all();
        $user = Auth::user();

        return view('crud.pcBuild.create', compact('products', 'user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        $validated = $request->validate([
            'nama_build' => 'required',
            'komponen' => 'required|array',
            'komponen.motherboard' => 'required',
            'komponen.cpu' => 'required',
            'komponen.ram' => 'required',
            'komponen.psu' => 'required',
            'komponen.storage' => 'required',
        ]);

        $build = PcBuild::create([
            'id_user' => $request->id_user,
            'nama_build' => $validated['nama_build'],
        ]);

        foreach ($request->komponen as $k => $v) {
            // dd($v);
            $build->buildDetail()->create([
                'id_produk' => $v,
                'bagian_komponen' => $k,
            ]);
        }

        return redirect()->route('dashboard.manage.pcBuild.index')
            ->with('success', 'PC build berhasil disimpan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(int $id)
    {
        $build = PcBuild::with('buildDetail')->find($id);
        $products = Product::all();
        $user = Auth::user();

        return view('crud.pcBuild.edit', compact('products', 'user', 'build'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id)
    {
        $build = PcBuild::findOrFail($id);

        $request->validate([
            'nama_build' => 'required',
            'komponen' => 'required|array',
            'komponen.motherboard.produk' => 'required',
            'komponen.cpu.produk' => 'required',
            'komponen.ram.produk' => 'required',
            'komponen.psu.produk' => 'required',
            'komponen.storage.produk' => 'required',
        ]);

        foreach ($request->komponen as $k => $v) {
            $detail = $build->buildDetail()->find($v['id']);
            if ($detail)
                $detail->update([
                    'id_produk' => $v['produk'],
                    'bagian_komponen' => $k,
                ]);
        }

        $build->update($request->only('id_user', 'nama_build'));

        return redirect()->route('dashboard.manage.pcBuild.index')
            ->with('success', 'PC build berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id)
    {
        $build = PcBuild::with('buildDetail')->find($id);

        foreach ($build->buildDetail() as $detail) {
            $detail->delete();
        }

        $build->delete();

        return redirect()->back()->with('success', 'PC build dihapus!');
    }
}
