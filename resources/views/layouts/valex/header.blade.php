@php
    $user = auth()->user();
    $logo = fn (string $name) => asset('assets/valex/img/brand/' . $name);
    $navNotifications = $user->notifications()->latest()->take(8)->get();
    $navUnreadCount = $navNotifications->whereNull('read_at')->count();
@endphp

<div class="main-header sticky side-header nav nav-item">
    <div class="container-fluid">
        <div class="main-header-left">
            <div class="responsive-logo">
                <a href="{{ route('organizations.dashboard') }}"><img src="{{ $logo('logo.png') }}" class="logo-1" alt="i-Page"></a>
                <a href="{{ route('organizations.dashboard') }}"><img src="{{ $logo('logo-white.png') }}" class="dark-logo-1" alt="i-Page"></a>
                <a href="{{ route('organizations.dashboard') }}"><img src="{{ $logo('favicon.png') }}" class="logo-2" alt="i-Page"></a>
                <a href="{{ route('organizations.dashboard') }}"><img src="{{ $logo('favicon-white.png') }}" class="dark-logo-2" alt="i-Page"></a>
            </div>
            <div class="app-sidebar__toggle" data-toggle="sidebar">
                <a class="open-toggle" href="#"><i class="header-icon fe fe-align-left"></i></a>
                <a class="close-toggle" href="#"><i class="header-icons fe fe-x"></i></a>
            </div>
        </div>

        <div class="main-header-right">
            <ul class="nav">
                {{-- Theme toggle --}}
                <li>
                    <a class="new nav-link" href="#" id="valexThemeToggle" title="{{ __('Toggle Theme') }}">
                        <i class="fe fe-moon header-icon-svgs"></i>
                    </a>
                </li>
            </ul>

            {{-- Notifications --}}
            <div class="dropdown nav-item main-header-notification">
                <a class="new nav-link" href="#" data-toggle="dropdown" aria-label="{{ __('Notifications') }}">
                    <i class="fe fe-bell header-icon-svgs"></i>
                    @if ($navUnreadCount > 0)<span class="pulse"></span>@endif
                </a>
                <div class="dropdown-menu">
                    <div class="menu-header-content bg-primary">
                        <h6 class="dropdown-title mb-1 tx-15 text-white font-weight-semibold">{{ __('Notifications') }}</h6>
                        <p class="dropdown-title-text subtext mb-0 text-white op-6 pb-0 tx-12">
                            {{ trans_choice('{0} No unread notifications|{1} You have 1 unread notification|[2,*] You have :count unread notifications', $navUnreadCount, ['count' => $navUnreadCount]) }}
                        </p>
                    </div>
                    <div class="main-notification-list Notification-scroll">
                        @forelse ($navNotifications as $notification)
                            <a class="d-flex p-3 border-bottom" href="{{ $notification->data['link'] ?? route('user.notifications') }}">
                                <div class="notifyimg {{ $notification->isRead() ? 'bg-secondary' : 'bg-primary' }}">
                                    <i class="la la-bell text-white"></i>
                                </div>
                                <div class="mx-3">
                                    <h5 class="notification-label mb-1">{{ $notification->data['message'] ?? ucfirst(str_replace('_', ' ', $notification->type)) }}</h5>
                                    <div class="notification-subtext">{{ $notification->created_at->diffForHumans() }}</div>
                                </div>
                            </a>
                        @empty
                            <div class="p-4 text-center text-muted">{{ __('No notifications yet') }}</div>
                        @endforelse
                    </div>
                    <div class="dropdown-footer">
                        <a href="{{ route('user.notifications') }}">{{ __('View All') }}</a>
                    </div>
                </div>
            </div>

            <div class="nav-item full-screen fullscreen-button">
                <a class="new nav-link full-screen-link" href="#"><i class="fe fe-maximize header-icon-svgs"></i></a>
            </div>

            {{-- Profile --}}
            <div class="dropdown main-profile-menu nav nav-item nav-link">
                <a class="profile-user d-flex" href="#">
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
