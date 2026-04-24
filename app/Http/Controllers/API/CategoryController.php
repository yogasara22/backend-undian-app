<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * GET /api/categories
     * List semua kategori beserta jumlah hadiah (prizeCount)
     */
    public function index()
    {
        $categories = Category::withCount('prizes')->orderBy('name')->get();

        $data = $categories->map(function ($cat) {
            return [
                'id'          => $cat->id,
                'name'        => $cat->name,
                'description' => $cat->description,
                'color'       => $cat->color,
                'prizeCount'  => $cat->prizes_count,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
        ]);
    }

    /**
     * POST /api/categories
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:50',
        ]);

        $category = Category::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil ditambahkan.',
            'data'    => $category,
        ], 201);
    }

    /**
     * PUT /api/categories/{id}
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'color'       => 'nullable|string|max:50',
        ]);

        $category->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil diperbarui.',
            'data'    => $category->fresh(),
        ]);
    }

    /**
     * DELETE /api/categories/{id}
     */
    public function destroy(Category $category)
    {
        $category->delete();

        return response()->json([
            'success' => true,
            'message' => 'Kategori berhasil dihapus.',
        ]);
    }
}
