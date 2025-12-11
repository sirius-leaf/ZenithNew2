<?php

namespace CodeTests\SingleModul;

use Illuminate\Http\Request;
use App\Models\Variant;

class AddToCart
{
    /**
     * Menambahkan item ke keranjang.
     * Replika dari CartController::add
     */
    public function add(Request $request, $id_varian)
    {
        $variant = Variant::findOrFail($id_varian);
        $kuantitas = $request->input('kuantitas', 1); // Ambil kuantitas, default 1

        // Validasi stok
        if ($variant->stok < $kuantitas) {
            return redirect()->back()->with('error', 'Stok tidak mencukupi.');
        }

        $cart = session()->get('cart', []);

        // Cek jika item sudah ada di keranjang, tambahkan kuantitasnya
        if (isset($cart[$id_varian])) {
            $cart[$id_varian]['kuantitas'] += $kuantitas;
        } else {
            // Jika item baru
            $cart[$id_varian] = [
                'kuantitas' => $kuantitas
            ];
        }

        session()->put('cart', $cart);
        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke keranjang!');
    }
}
