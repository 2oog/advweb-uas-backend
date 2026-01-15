<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Resto POS</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .accordion-button:not(.collapsed) {
            background-color: #e7f1ff;
            color: #0c63e4;
        }
    </style>
</head>
<body>

<!-- TOP NAVBAR -->
@include('partials.navbar')

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0"><i class="fas fa-chart-pie me-2"></i>Admin Dashboard</h4>
            <span class="text-muted">Welcome, {{ $user['name'] ?? 'Admin' }}</span>
        </div>
    </div>

    <!-- SALES DASHBOARD (ALWAYS VISIBLE) -->
    <div class="row g-4 mb-4">
        <!-- Sales Card -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Orders</h5>
                    <h2 class="display-4 fw-bold text-dark">{{ $stats['total_orders'] ?? 0 }}</h2>
                </div>
            </div>
        </div>
        <!-- Revenue Card -->
        <div class="col-md-6">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body">
                    <h5 class="card-title text-muted">Total Revenue</h5>
                    <h2 class="display-4 fw-bold text-success">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- ACCORDION MANAGEMENT SECTIONS -->
    <div class="accordion shadow-sm" id="adminAccordion">
        
        <!-- MENU MANAGEMENT -->
        <div class="accordion-item border-0 mb-3 rounded overflow-hidden">
            <h2 class="accordion-header" id="headingMenu">
                <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMenu" aria-expanded="true" aria-controls="collapseMenu">
                    <i class="fas fa-utensils me-2"></i> Menu Management
                </button>
            </h2>
            <div id="collapseMenu" class="accordion-collapse collapse show" aria-labelledby="headingMenu" data-bs-parent="#adminAccordion">
                <div class="accordion-body bg-white p-0">
                    <div class="p-3 d-flex justify-content-end border-bottom">
                         <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addMenuItemModal">
                            <i class="fas fa-plus me-1"></i> Add Menu Item
                        </button>
                    </div>
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
                                @forelse($menuItems as $item)
                                <tr>
                                    <td class="ps-4">
                                        @php
                                            $imageAsset = $item['image_asset'] ?? 'utensils';
                                            $isIcon = !str_contains($imageAsset, '/') && !str_contains($imageAsset, '.') && strlen($imageAsset) < 20;
                                        @endphp
                                        @if($isIcon)
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                                <i class="fas fa-{{ $imageAsset }} text-muted fs-4"></i>
                                            </div>
                                        @else
                                            <img src="{{ str_starts_with($imageAsset, 'http') ? $imageAsset : config('api.server_url') . '/' . ltrim($imageAsset, '/') }}" 
                                                 class="rounded shadow-sm" style="width: 50px; height: 50px; object-fit: cover;"
                                                 onerror="this.src='{{ config('api.url') }}/../images/placeholder.jpg'">
                                        @endif
                                    </td>
                                    <td class="fw-bold text-dark">{{ $item['name'] }}</td>
                                    <td>Rp {{ number_format($item['price'], 0, ',', '.') }}</td>
                                    <td class="text-end pe-4">
                                        <button class="btn btn-sm btn-outline-primary me-2" onclick="openEditModal({{ json_encode($item) }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger" onclick="deleteMenuItem({{ $item['id'] }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted">No items found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- USER MANAGEMENT -->
        <div class="accordion-item border-0 rounded overflow-hidden">
            <h2 class="accordion-header" id="headingUsers">
                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUsers" aria-expanded="false" aria-controls="collapseUsers">
                    <i class="fas fa-users-cog me-2"></i> User Management
                </button>
            </h2>
            <div id="collapseUsers" class="accordion-collapse collapse" aria-labelledby="headingUsers" data-bs-parent="#adminAccordion">
                <div class="accordion-body bg-white p-0">
                    <div class="p-3 d-flex justify-content-end border-bottom">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-plus me-1"></i> Add User
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Registered At</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($users as $u)
                                <tr>
                                    <td class="ps-4 fw-bold text-dark">{{ $u['name'] }}</td>
                                    <td>{{ $u['email'] }}</td>
                                    <td>
                                        <span class="badge bg-{{ ($u['role'] ?? '') === 'admin' ? 'warning' : 'info' }} text-dark">
                                            {{ ucfirst($u['role'] ?? 'employee') }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($u['created_at'])->format('d M Y, H:i') }}</td>
                                    <td class="text-end pe-4">
                                        @if(($user['id'] ?? null) !== $u['id'])
                                            <button class="btn btn-sm btn-outline-danger" onclick="confirmDeleteUser({{ $u['id'] }}, '{{ $u['name'] }}')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        @else
                                            <span class="text-muted small">Current User</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- === MODALS === -->

<!-- Add Menu Item Modal -->
<div class="modal fade" id="addMenuItemModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add New Menu Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/admin/menu') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Menu Name</label>
                        <input type="text" class="form-control" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                        <div class="form-text">Leave empty to use default icon</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Item</button>
                </div>
            </form>
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
            <form id="edit-menu-form" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" value="PUT">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Menu Name</label>
                        <input type="text" class="form-control" name="name" id="edit-menu-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" id="edit-menu-price" required min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Image (Leave empty to keep current)</label>
                        <input type="file" class="form-control" name="image" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Item</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Add New User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ url('/admin/users') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" class="form-control" name="name" id="user-name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" id="user-email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="employee">Employee</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="password" id="generated-password" required minlength="8" placeholder="Click generate">
                            <button class="btn btn-outline-secondary" type="button" onclick="generatePassword()">
                                <i class="fas fa-random"></i> Generate
                            </button>
                            <button class="btn btn-outline-secondary" type="button" onclick="copyPassword()">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" onclick="copyPassword()">Create User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete User Confirmation Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete user <b id="delete-user-name"></b>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="delete-user-form" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Menu Item Confirmation Modal -->
<div class="modal fade" id="deleteMenuModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this menu item?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="delete-menu-form" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Toast Container -->
<div class="toast-container position-fixed bottom-0 end-0 p-3" id="toast-container"></div>

<script>
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

    // --- MENU FUNCTIONS ---
    function openEditModal(item) {
        document.getElementById('edit-menu-name').value = item.name;
        document.getElementById('edit-menu-price').value = item.price;
        document.getElementById('edit-menu-form').action = '{{ url("/admin/menu") }}/' + item.id;
        
        new bootstrap.Modal(document.getElementById('editMenuItemModal')).show();
    }

    function deleteMenuItem(id) {
        document.getElementById('delete-menu-form').action = '{{ url("/admin/menu") }}/' + id;
        new bootstrap.Modal(document.getElementById('deleteMenuModal')).show();
    }

    // --- USER MANAGEMENT LOGIC ---
    function generatePassword() {
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
        let password = "";
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById("generated-password").value = password;
    }

    function copyPassword() {
        const name = document.getElementById("user-name").value || 'User';
        const email = document.getElementById("user-email").value || 'email@example.com';
        const password = document.getElementById("generated-password").value;
        
        if (password) {
            const textToCopy = `Name: ${name}\nEmail: ${email}\nPassword: ${password}\nPlease change your password when logging in for the first time.`;
            
            navigator.clipboard.writeText(textToCopy).then(() => {
                showToast("Password copied to clipboard", "success");
            }).catch(err => {
                console.error('Failed to copy: ', err);
            });
        }
    }

    function confirmDeleteUser(id, name) {
        document.getElementById('delete-user-name').innerText = name;
        document.getElementById('delete-user-form').action = `{{ url('/admin/users') }}/${id}`;
        
        new bootstrap.Modal(document.getElementById('deleteUserModal')).show();
    }

    // Auto-generate password when Add User modal is opened
    document.getElementById('addUserModal').addEventListener('show.bs.modal', event => {
        generatePassword();
    });
</script>

</body>
</html>
