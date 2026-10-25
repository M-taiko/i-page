<?php

namespace App\Support;

use App\Models\Channel;
use App\Models\Organization;
use App\Models\OrganizationTemplate;

/**
 * Registry of organization design templates (config/designs.php, ADR-012).
 * A design key is only unique within an industry, so it is always resolved with the industry.
 */
class Designs
{
    /** @return array<string,array> designs of an industry, keyed by design key */
    public static function forIndustry(?string $industryKey): array
    {
        return config("designs.industries.$industryKey", []);
    }

    /** All designs for the create form: industry => [key => design]. */
    public static function all(): array
    {
        return config('designs.industries', []);
    }

    public static function find(?string $industryKey, ?string $designKey): ?array
    {
        if (! $industryKey || ! $designKey) {
            return null;
        }

        $design = self::forIndustry($industryKey)[$designKey] ?? null;

        return $design ? $design + ['key' => $designKey, 'industry' => $industryKey] : null;
    }

    public static function validFor(?OrganizationTemplate $template, ?string $designKey): bool
    {
        return $template !== null && isset(self::forIndustry($template->industry_key)[$designKey]);
    }

    /** The organization's resolved design, or null for the classic page. */
    public static function forOrganization(Organization $organization): ?array
    {
        $industry = $organization->template?->industry_key;

        return self::find($industry, $organization->design_key);
    }

    /** Options with defaults filled in (colors fall back to the design accent). */
    public static function options(Organization $organization, array $design): array
    {
        $saved = $organization->design_options ?? [];
        $primary = $saved['primary_color'] ?? $design['accent'];

        return [
            'primary_color' => $primary,
            'secondary_color' => $saved['secondary_color'] ?? $primary,
            'cover_path' => self::coverPaths($saved)[0] ?? null,
            'cover_paths' => self::coverPaths($saved),
            'cover' => self::coverSettings($saved),
            'show_posts' => (bool) ($saved['show_posts'] ?? true),
            'show_stats' => (bool) ($saved['show_stats'] ?? true),
        ];
    }

    // ------------------------------------------------------------- Channels

    /** @return array<string,array> channel designs keyed by design key */
    public static function channelDesigns(): array
    {
        return config('designs.channels', []);
    }

    public static function findChannel(?string $designKey): ?array
    {
        $design = $designKey ? (self::channelDesigns()[$designKey] ?? null) : null;

        return $design ? $design + ['key' => $designKey] : null;
    }

    /** The channel's effective design: its own, else its parent's, else null (classic page). */
    public static function forChannel(Channel $channel): ?array
    {
        $key = $channel->design_key ?? $channel->parentChannels->first()?->design_key;

        return self::findChannel($key);
    }

    /** Validation rules for the channel design fields of the channel forms. */
    public static function channelRules(): array
    {
        return [
            'design_key' => ['nullable', \Illuminate\Validation\Rule::in(array_keys(self::channelDesigns()))],
            'design_primary_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }

    /** Model attributes (design_key, design_options) from validated channel form data. */
    public static function channelAttributes(array $validated): array
    {
        $color = $validated['design_primary_color'] ?? null;

        return [
            'design_key' => $validated['design_key'] ?? null,
            'design_options' => $color ? ['primary_color' => $color] : null,
        ];
    }

    /** Channel options: channel color, else the organization's, else the design accent. */
    public static function channelOptions(Channel $channel, Organization $organization, array $design): array
    {
        $own = $channel->design_options ?? [];
        $org = $organization->design_options ?? [];
        $primary = $own['primary_color'] ?? $org['primary_color'] ?? $design['accent'];

        return [
            'primary_color' => $primary,
            'secondary_color' => $own['secondary_color'] ?? $org['secondary_color'] ?? $primary,
            'cover_path' => self::coverPaths($own)[0] ?? null,
            'cover_paths' => self::coverPaths($own),
            'cover' => self::coverSettings($own),
            'show_posts' => true,
            'show_stats' => true,
        ];
    }

    public const COVER_HEIGHTS = ['compact' => 180, 'standard' => 260, 'tall' => 380];
    public const COVER_MAX_IMAGES = 8;

    /** Cover image paths in display order (the legacy single `cover_path` is still honored). */
    public static function coverPaths(array $saved): array
    {
        $paths = $saved['cover_paths'] ?? (empty($saved['cover_path']) ? [] : [$saved['cover_path']]);

        return array_values(array_filter((array) $paths, 'is_string'));
    }

    /** Cover carousel behavior with defaults and clamped values. */
    public static function coverSettings(array $saved): array
    {
        $height = isset(self::COVER_HEIGHTS[$saved['cover_height'] ?? '']) ? $saved['cover_height'] : 'standard';

        return [
            'autoplay' => (bool) ($saved['cover_autoplay'] ?? true),
            'interval' => max(2, min(15, (int) ($saved['cover_interval'] ?? 5))),
            'effect' => ($saved['cover_effect'] ?? 'slide') === 'fade' ? 'fade' : 'slide',
            'arrows' => (bool) ($saved['cover_arrows'] ?? true),
            'indicators' => (bool) ($saved['cover_indicators'] ?? true),
            'height' => $height,
            'height_px' => self::COVER_HEIGHTS[$height],
        ];
    }

    /** Validated options array built from request input (empty values dropped). */
    public static function cleanOptions(array $input): array
    {
        $hex = fn ($v) => is_string($v) && preg_match('/^#[0-9a-fA-F]{6}$/', $v) ? $v : null;
        $cover = self::coverSettings($input);

        return array_filter([
            'primary_color' => $hex($input['primary_color'] ?? null),
            'secondary_color' => $hex($input['secondary_color'] ?? null),
            'cover_paths' => ! empty($input['cover_paths']) ? array_values($input['cover_paths']) : null,
            'cover_autoplay' => $cover['autoplay'],
            'cover_interval' => $cover['interval'],
            'cover_effect' => $cover['effect'],
            'cover_arrows' => $cover['arrows'],
            'cover_indicators' => $cover['indicators'],
            'cover_height' => $cover['height'],
            'show_posts' => (bool) ($input['show_posts'] ?? true),
            'show_stats' => (bool) ($input['show_stats'] ?? true),
        ], fn ($v) => $v !== null);
    }
}
