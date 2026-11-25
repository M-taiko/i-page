<?php
namespace App\Services;

use App\Models\{Channel, Organization};

class IndustryModuleService
{
    public function available(Organization $organization): array
    {
        $keys = config('industry-modules.templates.' . ($organization->template?->key ?? ''), []);
        return array_intersect_key(config('industry-modules.catalog', []), array_flip($keys));
    }

    public function enabled(Channel $channel, string $key): bool
    {
        $module = $this->available($channel->organization)[$key] ?? null;
        return $module && $module['ready'] && in_array($key, $channel->design_options['modules'] ?? [], true);
    }
}
