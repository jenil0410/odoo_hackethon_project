<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <div class="mdi mdi-close close-menu"></div>
        <a href="{{ route('dashboard') }}" class="app-brand-link">
            <span class="sidebar-logo-wrap">
                <img src="{{ asset('assets/img/branding/main-logo.png') }}" class="sidebar-logo" alt="EcoTrace Logo">
            </span>
        </a>
    </div>

    <ul class="menu-inner py-1">
        <li class="menu-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-home-outline"></i>
                <div>Dashboard</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('user.*') ? 'active' : '' }}">
            <a href="{{ route('user.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-account-outline"></i>
                <div>User Management</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('role.*') ? 'active' : '' }}">
            <a href="{{ route('role.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-shield-account-outline"></i>
                <div>Role Management</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('activity-log.*') ? 'active' : '' }}">
            <a href="{{ route('activity-log.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-history"></i>
                <div>Activity Logs</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('vehicle-registry.*') ? 'active' : '' }}">
            <a href="{{ route('vehicle-registry.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-truck-outline"></i>
                <div>Vehicle Registry</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('driver.*') ? 'active' : '' }}">
            <a href="{{ route('driver.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-steering"></i>
                <div>Driver Management</div>
            </a>
        </li>

        <li class="menu-item {{ request()->routeIs('trip.*') ? 'active' : '' }}">
            <a href="{{ route('trip.index') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-map-marker-path"></i>
                <div>Trip Management</div>
            </a>
        </li>

        <li class="menu-item">
            <a href="{{ route('logout') }}" class="menu-link" onclick="event.preventDefault();document.getElementById('logout-form').submit();">
                <i class="menu-icon tf-icons mdi mdi-logout"></i>
                <div>Logout</div>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
            </a>
        </li>
    </ul>
</aside>
