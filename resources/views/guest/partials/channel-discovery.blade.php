@php
 $cover=\App\Support\Designs::coverPaths($channel->design_options??[])[0]??$channel->logo_path;
 $parents=[];$seen=[$channel->id];$cursor=$channel;
 while($parent=$cursor->parentChannels()->where('organization_id',$channel->organization_id)->where('type','public')->first()){
   if(in_array($parent->id,$seen)) break;
   $parents[]=$parent;$seen[]=$parent->id;$cursor=$parent;
 }
@endphp
<article class="discover-channel">
 <button type="button" class="channel-preview" aria-expanded="false" data-url="{{ route('guest.channel-detail',[$channel->organization_id,$channel->slug]) }}" aria-label="{{ $channel->name }}">
  @if($cover)<img class="channel-cover" src="{{ Storage::url($cover) }}" alt="" loading="lazy">@endif
  <span class="channel-overlay"></span>
  <span class="channel-identity">@if($channel->logo_path)<img src="{{ Storage::url($channel->logo_path) }}" alt="" loading="lazy">@endif<strong>{{ $channel->name }}</strong></span>
 </button>
 <nav class="channel-breadcrumbs" aria-label="{{ __('Channel hierarchy') }}">
  <a href="{{ route('guest.organization-detail',$channel->organization_id) }}">{{ $channel->organization?->name }}</a>
  @foreach(array_reverse($parents) as $parent)<span>›</span><a href="{{ route('guest.channel-detail',[$parent->organization_id,$parent->slug]) }}">{{ $parent->name }}</a>@endforeach
  <span>›</span><a href="{{ route('guest.channel-detail',[$channel->organization_id,$channel->slug]) }}">{{ $channel->name }}</a>
 </nav>
 <small class="channel-hint">{{ __('Tap to preview, tap again to visit') }}</small>
</article>
