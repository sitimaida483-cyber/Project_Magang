<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Presensi Bidtik Polda Kalsel</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0e2238 0%, #17385c 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            border: none;
        }
        .card-header-custom {
            background: #091624;
            color: #ffffff;
            padding: 2.2rem 2rem 1.8rem;
            text-align: center;
        }
        .gold-badge {
            display: inline-block;
            background: #e5a93c;
            color: #000;
            font-weight: 700;
            font-size: 0.75rem;
            padding: 4px 12px;
            border-radius: 30px;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }
        .form-control {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #cbd5e1;
        }
        .form-control:focus {
            border-color: #1e3a8a;
            box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.15);
        }
        .btn-police {
            background-color: #0e2238;
            color: #ffffff;
            border-radius: 8px;
            padding: 0.8rem;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
        }
        .btn-police:hover {
            background-color: #17385c;
            color: #e5a93c;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <div class="card-header-custom">
            <span class="gold-badge">SISTEM PRESENSI</span>
            <h4 class="fw-bold mb-1">BIDTIK POLDA KALSEL</h4>
            <p class="text-white-50 mb-0 small">Panel Masuk Khusus Administrator</p>
        </div>

        <div class="p-4 p-md-4">
            @if(session('sukses'))
                <div class="alert alert-success alert-dismissible fade show small" role="alert">
                    {{ session('sukses') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show small" role="alert">
                    {{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show small" role="alert">
                    <ul class="mb-0 ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <form action="{{ route('admin.login.proses') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold text-secondary small">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control border-start-0 @error('username') is-invalid @enderror" 
                               id="username" name="username" value="{{ old('username') }}" 
                               placeholder="Masukkan username" required autofocus>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-semibold text-secondary small">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control border-start-0 @error('password') is-invalid @enderror" 
                               id="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="ingat_saya" id="ingat_saya">
                        <label class="form-check-label text-muted small" for="ingat_saya">
                            Ingat saya
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-police w-100 mb-3 shadow-sm">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Panel
                </button>
            </form>

            <div class="text-center pt-2 border-top">
                <a href="{{ url('/presensi') }}" class="text-decoration-none small text-muted">
                    <i class="bi bi-arrow-left me-1"></i> Menuju Halaman Presensi Anggota
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
