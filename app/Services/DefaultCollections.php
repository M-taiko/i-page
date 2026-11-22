<?php

namespace App\Services;

use App\Models\User;

/**
 * Every user starts with a few ready-made collections for the kinds of places they follow
 * (docs/27 "Default Collections"). Created once — when the account is created — so a collection
 * the user deletes or renames is never brought back. Users add, rename and delete freely afterwards.
 */
class DefaultCollections
{
    /** [name, icon, color] in display order. */
    public const DEFAULTS = [
        ['Education', '🎓', '#2563eb'],
        ['Hotels', '🏨', '#7c3aed'],
        ['Supermarkets', '🛒', '#059669'],
        ['Restaurants', '🍽️', '#d97706'],
        ['Healthcare', '🏥', '#dc2626'],
    ];

    /** @return int how many collections were created */
    public function createFor(User $user): int
    {
        $existing = $user->collections()->pluck('name')->map(fn ($n) => mb_strtolower($n))->all();
        $order = (int) ($user->collections()->max('sort_order') ?? 0);
        $created = 0;

        foreach (self::DEFAULTS as [$name, $icon, $color]) {
            if (in_array(mb_strtolower($name), $existing, true)) {
                continue;
            }
            $user->collections()->create([
                'name' => $name, 'icon' => $icon, 'color' => $color,
                'sort_order' => ++$order, 'is_pinned' => false, 'is_muted' => false, 'is_favorites' => false,
            ]);
            $created++;
        }

        return $created;
    }
}
