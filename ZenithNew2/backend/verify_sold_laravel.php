<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

$user = User::where('role', 'penjual')->first();

if ($user && $user->toko) {
    echo "Testing for Toko: " . $user->toko->toko_name . "\n";
    $products = Product::where('id_toko', $user->toko->id)->with(['variant'])->get();
    foreach ($products as $product) {
        $calculatedSold = $product->variant->sum(function ($variant) {
            return $variant->detailPesanans()->sum('kuantitas');
        });
        echo "Product: " . $product->nama_produk . " - Sold: " . $calculatedSold . "\n";
    }
} else {
    echo "No seller with toko found.\n";
}
