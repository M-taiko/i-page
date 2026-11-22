<?php

use App\Models\User;
use App\Services\DefaultCollections;
use Illuminate\Database\Migrations\Migration;

/** Existing users get the default collections once (new users get them on sign-up) — docs/27. */
return new class extends Migration
{
    public function up(): void
    {
        $defaults = app(DefaultCollections::class);
        User::query()->chunkById(200, function ($users) use ($defaults) {
            foreach ($users as $user) {
                $defaults->createFor($user);
            }
        });
    }

    public function down(): void
    {
        // Collections belong to the users now; nothing to undo.
    }
};
