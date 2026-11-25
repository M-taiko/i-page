@if(!in_array($node->id,$seen))
@php $branchSeen = array_merge($seen,[$node->id]); $children = $treeChildren[$node->id] ?? []; @endphp
<li class="ip-channel-node">
@if($children)<details @if(count($seen) === 0) open @endif><summary><i class="fe fe-layers"></i><strong>{{ $node->name }}</strong><span class="badge badge-light">{{ count($children) }}</span></summary>@endif
<div class="ip-channel-node-row">
<a href="{{ route('tenant.channels.show',$node) }}"><i class="fe fe-{{ $node->type === 'public' ? 'globe' : 'lock' }}"></i> {{ $children ? __('Open channel') : $node->name }}</a>
<span class="text-muted small">{{ $node->users_count }} {{ __('members') }} · {{ $node->posts_count }} {{ __('posts') }}</span>
@if($node->canAdminister(auth()->user()))<a class="btn btn-sm btn-outline-primary" href="{{ route('tenant.channels.create',['parent'=>$node->id]) }}" aria-label="{{ __('Add sub-channel to') }} {{ $node->name }}"><i class="fe fe-plus"></i> {{ __('Sub-channel') }}</a><a class="btn btn-sm btn-light" href="{{ route('tenant.channels.edit',$node) }}">{{ __('Edit') }}</a>@endif
</div>
@if($children)<ul>@foreach($children as $child) @include('tenant.channels._tree-node',['node'=>$child,'seen'=>$branchSeen]) @endforeach</ul></details>@endif
</li>
@endif
