<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant POS</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .menu-item-card { cursor: pointer; transition: transform 0.1s; }
        .menu-item-card:active { transform: scale(0.98); }
        .cart-sticky { position: sticky; top: 20px; }
        /* Mobile specific adjustments */
        @media (max-width: 768px) {
            .mobile-hidden { display: none !important; }
            .mobile-only { display: block !important; }
            .cart-sticky { position: static; }
            .view-section { display: none; }
            .view-section.active { display: block; }
            #bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; z-index: 1000; }
        }
        @media (min-width: 769px) {
            .mobile-only { display: none !important; }
        }
        .img-placeholder {
            height: 100px; width: 100px; display: flex; align-items: center; justify-content: center; font-size: 2rem; border-radius: 0.5rem; 
        }

        .no-select {
            -webkit-user-select: none; /* Safari */
            -ms-user-select: none; /* IE 10 and IE 11 */
            user-select: none; /* Standard syntax */
        }
    </style>
</head>
<body>

<!-- TOP NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark sticky-top shadow-sm">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="#">
            <i class="fas fa-utensils me-2"></i>Resto POS
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="{{ url('/') }}">
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

<div class="container-fluid py-3">

    <!-- PAGE: ORDER -->
    <div id="page-order">
        <div class="row">
            
            <!-- LEFT COLUMN: MENU -->
            <div class="col-md-8 view-section active" id="view-menu">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="fw-bold text-dark mb-0">Menu</h4>
                    
                    <!-- Mobile Cart Toggle Button -->
                    <button class="btn btn-primary position-relative mobile-only" onclick="toggleCartMobile()">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="mobile-cart-count">0</span>
                    </button>
                </div>

                <!-- Menu Grid -->
                <div class="row g-3" id="menu-grid">
                    <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
                </div>
            </div>

            <!-- RIGHT COLUMN: CART & PAYMENT (Sticky Desktop) -->
            <div class="col-md-4 view-section mobile-hidden" id="view-cart">
                <div class="card shadow-sm border-0 cart-sticky" style="height: calc(100vh - 100px); overflow-y: auto;">
                    <div class="card-header bg-white border-bottom-0 py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-bold"><i class="fas fa-shopping-cart me-2"></i>Current Order</h5>
                            <button class="btn btn-sm btn-outline-secondary mobile-only" onclick="toggleCartMobile()"><i class="fas fa-times"></i> Close</button>
                        </div>
                    </div>
                    
                    <div class="card-body p-0 d-flex flex-column" style="height: 100%;">
                        <!-- Cart Items -->
                        <div class="grow overflow-auto p-3" id="cart-items">
                            <div class="text-center text-muted mt-5">
                                <i class="fas fa-shopping-basket fa-3x mb-3 opacity-50"></i>
                                <p>Cart is empty</p>
                            </div>
                        </div>

                        <!-- Totals Section -->
                        <div class="p-3 bg-light border-top">
                            <!-- Table Number Input -->
                            <div class="mb-3">
                                <label for="table-number" class="form-label small fw-bold text-muted">Table Number</label>
                                <input type="text" class="form-control" id="table-number" placeholder="e.g. 5A">
                            </div>

                            <div class="row mb-2">
                                <div class="col-6">
                                    <label class="small text-muted">Global Discount (Rp)</label>
                                    <input type="number" class="form-control form-control-sm" id="global-discount" value="0" onchange="renderCart()">
                                </div>
                                <div class="col-6">
                                    <label class="small text-muted">Tax (%)</label>
                                    <input type="number" class="form-control form-control-sm" id="tax-percent" value="10" onchange="renderCart()">
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mb-1">
                                <span>Subtotal</span>
                                <span class="fw-bold" id="summ-subtotal">Rp 0</span>
                            </div>
                            <div class="d-flex justify-content-between mb-1 text-success">
                                <small>Discount</small>
                                <small id="summ-discount">Rp 0</small>
                            </div>
                            <div class="d-flex justify-content-between mb-3 text-muted">
                                <small>Tax</small>
                                <small id="summ-tax">Rp 0</small>
                            </div>
                            <div class="d-flex justify-content-between mb-3">
                                <span class="h5 fw-bold">Total</span>
                                <span class="h4 fw-bold text-primary" id="summ-total">Rp 0</span>
                            </div>
                            
                            <!-- Actions -->
                            <div class="d-grid gap-2">
                                <button class="btn btn-success btn-lg fw-bold" onclick="showPaymentModal()" id="btn-pay" disabled>
                                    Pay Now
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>



</div>

<!-- Edit Item Modal -->
<div class="modal fade" id="editItemModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="edit-item-id">
                <div class="mb-3">
                    <label class="form-label">Menu Name</label>
                    <input type="text" class="form-control" id="edit-menu-name">
                </div>
                <div class="row">
                    <div class="col-6 mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" id="edit-item-price">
                    </div>
                    <div class="col-6 mb-3">
                        <label class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="edit-item-qty" min="1">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Item Discount (Rp)</label>
                    <input type="number" class="form-control" id="edit-item-discount" value="0">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="saveItemChanges()">Save Changes</button>
            </div>
        </div>
    </div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold">Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-4">
                <div id="payment-step-1">
                    <span class="badge bg-primary mb-3">QRIS</span>
                    <h1 class="display-4 fw-bold mb-4" id="pay-modal-total">Rp 0</h1>
                    <div class="bg-white p-3 border rounded d-inline-block mb-3 shadow-sm">
                        <!-- QR Code Placeholder -->
                         <div style="width: 200px; height: 200px; background: url('https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=ExamplePayment') center/cover no-repeat;"></div>
                    </div>
                    <p class="text-muted small">Scan to pay</p>
                    <button class="btn btn-success w-100 py-3 fw-bold mt-3" onclick="processPayment()">
                        <i class="fas fa-check-circle me-2"></i> Simulate Payment Received
                    </button>
                </div>

                <div id="payment-step-success" class="d-none">
                    <div class="mb-4 text-success">
                         <i class="fas fa-check-circle fa-5x"></i>
                    </div>
                    <h3 class="fw-bold mb-2">Payment Successful!</h3>
                    <p class="text-muted">Order ID: #<span id="success-order-id"></span></p>
                    
                    <div class="d-grid gap-2 mt-4">
                        <button class="btn btn-dark" onclick="printReceipt()"><i class="fas fa-print me-2"></i>Print Receipt</button>
                        <button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // --- STATE ---
    let cart = []; 
    let menuItems = [];
    let currentOrderId = null;
    const apiToken = "{{ $token }}";

    // --- API CALLS ---
    async function fetchMenu() {
        try {
            const res = await fetch('{{ url("/api/menu-items") }}', {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': `Bearer ${apiToken}`
                }
            });
            if (!res.ok) throw new Error('Status: ' + res.status);
            menuItems = await res.json();
            renderMenu();
        } catch (e) {
            console.error(e);
            console.error(e);
            showToast('Failed to load menu: ' + e.message, 'danger');
        }
    }



    async function submitOrder() {
        const tableNum = document.getElementById('table-number').value;
        if (!tableNum) {
            showToast('Please enter a table number!', 'warning');
            throw new Error('Table number required');
        }

        // Recalculate everything one last time to be sure
        let subtotal = 0;
        cart.forEach(item => {
            const price = parseFloat(item.price);
            const qty = parseInt(item.qty);
            const disc = parseFloat(item.item_discount || 0);
            subtotal += (price * qty) - disc;
        });

        const globalDiscount = parseFloat(document.getElementById('global-discount').value) || 0;
        const taxPercent = parseFloat(document.getElementById('tax-percent').value) || 0;
        const taxableAmount = Math.max(0, subtotal - globalDiscount);
        const taxAmount = Math.round(taxableAmount * (taxPercent / 100)); // API expects integer usually? Migration said decimal for tax percent, but tax_amount logic in controller was round(). 
        // Migration of taxes: integer tax_amount.
        // Let's send integer tax_amount.
        const totalAmount = Math.round(taxableAmount + taxAmount);

        // API Payload
        const payload = {
            payment_method: 'QRIS',
            table_number: tableNum,
            subtotal: Math.round(subtotal),
            tax_amount: taxAmount,
            total_amount: totalAmount,
            tax_percent: taxPercent,
            global_discount: globalDiscount,
            order_items: cart.map(i => ({
                id: i.id,
                menu_name: i.name, // Manual name override
                quantity: i.qty,
                price: parseInt(i.price), // Manual price
                subtotal: Math.round((i.price * i.qty) - (i.item_discount || 0)), // Manual subtotal per item
                item_discount: parseFloat(i.item_discount || 0)
            }))
        };

        const res = await fetch('{{ url("/api/orders") }}', {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'Authorization': `Bearer ${apiToken}`
            },
            body: JSON.stringify(payload)
        });

        if (!res.ok) {
            const errData = await res.json();
            showToast('Order failed: ' + (errData.message || 'Unknown'), 'danger');
            throw new Error('Order failed');
        }
        return await res.json();
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


    // --- RENDERING ---
    function renderMenu() {
        const grid = document.getElementById('menu-grid');
        grid.innerHTML = menuItems.map(item => {
            const isUrl = item.image_asset && (item.image_asset.startsWith('http') || item.image_asset.includes('/'));
            const imageHtml = isUrl 
                ? `<img src="${item.image_asset}" class="w-100 h-100 rounded" style="object-fit: cover;" alt="${item.name}">`
                : `<i class="fas fa-${item.image_asset || 'utensils'}"></i>`;
            
            return `
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card h-100 border-0 shadow-sm menu-item-card no-select" onclick="addToCart(${item.id})">
                    <div class="card-body p-2 d-flex flex-col align-items-center text-center">
                        <div class="img-placeholder bg-light text-secondary mb-2 overflow-hidden">
                             ${imageHtml}
                        </div>
                        <h6 class="card-title fw-bold text-dark mb-1 text-truncate w-100 no-select">${item.name}</h6>
                        <p class="card-text text-primary fw-bold no-select">Rp ${Number(item.price).toLocaleString()}</p>
                    </div>
                </div>
            </div>
            `;
        }).join('');
    }

    function renderCart() {
        const container = document.getElementById('cart-items');
        if (cart.length === 0) {
            container.innerHTML = `
                <div class="text-center text-muted mt-5">
                    <i class="fas fa-shopping-basket fa-3x mb-3 opacity-50"></i>
                    <p>Cart is empty</p>
                </div>`;
            document.getElementById('btn-pay').disabled = true;
            updateTotals(0, 0, 0, 0);
            updateMobileBadge(0);
            return;
        }

        container.innerHTML = cart.map(item => {
            const itemPrice = parseFloat(item.price);
            const itemQty = parseInt(item.qty);
            const itemDiscount = parseFloat(item.item_discount || 0);
            // Calculate line subtotal: (Price * Qty) - Discount
            const lineSubtotal = (itemPrice * itemQty) - itemDiscount;

            return `
            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                <div class="flex-grow-1">
                    <h6 class="mb-0 fw-bold">${item.name}</h6>
                    <div class="d-flex align-items-center mt-1">
                        <small class="text-muted me-2">Rp ${itemPrice.toLocaleString()} x ${itemQty}</small>
                        ${itemDiscount > 0 ? `<small class="text-danger ms-2">(-Rp ${itemDiscount.toLocaleString()})</small>` : ''}
                    </div>
                </div>
                <div class="d-flex align-items-center">
                    <span class="fw-bold me-3">Rp ${lineSubtotal.toLocaleString()}</span>
                    <button class="btn btn-sm btn-outline-primary border-0 me-1" onclick="openEditModal(${item.id})"><i class="fas fa-edit"></i></button>
                    <button class="btn btn-sm btn-outline-danger border-0" onclick="removeFromCart(${item.id})"><i class="fas fa-trash"></i></button>
                </div>
            </div>
            `;
        }).join('');

        // Calculations
        let subtotal = 0;
        cart.forEach(item => {
            const price = parseFloat(item.price);
            const qty = parseInt(item.qty);
            const disc = parseFloat(item.item_discount || 0);
            subtotal += (price * qty) - disc;
        });

        const globalDiscount = parseFloat(document.getElementById('global-discount').value) || 0;
        const taxPercent = parseFloat(document.getElementById('tax-percent').value) || 0;

        // Apply global discount first? Rules usually: Subtotal -> Discount -> Tax.
        // Let's assume Subtotal is aggregated item totals (already net of item discounts).
        // Then subtract global discount.
        // Then apply tax on the result.
        
        // Instructor requirement usually: (Subtotal - GlobalDiscount) * Tax%
        // Let's stick to standard: Taxable Amount = Subtotal - Global Discount.
        // Ensure taxable amount isn't negative.
        const taxableAmount = Math.max(0, subtotal - globalDiscount);
        const tax = taxableAmount * (taxPercent / 100);
        const total = taxableAmount + tax;

        updateTotals(subtotal, tax, total, globalDiscount);
        document.getElementById('btn-pay').disabled = false;
        
        const totalQty = cart.reduce((acc, item) => acc + item.qty, 0);
        updateMobileBadge(totalQty);
    }

    function updateTotals(sub, tax, total, globalDisc) {
        document.getElementById('summ-subtotal').innerText = 'Rp ' + sub.toLocaleString();
        document.getElementById('summ-discount').innerText = 'Rp ' + globalDisc.toLocaleString();
        document.getElementById('summ-tax').innerText = 'Rp ' + tax.toLocaleString(); // Label text 10% might need update if dynamic
        document.getElementById('summ-total').innerText = 'Rp ' + total.toLocaleString();
        document.getElementById('pay-modal-total').innerText = 'Rp ' + total.toLocaleString();
    }
    
    function updateMobileBadge(count) {
        const badge = document.getElementById('mobile-cart-count');
        badge.innerText = count;
        badge.style.display = count > 0 ? 'inline-block' : 'none';
    }


    // --- ACTIONS ---
    // --- ACTIONS ---
    const editModal = new bootstrap.Modal(document.getElementById('editItemModal'));

    function openEditModal(id) {
        const item = cart.find(c => c.id === id);
        if (!item) return;

        document.getElementById('edit-item-id').value = item.id;
        document.getElementById('edit-menu-name').value = item.name;
        document.getElementById('edit-item-price').value = item.price;
        document.getElementById('edit-item-qty').value = item.qty;
        document.getElementById('edit-item-discount').value = item.item_discount || 0;

        editModal.show();
    }

    function saveItemChanges() {
        const id = parseInt(document.getElementById('edit-item-id').value);
        const name = document.getElementById('edit-menu-name').value;
        const price = parseFloat(document.getElementById('edit-item-price').value);
        const qty = parseInt(document.getElementById('edit-item-qty').value);
        const discount = parseFloat(document.getElementById('edit-item-discount').value);

        const item = cart.find(c => c.id === id);
        if (item) {
            item.name = name;
            item.price = price;
            item.qty = qty;
            item.item_discount = discount;
            renderCart();
            editModal.hide();
        }
    }

    // Removed updateCartQty as we use modal now (or keep for quick qty change if you want, but I replaced the input with buttons usually. 
    // Wait, previous renderCart replaced input with just text and Edit button. So updateCartQty is dead code unless I kept the input.
    // I replaced the input in renderCart with just text display. So I can remove updateCartQty.

    function addToCart(id) {
        const item = menuItems.find(i => i.id === id);
        // Check if existing item in cart matches ID. 
        // Note: If user edits name/price, it's still same ID. 
        // If we want allow "variants", we need unique cart IDs. 
        // For simple manual input, let's assume one entry per Menu ID.
        // If they want to add same item twice with different prices, that requires "unique cart entry id".
        // Current requirement: "edit button ... change menu name".
        // Let's stick to ID based.

        const existing = cart.find(c => c.id === id);
        if (existing) {
            existing.qty++;
        } else {
            cart.push({ 
                ...item, 
                qty: 1, 
                item_discount: 0,
                // price and name are already there
            });
        }
        renderCart();
    }

    function removeFromCart(id) {
        cart = cart.filter(c => c.id !== id);
        renderCart();
    }

    // Mobile Cart Toggle (Within "Order" Page)
    function toggleCartMobile() {
        const cartView = document.getElementById('view-cart');
        const menuView = document.getElementById('view-menu');
        
        if (cartView.classList.contains('mobile-hidden')) {
            // Show Cart
            cartView.classList.remove('mobile-hidden');
            menuView.classList.add('mobile-hidden');
        } else {
            // Show Menu
            cartView.classList.add('mobile-hidden');
            menuView.classList.remove('mobile-hidden');
        }
    }

    // --- MODALS ---
    const paymentModal = new bootstrap.Modal(document.getElementById('paymentModal'));

    function showPaymentModal() {
        // Validation: Empty Cart
        if (cart.length === 0) {
            showToast('Cart is empty!', 'warning');
            return;
        }

        // Validation: Table Number
        const tableNum = document.getElementById('table-number').value;
        if (!tableNum || tableNum.trim() === '') {
            showToast('Please enter a table number!', 'warning');
            return;
        }

        document.getElementById('payment-step-1').classList.remove('d-none');
        document.getElementById('payment-step-success').classList.add('d-none');
        paymentModal.show();
    }
    
    // Note: History logic moved to main page, so showHistoryModal is removed.

    async function processPayment() {
        try {
            const order = await submitOrder();
            currentOrderId = order.id;
            

            document.getElementById('payment-step-1').classList.add('d-none');
            document.getElementById('payment-step-success').classList.remove('d-none');
            document.getElementById('success-order-id').innerText = order.id;
            
            // Clear cart
            cart = [];
            renderCart();
            
        } catch (e) {
            showToast('Payment Processing Failed!', 'danger');
        }
    }
    
    function printReceipt() {
        if(currentOrderId) requestPrint(currentOrderId);
    }

    // --- INIT ---
    fetchMenu();
    updateMobileBadge(0); 
</script>
<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container"></div>

<script>
    function showToast(message, type = 'primary') {
        const container = document.getElementById('toast-container');
        const id = 'toast-' + Date.now();
        
        let icon = 'info-circle';
        if(type === 'success') icon = 'check-circle';
        if(type === 'danger') icon = 'exclamation-triangle';
        if(type === 'warning') icon = 'exclamation-circle';

        const html = `
            <div id="${id}" class="toast align-items-center text-white bg-${type} border-0" role="alert" aria-live="assertive" aria-atomic="true">
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

    // Override generic alert just in case
    window.alert = function(msg) { showToast(msg, 'warning'); }
</script>
</body>
</html>
