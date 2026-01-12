<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Resto POS</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { max-width: 400px; width: 100%; }
    </style>
</head>
<body>

    <div class="card shadow-lg border-0 login-card">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="fas fa-utensils fa-3x text-primary mb-3"></i>
                <h3 class="fw-bold">Resto POS</h3>
                <p class="text-muted">Please sign in to continue</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ url('/login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" class="form-control" id="email" name="email" required autofocus value="{{ old('email') }}">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary fw-bold py-2">Sign In</button>
                </div>
                <div class="text-center mt-3">
                    <small>Don't have an Employee account? <a href="{{ url('/register') }}">Register</a></small>
                </div>
            </form>
        </div>
        <div class="card-footer bg-light text-center py-3 border-0 rounded-bottom">
            <small class="text-muted">&copy; {{ date('Y') }} Resto POS System</small>
        </div>
    </div>

</body>
</html>
