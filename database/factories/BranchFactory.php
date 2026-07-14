<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

class BranchFactory extends Factory
{
    protected $model = Location::class;

    public function definition(): array
    {
        $cities = ['Jeddah', 'Riyadh', 'Medina', 'Dammam', 'Khobar', 'Makkah'];

        return [
            'name' => 'Hilton ' . fake()->randomElement($cities),
            'city' => fake()->randomElement($cities),
            'country' => 'Saudi Arabia',
            'timezone' => 'Asia/Riyadh',
        ];
    }
}
