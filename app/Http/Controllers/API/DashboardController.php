<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Participant;
use App\Models\Prize;
use App\Models\Winner;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * GET /api/dashboard/stats
     * Statistik ringkasan untuk dashboard admin
     */
    public function stats()
    {
        $totalParticipants = Participant::count();
        $totalWinners      = Winner::distinct('participant_id')->count('participant_id');
        $totalPrizes       = Prize::sum('qty');
        $prizesClaimed     = Winner::count();
        $remainingPrizes   = max(0, $totalPrizes - $prizesClaimed);

        // Peserta yang belum menang
        $remainingParticipants = Participant::whereDoesntHave('winners')->count();

        return response()->json([
            'success' => true,
            'data'    => [
                'totalParticipants'     => $totalParticipants,
                'totalWinners'          => $totalWinners,
                'remainingParticipants' => $remainingParticipants,
                'totalPrizes'           => (int) $totalPrizes,
                'prizesClaimed'         => $prizesClaimed,
                'remainingPrizes'       => $remainingPrizes,
            ],
        ]);
    }

    /**
     * GET /api/dashboard/recent-winners
     * Daftar pemenang terakhir dengan detail (eager loaded), mendukung pagination & search
     */
    public function recentWinners(\Illuminate\Http\Request $request)
    {
        $query = Winner::with(['participant', 'prize.category'])
            ->orderBy('drawn_at', 'desc');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('participant', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                })->orWhereHas('prize', function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%");
                });
            });
        }

        if ($category = $request->input('category')) {
            $query->whereHas('prize.category', function ($q) use ($category) {
                $q->where('name', $category);
            });
        }

        $perPage = min((int) $request->input('per_page', 10), 100);
        $winners = $query->paginate($perPage);

        $data = collect($winners->items())->map(function (Winner $w) {
            return [
                'id'           => $w->id,
                'drawnAt'      => $w->drawn_at?->toIso8601String(),
                'participant'  => [
                    'id'         => $w->participant?->id,
                    'name'       => $w->participant?->name,
                    'ktpNumber'  => $w->participant?->nik,
                    'department' => $w->participant?->department,
                    'shopName'   => $w->participant?->shop_name,
                ],
                'prize' => [
                    'id'       => $w->prize?->id,
                    'name'     => $w->prize?->name,
                    'value'    => $w->prize?->value,
                    'imageUrl' => $w->prize?->image_url
                        ? asset('storage/' . $w->prize->image_url)
                        : null,
                    'category' => $w->prize?->category ? [
                        'id'    => $w->prize->category->id,
                        'name'  => $w->prize->category->name,
                        'color' => $w->prize->category->color,
                    ] : null,
                ],
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $data,
            'meta'    => [
                'current_page' => $winners->currentPage(),
                'last_page'    => $winners->lastPage(),
                'per_page'     => $winners->perPage(),
                'total'        => $winners->total(),
            ],
        ]);
    }
}
