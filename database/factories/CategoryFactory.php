<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $categories = [
            ['name' => 'Hadiah Utama',    'color' => 'amber',  'description' => 'Hadiah utama paling bergengsi'],
            ['name' => 'Hadiah Kedua',    'color' => 'violet', 'description' => 'Hadiah kategori kedua'],
            ['name' => 'Hadiah Hiburan',  'color' => 'blue',   'description' => 'Hadiah hiburan untuk semua peserta'],
            ['name' => 'Hadiah Spesial',  'color' => 'emerald','description' => 'Hadiah spesial pilihan panitia'],
        ];

        $picked = $this->faker->unique()->randomElement($categories);

        return [
            'name'        => $picked['name'],
            'color'       => $picked['color'],
            'description' => $picked['description'],
        ];
    }
}
