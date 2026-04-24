<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /**
     * GET /api/settings
     * Ambil semua konfigurasi sebagai key-value object
     */
    public function index()
    {
        $settings = Setting::all()->mapWithKeys(function ($setting) {
            return [$setting->params_key => $setting->params_value];
        });

        return response()->json([
            'success' => true,
            'data'    => $settings,
        ]);
    }

    /**
     * PUT /api/settings
     * Simpan atau update konfigurasi (batch upsert)
     * Body: { "app_appearance": { ... }, "another_key": { ... } }
     */
    public function update(Request $request)
    {
        $data = $request->validate([
            '*' => 'nullable',
        ]);

        // Jika request body adalah flat JSON object, iterasi tiap key
        foreach ($request->all() as $key => $value) {
            Setting::updateOrCreate(
                ['params_key' => $key],
                ['params_value' => $value]
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Pengaturan berhasil disimpan.',
        ]);
    }

    /**
     * GET /api/settings/{key}
     * Ambil satu konfigurasi berdasarkan key
     */
    public function show(string $key)
    {
        $setting = Setting::find($key);

        if (! $setting) {
            return response()->json([
                'success' => false,
                'message' => 'Pengaturan tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => [
                'key'   => $setting->params_key,
                'value' => $setting->params_value,
            ],
        ]);
    }
}
