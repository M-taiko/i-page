<div class="alert alert-info">{{ __('Choose channels and set permissions for each one. Full management includes settings, members, QR codes and all post actions, and is inherited by sub-channels.') }}</div>
<div class="form-group"><label for="accessSearch">{{ __('Find a channel') }}</label><input type="search" id="accessSearch" class="form-control" placeholder="{{ __('Search by channel or parent name') }}"></div>
<div class="table-responsive access-matrix-wrap"><table class="table table-hover access-matrix"><thead><tr><th>{{ __('Channel access') }}</th><th>{{ __('Publish posts') }}</th><th>{{ __('Edit posts') }}</th><th>{{ __('Delete posts') }}</th><th>{{ __('Manage bookings') }}</th><th>{{ __('Full management') }}</th></tr></thead><tbody>
@foreach($channels as $channel)
@php
    $pivot = isset($user) ? $channel->users->first()?->pivot : null;
    $locked = isset($user) && (\App\Models\Channel::isOrgManager($user, $organization->id) || $channel->admin_user_id === $user->id);
    $selected = old('grants.'.$channel->id.'.selected', $pivot && $pivot->status === 'approved' ? 1 : 0);
    $effective = isset($user) ? $channel->roleFor($user) : null;
@endphp
<tr data-channel-row><td style="min-width:240px"><label class="mb-0 d-flex align-items-center" style="gap:10px"><input type="checkbox" class="channel-select" name="grants[{{ $channel->id }}][selected]" value="1" @checked($selected || $locked) @disabled($locked)><span><strong>{{ $channel->name }}</strong><small class="d-block text-muted">{{ $channel->parentChannels->pluck('name')->implode(' / ') }}</small></span></label>
@if($locked)<small class="text-warning">{{ __('Organization manager or channel owner: access is managed by that role.') }}</small>
@elseif($effective && $effective !== ($pivot?->role))<small class="text-info">{{ __('Additional permissions are inherited from a parent channel.') }}</small>@endif</td>
@foreach(['post'=>__('Publish posts'),'edit'=>__('Edit posts'),'delete'=>__('Delete posts'),'manage_bookings'=>__('Manage bookings'),'full'=>__('Full management')] as $ability=>$label)
<td><input type="checkbox" class="channel-ability" aria-label="{{ $label }} — {{ $channel->name }}" name="grants[{{ $channel->id }}][{{ $ability }}]" value="1" @checked(old('grants.'.$channel->id.'.'.$ability, $locked || ($ability === 'full' ? $pivot?->role === 'admin' : ($pivot?->{'can_'.$ability} ?? false)))) @disabled($locked)></td>
@endforeach</tr>
@endforeach
</tbody></table></div>
<p class="text-muted small">{{ __('Unselected channels have no direct membership. Organization roles, channel ownership and inherited permissions remain effective.') }}</p>
@push('styles')<style>.access-matrix-wrap{max-height:540px;border:1px solid var(--ip-border,#e4e8f0);border-radius:12px;margin-bottom:18px}.access-matrix thead th{position:sticky;top:0;z-index:1}.access-matrix input[type=checkbox]{width:18px;height:18px;accent-color:var(--ip-accent,#5965e8);cursor:pointer}.access-matrix th:not(:first-child),.access-matrix td:not(:first-child){text-align:center}.access-matrix tr:has(.channel-select:checked){background:rgba(93,106,232,.035)}.access-matrix td{vertical-align:middle!important}</style>@endpush
@push('scripts')<script>document.addEventListener('DOMContentLoaded',()=>{document.querySelector('#accessSearch').addEventListener('input',function(){const q=this.value.toLowerCase();document.querySelectorAll('[data-channel-row]').forEach(row=>row.hidden=!row.textContent.toLowerCase().includes(q));});document.querySelectorAll('[data-channel-row]').forEach(row=>{row.querySelectorAll('.channel-ability').forEach(input=>input.addEventListener('change',()=>{if(input.checked)row.querySelector('.channel-select').checked=true;}));});});</script>@endpush
