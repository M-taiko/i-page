{{-- One channel checkbox in the tenant post target picker (ADR-016). Expects $node ['channel', 'children'], $chosen (ids). --}}
@php $c = $node['channel']; @endphp
<li>
    <div class="custom-control custom-checkbox">
        <input type="checkbox" class="custom-control-input" id="target_{{ $c->id }}" name="channel_ids[]" value="{{ $c->id }}" @checked(in_array($c->id, $chosen, true))>
        <label class="custom-control-label" for="target_{{ $c->id }}">
            <i class="fe fe-{{ $c->type === 'private' ? 'lock text-warning' : 'message-circle text-primary' }}"></i> {{ $c->name }}
            @if ($c->type === 'private')<span class="badge badge-warning-transparent">{{ __('Private') }}</span>@endif
        </label>
    </div>
    @if ($node['children'])
        <ul>
            @foreach ($node['children'] as $child)
                @include('organizations.hub._target-node', ['node' => $child, 'chosen' => $chosen])
            @endforeach
        </ul>
    @endif
</li>
