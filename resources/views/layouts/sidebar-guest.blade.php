<style>
    .sidebar-modern {
        position: fixed;
        left: 0;
        top: 0;
        bottom: 0;
        width: 260px;
        background: var(--surface-bg);
        border-right: 1px solid var(--surface-border);
        display: flex;
        flex-direction: column;
        z-index: var(--z-fixed);
        overflow-y: auto;
        transition: transform var(--transition-base);
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
    }

    .sidebar-section {
        margin-bottom: var(--space-6);
    }

    .sidebar-section-title {
        font-size: var(--text-xs);
        font-weight: var(--font-weight-semibold);
        text-transform: uppercase;
        color: var(--text-tertiary);
        margin: var(--space-4) 0 var(--space-2);
        padding: 0 var(--space-3);
        letter-spacing: 0.5px;
    }

    .sidebar-nav {
        display: flex;
        flex-direction: column;
        gap: var(--space-2);
    }

    .sidebar-item {
        display: flex;
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

    .sidebar-search {
        padding: var(--space-4);
        border-bottom: 1px solid var(--surface-border);
    }

    .sidebar-search-input {
        width: 100%;
        padding: var(--space-2) var(--space-3);
        border: 1px solid var(--surface-border);
        border-radius: var(--radius-md);
        background-color: var(--surface-bg);
        color: var(--text-primary);
        font-size: var(--text-sm);
    }

    .sidebar-search-input::placeholder {
        color: var(--text-tertiary);
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
            <p class="sidebar-subtitle">Discover</p>
        </div>
    </div>

    <!-- Search Organizations -->
    <div class="sidebar-search">
        <input type="text" id="orgSearch" class="sidebar-search-input" placeholder="Search organizations...">
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
    </div>

    <!-- Footer -->
    <div class="sidebar-footer">
        @if(auth()->check())
        <!-- User Info (Authenticated) -->
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
        <a href="{{ route('user.explore-organizations') }}" class="sidebar-item">
            <i class="bi bi-search"></i>
            <span>{{ __('Browse') }}</span>
        </a>

        <form id="logout-form" method="POST" action="{{ route('logout') }}" style="display: none;">
            @csrf
        </form>
        <button type="button" class="sidebar-item" style="color: var(--danger-600); border: none; background: none; cursor: pointer; width: 100%; text-align: left;" onclick="document.getElementById('logout-form').submit();">
            <i class="bi bi-box-arrow-right"></i>
            <span>{{ __('Logout') }}</span>
        </button>
        @else
        <!-- Login/Register (Unauthenticated) -->
        <a href="{{ route('login') }}" class="sidebar-item" style="text-decoration: none; display: flex; align-items: center; justify-content: center; background: var(--primary-600); color: white; margin-bottom: var(--space-3);">
            <i class="bi bi-box-arrow-in-right"></i>
            <span>{{ __('Login') }}</span>
        </a>
        <a href="{{ route('register') }}" class="sidebar-item" style="text-decoration: none; display: flex; align-items: center; justify-content: center; border: 1px solid var(--primary-600); color: var(--primary-600);">
            <i class="bi bi-person-plus"></i>
            <span>{{ __('Register') }}</span>
        </a>
        @endif
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

    // Search organizations
    document.getElementById('orgSearch')?.addEventListener('keyup', function(e) {
        // TODO: Implement search functionality
        console.log('Search:', this.value);
    });
</script>
