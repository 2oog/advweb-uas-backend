<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="{{ url('/') }}">
            <i class="fas fa-utensils me-2"></i>Resto POS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                @if(isset($user) && ($user['role'] ?? '') === 'admin')
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('admin/dashboard*') ? 'active' : '' }}" href="{{ url('/admin/dashboard') }}">
                        <i class="fas fa-chart-line me-1 text-warning"></i> Admin Dashboard
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                        <i class="fas fa-cash-register me-1"></i> Order
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->is('pos/history*') ? 'active' : '' }}" href="{{ url('/pos/history') }}">
                        <i class="fas fa-history me-1"></i> History
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link {{ request()->is('settings*') ? 'active' : '' }}" href="{{ url('/settings') }}">
                        <i class="fas fa-cog me-1"></i> Settings
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-danger fw-bold" href="{{ route('logout') }}">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>
