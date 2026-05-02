<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Services\LotteryService;

class LotteryController extends Controller
{
    public function draw(\Illuminate\Http\Request $request, LotteryService $service)
    {
        try {
            $prizeId = $request->input('prize_id');
            $result = $service->draw($prizeId ? (int)$prizeId : null);

            return response()->json([
                'success' => true,
                'data'    => [
                    'winner' => [
                        'id'         => $result['participant']->id,
                        'name'       => $result['participant']->name,
                        'ktpNumber'  => $result['participant']->nik,
                        'department' => $result['participant']->department,
                        'shopName'   => $result['participant']->shop_name,
                    ],
                    'prize' => [
                        'id'       => $result['prize']->id,
                        'name'     => $result['prize']->name,
                        'imageUrl' => $result['prize']->image_url
                            ? asset('storage/' . ltrim($result['prize']->image_url, '/storage/'))
                            : null,
                        'value'    => $result['prize']->value,
                    ],
                ],
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}