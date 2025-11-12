<?php

namespace App\Http\Controllers;

use App\Models\Variant;
use Illuminate\Http\Request;

class CartController extends Controller
{
    /**
     * Menampilkan halaman keranjang belanja.
     */
    public function index()
    {
        $cart = session()->get('cart', []);
        $cartItems = [];
        $totalPrice = 0;

        if (!empty($cart)) {
            $variantIds = array_keys($cart);
            $variants = Variant::with('product.toko')->whereIn('id_varian', $variantIds)->get();

            foreach ($variants as $variant) {
                $kuantitas = $cart[$variant->id_varian]['kuantitas'];
                $subtotal = $variant->harga * $kuantitas;

                $cartItems[] = [
                    'variant' => $variant,
                    'kuantitas' => $kuantitas,
                    'subtotal' => $subtotal
                ];
                $totalPrice += $subtotal;
            }
        }

        return view('cart.index', compact('cartItems', 'totalPrice'));
    }

    /**
     * Menambahkan item ke keranjang.
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

    /**
     * Update kuantitas item di keranjang.
     */
    public function update(Request $request, $id_varian)
    {
        $cart = session()->get('cart');
        $kuantitas = $request->input('kuantitas');

        if (isset($cart[$id_varian]) && $kuantitas > 0) {
            // Cek stok sebelum update
            $variant = Variant::find($id_varian);
            if ($variant->stok < $kuantitas) {
                return redirect()->route('cart.index')->with('error', 'Stok untuk ' . $variant->nama_varian . ' tidak mencukupi.');
            }

            $cart[$id_varian]['kuantitas'] = $kuantitas;
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index');
    }

    /**
     * Menghapus item dari keranjang.
     */
    public function remove($id_varian)
    {
        $cart = session()->get('cart');
        if (isset($cart[$id_varian])) {
            unset($cart[$id_varian]);
            session()->put('cart', $cart);
        }

        return redirect()->route('cart.index')->with('success', 'Produk berhasil dihapus dari keranjang.');
    }
}
