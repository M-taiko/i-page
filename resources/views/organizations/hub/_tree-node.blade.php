{{-- One channel in the channel-managers tree with its managers (ADR-016).
     Expects $node ['channel', 'children'], $assignments (grouped by channel_id), $inherited (names managing from above). --}}
@php
    $c = $node['channel'];
    $rows = $assignments[$c->id] ?? collect();
    $roleLabels = ['admin' => __('Admin'), 'moderator' => __('Moderator'), 'member' => __('Member')];
    $roleVariant = ['admin' => 'primary', 'moderator' => 'info', 'member' => 'light'];
    $nextInherited = $inherited + $rows->whereIn('role', ['admin', 'moderator'])->mapWithKeys(fn ($r) => [$r->user_id => trim($r->first_name . ' ' . $r->last_name) . ' (' . $roleLabels[$r->role] . ')'])->all();
@endphp
<li>
    <div class="tree-row">
        <span class="avatar avatar-sm brround {{ $c->type === 'private' ? 'bg-warning-transparent text-warning' : 'bg-primary-transparent text-primary' }}">
            <i class="fe fe-{{ $c->type === 'private' ? 'lock' : 'message-circle' }}"></i>
        </span>
        <div class="flex-grow-1" style="min-width: 0;">
            <a href="{{ route('tenant.channels.show', $c) }}" class="font-weight-bold">{{ $c->name }}</a>
            <span class="small text-muted">· {{ $c->users_count }} {{ __('members') }} · {{ $c->posts_count }} {{ __('posts') }}</span>
            <div class="mt-1">
                @foreach ($rows as $r)
                    <span class="badge badge-{{ $roleVariant[$r->role] ?? 'light' }} mb-1" style="font-size: 12px;">
                        {{ trim($r->first_name . ' ' . $r->last_name) }} · {{ $roleLabels[$r->role] ?? $r->role }}
                        @if ($r->role === 'member')
                            ({{ collect(['post' => $r->can_post, 'edit' => $r->can_edit, 'delete' => $r->can_delete])->filter()->keys()->map(fn ($k) => __(ucfirst($k)))->implode(', ') }})
                        @endif
                        <form action="{{ route('organizations.channel-managers.destroy', [$c->id, $r->user_id]) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Remove this management role?') }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-link p-0 text-white" style="font-size: 12px; line-height: 1;" aria-label="{{ __('Remove') }}">✕</button>
                        </form>
                    </span>
                @endforeach
                @if ($inherited)
                    <span class="small text-muted d-block">↳ {{ __('Managed from above by: :names', ['names' => implode('، ', $inherited)]) }}</span>
                @endif
            </div>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary js-assign" data-channel="{{ $c->id }}" title="{{ __('Assign') }}"><i class="fe fe-user-plus"></i></button>
    </div>
    @if ($node['children'])
        <ul>
            @foreach ($node['children'] as $child)
                @include('organizations.hub._tree-node', ['node' => $child, 'assignments' => $assignments, 'inherited' => $nextInherited])
            @endforeach
        </ul>
    @endif
</li>
