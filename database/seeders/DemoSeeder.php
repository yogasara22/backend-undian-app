<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Participant;
use App\Models\Prize;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    /**
     * Run demo seeds untuk environment lokal / testing.
     */
    public function run(): void
    {
        // 1. Kategori hadiah
        $categories = [
            ['name' => 'Hadiah Utama',   'color' => 'amber',  'description' => 'Hadiah utama paling bergengsi'],
            ['name' => 'Hadiah Kedua',   'color' => 'violet', 'description' => 'Hadiah kategori kedua'],
            ['name' => 'Hadiah Hiburan', 'color' => 'blue',   'description' => 'Hadiah hiburan untuk semua peserta'],
        ];

        foreach ($categories as $cat) {
            Category::firstOrCreate(['name' => $cat['name']], $cat);
        }

        // 2. Hadiah
        $utama   = Category::where('name', 'Hadiah Utama')->first();
        $kedua   = Category::where('name', 'Hadiah Kedua')->first();
        $hiburan = Category::where('name', 'Hadiah Hiburan')->first();

        $prizes = [
            ['category_id' => $utama->id,   'name' => 'Mobil SUV',            'value' => 'Kendaraan',  'qty' => 1],
            ['category_id' => $utama->id,   'name' => 'Sepeda Motor',          'value' => 'Kendaraan',  'qty' => 2],
            ['category_id' => $kedua->id,   'name' => 'Smart TV 55"',          'value' => 'Elektronik', 'qty' => 3],
            ['category_id' => $kedua->id,   'name' => 'Laptop Gaming',         'value' => 'Elektronik', 'qty' => 2],
            ['category_id' => $kedua->id,   'name' => 'Smartphone Flagship',   'value' => 'Elektronik', 'qty' => 5],
            ['category_id' => $hiburan->id, 'name' => 'Tabungan 5 Juta',       'value' => 'Uang Tunai', 'qty' => 10],
            ['category_id' => $hiburan->id, 'name' => 'Voucher Belanja 1 Juta','value' => 'Voucher',    'qty' => 20],
        ];

        foreach ($prizes as $prize) {
            Prize::firstOrCreate(
                ['name' => $prize['name']],
                array_merge($prize, ['description' => null, 'image_url' => null])
            );
        }

        // 3. Peserta demo (100 orang)
        if (Participant::count() === 0) {
            Participant::factory(100)->create();
        }

        // 4. Setting default aplikasi
        Setting::updateOrCreate(
            ['params_key' => 'app_appearance'],
            [
                'params_value' => [
                    'title'    => 'UNDIAN BERHADIAH 2026',
                    'subtitle' => 'Selamat kepada pemenang!',
                    'gradients' => [
                        ['from' => '#1a1a2e', 'to' => '#16213e'],
                        ['from' => '#0f3460', 'to' => '#533483'],
                    ],
                    'duration'   => 5000,
                    'typography' => [
                        'font'      => 'Inter',
                        'titleSize' => '4xl',
                        'bodySize'  => 'lg',
                    ],
                ],
            ]
        );
    }
}
