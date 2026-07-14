<?php

namespace App\Providers;

use App\Models\AudienceSegment;
use App\Models\Brand;
use App\Models\Channel;
use App\Models\Group;
use App\Models\Location;
use App\Models\Organization;
use App\Models\Post;
use App\Models\SlaRule;
use App\Models\Ticket;
use App\Models\User;
use App\Models\UserCollection;
use App\Policies\AudienceSegmentPolicy;
use App\Policies\BrandPolicy;
use App\Policies\ChannelPolicy;
use App\Policies\GroupPolicy;
use App\Policies\LocationPolicy;
use App\Policies\OrganizationPolicy;
use App\Policies\PostPolicy;
use App\Policies\SlaRulePolicy;
use App\Policies\TicketPolicy;
use App\Policies\UserCollectionPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        AudienceSegment::class => AudienceSegmentPolicy::class,
        Brand::class => BrandPolicy::class,
        Channel::class => ChannelPolicy::class,
        Group::class => GroupPolicy::class,
        Location::class => LocationPolicy::class,
        Organization::class => OrganizationPolicy::class,
        Post::class => PostPolicy::class,
        SlaRule::class => SlaRulePolicy::class,
        Ticket::class => TicketPolicy::class,
        User::class => UserPolicy::class,
        UserCollection::class => UserCollectionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('view-dashboard', fn (User $user) => $user->hasPermissionTo('dashboard.view'));
        Gate::define('manage-users', fn (User $user) => $user->hasPermissionTo('user.manage'));
        Gate::define('manage-channels', fn (User $user) => $user->hasPermissionTo('channel.create'));
        Gate::define('manage-groups', fn (User $user) => $user->hasPermissionTo('group.manage'));
    }
}
