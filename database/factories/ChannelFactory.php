<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChannelFactory extends Factory
{
    protected $model = Channel::class;

    public function definition(): array
    {
        $name = fake()->word() . ' ' . fake()->word();

        return [
            'name' => ucfirst($name),
            'slug' => Str::slug($name) . '-' . Str::random(6),
            'type' => fake()->randomElement(['public', 'private']),
            'audience_profile' => fake()->randomElement(['business', 'public', 'private']),
            'audience_count' => fake()->numberBetween(10, 500),
            'logo_path' => null,
            'admin_user_id' => User::factory(),
            'status' => 'active',
            'qr_path' => null,
            'share_url' => null,
        ];
    }

    public function public(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'public',
        ]);
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'private',
        ]);
    }

    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'archived',
        ]);
    }
}
