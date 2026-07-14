<?php
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/bootstrap/app.php';

use App\Models\Post;
use App\Models\Channel;

$posts = Post::whereHas('channel', function ($query) {
    $query->where('organization_id', 6)
          ->where('type', 'public');
})
->where('status', 'published')
->latest('published_at')
->with('channel')
->get();

echo "Total Posts: " . $posts->count() . "\n\n";
foreach($posts as $post) {
    echo "Post ID: {$post->id}\n";
    echo "  Body: " . substr($post->body, 0, 50) . "...\n";
    echo "  Channel: {$post->channel->name} (ID: {$post->channel->id}, Type: {$post->channel->type})\n";
    echo "  Status: {$post->status}\n";
    echo "  Published: {$post->published_at}\n\n";
}

echo "\n\n=== CHANNELS ===\n";
$channels = Channel::where('organization_id', 6)->where('type', 'public')->get();
echo "Total Public Channels: " . $channels->count() . "\n\n";
foreach($channels as $channel) {
    echo "Channel ID: {$channel->id}\n";
    echo "  Name: {$channel->name}\n";
    echo "  Type: {$channel->type}\n";
    $channelPosts = $channel->posts()->where('status', 'published')->count();
    echo "  Published Posts: {$channelPosts}\n\n";
}
