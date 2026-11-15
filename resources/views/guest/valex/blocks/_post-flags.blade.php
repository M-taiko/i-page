{{-- Pinned / inherited-from-parent markers for Valex post blocks (ADR-014). Expects $post; $channel is set on channel pages. --}}
@if ($post->isPinned())<span class="badge badge-primary">📌 {{ __('Pinned') }}</span>@endif
@if (isset($channel) && $post->channel_id !== $channel->id && $post->channel)<span class="badge badge-light border">↳ {{ __('From :channel', ['channel' => $post->channel->name]) }}</span>@endif
