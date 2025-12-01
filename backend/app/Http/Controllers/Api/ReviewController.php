<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Review;
use App\Models\DetailPesanan;

class ReviewController extends Controller
{
    /**
     * Store a newly created resource in storage.
     *
     * @OA\Post(
     *     path="/api/reviews",
     *     tags={"Reviews"},
     *     summary="Create a review",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"id_produk","id_pesanan","rating","komentar"},
     *                 @OA\Property(property="id_produk", type="integer"),
     *                 @OA\Property(property="id_variant", type="integer"),
     *                 @OA\Property(property="id_pesanan", type="integer"),
     *                 @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
     *                 @OA\Property(property="komentar", type="string"),
     *                 @OA\Property(property="images[]", type="array", @OA\Items(type="string", format="binary"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Review created",
     *         @OA\JsonContent(type="object")
     *     ),
     *     @OA\Response(response=400, description="Already reviewed")
     * )
     */
    public function store(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'id_produk' => 'required|exists:products,id_produk',
            'id_variant' => 'nullable|exists:variants,id_varian',
            'id_pesanan' => 'required|exists:pesanans,id',
            'rating' => 'required|integer|min:1|max:5',
            'komentar' => 'required|string',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();

        // Check if already reviewed
        $existingReview = Review::where('id_pesanan', $request->id_pesanan)
            ->where('id_produk', $request->id_produk)
            ->where('id_variant', $request->id_variant)
            ->first();

        if ($existingReview) {
            return response()->json(['message' => 'Anda sudah mengulas produk ini untuk pesanan ini.'], 400);
        }

        $review = Review::create([
            'id_user' => $user->id,
            'id_produk' => $request->id_produk,
            'id_variant' => $request->id_variant,
            'id_pesanan' => $request->id_pesanan,
            'rating' => $request->rating,
            'komentar' => $request->komentar,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('review_images', 'public');
                \App\Models\ReviewImage::create([
                    'id_review' => $review->id_ulasan,
                    'image_path' => $path,
                ]);
            }
        }

        return response()->json(['message' => 'Ulasan berhasil dikirim.', 'data' => $review], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/reviews/{productId}",
     *     tags={"Reviews"},
     *     summary="Get reviews for a product",
     *     @OA\Parameter(
     *         name="productId",
     *         in="path",
     *         description="Product ID",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of reviews",
     *         @OA\JsonContent(type="object")
     *     )
     * )
     */
    public function index($productId)
    {
        $reviews = Review::with(['user', 'images', 'variant'])
            ->where('id_produk', $productId)
            ->latest()
            ->get();

        return response()->json(['data' => $reviews]);
    }

    public function canReview($productId)
    {
        // Deprecated or can be updated if needed, but we rely on order detail page now
        return response()->json(['message' => 'Use order detail to review']);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $review = Review::findOrFail($id);

        $validated = $request->validate([
            'komentar' => 'required',
            'rating' => 'required',
        ]);

        $review->update($request->only('komentar', 'rating'));

        return response()->json([
            'status' => 'success',
            'message' => 'Ulasan berhasil diperbarui!',
            'data' => $review
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $review = Review::findOrFail($id);
        $review->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Ulasan dihapus!'
        ]);
    }
}
