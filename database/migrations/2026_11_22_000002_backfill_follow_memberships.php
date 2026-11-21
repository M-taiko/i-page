<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * People who followed an organization before follow-subscriptions existed (docs/27) join its main public
 * channels now, muted and marked source = follow — exactly what following does today.
 */
return new class extends Migration
{
    public function up(): void
    {
        $now = now();
        $followers = DB::table('organization_followers')->get(['organization_id', 'user_id']);

        foreach ($followers->groupBy('organization_id') as $organizationId => $rows) {
            $mainIds = DB::table('channels')
                ->where('organization_id', $organizationId)->where('type', 'public')->where('status', 'active')->whereNull('deleted_at')
                ->whereNotIn('id', DB::table('channel_channel')->select('child_channel_id'))
                ->pluck('id');

            foreach ($mainIds as $channelId) {
                $existing = DB::table('channel_user')->where('channel_id', $channelId)->pluck('user_id')->all();
                $insert = $rows->pluck('user_id')->diff($existing)->map(fn ($userId) => [
                    'channel_id' => $channelId, 'user_id' => $userId, 'role' => 'member', 'status' => 'approved', 'source' => 'follow',
                    'joined_at' => $now, 'muted_at' => $now, 'can_post' => false, 'can_edit' => false, 'can_delete' => false,
                ])->values()->all();
                foreach (array_chunk($insert, 500) as $chunk) {
                    DB::table('channel_user')->insert($chunk);
                }
            }
        }
    }

    public function down(): void
    {
        // Follow memberships are regular data now; nothing to undo.
    }
};
