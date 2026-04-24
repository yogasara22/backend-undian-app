<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ScheduledWinner;
use Illuminate\Http\Request;

class ScheduledWinnerController extends Controller
{
    /**
     * GET /api/scheduled-winners
     * Daftar antrian pemenang khusus
     */
    public function index()
    {
        $scheduledWinners = ScheduledWinner::with('prize')
            ->orderBy('priority')
            ->orderBy('created_at')
            ->get()
            ->map(function (ScheduledWinner $sw) {
                return [
                    'id'       => $sw->id,
                    'nik'      => $sw->nik,
                    'name'     => $sw->name,
                    'priority' => $sw->priority,
                    'isUsed'   => $sw->is_used,
                    'prize'    => $sw->prize ? [
                        'id'   => $sw->prize->id,
                        'name' => $sw->prize->name,
                    ] : null,
                    'createdAt' => $sw->created_at?->toIso8601String(),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $scheduledWinners,
        ]);
    }

    /**
     * POST /api/scheduled-winners
     * Tambahkan seseorang ke antrian Fixed Winner
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nik'      => 'required|string|max:16',
            'name'     => 'required|string|max:255',
            'prize_id' => 'required|exists:prizes,id',
            'priority' => 'nullable|integer|min:0',
        ]);

        $scheduledWinner = ScheduledWinner::create([
            'nik'      => $validated['nik'],
            'name'     => $validated['name'],
            'prize_id' => $validated['prize_id'],
            'priority' => $validated['priority'] ?? 0,
            'is_used'  => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pemenang terjadwal berhasil ditambahkan.',
            'data'    => [
                'id'       => $scheduledWinner->id,
                'nik'      => $scheduledWinner->nik,
                'name'     => $scheduledWinner->name,
                'priority' => $scheduledWinner->priority,
                'isUsed'   => $scheduledWinner->is_used,
            ],
        ], 201);
    }

    /**
     * DELETE /api/scheduled-winners/{id}
     * Hapus dari antrian
     */
    public function destroy(ScheduledWinner $scheduledWinner)
    {
        $scheduledWinner->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pemenang terjadwal berhasil dihapus.',
        ]);
    }
}
