<?php
namespace App\Http\Controllers;

use App\Models\Channel;
use App\Services\IndustryModuleService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChannelModuleController extends Controller
{
    public function update(Request $request, Channel $channel, IndustryModuleService $modules)
    {
        abort_unless($request->user()->currentOrganization?->id === $channel->organization_id && $channel->canAdminister($request->user()), 403);
        abort_unless($channel->organization->isActive() && $channel->organization->is_active, 403);
        $keys = array_keys(array_filter($modules->available($channel->organization), fn ($m) => $m['ready']));
        $data = $request->validate(['modules' => 'nullable|array', 'modules.*' => ['string', 'distinct', Rule::in($keys)]]);
        $request->validate(['booking_property_id'=>'nullable|integer']);
        $options = app(\App\Services\HotelPropertyService::class)->configure($channel->organization,$request->user(),in_array('bookings',$data['modules'] ?? []),$request->integer('booking_property_id') ?: null,$channel->design_options ?? [],$channel);
        \Illuminate\Support\Facades\DB::transaction(function () use ($channel, $data, $options) {
            $locked = Channel::whereKey($channel->id)->lockForUpdate()->firstOrFail();
            $merged = array_replace($locked->design_options ?? [],$options);
            $merged['modules'] = array_values($data['modules'] ?? []);
            $locked->update(['design_options' => $merged]);
        });
        return back()->with('success', __('Channel modules saved.'));
    }
}
