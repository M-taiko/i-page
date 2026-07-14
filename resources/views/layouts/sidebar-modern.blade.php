<style>
    /* Sidebar Styles */
    .sidebar-modern {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        width: 260px;
        background: var(--surface-bg);
        border-right: 1px solid var(--surface-border);
        display: flex !important;
        flex-direction: column;
        z-index: var(--z-fixed);
        overflow-y: auto;
        transition: transform var(--transition-base);
        visibility: visible !important;
        transform: translateX(0) !important;
    }

    @media (max-width: 1024px) {
        .sidebar-modern {
            width: 240px;
        }
    }

    @media (max-width: 768px) {
        .sidebar-modern {
            position: fixed;
            left: 0;
            top: 0;
            width: 240px;
            transform: translateX(-100%);
        }

        .sidebar-modern.show {
            transform: translateX(0);
        }
    }

    .sidebar-header {
        padding: var(--space-6);
        border-bottom: 1px solid var(--surface-border);
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }

    .sidebar-logo {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-lg);
        background: linear-gradient(135deg, var(--primary-600), var(--secondary-600));
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: var(--font-weight-bold);
        font-size: var(--text-lg);
    }

    .sidebar-title {
        margin: 0;
        font-size: var(--text-base);
        font-weight: var(--font-weight-bold);
        color: var(--text-primary);
    }

    .sidebar-subtitle {
        margin: 0;
        font-size: var(--text-xs);
        color: var(--text-tertiary);
    }

    .sidebar-content {
        flex: 1;
        padding: var(--space-4);
        overflow-y: auto;
        display: block !important;
        visibility: visible !important;
    }

    .sidebar-section {
        margin-bottom: var(--space-6);
        display: block;
    }

    .sidebar-section-title {
        font-size: var(--text-xs);
        font-weight: var(--font-weight-semibold);
        text-transform: uppercase;
        color: var(--text-tertiary);
        margin: var(--space-4) 0 var(--space-2);
        padding: 0 var(--space-3);
        letter-spacing: 0.5px;
        display: block;
    }

    .sidebar-nav {
        display: flex !important;
        flex-direction: column;
        gap: var(--space-2);
        visibility: visible !important;
    }

    .sidebar-item {
        display: flex !important;
        align-items: center;
        gap: var(--space-3);
        padding: var(--space-3) var(--space-4);
        border-radius: var(--radius-lg);
        color: var(--text-secondary);
        text-decoration: none;
        transition: all var(--transition-fast);
        cursor: pointer;
        font-size: var(--text-sm);
        font-weight: var(--font-weight-medium);
        border: none;
        background: none;
        width: 100%;
        text-align: left;
        visibility: visible !important;
    }

    .sidebar-item:hover {
        color: var(--text-primary);
        background-color: var(--surface-hover);
    }

    .sidebar-item.active {
        color: var(--primary-600);
        background-color: var(--primary-50);
        border-left: 3px solid var(--primary-600);
        padding-left: calc(var(--space-4) - 3px);
    }

    .sidebar-item i {
        font-size: var(--text-lg);
        width: 20px;
    }

    .sidebar-footer {
        padding: var(--space-6);
        border-top: 1px solid var(--surface-border);
        display: flex;
        flex-direction: column;
        gap: var(--space-4);
    }

    .sidebar-user {
        display: flex;
        align-items: center;
        gap: var(--space-3);
    }

    .sidebar-user-avatar {
        width: 40px;
        height: 40px;
        border-radius: var(--radius-lg);
        background: linear-gradient(135deg, var(--primary-100), var(--secondary-100));
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: var(--font-weight-bold);
        color: var(--primary-700);
        font-size: var(--text-sm);
        overflow: hidden;
    }

    .sidebar-user-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .sidebar-user-info {
        flex: 1;
    }

    .sidebar-user-name {
        font-size: var(--text-sm);
        font-weight: var(--font-weight-semibold);
        color: var(--text-primary);
        margin: 0;
    }

    .sidebar-user-role {
        font-size: var(--text-xs);
        color: var(--text-tertiary);
        margin: 0;
    }

    .sidebar-user-menu {
        display: flex;
        gap: var(--space-2);
    }

    .sidebar-user-btn {
        background: none;
        border: none;
        color: var(--text-secondary);
        cursor: pointer;
        font-size: var(--text-lg);
        padding: var(--space-2);
        border-radius: var(--radius-lg);
        transition: all var(--transition-fast);
    }

    .sidebar-user-btn:hover {
        color: var(--text-primary);
        background-color: var(--surface-hover);
    }

    /* Sidebar Toggle Button */
    .sidebar-toggle {
        display: none;
        position: fixed;
        top: var(--space-4);
        left: var(--space-4);
        z-index: var(--z-fixed) + 1;
        background: var(--surface-bg);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-lg);
        padding: var(--space-2);
        cursor: pointer;
        font-size: var(--text-lg);
    }

    @media (max-width: 768px) {
        .sidebar-toggle {
            display: flex;
        }
    }

    /* Overlay for mobile */
    .sidebar-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: calc(var(--z-fixed) - 1);
    }

    @media (max-width: 768px) {
        .sidebar-overlay.show {
            display: block;
        }
    }

    /* Scrollbar */
    .sidebar-content::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-content::-webkit-scrollbar-track {
        background: transparent;
    }

    .sidebar-content::-webkit-scrollbar-thumb {
        background: var(--neutral-300);
        border-radius: var(--radius-full);
    }

    .sidebar-content::-webkit-scrollbar-thumb:hover {
        background: var(--neutral-400);
    }
</style>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

<!-- Sidebar -->
<aside class="sidebar-modern" id="sidebarModern">
    <!-- Header -->
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <i class="bi bi-globe"></i>
        </div>
        <div>
            <h3 class="sidebar-title">IPAGE</h3>
            <p class="sidebar-subtitle">
                @if(auth()->check() && auth()->user()->hasRole('super_admin'))
                    Super Admin
                @elseif(isset($currentOrganization) && $currentOrganization)
                    {{ $currentOrganization->name }}
                @else
                    Organization Hub
                @endif
            </p>
        </div>
    </div>


    <!-- Navigation -->
    <div class="sidebar-content">
        <!-- Main Section -->
        <div class="sidebar-section">
            <nav class="sidebar-nav">
                <a href="{{ route('user.feed') }}" @class(['sidebar-item', 'active' => request()->routeIs('user.feed')])>
                    <i class="bi bi-newspaper"></i>
                    <span>{{ __('Feed') }}</span>
                </a>
            </nav>
        </div>

        <!-- Communication Section -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('Communication') }}</div>
            <nav class="sidebar-nav">
                <a href="{{ route('posts.index') }}" @class(['sidebar-item', 'active' => request()->routeIs('posts.*')])>
                    <i class="bi bi-file-text"></i>
                    <span>{{ __('Posts') }}</span>
                </a>
                <a href="{{ route('audience-segments.index') }}" @class(['sidebar-item', 'active' => request()->routeIs('audience-segments.*')])>
                    <i class="bi bi-people"></i>
                    <span>{{ __('Audiences') }}</span>
                </a>
            </nav>
        </div>

        <!-- CRM Section -->
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('CRM') }}</div>
            <nav class="sidebar-nav">
                <a href="{{ route('tickets.index') }}" @class(['sidebar-item', 'active' => request()->routeIs('tickets.*')])>
                    <i class="bi bi-ticket"></i>
                    <span>{{ __('Tickets') }}</span>
                </a>
            </nav>
        </div>

        <!-- Organization Section -->
        @if(auth()->user()->organizations()->exists() || auth()->user()->hasRole('super_admin'))
        @php
            $sidebarOrg = auth()->user()->currentOrganization;
        @endphp
        <div class="sidebar-section">
            <div class="sidebar-section-title">
                {{ __('Organization') }}
                @if(auth()->user()->hasRole('super_admin') && $sidebarOrg)
                    <span style="font-weight: normal; color: var(--text-tertiary);">— {{ $sidebarOrg->name }}</span>
                @endif
            </div>
            <nav class="sidebar-nav">
                <a href="{{ route('organizations.dashboard') }}" @class(['sidebar-item', 'active' => request()->routeIs('organizations.dashboard')])>
                    <i class="bi bi-graph-up"></i>
                    <span>{{ __('Dashboard') }}</span>
                </a>
                <a href="{{ route('tenant.channels.index') }}" @class(['sidebar-item', 'active' => request()->routeIs('tenant.channels.*')])>
                    <i class="bi bi-chat-dots"></i>
                    <span>{{ __('Channels') }}</span>
                </a>
                @if($sidebarOrg)
                    <a href="{{ route('organizations.brands.index', $sidebarOrg) }}" @class(['sidebar-item', 'active' => request()->routeIs('organizations.brands.*')])>
                        <i class="bi bi-bookmark-star"></i>
                        <span>{{ __('Brands') }}</span>
                    </a>
                    @can('user.manage')
                        <a href="{{ route('dashboard.users.index', $sidebarOrg) }}" @class(['sidebar-item', 'active' => request()->routeIs('dashboard.users.*')])>
                            <i class="bi bi-people"></i>
                            <span>{{ __('Team') }}</span>
                        </a>
                    @endcan
                @endif
                <a href="{{ route('organizations.settings') }}" @class(['sidebar-item', 'active' => request()->routeIs('organizations.settings')])>
                    <i class="bi bi-gear-wide-connected"></i>
                    <span>{{ __('Settings') }}</span>
                </a>
            </nav>
        </div>
        @endif

        <!-- Followed Organizations Section -->
        @php
            $followedOrganizations = auth()->user()->followedOrganizations()->get();
        @endphp
        @if($followedOrganizations->count() > 0)
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('Following') }}</div>
            <nav class="sidebar-nav">
                @foreach($followedOrganizations as $org)
                <a href="{{ route('dashboard.home', $org->id) }}" @class(['sidebar-item', 'active' => request()->segment(2) == $org->id])>
                    <i class="bi bi-building"></i>
                    <span>{{ $org->name }}</span>
                </a>
                @endforeach
            </nav>
        </div>
        @endif

        <!-- Admin Section -->
        @if(auth()->user()->hasRole('super_admin'))
        <div class="sidebar-section">
            <div class="sidebar-section-title">{{ __('Admin') }}</div>
            <nav class="sidebar-nav">
                <a href="{{ route('admin.organizations.index') }}" @class(['sidebar-item', 'active' => request()->routeIs('admin.*')])>
                    <i class="bi bi-shield-check"></i>
                    <span>{{ __('Organizations') }}</span>
                </a>
            </nav>
        </div>
        @endif
    </div>

    <!-- Footer -->
    <div class="sidebar-footer">
        <!-- User Info -->
        <div class="sidebar-user">
            <div class="sidebar-user-avatar">
                @if (auth()->user()->avatar_path)
                    <img src="{{ Storage::url(auth()->user()->avatar_path) }}" alt="{{ auth()->user()->name }}">
                @else
                    {{ auth()->user()->initials }}
                @endif
            </div>
            <div class="sidebar-user-info">
                <h4 class="sidebar-user-name">{{ auth()->user()->full_name }}</h4>
                <p class="sidebar-user-role">{{ auth()->user()->display_role }}</p>
            </div>
        </div>

        <!-- User Actions -->
        <a href="{{ route('profile.settings') }}" class="sidebar-item">
            <i class="bi bi-gear"></i>
            <span>{{ __('Settings') }}</span>
        </a>

        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
            @csrf
        </form>
        <button type="button" class="sidebar-item" style="color: var(--danger-600); border: none; background: none; cursor: pointer; width: 100%; text-align: left;" onclick="document.getElementById('logout-form').submit();">
            <i class="bi bi-box-arrow-right"></i>
            <span>{{ __('Logout') }}</span>
        </button>
    </div>
</aside>

<!-- Sidebar Toggle Button -->
<button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
    <i class="bi bi-list"></i>
</button>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebarModern');
        const overlay = document.getElementById('sidebarOverlay');
        const toggle = document.getElementById('sidebarToggle');

        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');

        if (sidebar.classList.contains('show')) {
            toggle.innerHTML = '<i class="bi bi-x"></i>';
        } else {
            toggle.innerHTML = '<i class="bi bi-list"></i>';
        }
    }

    // Close sidebar when clicking overlay
    document.getElementById('sidebarOverlay')?.addEventListener('click', toggleSidebar);

    // Close sidebar when clicking sidebar items on mobile
    if (window.innerWidth <= 768) {
        document.querySelectorAll('.sidebar-item').forEach(item => {
            item.addEventListener('click', () => {
                if (window.innerWidth <= 768) {
                    toggleSidebar();
                }
            });
        });
    }

    function switchHotel() {
        const orgId = document.getElementById('hotelSelector').value;
        window.location.href = `/organization/${orgId}/dashboard`;
    }
</script>
