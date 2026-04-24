<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrizeFactory extends Factory
{
    public function definition(): array
    {
        $prizes = [
            ['name' => 'Sepeda Motor',       'value' => 'Kendaraan',         'qty' => 1],
            ['name' => 'Mobil SUV',          'value' => 'Kendaraan',         'qty' => 1],
            ['name' => 'Kulkas 2 Pintu',     'value' => 'Elektronik',        'qty' => 2],
            ['name' => 'Smartphone Flagship','value' => 'Elektronik',        'qty' => 3],
            ['name' => 'Smart TV 55"',       'value' => 'Elektronik',        'qty' => 2],
            ['name' => 'Laptop Gaming',      'value' => 'Elektronik',        'qty' => 1],
            ['name' => 'Tabungan 10 Juta',   'value' => 'Uang Tunai',        'qty' => 5],
            ['name' => 'Tabungan 5 Juta',    'value' => 'Uang Tunai',        'qty' => 5],
            ['name' => 'Voucher Belanja 1 Juta', 'value' => 'Voucher',       'qty' => 10],
            ['name' => 'Paket Liburan Bali', 'value' => 'Perjalanan Wisata', 'qty' => 2],
        ];

        $picked = $this->faker->randomElement($prizes);

        return [
            'category_id' => Category::inRandomOrder()->first()?->id,
            'name'        => $picked['name'],
            'description' => $this->faker->optional(0.7)->sentence(),
            'value'       => $picked['value'],
            'image_url'   => null,
            'qty'         => $picked['qty'],
        ];
    }
}
