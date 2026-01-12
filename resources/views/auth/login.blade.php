<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - LeDantec Budget</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: linear-gradient(135deg, var(--slate-900) 0%, var(--primary-900) 100%);
        }

        .login-card {
            background: white;
            padding: 2.5rem;
            border-radius: 1rem;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
        }

        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-logo {
            font-size: 3rem;
            margin-bottom: 0.5rem;
            display: block;
        }

        .input-group {
            margin-bottom: 1.25rem;
        }

        .input-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--slate-700);
        }

        .input-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .input-group input:focus {
            border-color: var(--primary-500);
            box-shadow: 0 0 0 2px var(--primary-100);
        }
    </style>
</head>

<body>
    <div class="login-card">
        <div class="login-header">
            <span class="brand-logo">🏥</span>
            <h1 style="font-size: 1.5rem; font-weight: 700; color: var(--slate-900);">Connexion</h1>
            <p style="color: var(--text-muted);">Gestion Budgétaire Hospitalière</p>
        </div>

        <form action="{{ route('login.submit') }}" method="POST">
            @csrf

            <div class="input-group">
                <label>Email Professionnel</label>
                <input type="email" name="email" required placeholder="nom@ledantec.sn" value="{{ old('email') }}">
                @error('email') <div style="color: var(--danger); font-size: 0.8rem; margin-top: 0.25rem;">
                {{ $message }}</div> @enderror
            </div>

            <div class="input-group">
                <label>Mot de passe</label>
                <input type="password" name="password" required placeholder="••••••••">
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;">Se connecter</button>
        </form>

        <div
            style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-color); font-size: 0.85rem;">
            <p style="color: var(--text-muted); margin-bottom: 0.5rem;">Comptes de démonstration :</p>
            <div style="display: grid; gap: 0.5rem; color: var(--slate-600);">
                <code>admin@ledantec.sn / password</code>
                <code>fall@ledantec.sn / password</code> (Chirurgie)
            </div>
        </div>
    </div>
</body>

</html>