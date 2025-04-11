<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Stock Management System') }} - @yield('title')</title>
    
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    
    @stack('styles')
</head>
<body>
    <div id="app">
        @include('layouts.partials.topbar')
        
        <div class="container-fluid">
            <div class="row">
                @include('layouts.partials.sidebar')
                
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                    @include('layouts.partials.alerts')
                    
                    @yield('content')
                </main>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}"></script>
    
    @stack('scripts')
</body>
</html> 