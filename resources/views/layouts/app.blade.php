<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'IoT Apps') }}</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1e40af;
            --secondary-color: #f1f5f9;
            --accent-color: #10b981;
            --danger-color: #ef4444;
            --warning-color: #f59e0b;
            --dark-color: #1e293b;
            --navbar-dark: #1e3a8a;
            --navbar-darker: #1e40af;
            --light-color: #f8fafc;
            --border-color: #e2e8f0;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-color);
            color: var(--text-primary);
        }
        
        .navbar-modern {
            background: linear-gradient(135deg, var(--navbar-darker) 0%, var(--navbar-dark) 100%);
            box-shadow: var(--shadow);
            border: none;
        }
        
        .navbar-brand {
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .nav-link {
            font-weight: 500;
            transition: all 0.2s ease;
            border-radius: 8px;
            margin: 0 4px;
            padding: 8px 16px !important;
        }
        
        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-1px);
        }
        
        .card-modern {
            border: none;
            border-radius: 16px;
            box-shadow: var(--shadow);
            transition: all 0.2s ease;
            background-color: white;
        }
        
        .card-modern:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }
        
        .card-header-modern {
            background: linear-gradient(135deg, var(--primary-color) 0%, #8b5cf6 100%);
            color: white;
            border-radius: 16px 16px 0 0 !important;
            border: none;
            padding: 1.5rem;
        }
        
        .btn-modern {
            border-radius: 12px;
            font-weight: 500;
            padding: 12px 24px;
            transition: all 0.2s ease;
            border: none;
        }
        
        .btn-modern:hover {
            transform: translateY(-1px);
            box-shadow: var(--shadow);
        }
        
        .btn-primary-modern {
            background: linear-gradient(135deg, var(--primary-color) 0%, #8b5cf6 100%);
            color: white;
        }
        
        .btn-success-modern {
            background: linear-gradient(135deg, var(--accent-color) 0%, #059669 100%);
            color: white;
        }
        
        .stats-card {
            text-align: center;
            padding: 2rem 1rem;
            border-radius: 16px;
            background: white;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: all 0.2s ease;
        }
        
        .stats-card:hover {
            box-shadow: var(--shadow-lg);
            transform: translateY(-2px);
        }
        
        .stats-number {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stats-label {
            color: var(--text-secondary);
            font-weight: 500;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .icon-wrapper {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .dropdown-menu {
            border: none;
            border-radius: 12px;
            box-shadow: var(--shadow-lg);
            padding: 0.5rem;
        }
        
        .dropdown-item {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            transition: all 0.2s ease;
        }
        
        .dropdown-item:hover {
            background-color: var(--secondary-color);
        }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div id="app">
        @auth
        <!-- MODERN NAVIGATION BAR -->
        <nav class="navbar navbar-expand-lg navbar-dark navbar-modern" style="min-height: 70px; z-index: 1000;">
            <div class="container-fluid px-4">
                <a class="navbar-brand fw-bold" href="{{ url('/dashboard') }}">
                    <i class="fas fa-microchip me-2"></i>IoT Apps
                </a>
                
                <!-- Navigation links -->
                <div class="d-flex flex-wrap align-items-center">
                    <ul class="navbar-nav d-flex flex-row me-4">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ url('/dashboard') }}">
                                <i class="fas fa-home me-2"></i>Dashboard
                            </a>
                        </li>
                        @if(Auth::user()->isAdmin())
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ route('users.index') }}">
                                <i class="fas fa-users me-2"></i>Users
                            </a>
                        </li>
                        @endif
                        <li class="nav-item">
                            <a class="nav-link text-white" href="{{ url('/devices') }}">
                                <i class="fas fa-microchip me-2"></i>Devices
                            </a>
                        </li>
                    </ul>
                    
                    <!-- User dropdown -->
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 32px; height: 32px; font-size: 14px; font-weight: 600;">
                                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                            </div>
                            {{ Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ url('/profile') }}">
                                <i class="fas fa-user me-2"></i>Profile
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="fas fa-sign-out-alt me-2"></i>Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
        @endauth

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- DataTables -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    
    @stack('scripts')
</body>
</html>