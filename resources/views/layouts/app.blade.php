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

    @vite(['resources/css/app.css', 'resources/js/app.js'])
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
                <a href="{{ route('reporting.global') }}" class="nav-item {{ request()->routeIs('reporting.*') ? 'active' : '' }}">
                    <span class="icon">📈</span> Reporting
                </a>

                <div class="nav-section">Budget</div>
                <a href="{{ route('besoins.index') }}" class="nav-item {{ request()->routeIs('besoins.*') ? 'active' : '' }}">
                    <span class="icon">📝</span> Besoins
                </a>
                <a href="{{ route('budget.arbitrage') }}" class="nav-item {{ request()->routeIs('budget.arbitrage') ? 'active' : '' }}">
                    <span class="icon">⚖️</span> Arbitrage
                </a>
                <a href="{{ route('execution.index') }}" class="nav-item {{ request()->routeIs('execution.*') ? 'active' : '' }}">
                    <span class="icon">💸</span> Exécution
                </a>

                @if(Auth::check() && Auth::user()->role === 'admin')
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
                <a href="{{ route('ajustements.create') }}" class="nav-item {{ request()->routeIs('ajustements.*') ? 'active' : '' }}">
                    <span class="icon">↔️</span> Virements
                </a>
                @endif
                <a href="{{ route('reporting.accounts') }}" class="nav-item {{ request()->routeIs('reporting.accounts') ? 'active' : '' }}">
                    <span class="icon">📊</span> Par Compte
                </a>
            </nav>

            <div class="user-profile">
                <div class="avatar">
                    {{ substr(Auth::user()->name ?? 'U', 0, 1) }}
                </div>
                <div class="info" style="flex: 1;">
                    <p class="name">{{ Auth::user()->name ?? 'Invité' }}</p>
                    <p class="role">{{ ucfirst(Auth::user()->role ?? '') }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--danger);" title="Déconnexion">⏻</button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <h2 class="page-title">@yield('title', 'Tableau de bord')</h2>
                <div class="actions">
                    <button class="btn-icon">🔔</button>
                    <button class="btn-icon">⚙️</button>
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