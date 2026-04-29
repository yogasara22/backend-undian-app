<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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

    /**
     * POST /api/settings/background-image
     * Upload gambar background dan simpan sebagai file
     */
    public function uploadBackgroundImage(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120', // max 5MB
        ]);

        // Hapus gambar lama jika ada
        $setting = Setting::find('app_appearance');
        if ($setting && is_array($setting->params_value)) {
            $oldImage = $setting->params_value['backgroundImage'] ?? null;
            if ($oldImage && !str_starts_with($oldImage, 'data:')) {
                // Old image is a file path, delete it
                $relativePath = str_replace('/storage/', '', parse_url($oldImage, PHP_URL_PATH) ?? '');
                if ($relativePath) {
                    Storage::disk('public')->delete($relativePath);
                }
            }
        }

        $path = $request->file('image')->store('backgrounds', 'public');
        $imageUrl = asset('storage/' . $path);

        // Update app_appearance setting dengan URL gambar baru
        $currentValue = $setting ? $setting->params_value : [];
        if (!is_array($currentValue)) {
            $currentValue = [];
        }
        $currentValue['backgroundImage'] = $imageUrl;
        $currentValue['useImageBackground'] = true;

        Setting::updateOrCreate(
            ['params_key' => 'app_appearance'],
            ['params_value' => $currentValue]
        );

        return response()->json([
            'success'  => true,
            'message'  => 'Background image berhasil diupload.',
            'data'     => [
                'backgroundImage' => $imageUrl,
            ],
        ]);
    }

    /**
     * DELETE /api/settings/background-image
     * Hapus gambar background
     */
    public function deleteBackgroundImage()
    {
        $setting = Setting::find('app_appearance');
        if ($setting && is_array($setting->params_value)) {
            $oldImage = $setting->params_value['backgroundImage'] ?? null;
            if ($oldImage && !str_starts_with($oldImage, 'data:')) {
                $relativePath = str_replace('/storage/', '', parse_url($oldImage, PHP_URL_PATH) ?? '');
                if ($relativePath) {
                    Storage::disk('public')->delete($relativePath);
                }
            }

            $currentValue = $setting->params_value;
            $currentValue['backgroundImage'] = '';
            $currentValue['useImageBackground'] = false;
            $setting->update(['params_value' => $currentValue]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Background image berhasil dihapus.',
        ]);
    }
}
