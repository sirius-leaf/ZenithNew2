<?php

use App\Models\Product;
use App\Models\Variant;
use App\Models\DetailPesanan;
use App\Models\Toko;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

// Mock login as a seller
$user = User::where('role', 'penjual')->first();
if (!$user) {
    echo "No seller found.\n";
    exit;
}
Auth::login($user);

if (!$user->toko) {
    echo "User has no toko.\n";
    exit;
}

echo "Testing for Toko: " . $user->toko->toko_name . "\n";

$products = Product::where('id_toko', $user->toko->id)
    ->with(['variant'])
    ->get();

foreach ($products as $product) {
    $calculatedSold = $product->variant->sum(function ($variant) {
        return $variant->detailPesanans()->sum('kuantitas');
    });

    echo "Product: " . $product->nama_produk . " (ID: " . $product->id_produk . ")\n";
    echo "  Calculated Sold: " . $calculatedSold . "\n";

    // Check if relation works
    foreach ($product->variant as $variant) {
        $count = $variant->detailPesanans()->count();
        echo "    Variant: " . $variant->nama_varian . " - DetailPesanan Count: " . $count . "\n";
    }
}
