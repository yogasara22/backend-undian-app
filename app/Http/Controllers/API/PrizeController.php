<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Prize;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PrizeController extends Controller
{
    /**
     * GET /api/prizes
     * List semua hadiah beserta kategori
     */
    public function index(Request $request)
    {
        $query = Prize::with('category')->withCount('winners');

        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        if ($request->boolean('available')) {
            $query->whereRaw('qty > (SELECT COUNT(*) FROM winners WHERE winners.prize_id = prizes.id)');
        }

        $prizes = $query->orderBy('name')->get();

        $data = $prizes->map(fn($prize) => $this->formatPrize($prize));

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * POST /api/prizes
     * Tambah hadiah baru (support upload image)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'value'       => 'nullable|string|max:255',
            'qty'         => 'required|integer|min:1',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path     = $request->file('image')->store('prizes', 'public');
            $imageUrl = 'prizes/' . basename($path);
        }

        $prize = Prize::create([
            'category_id' => $validated['category_id'] ?? null,
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'value'       => $validated['value'] ?? null,
            'qty'         => $validated['qty'],
            'image_url'   => $imageUrl,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Hadiah berhasil ditambahkan.',
            'data'    => $this->formatPrize($prize->load('category')),
        ], 201);
    }

    /**
     * GET /api/prizes/{id}
     */
    public function show(Prize $prize)
    {
        return response()->json([
            'success' => true,
            'data'    => $this->formatPrize($prize->load('category')),
        ]);
    }

    /**
     * POST /api/prizes/{id} (dengan _method=PUT untuk form-data dengan file)
     * Update hadiah
     */
    public function update(Request $request, Prize $prize)
    {
        $validated = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'value'       => 'nullable|string|max:255',
            'qty'         => 'sometimes|required|integer|min:0',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // Hapus gambar lama jika ada
            if ($prize->image_url) {
                Storage::disk('public')->delete($prize->image_url);
            }
            $path = $request->file('image')->store('prizes', 'public');
            $validated['image_url'] = 'prizes/' . basename($path);
        }

        unset($validated['image']);
        $prize->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Hadiah berhasil diperbarui.',
            'data'    => $this->formatPrize($prize->fresh('category')),
        ]);
    }

    /**
     * DELETE /api/prizes/{id}
     */
    public function destroy(Prize $prize)
    {
        if ($prize->image_url) {
            Storage::disk('public')->delete($prize->image_url);
        }
        $prize->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hadiah berhasil dihapus.',
        ]);
    }

    /**
     * Format prize data untuk response (camelCase, imageUrl absolut)
     */
    private function formatPrize(Prize $prize): array
    {
        return [
            'id'          => $prize->id,
            'name'        => $prize->name,
            'description' => $prize->description,
            'value'       => $prize->value,
            'qty'         => $prize->qty,
            'remainingQty'=> $prize->remaining_qty,
            'imageUrl'    => $prize->image_url ? asset('storage/' . $prize->image_url) : null,
            'categoryId'  => $prize->category_id,
            'category'    => $prize->relationLoaded('category') && $prize->category ? [
                'id'    => $prize->category->id,
                'name'  => $prize->category->name,
                'color' => $prize->category->color,
            ] : null,
        ];
    }
}
