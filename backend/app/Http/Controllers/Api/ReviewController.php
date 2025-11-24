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
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'komentar' => 'required',
            'rating' => 'required',
            'id_produk' => 'required',
        ]);

        $review = Review::create([
            'komentar' => $validated['komentar'],
            'rating' => $validated['rating'],
            'id_produk' => $validated['id_produk'],
            'id_user' => Auth::id(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Ulasan berhasil disimpan!',
            'data' => $review
        ]);
    }

    public function getReview($productId)
    {
        $review = Review::where('id_produk', $productId)
            ->where('id_user', Auth::id())
            ->first();

        if ($review) {
            return response()->json($review);
        }

        return response()->json(null);
    }

    public function canReview($productId)
    {
        $userId = Auth::id();

        // Cek apakah user pernah membeli produk ini
        $hasPurchased = DetailPesanan::whereHas('variant.product', function ($query) use ($productId) {
            $query->where('id_produk', $productId); // atau 'id' jika primary key-nya id
        })
            ->whereHas('pesanan', function ($query) use ($userId) {
                $query->where('user_id', $userId)
                    ->where('status', 'paid'); // opsional: hanya pesanan selesai
            })
            ->exists();

        return response()->json(['can_review' => $hasPurchased]);
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
