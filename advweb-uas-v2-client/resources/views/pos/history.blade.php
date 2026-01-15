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
@include('partials.navbar')


<div class="container py-4">
    <h4 class="fw-bold mb-3"><i class="fas fa-clock me-2"></i>Transaction History</h4>
    
    <!-- FILTERS -->
    <form method="GET" action="{{ url('/pos/history') }}" class="filter-bar d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div class="d-flex gap-2 flex-wrap">
            <select name="month" class="form-select form-select-sm" style="width: auto;">
                <option value="">All Months</option>
                @for($i = 1; $i <= 12; $i++)
                    <option value="{{ $i }}" {{ ($filters['month'] ?? '') == $i ? 'selected' : '' }}>{{ date('F', mktime(0, 0, 0, $i, 1)) }}</option>
                @endfor
            </select>
            <select name="year" class="form-select form-select-sm" style="width: auto;">
                <option value="">All Years</option>
                @for($i = 0; $i < 5; $i++)
                    @php $year = date('Y') - $i; @endphp
                    <option value="{{ $year }}" {{ ($filters['year'] ?? '') == $year ? 'selected' : '' }}>{{ $year }}</option>
                @endfor
            </select>
            <button type="submit" class="btn btn-sm btn-primary">
                <i class="fas fa-filter me-1"></i> Apply
            </button>
        </div>
        
        <div class="d-flex gap-2 align-items-center">
             <select name="sort_by" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="order_date" {{ ($filters['sort_by'] ?? 'order_date') == 'order_date' ? 'selected' : '' }}>Date</option>
                <option value="total_amount" {{ ($filters['sort_by'] ?? '') == 'total_amount' ? 'selected' : '' }}>Amount</option>
             </select>
             <select name="sort_dir" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="desc" {{ ($filters['sort_dir'] ?? 'desc') == 'desc' ? 'selected' : '' }}>Newest/Highest</option>
                <option value="asc" {{ ($filters['sort_dir'] ?? '') == 'asc' ? 'selected' : '' }}>Oldest/Lowest</option>
             </select>
        </div>
    </form>

    <!-- HISTORY LIST -->
    <div id="history-list" class="row g-2">
        @if(count($orders) === 0)
            <div class="col-12 text-center text-muted py-5">No transactions found for this selection.</div>
        @else
            @foreach($orders as $order)
            <div class="col-12">
                <div class="card history-card border shadow-sm p-3" onclick="openOrderModal({{ $order['id'] }})">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center">
                             <div class="bg-light rounded-circle p-3 me-3 text-primary">
                                <i class="fas fa-receipt fa-lg"></i>
                             </div>
                             <div>
                                <h6 class="fw-bold mb-1">Order #{{ $order['id'] }}</h6>
                                <p class="mb-0 small text-muted">
                                    <i class="far fa-calendar-alt me-1"></i> {{ \Carbon\Carbon::parse($order['order_date'])->format('d M Y, H:i') }}
                                    <span class="mx-2">•</span>
                                    <i class="fas fa-chair me-1"></i> Table {{ $order['table_number'] ?? '-' }}
                                </p>
                             </div>
                        </div>
                        <div class="text-end">
                            <h5 class="fw-bold mb-1 text-success">Rp {{ number_format($order['total_amount'], 0, ',', '.') }}</h5>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill">PAID</span>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        @endif
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
    // Pre-loaded data from controller (via Guzzle)
    let allOrders = @json($orders);
    let currentOrderId = null;
    const apiToken = "{{ $token }}";
    const apiUrl = "{{ $apiUrl }}";

    function openOrderModal(orderId) {
        const order = allOrders.find(o => o.id === orderId);
        if(!order) return;

        currentOrderId = orderId;
        const modalBody = document.getElementById('modal-body-content');
        
        let itemsHtml = '';
        if (order.order_items) {
            itemsHtml = order.order_items.map(i => {
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
            }).join('');
        }

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
            <div class="mb-3">${itemsHtml}</div>
            
            <div class="border-top pt-2">
                <div class="d-flex justify-content-between small text-muted mb-1">
                    <span>Subtotal</span>
                    <span>Rp ${Number(order.subtotal).toLocaleString()}</span>
                </div>
                ${parseFloat(order.global_discount_percent) > 0 ? `
                <div class="d-flex justify-content-between small text-danger mb-1">
                    <span>Global Discount (${Number(order.global_discount_percent)}%)</span>
                    <span>-Rp ${Number(order.subtotal - (order.total_amount - order.tax_amount)).toLocaleString()}</span>
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
           const res = await fetch(`${apiUrl}/orders/${orderId}/print`, { 
               method: 'POST',
               headers: {
                   'Authorization': `Bearer ${apiToken}`,
                   'Accept': 'application/json'
               }
           });
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
</script>
</body>
</html>
