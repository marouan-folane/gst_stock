<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Stock Manager') }} - Login</title>
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    
    <style>
        .login-page {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            min-height: 100vh;
        }
        .login-card {
            max-width: 450px;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
        }
        .login-header {
            background: rgba(78, 115, 223, 0.1);
            border-radius: 10px 10px 0 0;
        }
        .app-logo {
            max-width: 80px;
        }
    </style>
</head>
<body class="login-page">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12">
                <div class="card login-card mx-auto my-5">
                    <div class="login-header text-center py-5">
                        <img src="{{ asset('img/logo.png') }}" alt="Stock Manager Logo" class="app-logo mb-3">
                        <h1 class="h4 text-primary mb-2">{{ config('app.name', 'Stock Manager') }}</h1>
                        <p class="text-muted">Sign In to Your Account</p>
                    </div>
                    <div class="card-body p-4 p-md-5">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            
                            <div class="mb-4">
                                <label for="email" class="form-label">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                                </div>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-4">
                                <label for="password" class="form-label">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" required autocomplete="current-password">
                                </div>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-4 form-check">
                                <input type="checkbox" class="form-check-input" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label" for="remember">Remember Me</label>
                            </div>
                            
                            <div class="d-grid mb-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt me-2"></i> Sign In
                                </button>
                            </div>
                            
                            @if (Route::has('password.request'))
                                <div class="text-center">
                                    <a href="{{ route('password.request') }}" class="text-decoration-none">
                                        Forgot Your Password?
                                    </a>
                                </div>
                            @endif
                        </form>
                    </div>
                    <div class="card-footer py-3 text-center bg-light border-0 rounded-bottom">
                        <div class="text-muted">
                            &copy; {{ date('Y') }} {{ config('app.name', 'Stock Manager') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html> 