<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'ipage_id' => 'IP' . str_pad(fake()->unique()->numberBetween(100000, 999999), 6, '0', STR_PAD_LEFT),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'mobile' => fake()->phoneNumber(),
            'dob' => fake()->dateTimeBetween('-65 years', '-18 years'),
            'gender' => fake()->randomElement(['male', 'female', 'other']),
            'nationality' => fake()->country(),
            'job_title' => fake()->jobTitle(),
            'department' => fake()->randomElement(['Front Desk', 'Housekeeping', 'Kitchen', 'Management', 'Security']),
            'location_id' => null,
            'avatar_path' => null,
            'is_vip' => fake()->boolean(10),
            'check_in_at' => now(),
            'check_out_at' => fake()->dateTime(),
            'last_seen_at' => now(),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_at' => now()->subDays(fake()->numberBetween(1, 5)),
            'check_out_at' => now()->addDays(fake()->numberBetween(1, 7)),
            'is_vip' => false,
        ]);
    }

    public function vip(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_vip' => true,
        ]);
    }

    public function staff(): static
    {
        return $this->state(fn (array $attributes) => [
            'check_in_at' => null,
            'check_out_at' => null,
        ]);
    }
}
