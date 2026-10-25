<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Validation and persistence of the format-specific parts of a post (ADR-013):
 * format, card style, badge, priority, reactions and the per-format settings.
 */
class PostFormatService
{
    public function __construct(private MediaService $media)
    {
    }

    /** Validation rules for the format fields; the base rules (body, ...) stay in the controller. */
    public function rules(Request $request): array
    {
        $formats = array_keys(config('posts.formats'));
        $format = in_array($request->input('post_format'), $formats, true) ? $request->input('post_format') : 'standard';
        $limits = config('posts.limits');

        $rules = [
            'post_format' => ['nullable', Rule::in($formats)],
            'card_style' => ['nullable', Rule::in(array_keys(config('posts.card_styles')))],
            'priority' => ['nullable', Rule::in(array_keys(config('posts.priorities')))],
            'badge_key' => ['nullable', Rule::in(array_keys(config('posts.badges')))],
            'badge_label' => 'nullable|string|max:40',
            'reactions' => 'nullable|array',
            'reactions.*' => [Rule::in(array_keys(config('posts.reactions')))],
        ];

        return $rules + match ($format) {
            'gallery' => [
                'gallery' => 'required|array|min:1|max:' . $limits['gallery_images'],
                'gallery.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            ],
            'poll' => [
                'poll_options' => 'required|array|min:' . $limits['poll_options_min'] . '|max:' . $limits['poll_options_max'],
                'poll_options.*' => 'nullable|string|max:150',
                'poll_multiple' => 'nullable|boolean',
                'poll_ends_at' => 'nullable|date|after:now',
                'poll_show_results' => 'nullable|in:always,after_vote',
            ],
            'event' => [
                'event_starts_at' => 'required|date',
                'event_ends_at' => 'nullable|date|after_or_equal:event_starts_at',
                'event_location' => 'nullable|string|max:200',
                'event_capacity' => 'nullable|integer|min:1|max:100000',
            ],
            default => [],
        };
    }

    /** The validated format of a request (defaults to standard). */
    public function formatOf(Request $request): string
    {
        $format = $request->input('post_format');

        return array_key_exists($format, config('posts.formats')) ? $format : 'standard';
    }

    /**
     * Apply validated data to a NEW post (not yet saved). Returns a callback to run after save()
     * that stores relational parts (poll options, gallery images).
     */
    public function fill(Post $post, array $data, Request $request): \Closure
    {
        $format = $this->formatOf($request);
        $options = [
            'reactions' => array_values($data['reactions'] ?? ['like']) ?: ['like'],
        ];

        $post->post_format = $format;
        $post->card_style = $data['card_style'] ?? 'classic';
        $post->priority = $data['priority'] ?? 'medium';
        $post->badge_key = $data['badge_key'] ?? null;
        $post->badge_label = ($data['badge_key'] ?? null) ? ($data['badge_label'] ?? null) : null;

        $pollOptions = [];
        if ($format === 'poll') {
            $pollOptions = array_values(array_filter(array_map('trim', $data['poll_options'] ?? []), fn ($o) => $o !== ''));
            $options['poll'] = [
                'multiple' => (bool) ($data['poll_multiple'] ?? false),
                'ends_at' => ! empty($data['poll_ends_at']) ? \Illuminate\Support\Carbon::parse($data['poll_ends_at'])->toIso8601String() : null,
                'show_results' => $data['poll_show_results'] ?? 'always',
            ];
        } elseif ($format === 'event') {
            $options['event'] = [
                'starts_at' => \Illuminate\Support\Carbon::parse($data['event_starts_at'])->toIso8601String(),
                'ends_at' => ! empty($data['event_ends_at']) ? \Illuminate\Support\Carbon::parse($data['event_ends_at'])->toIso8601String() : null,
                'location' => $data['event_location'] ?? null,
                'capacity' => ! empty($data['event_capacity']) ? (int) $data['event_capacity'] : null,
            ];
        } elseif ($format === 'gallery') {
            $options['gallery'] = ['autoplay' => true, 'interval' => 4];
        }
        $post->options = $options;

        return function (Post $saved) use ($format, $pollOptions, $request) {
            foreach ($pollOptions as $i => $label) {
                $saved->pollOptions()->create(['label' => $label, 'sort_order' => $i]);
            }

            if ($format === 'gallery') {
                foreach ($request->file('gallery', []) as $file) {
                    $this->media->upload($file, $saved, 'public', 'gallery');
                }
            }
        };
    }

    /** Extra check the generic rules cannot express: a poll needs enough non-empty options. */
    public function pollOptionsAreEnough(array $data): bool
    {
        $filled = array_filter(array_map('trim', $data['poll_options'] ?? []), fn ($o) => $o !== '');

        return count($filled) >= config('posts.limits.poll_options_min');
    }
}
