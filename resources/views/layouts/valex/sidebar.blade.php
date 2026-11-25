@php
    $user = auth()->user();
    $isSuperAdmin = $user->hasRole('super_admin');
    $workspaceHome = $isSuperAdmin ? route('admin.dashboard') : route('organizations.dashboard');
    $sidebarOrg = $user->currentOrganization;
    $sidebarAccess = \App\Support\SidebarAccess::for($user, $sidebarOrg);
    $followedOrganizations = $user->followedOrganizations()->get();
    // Super admins manage the platform, not a single organization, so they never get the Organization section.
    $showOrganization = ! $isSuperAdmin && $sidebarAccess->activeMember;
    $showCommunication = $sidebarAccess->canPublish() || $sidebarAccess->canManageOrganization();
    $showCrm = $sidebarAccess->canManageTickets($user);
    $showServiceCatalog = $sidebarOrg && app(\App\Services\TenantServiceCatalog::class)->canView($user,$sidebarOrg);
    $logo = fn (string $name) => asset('assets/valex/img/brand/' . $name);
@endphp

<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
<aside class="app-sidebar sidebar-scroll">
    <div class="main-sidebar-header active">
        <a class="desktop-logo logo-light active" href="{{ $workspaceHome }}"><x-brand-logo animate="idle" :height="34" class="main-logo" /></a>
        <a class="desktop-logo logo-dark active" href="{{ $workspaceHome }}"><x-brand-logo animate="idle" :height="34" class="main-logo dark-theme" style="--bl-ink-1: #e0e7ff; --bl-ink-2: #c4b5fd;" /></a>
        <a class="logo-icon mobile-logo icon-light active" href="{{ $workspaceHome }}"><x-brand-logo variant="mark" animate="idle" :height="34" class="logo-icon" /></a>
        <a class="logo-icon mobile-logo icon-dark active" href="{{ $workspaceHome }}"><x-brand-logo variant="mark" animate="idle" :height="34" class="logo-icon dark-theme" /></a>
    </div>

    <div class="main-sidemenu">
        <div class="app-sidebar__user clearfix">
            <div class="dropdown user-pro-body">
                <div>
                    @if ($user->avatar_path)
                        <img alt="{{ $user->name }}" class="avatar avatar-xl brround" src="{{ Storage::url($user->avatar_path) }}">
                    @else
                        <span class="avatar avatar-xl brround bg-primary">{{ $user->initials }}</span>
                    @endif
                    <span class="avatar-status profile-status bg-green"></span>
                </div>
                <div class="user-info">
                    <h4 class="font-weight-semibold mt-3 mb-0">{{ $user->full_name }}</h4>
                    <span class="mb-0 text-muted">
                        @if ($isSuperAdmin)
                            {{ __('Super Admin') }}
                        @elseif ($sidebarOrg)
                            {{ $sidebarOrg->name }}
                        @else
                            {{ $user->display_role }}
                        @endif
                    </span>
                </div>
            </div>
        </div>

        <div class="ip-nav-search">
            <i class="fe fe-search" aria-hidden="true"></i>
            <input id="workspace-nav-search" type="search" autocomplete="off" placeholder="{{ $isRtl ? 'ابحث في القائمة…' : 'Find a page…' }}" aria-label="{{ $isRtl ? 'البحث في قائمة التنقل' : 'Search navigation' }}">
            <kbd>/</kbd>
        </div>
        <p id="workspace-nav-empty" class="ip-nav-empty" hidden>{{ $isRtl ? 'لا توجد صفحات مطابقة' : 'No matching pages' }}</p>
        <ul class="side-menu">
            @unless ($isSuperAdmin)
            {{-- Main --}}
            <li class="side-item side-item-category">{{ __('Main') }}</li>
            <li class="slide">
                <a @class(['side-menu__item', 'active' => request()->routeIs('user.feed')]) href="{{ route('user.feed') }}">
                    <i class="side-menu__icon fe fe-rss"></i><span class="side-menu__label">{{ __('Feed') }}</span>
                </a>
            </li>

            @if ($showCommunication)
            {{-- Communication --}}
            <li class="side-item side-item-category">{{ __('Communication') }}</li>
            @if ($sidebarAccess->canPublish())
            <li class="slide">
                <a @class(['side-menu__item', 'active' => request()->routeIs('posts.*')]) href="{{ route('posts.index') }}">
                    <i class="side-menu__icon fe fe-file-text"></i><span class="side-menu__label">{{ __('Posts') }}</span>
                </a>
            </li>
            @endif
            @if ($sidebarAccess->canManageOrganization())
            <li class="slide">
                <a @class(['side-menu__item', 'active' => request()->routeIs('audience-segments.*')]) href="{{ route('audience-segments.index') }}">
                    <i class="side-menu__icon fe fe-users"></i><span class="side-menu__label">{{ __('Audiences') }}</span>
                </a>
            </li>
            @endif
            @endif

            @if ($showCrm)
            {{-- CRM --}}
            <li class="side-item side-item-category">{{ __('CRM') }}</li>
            <li class="slide">
                <a @class(['side-menu__item', 'active' => request()->routeIs('tickets.*')]) href="{{ route('tickets.index') }}">
                    <i class="side-menu__icon fe fe-tag"></i><span class="side-menu__label">{{ __('Tickets') }}</span>
                </a>
            </li>
            @endif

            @endunless

            @if($sidebarOrg && !$isSuperAdmin && $sidebarAccess->canManageOrganization())
            <li class="slide"><a @class(['side-menu__item', 'active' => request()->routeIs('tenant.actions.*')]) href="{{ route('tenant.actions.index') }}"><i class="side-menu__icon fe fe-check-square"></i><span class="side-menu__label">{{ __('Action center') }}</span></a></li>
            @endif
            @if($showServiceCatalog)
            <li class="slide"><a @class(['side-menu__item','active'=>request()->routeIs('tenant.services.*')]) href="{{ route('tenant.services.index') }}"><i class="side-menu__icon fe fe-grid"></i><span class="side-menu__label">{{ __('Service catalog') }}</span></a></li>
            @endif
            {{-- Organization --}}
            @if ($showOrganization)
                @if($sidebarOrg?->template?->key === 'hotel')
                <li class="slide"><a @class(['side-menu__item','active'=>request()->routeIs('tenant.hotel-hierarchy.*')]) href="{{ route('tenant.hotel-hierarchy.index') }}"><i class="side-menu__icon fe fe-globe"></i><span class="side-menu__label">{{ __('Hotel group') }}</span></a></li>
                @endif
                <li class="side-item side-item-category">
                    {{ __('Organization') }}@if ($isSuperAdmin && $sidebarOrg) — {{ $sidebarOrg->name }}@endif
                </li>
                <li class="slide">
                    <a @class(['side-menu__item', 'active' => request()->routeIs('organizations.dashboard')]) href="{{ $workspaceHome }}">
                        <i class="side-menu__icon fe fe-bar-chart-2"></i><span class="side-menu__label">{{ __('Dashboard') }}</span>
                    </a>
                </li>
                <li class="slide">
                    <a @class(['side-menu__item', 'active' => request()->routeIs('tenant.channels.*')]) href="{{ route('tenant.channels.index') }}">
                        <i class="side-menu__icon fe fe-message-circle"></i><span class="side-menu__label">{{ __('Channels') }}</span>
                    </a>
                </li>
                @if ($sidebarOrg)
                    @if ($sidebarAccess->canManageOrganization())
                    <li class="slide">
                        <a @class(['side-menu__item', 'active' => request()->routeIs('organizations.posts.*')]) href="{{ route('organizations.posts.index') }}">
                            <i class="side-menu__icon fe fe-send"></i><span class="side-menu__label">{{ __('Tenant posts') }}</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a @class(['side-menu__item', 'active' => request()->routeIs('organizations.channel-managers.*')]) href="{{ route('organizations.channel-managers.index') }}">
                            <i class="side-menu__icon fe fe-shield"></i><span class="side-menu__label">{{ __('Channel managers') }}</span>
                        </a>
                    </li>
                    @endif
                    @if ($sidebarAccess->canManageOrganization())
                    <li class="slide">
                        <a @class(['side-menu__item', 'active' => request()->routeIs('organizations.people')]) href="{{ route('organizations.people') }}">
                            <i class="side-menu__icon fe fe-users"></i><span class="side-menu__label">{{ __('People') }}</span>
                        </a>
                    </li>
                    @endif
                    @if ($sidebarAccess->canManageOrganization())
                        <li class="slide">
                            <a @class(['side-menu__item', 'active' => request()->routeIs('dashboard.users.*')]) href="{{ route('dashboard.users.index', $sidebarOrg) }}">
                                <i class="side-menu__icon fe fe-user-plus"></i><span class="side-menu__label">{{ __('Team') }}</span>
                            </a>
                        </li>
                    @endif
                @endif
                @if ($sidebarAccess->canManageOrganization())
                <li class="slide">
                    <a @class(['side-menu__item', 'active' => request()->routeIs('organizations.settings')]) href="{{ route('organizations.settings') }}">
                        <i class="side-menu__icon fe fe-settings"></i><span class="side-menu__label">{{ __('Settings') }}</span>
                    </a>
                </li>
                @endif

                @if ($sidebarOrg && $sidebarAccess->canManageOrganization())
                    <li class="side-item side-item-category">{{ __('Organization CRM') }}</li>
                    <li class="slide">
                        <a @class(['side-menu__item', 'active' => request()->routeIs('organizations.crm.contacts', 'organizations.crm.contact')]) href="{{ route('organizations.crm.contacts') }}">
                            <i class="side-menu__icon fe fe-users"></i><span class="side-menu__label">{{ __('Contacts') }}</span>
                        </a>
                    </li>
                    <li class="slide">
                        <a @class(['side-menu__item', 'active' => request()->routeIs('organizations.crm.activity')]) href="{{ route('organizations.crm.activity') }}">
                            <i class="side-menu__icon fe fe-activity"></i><span class="side-menu__label">{{ __('Activity') }}</span>
                        </a>
                    </li>
                @endif
            @endif

            {{-- Following --}}
            @if (! $isSuperAdmin && $followedOrganizations->isNotEmpty())
                <li class="side-item side-item-category">{{ __('Following') }}</li>
                @foreach ($followedOrganizations as $org)
                    <li class="slide">
                        <a @class(['side-menu__item', 'active' => request()->segment(2) == $org->id]) href="{{ route('dashboard.home', $org->id) }}">
                            <i class="side-menu__icon fe fe-briefcase"></i><span class="side-menu__label">{{ $org->name }}</span>
                        </a>
                    </li>
                @endforeach
            @endif

            {{-- Admin --}}
            @if ($isSuperAdmin)
                <li class="side-item side-item-category">{{ __('Admin') }}</li>
                <li class="slide">
                    <a @class(['side-menu__item', 'active' => request()->routeIs('admin.dashboard')]) href="{{ route('admin.dashboard') }}">
                        <i class="side-menu__icon fe fe-pie-chart"></i><span class="side-menu__label">{{ __('Super Admin Dashboard') }}</span>
                    </a>
                </li>
                <li class="slide">
                    <a @class(['side-menu__item', 'active' => request()->routeIs('admin.organizations.*')]) href="{{ route('admin.organizations.index') }}">
                        <i class="side-menu__icon fe fe-shield"></i><span class="side-menu__label">{{ __('Organizations') }}</span>
                    </a>
                </li>

                <li class="side-item side-item-category">{{ __('CRM') }}</li>
                <li class="slide">
                    <a @class(['side-menu__item', 'active' => request()->routeIs('admin.crm.index', 'admin.crm.organization')]) href="{{ route('admin.crm.index') }}">
                        <i class="side-menu__icon fe fe-briefcase"></i><span class="side-menu__label">{{ __('Organizations 360') }}</span>
                    </a>
                </li>
                <li class="slide">
                    <a @class(['side-menu__item', 'active' => request()->routeIs('admin.crm.people', 'admin.crm.person')]) href="{{ route('admin.crm.people') }}">
                        <i class="side-menu__icon fe fe-users"></i><span class="side-menu__label">{{ __('People') }}</span>
                    </a>
                </li>
                <li class="slide">
                    <a @class(['side-menu__item', 'active' => request()->routeIs('admin.crm.activity')]) href="{{ route('admin.crm.activity') }}">
                        <i class="side-menu__icon fe fe-activity"></i><span class="side-menu__label">{{ __('Activity Log') }}</span>
                    </a>
                </li>
            @endif
        </ul>
    </div>
</aside>
