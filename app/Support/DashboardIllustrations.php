<?php
namespace App\Support;

final class DashboardIllustrations
{
    /** Decorative scenes follow the page purpose, never tenant/user data. */
    public static function forRoute(?string $route): string
    {
        $route = strtolower($route ?? '');
        return match (true) {
            str_contains($route, 'notifications') => 'notifications',
            str_contains($route, 'settings'), str_contains($route, 'profile') => 'workspace-settings',
            str_contains($route, 'activity') => 'workspace-activity',
            (str_contains($route, 'channel-access') || str_contains($route, 'channel-invitations') || str_contains($route, 'channel-managers')) => 'workspace-permissions',
            str_contains($route, 'locations') => 'workspace-location',
            str_contains($route, 'sla-rules'), str_contains($route, 'ticket-categories') => 'workspace-settings',
            str_contains($route, 'audience') => 'workspace-audience',
            str_contains($route, 'posts'), str_contains($route, 'feeds'), str_contains($route, 'feed') => 'feed',
            str_contains($route, 'channels') => 'channels',
            str_contains($route, 'users'), str_contains($route, 'people'), str_contains($route, 'person'), str_contains($route, 'contact'), str_contains($route, 'groups') => 'workspace-team',
            str_contains($route, 'dashboard'), str_contains($route, 'overview') => 'workspace-dashboard',
            str_contains($route, 'organizations'), str_contains($route, 'organization'), str_contains($route, 'admin.crm') => 'workspace-organization',
            str_contains($route, 'tickets') => 'workspace-support',
            str_contains($route, 'actions') => 'workspace-checklist',
            default => 'workspace-dashboard',
        };
    }
}
