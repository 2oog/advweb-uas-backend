<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Resto POS</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
    </style>
</head>
<body>

<!-- TOP NAVBAR -->
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
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/') }}">
                        <i class="fas fa-cash-register me-1"></i> Order
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ url('/pos/history') }}">
                        <i class="fas fa-history me-1"></i> History
                    </a>
                </li>
                @if(auth()->user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link text-warning fw-bold active" href="{{ url('/admin/dashboard') }}">
                        <i class="fas fa-chart-line me-1"></i> Admin Dashboard
                    </a>
                </li>
                @endif
                <li class="nav-item">
                    <a class="nav-link text-danger fw-bold" href="{{ route('logout') }}">
                        <i class="fas fa-sign-out-alt me-1"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0"><i class="fas fa-chart-pie me-2"></i>Sales Dashboard</h4>
        <span class="text-muted">Welcome, {{ auth()->user()->name }}</span>
    </div>

    <div class="row g-4 mb-4">
        <!-- Sales Card -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Orders</h5>
                    <h2 class="display-4 fw-bold text-dark" id="total-orders">-</h2>
                </div>
            </div>
        </div>
        <!-- Revenue Card -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Revenue</h5>
                    <h2 class="display-4 fw-bold text-success" id="total-revenue">-</h2>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    async function loadStats() {
        try {
            const token = "{{ auth()->user()->createToken('dashboard')->plainTextToken }}"; 

            const res = await fetch('{{ url("/api/admin/sales") }}', {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Accept': 'application/json'
                }
            });

            if (!res.ok) throw new Error('Failed to load');
            const data = await res.json();

            document.getElementById('total-orders').innerText = data.total_orders;
            document.getElementById('total-revenue').innerText = 'Rp ' + Number(data.total_revenue).toLocaleString();
            document.getElementById('loading-msg').style.display = 'none';
        } catch (e) {
            console.error(e);
            document.getElementById('loading-msg').innerText = 'Error loading data.';
        }
    }

    loadStats();
</script>

</body>
</html>
