<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\GroupRepositoryInterface;
use App\Repositories\Contracts\ChannelRepositoryInterface;
use App\Repositories\Contracts\PostRepositoryInterface;
use App\Repositories\Eloquent\UserRepository;
use App\Repositories\Eloquent\GroupRepository;
use App\Repositories\Eloquent\ChannelRepository;
use App\Repositories\Eloquent\PostRepository;
use App\Observers\AuditableModelObserver;
use App\Observers\MembershipAuditObserver;
use App\Observers\LocationMembershipAuditObserver;
use App\Observers\ResourceAuditObserver;
use App\Models\Organization;
use App\Models\Location;
use App\Models\Department;
use App\Models\Group;
use App\Models\Channel;
use App\Models\Post;
use App\Models\Brand;
use App\Models\OrganizationMembership;
use App\Models\LocationMembership;
use App\Models\Ticket;
use App\Policies\OrganizationPolicy;
use App\Policies\BrandPolicy;
use App\Policies\LocationPolicy;
use App\Policies\ChannelPolicy;
use App\Policies\PostPolicy;
use App\Policies\PostApprovalPolicy;
use App\Policies\TicketPolicy;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(GroupRepositoryInterface::class, GroupRepository::class);
        $this->app->bind(ChannelRepositoryInterface::class, ChannelRepository::class);
        $this->app->bind(PostRepositoryInterface::class, PostRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Organization::class, OrganizationPolicy::class);
        Gate::policy(Brand::class, BrandPolicy::class);
        Gate::policy(Location::class, LocationPolicy::class);
        Gate::policy(Channel::class, ChannelPolicy::class);
        Gate::policy(Post::class, PostPolicy::class);
        Gate::policy(Ticket::class, TicketPolicy::class);
        Gate::define('approvePost', [PostApprovalPolicy::class, 'approve']);
        Gate::define('rejectPost', [PostApprovalPolicy::class, 'reject']);
        Gate::define('schedulePost', [PostApprovalPolicy::class, 'schedule']);

        Organization::observe(AuditableModelObserver::class);
        Group::observe(AuditableModelObserver::class);
        Channel::observe(AuditableModelObserver::class);
        Post::observe(AuditableModelObserver::class);

        OrganizationMembership::observe(MembershipAuditObserver::class);
        LocationMembership::observe(LocationMembershipAuditObserver::class);
        Brand::observe(ResourceAuditObserver::class);
        Location::observe(ResourceAuditObserver::class);
        Department::observe(ResourceAuditObserver::class);
    }
}
