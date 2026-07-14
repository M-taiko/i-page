<?php

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
use App\Models\Channel;
use Illuminate\Database\Eloquent\Factories\Factory;

class PostFactory extends Factory
{
    protected $model = Post::class;

    public function definition(): array
    {
        return [
            'author_id' => User::factory(),
            'channel_id' => Channel::factory(),
            'audience' => fake()->randomElement(['all', 'in_house', 'team', 'channel']),
            'body' => fake()->paragraph(),
            'image_path' => null,
            'status' => fake()->randomElement(['draft', 'pending_approval', 'published']),
            'published_at' => now(),
            'pinned_until' => null,
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'draft',
            'published_at' => null,
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending_approval',
            'published_at' => null,
        ]);
    }

    public function pinned(): static
    {
        return $this->state(fn (array $attributes) => [
            'pinned_until' => now()->addDays(30),
        ]);
    }
}
