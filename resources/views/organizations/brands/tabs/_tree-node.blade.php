{{-- One channel in the brand tree, with its children. Expects $node ['channel', 'children'], $depth. --}}
@php $c = $node['channel']; @endphp
<li>
    <div class="tree-row">
        <span class="avatar avatar-sm brround {{ $c->type === 'private' ? 'bg-warning-transparent text-warning' : 'bg-primary-transparent text-primary' }}">
            <i class="fe fe-{{ $c->type === 'private' ? 'lock' : 'message-circle' }}"></i>
        </span>
        <div class="flex-grow-1" style="min-width: 0;">
            <a href="{{ route('tenant.channels.show', $c) }}" class="font-weight-bold">{{ $c->name }}</a>
            <div class="small text-muted">
                {{ $c->users_count }} {{ __('members') }} · {{ $c->posts_count }} {{ __('posts') }}
                @if ($node['children']) · {{ trans_choice(':count sub-channel|:count sub-channels', count($node['children'])) }}@endif
            </div>
        </div>
        <x-valex.badge :variant="$c->type === 'private' ? 'warning' : 'primary'">{{ $c->type === 'private' ? __('Private') : __('Public') }}</x-valex.badge>
        @unless ($c->status === 'active')<x-valex.badge variant="secondary">{{ $c->status }}</x-valex.badge>@endunless
        <a href="{{ route('tenant.channels.posts.create', $c->id) }}" class="btn btn-sm btn-outline-primary" title="{{ __('Create Post') }}"><i class="fe fe-edit-3"></i></a>
        <a href="{{ route('tenant.channels.show', $c) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Manage') }}"><i class="fe fe-settings"></i></a>
    </div>
    @if ($node['children'])
        <ul>
            @foreach ($node['children'] as $child)
                @include('organizations.brands.tabs._tree-node', ['node' => $child, 'depth' => $depth + 1])
            @endforeach
        </ul>
    @endif
</li>
