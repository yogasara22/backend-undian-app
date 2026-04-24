<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    /**
     * GET /api/participants
     * List semua peserta (pagination + search)
     */
    public function index(Request $request)
    {
        $query = Participant::query();

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%")
                  ->orWhere('department', 'like', "%{$search}%")
                  ->orWhere('shop_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $perPage = (int) $request->input('per_page', 15);
        $participants = $query->orderBy('name')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data'    => $participants->items(),
            'meta'    => [
                'current_page' => $participants->currentPage(),
                'last_page'    => $participants->lastPage(),
                'per_page'     => $participants->perPage(),
                'total'        => $participants->total(),
            ],
        ]);
    }

    /**
     * POST /api/participants
     * Tambah peserta baru
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'nik'          => 'required|string|size:16|unique:participants,nik',
            'email'        => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'department'   => 'nullable|string|max:255',
            'shop_name'    => 'nullable|string|max:255',
            'address'      => 'nullable|string',
        ]);

        $participant = Participant::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Peserta berhasil ditambahkan.',
            'data'    => $participant,
        ], 201);
    }

    /**
     * GET /api/participants/{id}
     */
    public function show(Participant $participant)
    {
        return response()->json([
            'success' => true,
            'data'    => $participant,
        ]);
    }

    /**
     * PUT /api/participants/{id}
     * Update data peserta
     */
    public function update(Request $request, Participant $participant)
    {
        $validated = $request->validate([
            'name'         => 'sometimes|required|string|max:255',
            'nik'          => "sometimes|required|string|size:16|unique:participants,nik,{$participant->id}",
            'email'        => 'nullable|email|max:255',
            'phone_number' => 'nullable|string|max:20',
            'department'   => 'nullable|string|max:255',
            'shop_name'    => 'nullable|string|max:255',
            'address'      => 'nullable|string',
        ]);

        $participant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data peserta berhasil diperbarui.',
            'data'    => $participant->fresh(),
        ]);
    }

    /**
     * DELETE /api/participants/{id}
     * Soft delete peserta
     */
    public function destroy(Participant $participant)
    {
        $participant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Peserta berhasil dihapus.',
        ]);
    }
}
