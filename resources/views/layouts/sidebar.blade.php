<!-- Sidebar -->
<aside class="bg-primary text-white" style="width: 250px; min-height: 100vh; overflow-y: auto;">
    <div class="p-4">
        <!-- Logo & Title -->
        <div class="d-flex align-items-center mb-4">
            <i class="bi bi-globe fs-3 me-2"></i>
            <div>
                <h5 class="mb-0 text-white">IPAGE</h5>
                <small class="text-light">Organization Hub</small>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="nav flex-column">
            <a href="{{ route('dashboard.home') }}" class="nav-link text-white d-flex align-items-center {{ request()->routeIs('dashboard.home') ? 'active bg-light text-primary' : '' }}">
                <i class="bi bi-house me-2"></i>
                <span>Home</span>
            </a>

            <a href="{{ route('dashboard.channels.create') }}" class="nav-link text-white d-flex align-items-center {{ request()->routeIs('dashboard.channels.create') ? 'active bg-light text-primary' : '' }}">
                <i class="bi bi-plus-circle me-2"></i>
                <span>Create Channel</span>
            </a>

            <a href="{{ route('dashboard.users.index') }}" class="nav-link text-white d-flex align-items-center {{ request()->routeIs('dashboard.users.*') ? 'active bg-light text-primary' : '' }}">
                <i class="bi bi-people me-2"></i>
                <span>Users</span>
            </a>

            <a href="{{ route('dashboard.groups.index') }}" class="nav-link text-white d-flex align-items-center {{ request()->routeIs('dashboard.groups.*') ? 'active bg-light text-primary' : '' }}">
                <i class="bi bi-diagram-3 me-2"></i>
                <span>Groups</span>
            </a>

            <a href="{{ route('dashboard.feeds.index') }}" class="nav-link text-white d-flex align-items-center {{ request()->routeIs('dashboard.feeds.*') ? 'active bg-light text-primary' : '' }}">
                <i class="bi bi-newspaper me-2"></i>
                <span>News Feeds</span>
            </a>

            <a href="{{ route('dashboard.overview') }}" class="nav-link text-white d-flex align-items-center {{ request()->routeIs('dashboard.overview') ? 'active bg-light text-primary' : '' }}">
                <i class="bi bi-graph-up me-2"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('dashboard.settings.show') }}" class="nav-link text-white d-flex align-items-center {{ request()->routeIs('dashboard.settings.*') ? 'active bg-light text-primary' : '' }}">
                <i class="bi bi-gear me-2"></i>
                <span>Settings</span>
            </a>
        </nav>
    </div>

    <!-- User Profile Section -->
    <div class="border-top border-light mt-4 pt-4 px-4">
        <div class="d-flex align-items-center mb-3">
            <div class="bg-light text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px; font-weight: bold;">
                {{ auth()->user()?->initials ?? 'U' }}
            </div>
            <div class="ms-2 flex-grow-1">
                <div class="text-white small fw-bold">{{ auth()->user()?->full_name ?? 'Guest' }}</div>
                <div class="text-light" style="font-size: 11px;">{{ auth()->user()?->display_role ?? 'Guest' }}</div>
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light w-100 d-flex align-items-center justify-content-center">
                <i class="bi bi-box-arrow-right me-1"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>
