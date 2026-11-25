<?php

namespace Database\Seeders;

use App\Models\{AppointmentService, Channel, HotelUnit, Organization};
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HotelAppointmentDemoSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('Local demo only.');
        }

        $organization = Organization::where('slug', 'hilton-demo')->firstOrFail();
        $catalog = [
            ['name' => 'Room consultation', 'kind' => 'room', 'duration_minutes' => 30, 'slot_capacity' => 4, 'description' => 'Talk with the reservations team about rooms and stay preferences.'],
            ['name' => 'Restaurant table', 'kind' => 'restaurant', 'duration_minutes' => 120, 'slot_capacity' => 40, 'description' => 'Reserve a table for breakfast, lunch or dinner.'],
            ['name' => 'Spa treatment', 'kind' => 'spa', 'duration_minutes' => 60, 'slot_capacity' => 6, 'description' => 'Choose a convenient spa appointment.'],
            ['name' => 'Swimming pool', 'kind' => 'pool', 'duration_minutes' => 90, 'slot_capacity' => 25, 'description' => 'Reserve a pool access time.'],
            ['name' => 'Gym session', 'kind' => 'gym', 'duration_minutes' => 60, 'slot_capacity' => 15, 'description' => 'Book a gym session or trainer consultation.'],
            ['name' => 'Playground visit', 'kind' => 'playground', 'duration_minutes' => 60, 'slot_capacity' => 20, 'description' => 'Reserve a supervised playground time.'],
        ];

        app(\App\Services\TenantServiceCatalog::class)->seedDefaults($organization->fresh('template'));
        $serviceManagerIds = \App\Models\User::whereIn('email', ['admin@hilton.test','eg.admin@hilton.test','ae.admin@hilton.test'])->pluck('id');
        DB::table('organization_memberships')->where('organization_id',$organization->id)->whereIn('user_id',$serviceManagerIds)->update(['can_manage_services'=>false,'can_add_services'=>true,'can_edit_services'=>true,'can_delete_services'=>true]);
        DB::transaction(function () use ($organization, $catalog) {
            HotelUnit::where('organization_id', $organization->id)->where('kind', 'hotel')->with('channel')->get()->each(function ($hotel) use ($organization, $catalog) {
                $channel = $hotel->channel;
                $options = $channel->design_options ?? [];
                $options['modules'] = array_values(array_unique(array_merge($options['modules'] ?? [], ['bookings'])));
                $channel->update(['type' => 'public', 'audience_profile' => 'public', 'design_options' => $options]);

                DB::table('channel_user')->where('channel_id', $channel->id)->whereIn('role', ['admin', 'moderator'])
                    ->update(['can_manage_bookings' => true]);
            });
        });
    }
}
