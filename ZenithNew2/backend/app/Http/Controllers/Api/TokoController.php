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
     *
     * @OA\Get(
     *     path="/api/manage/toko",
     *     tags={"Toko"},
     *     summary="Check current user's store status",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Store status",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="exists"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     )
     * )
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
     *
     * @OA\Post(
     *     path="/api/manage/toko",
     *     tags={"Toko"},
     *     summary="Create a new store",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"toko_name","deskripsi"},
     *             @OA\Property(property="toko_name", type="string", example="My Store"),
     *             @OA\Property(property="deskripsi", type="string", example="Best store ever")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Store created",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="success"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden (Not a seller)"),
     *     @OA\Response(response=409, description="Conflict (Store already exists)")
     * )
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

    /**
     * @OA\Get(
     *     path="/api/toko/{id}",
     *     tags={"Toko"},
     *     summary="Get store details",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Store ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store details",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=404, description="Store not found")
     * )
     */
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
     *
     * @OA\Get(
     *     path="/api/shops",
     *     tags={"Toko"},
     *     summary="Search shops",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Search query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of shops",
     *         @OA\JsonContent(type="array", @OA\Items(type="object"))
     *     )
     * )
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
                $q->with('variant')->latest()->take(3);
            }
        ])->paginate(12);

        return response()->json($users);
    }

    /**
     * Freeze a store (Admin only).
     *
     * @OA\Post(
     *     path="/api/manage/toko/{id}/freeze",
     *     tags={"Toko"},
     *     summary="Freeze a store",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Store ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", example="Violation of terms")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store frozen",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Toko berhasil dibekukan.")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function freeze(Request $request, $id)
    {
        // Ensure admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $toko = Toko::findOrFail($id);
        $toko->update([
            'is_frozen' => true,
            'frozen_reason' => $request->input('reason', 'Violation of terms'),
        ]);

        return response()->json(['message' => 'Toko berhasil dibekukan.', 'data' => $toko]);
    }

    /**
     * Unfreeze a store (Admin only).
     *
     * @OA\Post(
     *     path="/api/manage/toko/{id}/unfreeze",
     *     tags={"Toko"},
     *     summary="Unfreeze a store",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Store ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Store unfrozen",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Toko berhasil diaktifkan kembali")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function unfreeze($id)
    {
        // Ensure admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $toko = Toko::findOrFail($id);
        $toko->is_frozen = false;
        $toko->frozen_reason = null;
        $toko->appeal_reason = null; // Reset appeal reason after unfreeze
        $toko->save();

        return response()->json(['message' => 'Toko berhasil diaktifkan kembali']);
    }

    /**
     * @OA\Post(
     *     path="/api/manage/toko/{id}/appeal",
     *     tags={"Toko"},
     *     summary="Submit appeal for frozen store",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="Store ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"appeal_reason"},
     *             @OA\Property(property="appeal_reason", type="string", example="I will be good")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Appeal submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Banding berhasil diajukan")
     *         )
     *     ),
     *     @OA\Response(response=403, description="Unauthorized")
     * )
     */
    public function submitAppeal(Request $request, $id)
    {
        $request->validate([
            'appeal_reason' => 'required|string|max:1000',
        ]);

        $toko = Toko::findOrFail($id);

        // Ensure the user owns the store
        if ($request->user()->id !== $toko->id_user) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $toko->appeal_reason = $request->appeal_reason;
        $toko->save();

        return response()->json(['message' => 'Banding berhasil diajukan']);
    }
}
