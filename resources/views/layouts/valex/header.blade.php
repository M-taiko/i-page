@php
    $user = auth()->user();
    $logo = fn (string $name) => asset('assets/valex/img/brand/' . $name);
    $navNotifications = $user->notifications()->latest()->take(8)->get();
    $navUnreadCount = $user->notifications()->whereNull('read_at')->count();
    $channelOptions = \App\Support\ChannelNavigation::options($user, $user->currentOrganization);
    $routeChannel = request()->route('channel');
    $selectedChannelId = $routeChannel instanceof \App\Models\Channel ? $routeChannel->id : (int) $routeChannel;
@endphp

<div class="main-header sticky side-header nav nav-item">
    <div class="container-fluid">
        <div class="main-header-left">
            <div class="responsive-logo">
                <a href="{{ $user->hasRole('super_admin') ? route('admin.dashboard') : route('organizations.dashboard') }}"><x-brand-logo animate="idle" :height="32" class="logo-1" /></a>
                <a href="{{ $user->hasRole('super_admin') ? route('admin.dashboard') : route('organizations.dashboard') }}"><x-brand-logo animate="idle" :height="32" class="dark-logo-1" style="--bl-ink-1: #e0e7ff; --bl-ink-2: #c4b5fd;" /></a>
                <a href="{{ $user->hasRole('super_admin') ? route('admin.dashboard') : route('organizations.dashboard') }}"><x-brand-logo variant="mark" animate="idle" :height="32" class="logo-2" /></a>
                <a href="{{ $user->hasRole('super_admin') ? route('admin.dashboard') : route('organizations.dashboard') }}"><x-brand-logo variant="mark" animate="idle" :height="32" class="dark-logo-2" /></a>
            </div>
            <div class="app-sidebar__toggle" data-toggle="sidebar">
                <a class="open-toggle" href="#" aria-label="{{ $isRtl ? 'فتح القائمة' : 'Open navigation' }}"><i class="header-icon fe fe-align-left"></i></a>
                <a class="close-toggle" href="#" aria-label="{{ $isRtl ? 'إغلاق القائمة' : 'Close navigation' }}"><i class="header-icons fe fe-x"></i></a>
            </div>
        </div>

        @if($channelOptions)
        <div class="ip-channel-switcher">
            <label class="sr-only" for="workspace-channel">{{ __('Switch channel') }}</label>
            <select id="workspace-channel" class="form-control" data-placeholder="{{ $isRtl ? 'ابحث عن قناة...' : 'Search and switch channel…' }}">
                <option value=""></option>
                @foreach($channelOptions as $option)
                <option value="{{ $option['url'] }}" @selected($selectedChannelId === $option['id'])>{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>
        @else
        <div class="ip-workspace-context">
            <span class="ip-context-dot" aria-hidden="true"></span>
            <span>{{ $user->hasRole('super_admin') ? ($isRtl ? 'إدارة المنصة' : 'Platform workspace') : ($user->currentOrganization?->name ?? ($isRtl ? 'مساحة العمل' : 'Workspace')) }}</span>
        </div>
        @endif
        <div class="main-header-right">
            <ul class="nav">
                {{-- Theme toggle --}}
                <li>
                    <a class="new nav-link" href="#" id="valexThemeToggle" aria-label="{{ $isRtl ? 'تغيير المظهر' : 'Toggle theme' }}" title="{{ __('Toggle Theme') }}">
                        <i class="fe fe-moon header-icon-svgs"></i>
                    </a>
                </li>
            </ul>

            {{-- Notifications (same items and endpoint as the phone bell: docs/27) --}}
            <div class="nav-item dvn" id="dvNotif" data-latest-url="{{ route('user.notifications.latest') }}" data-read-all-url="{{ route('user.notifications.read-all') }}">
                <button type="button" class="new nav-link dvn-btn {{ $navUnreadCount ? 'has-unread' : '' }}" id="dvNotifBtn" aria-haspopup="dialog" aria-expanded="false" aria-controls="dvNotifPanel" aria-label="{{ __('Notifications') }}">
                    <i class="fe fe-bell header-icon-svgs"></i>
                    <span class="dvn-badge" id="dvNotifBadge" @unless ($navUnreadCount) hidden @endunless>{{ $navUnreadCount > 99 ? '99+' : $navUnreadCount }}</span>
                </button>
                <div class="dvn-panel" id="dvNotifPanel" role="dialog" aria-label="{{ __('Notifications') }}" hidden>
                    <div class="dvn-head">
                        <div>
                            <strong>{{ __('Notifications') }}</strong>
                            <small id="dvNotifSub">{{ trans_choice('{0} You\'re all caught up|{1} 1 unread|[2,*] :count unread', $navUnreadCount, ['count' => $navUnreadCount]) }}</small>
                        </div>
                        <button type="button" class="dvn-all" id="dvNotifReadAll"><i class="fe fe-check-circle"></i> {{ __('Mark all read') }}</button>
                    </div>
                    <div class="dvn-list np-list" id="dvNotifList">
                        @include('notifications._panel-items', ['items' => $navNotifications])
                    </div>
                    <a class="dvn-foot" href="{{ route('user.notifications') }}">{{ __('See all notifications') }} <i class="fe fe-arrow-right"></i></a>
                </div>
            </div>
            <div class="nav-item full-screen fullscreen-button">
                <a class="new nav-link full-screen-link" href="#" aria-label="{{ $isRtl ? 'ملء الشاشة' : 'Full screen' }}"><i class="fe fe-maximize header-icon-svgs"></i></a>
            </div>

            {{-- Profile --}}
            <div class="dropdown main-profile-menu nav nav-item nav-link">
                <a class="profile-user d-flex" href="#" aria-label="{{ $isRtl ? 'قائمة الحساب' : 'Account menu' }}">
                    @if ($user->avatar_path)
                        <img alt="{{ $user->name }}" src="{{ Storage::url($user->avatar_path) }}">
                    @else
                        <span class="avatar brround bg-primary">{{ $user->initials }}</span>
                    @endif
                </a>
                <div class="dropdown-menu">
                    <div class="main-header-profile bg-primary p-3">
                        <div class="d-flex wd-100p">
                            <div class="my-auto">
                                <h6>{{ $user->full_name }}</h6>
                                <span>{{ $user->display_role }}</span>
                            </div>
                        </div>
                    </div>
                    <a class="dropdown-item" href="{{ route('profile.settings') }}"><i class="bx bx-cog"></i> {{ __('Settings') }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item"><i class="bx bx-log-out"></i> {{ __('Logout') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
