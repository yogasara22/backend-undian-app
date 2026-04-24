<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ParticipantFactory extends Factory
{
    public function definition(): array
    {
        // Generate NIK 16 digit
        $nik = $this->faker->numerify('################');

        return [
            'name'         => $this->faker->name(),
            'nik'          => $nik,
            'email'        => $this->faker->optional(0.7)->safeEmail(),
            'phone_number' => $this->faker->optional(0.8)->phoneNumber(),
            'department'   => $this->faker->randomElement([
                'Jakarta Pusat',
                'Jakarta Selatan',
                'Jakarta Utara',
                'Surabaya',
                'Bandung',
                'Medan',
                'Makassar',
                'Semarang',
                'Yogyakarta',
                'Palembang',
            ]),
            'shop_name'    => $this->faker->optional(0.9)->company(),
            'address'      => $this->faker->optional(0.8)->address(),
        ];
    }
}
