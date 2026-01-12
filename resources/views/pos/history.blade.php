<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - Resto POS</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .history-card { cursor: pointer; transition: transform 0.2s; }
        .history-card:hover { transform: translateY(-2px); border-color: #0d6efd !important; }
        .filter-bar { background: white; border-radius: 10px; padding: 15px; margin-bottom: 20px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
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
                    <a class="nav-link active" href="{{ url('/pos/history') }}">
                        <i class="fas fa-history me-1"></i> History
                    </a>
                </li>
                @if(auth()->user()->isAdmin())
                <li class="nav-item">
                    <a class="nav-link text-warning" href="{{ url('/admin/dashboard') }}">
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
    <h4 class="fw-bold mb-3"><i class="fas fa-clock me-2"></i>Transaction History</h4>
    
    <!-- FILTERS -->
    <div class="filter-bar d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div class="d-flex gap-2 flex-wrap">
            <select id="filter-month" class="form-select form-select-sm" style="width: auto;">
                <option value="">All Months</option>
                <option value="1">January</option>
                <option value="2">February</option>
                <option value="3">March</option>
                <option value="4">April</option>
                <option value="5">May</option>
                <option value="6">June</option>
                <option value="7">July</option>
                <option value="8">August</option>
                <option value="9">September</option>
                <option value="10">October</option>
                <option value="11">November</option>
                <option value="12">December</option>
            </select>
            <select id="filter-year" class="form-select form-select-sm" style="width: auto;">
                <option value="">All Years</option>
                <script>
                    const currentYear = new Date().getFullYear();
                    for(let i = 0; i < 5; i++) {
                        document.write(`<option value="${currentYear - i}">${currentYear - i}</option>`);
                    }
                </script>
            </select>
             <button class="btn btn-sm btn-primary" onclick="fetchHistory()">
                <i class="fas fa-filter me-1"></i> Apply
            </button>
        </div>
        
        <div class="d-flex gap-2 align-items-center">
             <select id="sort-by" class="form-select form-select-sm" onchange="fetchHistory()">
                <option value="order_date-desc">Newest First</option>
                <option value="order_date-asc">Oldest First</option>
                <option value="total_amount-desc">Highest Amount</option>
                <option value="total_amount-asc">Lowest Amount</option>
             </select>
        </div>
    </div>

    <!-- HISTORY LIST -->
    <div id="history-list" class="row g-2">
        <!-- Injected via JS -->
        <div class="text-center py-5 text-muted">Loading history...</div>
    </div>
</div>

<!-- ORDER DETAIL MODAL -->
<div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold">Order Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal-body-content">
                <!-- Content -->
            </div>
            <div class="modal-footer justify-content-between">
                 <button type="button" class="btn btn-outline-secondary" onclick="requestPrint(currentOrderId)">
                    <i class="fas fa-print me-1"></i> Reprint
                </button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container"></div>

<script>
    let allOrders = [];
    let currentOrderId = null;
    const apiToken = "{{ $token }}";

    async function fetchHistory() {
        const month = document.getElementById('filter-month').value;
        const year = document.getElementById('filter-year').value;
        const sort = document.getElementById('sort-by').value.split('-');
        
        const params = new URLSearchParams();
        if(month) params.append('month', month);
        if(year) params.append('year', year);
        params.append('sort_by', sort[0]);
        params.append('sort_dir', sort[1]);

        const listEl = document.getElementById('history-list');
        listEl.innerHTML = '<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div></div>';

        try {
            const res = await fetch(`{{ url("/api/orders") }}?${params.toString()}`, {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${apiToken}`
                }
            });
            if(!res.ok) throw new Error('API Error');
            
            allOrders = await res.json();
            renderHistory(allOrders);
        } catch (e) {
            console.error(e);
            listEl.innerHTML = '<p class="text-center text-danger py-5">Failed to load history.</p>';
        }
    }

    function renderHistory(orders) {
        const listEl = document.getElementById('history-list');
        
        if (orders.length === 0) {
            listEl.innerHTML = '<div class="col-12 text-center text-muted py-5">No transactions found for this selection.</div>';
            return;
        }

        const html = orders.map(order => `
            <div class="col-12">
                <div class="card history-card border shadow-sm p-3" onclick="openOrderModal(${order.id})">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                             <div class="bg-light rounded-circle p-3 me-3 text-primary">
                                <i class="fas fa-receipt fa-lg"></i>
                             </div>
                             <div>
                                <h6 class="fw-bold mb-1">Order #${order.id}</h6>
                                <p class="mb-0 small text-muted">
                                    <i class="far fa-calendar-alt me-1"></i> ${new Date(order.order_date).toLocaleString()}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-chair me-1"></i> Table ${order.table_number || '-'}
                                </p>
                             </div>
                        </div>
                        <div class="text-end">
                            <h5 class="fw-bold mb-1 text-success">Rp ${Number(order.total_amount).toLocaleString()}</h5>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill">PAID</span>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        listEl.innerHTML = html;
    }

    function openOrderModal(orderId) {
        const order = allOrders.find(o => o.id === orderId);
        if(!order) return;

        currentOrderId = orderId;
        const modalBody = document.getElementById('modal-body-content');
        
        modalBody.innerHTML = `
            <div class="text-center mb-4">
                <h3 class="fw-bold mb-0">Rp ${Number(order.total_amount).toLocaleString()}</h3>
                <p class="text-muted small">Total Payment</p>
            </div>
            
            <ul class="list-group list-group-flush mb-3">
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Order ID</span>
                    <span class="fw-bold">#${order.id}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Date & Time</span>
                    <span class="fw-bold">${new Date(order.order_date).toLocaleString()}</span>
                </li>
                <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Table Number</span>
                    <span class="fw-bold">${order.table_number || '-'}</span>
                </li>
                 <li class="list-group-item d-flex justify-content-between px-0">
                    <span class="text-muted">Payment Method</span>
                    <span class="fw-bold text-uppercase">${order.payment_method || 'CASH'}</span>
                </li>
            </ul>

            <h6 class="fw-bold mb-3 border-bottom pb-2">Items</h6>
            <div class="mb-3">
                ${order.order_items.map(i => {
                    const itemDisc = parseFloat(i.item_discount || 0);
                    return `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <span class="fw-bold d-block">${i.menu_name}</span>
                            <span class="small text-muted">${i.quantity} x Rp ${Number(i.price_at_time).toLocaleString()}</span>
                            ${itemDisc > 0 ? `<div class="small text-danger">Discount: -Rp ${Number(itemDisc).toLocaleString()}</div>` : ''}
                        </div>
                        <span class="fw-bold">Rp ${Number(i.subtotal).toLocaleString()}</span>
                    </div>
                    `;
                }).join('')}
            </div>
            
            <div class="border-top pt-2">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Subtotal</span>
                    <span>Rp ${Number(order.subtotal).toLocaleString()}</span>
                </div>
                ${parseFloat(order.global_discount) > 0 ? `
                <div class="d-flex justify-content-between small text-danger mb-1">
                    <span>Global Discount</span>
                    <span>-Rp ${Number(order.global_discount).toLocaleString()}</span>
                </div>` : ''}
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Total Before Tax</span>
                    <span>Rp ${Number(order.total_amount - order.tax_amount).toLocaleString()}</span>
                </div>
                 <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Tax (${Number(order.tax_percent)}%)</span>
                    <span>Rp ${Number(order.tax_amount).toLocaleString()}</span>
                </div>
                 <div class="d-flex justify-content-between fw-bold fs-5 mt-2">
                    <span>Total</span>
                    <span>Rp ${Number(order.total_amount).toLocaleString()}</span>
                </div>
            </div>
        `;

        new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
    }

    async function requestPrint(orderId) {
       try {
           const res = await fetch(`{{ url("/api/orders") }}/${orderId}/print`, { method: 'POST' });
           if(res.ok) showToast('Print command sent!', 'success');
           else showToast('Print failed (Check server logs)', 'danger');
       } catch (e) {
           showToast('Print connection error', 'danger');
       }
    }

    function showToast(message, type = 'primary') {
        const container = document.getElementById('toast-container');
        const id = 'toast-' + Date.now();
        
        let icon = 'info-circle';
        if(type === 'success') icon = 'check-circle';
        if(type === 'danger') icon = 'exclamation-triangle';
        if(type === 'warning') icon = 'exclamation-circle';

        const html = `
            <div id="${id}" class="toast align-items-center text-white bg-${type} border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="fas fa-${icon} me-2"></i> ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        `;
        
        const temp = document.createElement('div');
        temp.innerHTML = html;
        const toastEl = temp.firstElementChild;
        container.appendChild(toastEl);
        
        const toast = new bootstrap.Toast(toastEl, { delay: 3000 });
        toast.show();
        
        toastEl.addEventListener('hidden.bs.toast', () => {
            toastEl.remove();
        });
    }

    // Init
    // Set default month/year to empty (all) or current? 
    // Let's keep filters optional so they see everything by default, but new design allows selecting.
    fetchHistory();
</script>
</body>
</html>
