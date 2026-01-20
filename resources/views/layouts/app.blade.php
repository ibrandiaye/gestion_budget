<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Hospital Budget') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    @stack('scripts')
</head>

<body class="antialiased">
    <div class="app-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <div class="logo">🏥</div>
                <h1>LeDantec<span class="highlight">Budget</span></h1>
            </div>

            <nav class="nav-menu">
                <div class="nav-section">Gestion</div>
                <a href="{{ url('/') }}" class="nav-item {{ request()->is('/') ? 'active' : '' }}">
                    <span class="icon">📊</span> Tableau de bord
                </a>
                @if(Auth::user()->role === 'admin')
                <a href="{{ route('reporting.global') }}" class="nav-item {{ request()->routeIs('reporting.*') ? 'active' : '' }}">
                    <span class="icon">📈</span> Reporting
                </a>
                @endif

                <div class="nav-section">Budget</div>
                <a href="{{ route('besoins.index') }}" class="nav-item {{ request()->routeIs('besoins.*') ? 'active' : '' }}">
                    <span class="icon">📝</span> Besoins
                </a>
              
                @if(Auth::check() && Auth::user()->role === 'admin')
                <a href="{{ route('budget.arbitrage') }}" class="nav-item {{ request()->routeIs('budget.arbitrage') ? 'active' : '' }}">
                    <span class="icon">⚖️</span> Arbitrage
                </a>
                 <a href="{{ route('reporting.accounts') }}" class="nav-item {{ request()->routeIs('reporting.accounts') ? 'active' : '' }}">
                    <span class="icon">📊</span> Par Compte
                </a>
               
                <a href="{{ route('ajustements.index') }}" class="nav-item {{ request()->routeIs('ajustements.*') ? 'active' : '' }}">
                    <span class="icon">↔️</span> Virements
                </a>
                <a href="{{ route('execution.index') }}" class="nav-item {{ request()->routeIs('execution.*') ? 'active' : '' }}">
                    <span class="icon">💸</span> Exécution
                </a>

                
                <div class="nav-section">Administration</div>
                <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <span class="icon">👥</span> Utilisateurs
                </a>
                <a href="{{ route('services.index') }}" class="nav-item {{ request()->routeIs('services.*') ? 'active' : '' }}">
                    <span class="icon">🏢</span> Services
                </a>
                  <a href="{{ route('comptes.index') }}" class="nav-item {{ request()->routeIs('comptes.*') ? 'active' : '' }}">
                    <span class="icon">📑</span> Comptes
                </a>
                 <a href="{{ route('budgets.index') }}" class="nav-item {{ request()->routeIs('budgets.*') ? 'active' : '' }}">
                    <span class="icon">📅</span> Exercices
                </a>
                @endif
               
            </nav>

            <div class="user-profile">
                <div class="avatar">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                <div class="info" style="flex: 1;">
                    <p class="name">{{ Auth::user()->name ?? 'Invité' }}</p>
                    <p class="role" style="margin-bottom: 2px;">{{ ucfirst(Auth::user()->role ?? '') }}</p>
                    @if(Auth::user()->service)
                        <p class="service-name" style="font-size: 0.7rem; font-weight: 600; color: var(--primary-600); text-transform: uppercase; letter-spacing: 0.025em;">
                            <i class="fa fa-hospital-user" style="margin-right: 4px;"></i> {{ Auth::user()->service->nom }}
                        </p>
                    @endif
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--danger);" title="Déconnexion"><i class="fa fa-sign-out"></i></button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <h2 class="page-title">@yield('title', 'Tableau de bord')</h2>
                <div class="actions" style="display: flex; align-items: center; gap: 1.5rem;">
                    @if(Auth::user()->service)
                        <span style="background: var(--primary-50); color: var(--primary-700); padding: 0.4rem 0.8rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 0.5rem; border: 1px solid var(--primary-100);">
                            <span class="icon" style="font-size: 1rem;">🏢</span> {{ Auth::user()->service->nom }}
                        </span>
                    @endif
                     <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--danger); font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 0.4rem;" title="Déconnexion"><i class="fa fa-sign-out"></i> Déconnexion</button>
                </form>
                </div>
            </header>

            <div class="scrollable-content">
                @if(session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>

</html>