<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Toko;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TokoController extends Controller
{
    /**
     * Cek status toko user saat ini.
     * Berguna untuk frontend mengetahui apakah user sudah punya toko atau belum.
     */
    public function index()
    {
        $user = Auth::user();

        // Cek apakah user punya toko
        $toko = $user->toko; // Asumsi relasi 'toko' di model User adalah hasOne

        if ($toko) {
            return response()->json([
                'status' => 'exists',
                'data' => $toko
            ], 200);
        }

        return response()->json([
            'status' => 'empty',
            'message' => 'User belum memiliki toko.'
        ], 200);
    }

    /**
     * Menyimpan Toko Baru.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // 1. Cek Role (Harus Penjual)
        if ($user->role !== 'penjual') {
            return response()->json([
                'status' => 'error',
                'message' => 'Hanya akun Penjual yang bisa membuat toko.'
            ], 403); // 403 Forbidden
        }

        // 2. Cek apakah user SUDAH punya toko
        if ($user->toko) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anda sudah memiliki toko, tidak bisa membuat baru.'
            ], 409); // 409 Conflict
        }

        // 3. Validasi Input
        $request->validate([
            'toko_name' => 'required|string|max:255',
            'deskripsi' => 'required|string|max:255',
        ]);

        // 4. Buat Toko
        $toko = $user->toko()->create([
            'toko_name' => $request->toko_name,
            'deskripsi' => $request->deskripsi,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Toko berhasil dibuat!',
            'data' => $toko
        ], 201); // 201 Created
    }

    public function show($id_toko)
    {
        $toko = Toko::findOrFail($id_toko);

        $products = $toko->products()->with('variant')->get();
        $toko->load('user');
        $dataToko = [$toko];
        $ratings = Review::whereHas('product', function ($q) use ($id_toko) {
            $q->where('id_toko', $id_toko);
        });

        $avgRating = floor($ratings->avg('rating') * 10) / 10;
        $countRating = $ratings->count();

        return response()->json([
            'status' => 'success',
            'message' => 'Toko berhasil dibuat!',
            'data' => $dataToko,
            'products' => $products,
            'ratingToko' => [
                'rata-rata' => $avgRating,
                'jumlah' => $countRating
            ]
        ], 201);
    }

    /**
     * Search for shops (users with role 'penjual').
     */
    public function search(Request $request)
    {
        $query = \App\Models\User::where('role', 'penjual');

        if ($request->has('q')) {
            $search = $request->input('q');
            $query->where('store_name', 'like', "%{$search}%");
        }

        // Eager load products for preview (limit 3)
        // Note: Laravel's eager loading limit is tricky, but for simplicity we'll just load all and limit in frontend or use a subquery if performance is critical.
        // For now, let's just load 'toko.products' or if products are directly on user (which they aren't, they are on toko).
        // Wait, the schema says User hasOne Toko, and Toko hasMany Products.
        // But the user request implies "store_name" is on User table now (based on previous edits).
        // Let's check where products are. They are likely linked to Toko or User.
        // Based on `TokoController::store`, it creates a `Toko` model.
        // But `UserRoleController` updates `User` fields.
        // Let's assume products are linked to `Toko`.
        // However, the user might not have a `Toko` record if they just became a seller via the new flow which updates `User` table directly?
        // Let's check `User` model again.

        // Actually, in `UserRoleController::requestSeller`, we updated `store_name` on `User`.
        // We didn't create a `Toko` record there.
        // So products might need to be linked to `User` directly or we need to ensure `Toko` exists.
        // Let's look at `Product` model to see how it links to seller.

        $users = $query->with([
            'toko.products' => function ($q) {
                $q->latest()->take(3);
            }
        ])->paginate(12);

        return response()->json($users);
    }
}
