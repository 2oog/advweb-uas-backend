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
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-chart-pie me-2"></i>Sales Dashboard</h4>
            <span class="text-muted">Welcome, {{ auth()->user()->name }}</span>
        </div>
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

    <!-- Menu Management Section -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-utensils me-2"></i>Menu Management</h5>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMenuItemModal">
                <i class="fas fa-plus me-1"></i> Add Menu Item
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 100px;">Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="menu-table-body">
                        <tr><td colspan="4" class="text-center py-4"><div class="spinner-border text-primary"></div></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Menu Item Modal -->
<div class="modal fade" id="addMenuItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add New Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="add-menu-form">
                    <div class="mb-3">
                        <label class="form-label">Menu Name</label>
                        <input type="text" class="form-control" id="menu-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" id="menu-price" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" id="menu-image" accept="image/*">
                        <div class="form-text">Allocated path: public/images/</div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitNewItem()">Save Item</button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Menu Item Modal -->
<div class="modal fade" id="editMenuItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Edit Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="edit-menu-form">
                    <input type="hidden" id="edit-menu-id">
                    <div class="mb-3">
                        <label class="form-label">Menu Name</label>
                        <input type="text" class="form-control" id="edit-menu-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" id="edit-menu-price" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image (Leave empty to keep current)</label>
                        <input type="file" class="form-control" id="edit-menu-image" accept="image/*">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="updateMenuItem()">Update Item</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container"></div>

<script>
    const apiToken = "{{ auth()->user()->createToken('dashboard')->plainTextToken }}";
    let menuItems = [];

    // Toast Function
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

    // Override generic alert
    window.alert = function(msg) { showToast(msg, 'warning'); }

    // --- MAIN LOGIC ---

    async function loadStats() {
        try {
            const res = await fetch('{{ url("/api/admin/sales") }}', {
                headers: { 'Authorization': 'Bearer ' + apiToken, 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Failed to load stats');
            const data = await res.json();
            document.getElementById('total-orders').innerText = data.total_orders;
            document.getElementById('total-revenue').innerText = 'Rp ' + Number(data.total_revenue).toLocaleString();
        } catch (e) { console.error("Stats Error:", e); }
    }

    async function loadMenu() {
        try {
            const res = await fetch('{{ url("/api/menu-items") }}', {
                headers: { 'Authorization': 'Bearer ' + apiToken, 'Accept': 'application/json' }
            });
            if (!res.ok) throw new Error('Failed to load menu: ' + res.status);
            menuItems = await res.json();
            renderMenuTable();
        } catch (e) {
            console.error("Menu Error:", e);
            document.getElementById('menu-table-body').innerHTML = `<tr><td colspan="4" class="text-center text-danger">Error: ${e.message}</td></tr>`;
        }
    }

    function renderMenuTable() {
        const tbody = document.getElementById('menu-table-body');
        if (!Array.isArray(menuItems) || menuItems.length === 0) {
            tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-muted">No items found.</td></tr>`;
            return;
        }

        tbody.innerHTML = menuItems.map(item => {
            const imageAsset = item.image_asset || 'utensils'; 
            
            // Fix: Always prepend base URL for non-absolute paths
            let imageSrc = imageAsset;
            if (!imageAsset.startsWith('http')) {
                // If it starts with slash, remove it to avoid double slash with base url
                const path = imageAsset.startsWith('/') ? imageAsset.substring(1) : imageAsset;
                // Prepend base url if it looks like a path
                if (imageAsset.includes('/') || imageAsset.includes('.')) {
                    imageSrc = `{{ url('/') }}/${path}`;
                }
            }

            // Check if it's a fontawesome icon name
            const isIcon = !imageAsset.includes('/') && !imageAsset.includes('.') && imageAsset.length < 20;

            const imgHtml = isIcon 
                ? `<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;"><i class="fas fa-${imageAsset} text-muted fs-4"></i></div>`
                : `<img src="${imageSrc}" class="rounded shadow-sm" style="width: 50px; height: 50px; object-fit: cover;" onerror="this.src='{{ url('/images/placeholder.jpg') }}'">`;
            
            return `
            <tr>
                <td class="ps-4">${imgHtml}</td>
                <td class="fw-bold text-dark">${item.name}</td>
                <td>Rp ${Number(item.price).toLocaleString()}</td>
                <td class="text-end pe-4">
                    <button class="btn btn-sm btn-outline-primary me-2" onclick="openEditModal(${item.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-outline-danger" onclick="deleteMenuItem(${item.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
            `;
        }).join('');
    }

    async function submitNewItem() {
        const name = document.getElementById('menu-name').value;
        const price = document.getElementById('menu-price').value;
        const imageInput = document.getElementById('menu-image');
        
        if (!name || !price) { showToast("Please fill in name and price", "warning"); return; }

        const formData = new FormData();
        formData.append('name', name);
        formData.append('price', price);
        if (imageInput.files[0]) formData.append('image', imageInput.files[0]);
        else formData.append('image_asset', 'utensils'); 

        try {
            const res = await fetch('{{ url("/api/menu-items") }}', {
                method: 'POST',
                headers: { 'Authorization': 'Bearer ' + apiToken },
                body: formData
            });
            if (!res.ok) throw new Error((await res.json()).message || 'Failed to create');
            
            showToast("Menu item created successfully!", "success");
            document.getElementById('add-menu-form').reset();
            bootstrap.Modal.getOrCreateInstance(document.getElementById('addMenuItemModal')).hide();
            loadMenu(); 
        } catch (e) { showToast("Error: " + e.message, "danger"); }
    }

    // --- UPDATE & DELETE ---
    function openEditModal(id) {
        const item = menuItems.find(i => i.id === id);
        if (!item) {
            console.error("Item not found for ID:", id);
            return;
        }

        document.getElementById('edit-menu-id').value = item.id;
        document.getElementById('edit-menu-name').value = item.name;
        document.getElementById('edit-menu-price').value = item.price;
        document.getElementById('edit-menu-image').value = ''; 
        
        const modalEl = document.getElementById('editMenuItemModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    async function updateMenuItem() {
        const id = document.getElementById('edit-menu-id').value;
        const name = document.getElementById('edit-menu-name').value;
        const price = document.getElementById('edit-menu-price').value;
        const imageInput = document.getElementById('edit-menu-image');

        if (!name || !price) { showToast("Please fill in name and price", "warning"); return; }

        const formData = new FormData();
        formData.append('_method', 'PUT'); 
        formData.append('name', name);
        formData.append('price', price);
        if (imageInput.files[0]) formData.append('image', imageInput.files[0]);

        try {
            const res = await fetch(`{{ url("/api/menu-items") }}/${id}`, {
                method: 'POST', 
                headers: { 'Authorization': 'Bearer ' + apiToken },
                body: formData
            });

            if (!res.ok) throw new Error((await res.json()).message || 'Failed to update');

            showToast("Item updated successfully!", "success");
            
            const modalEl = document.getElementById('editMenuItemModal');
            bootstrap.Modal.getInstance(modalEl).hide();
            
            loadMenu();
        } catch (e) { showToast("Error: " + e.message, "danger"); }
    }

    async function deleteMenuItem(id) {
        if (!confirm("Are you sure you want to delete this item?")) return;

        try {
            const res = await fetch(`{{ url("/api/menu-items") }}/${id}`, {
                method: 'DELETE',
                headers: { 'Authorization': 'Bearer ' + apiToken, 'Accept': 'application/json' }
            });

            if (!res.ok) throw new Error('Failed to delete');

            showToast("Item deleted successfully!", "success");
            loadMenu();
        } catch (e) { showToast("Error: " + e.message, "danger"); }
    }

    // Init 
    document.addEventListener('DOMContentLoaded', () => {
        loadStats();
        loadMenu();
    });
</script>

</body>
</html>
