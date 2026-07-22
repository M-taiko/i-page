<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\Channel;
use App\Models\Department;
use App\Models\Organization;
use App\Models\OrganizationTemplate;
use Illuminate\Support\Str;

class OrganizationTemplateService
{
    /**
     * Seed default departments/channels for a newly created organization
     * from its chosen template. A one-time seed, not an ongoing constraint —
     * the organization is free to diverge afterwards.
     */
    public function applyToOrganization(Organization $organization, OrganizationTemplate $template, ?Brand $defaultBrand = null): void
    {
        $organization->update(['organization_template_id' => $template->id]);

        foreach ($template->default_departments ?? [] as $departmentName) {
            Department::firstOrCreate([
                'organization_id' => $organization->id,
                'name' => $departmentName,
            ], [
                'slug' => Str::slug($departmentName),
            ]);
        }

        foreach ($template->default_channels ?? [] as $channelConfig) {
            Channel::firstOrCreate([
                'organization_id' => $organization->id,
                'name' => $channelConfig['name'],
            ], [
                'slug' => Str::slug($channelConfig['name']) . '-' . Str::random(6),
                'type' => $channelConfig['type'] ?? 'public',
                'brand_id' => $defaultBrand?->id,
                'status' => 'active',
            ]);
        }
    }
}
