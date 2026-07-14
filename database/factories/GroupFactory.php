<?php

namespace Database\Factories;

use App\Models\Group;
use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    protected $model = Group::class;

    public function definition(): array
    {
        $departments = ['Front Desk', 'Housekeeping', 'Kitchen', 'Management', 'Security', 'IT Support', 'Finance'];

        return [
            'name' => fake()->randomElement($departments) . ' Team',
            'description' => fake()->sentence(),
            'branch_id' => Branch::factory(),
        ];
    }
}
