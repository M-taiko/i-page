<?php

namespace App\Support;

use App\Models\Channel;
use App\Models\Organization;
use App\Models\Post;
use Illuminate\Support\Str;

/**
 * schema.org JSON-LD for public pages (docs/27 "Sharing & SEO"). Each method returns a list of nodes
 * for the page's @graph; partials/seo adds the i-page publisher node.
 */
final class SeoSchema
{
    public static function organization(Organization $organization): array
    {
        $url = route('guest.organization-detail', $organization->id);

        return [
            array_filter([
                '@type' => 'Organization',
                '@id' => $url . '#org',
                'name' => $organization->name,
                'url' => $url,
                'description' => $organization->description,
                'email' => $organization->email,
                'telephone' => $organization->phone,
                'address' => ($organization->city || $organization->country) ? array_filter([
                    '@type' => 'PostalAddress',
                    'streetAddress' => $organization->address,
                    'addressLocality' => $organization->city,
                    'addressCountry' => $organization->country,
                ]) : null,
            ]),
            self::breadcrumbs([[__('Discover'), route('guest.home')], [$organization->name, $url]]),
        ];
    }

    public static function channel(Channel $channel, Organization $organization): array
    {
        $url = route('guest.channel-detail', [$organization->id, $channel->slug]);
        $trail = [[__('Discover'), route('guest.home')], [$organization->name, route('guest.organization-detail', $organization->id)]];
        foreach (array_reverse(self::publicAncestors($channel)) as $parent) {
            $trail[] = [$parent->name, route('guest.channel-detail', [$organization->id, $parent->slug])];
        }
        $trail[] = [$channel->name, $url];

        return [
            array_filter([
                '@type' => 'CollectionPage',
                '@id' => $url . '#page',
                'name' => $channel->name,
                'url' => $url,
                'description' => $channel->description,
                'isPartOf' => ['@id' => route('guest.organization-detail', $organization->id) . '#org'],
                'about' => ['@type' => 'Organization', 'name' => $organization->name, 'url' => route('guest.organization-detail', $organization->id)],
            ]),
            self::breadcrumbs($trail),
        ];
    }

    public static function post(Post $post): array
    {
        $url = route('guest.post-detail', $post->id);
        $org = $post->organization ?? $post->channel?->organization;
        $image = $post->coverUrl();

        $node = array_filter([
            '@type' => 'SocialMediaPosting',
            '@id' => $url . '#post',
            'url' => $url,
            'mainEntityOfPage' => $url,
            'headline' => Str::limit($post->title ?: $post->body, 105, ''),
            'articleBody' => Str::limit($post->body, 5000),
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified' => $post->updated_at?->toIso8601String(),
            'inLanguage' => $post->language,
            'image' => $image ? url($image) : null,
            'author' => $org ? ['@type' => 'Organization', 'name' => $post->sourceName() ?? $org->name, 'url' => route('guest.organization-detail', $org->id)] : null,
            'publisher' => ['@id' => rtrim(url('/'), '/') . '/#organization'],
            'interactionStatistic' => [
                ['@type' => 'InteractionCounter', 'interactionType' => 'https://schema.org/LikeAction', 'userInteractionCount' => $post->reactions()->count()],
                ['@type' => 'InteractionCounter', 'interactionType' => 'https://schema.org/CommentAction', 'userInteractionCount' => $post->comments()->where('status', 'approved')->count()],
            ],
        ]);

        $trail = [[__('Discover'), route('guest.home')]];
        if ($org) {
            $trail[] = [$org->name, route('guest.organization-detail', $org->id)];
        }
        if ($post->channel && $post->channel->type === 'public') {
            $trail[] = [$post->channel->name, route('guest.channel-detail', [$post->channel->organization_id, $post->channel->slug])];
        }
        $trail[] = [Str::limit($post->title ?: $post->body, 60), $url];

        return [$node, self::breadcrumbs($trail)];
    }

    /** @param array<int, array{0:string,1:string}> $items [name, url] */
    public static function breadcrumbs(array $items): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(fn ($item, $i) => [
                '@type' => 'ListItem', 'position' => $i + 1, 'name' => $item[0], 'item' => $item[1],
            ], $items, array_keys($items)),
        ];
    }

    /** Public ancestors, nearest first (cycle-safe, at most 6 levels). */
    private static function publicAncestors(Channel $channel): array
    {
        $out = [];
        $seen = [$channel->id];
        $current = $channel;
        while (count($out) < 6 && ($parent = $current->parentChannels()->where('type', 'public')->first()) && ! in_array($parent->id, $seen, true)) {
            $out[] = $parent;
            $seen[] = $parent->id;
            $current = $parent;
        }

        return $out;
    }
}
