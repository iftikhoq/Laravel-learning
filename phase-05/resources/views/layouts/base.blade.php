<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Phase 5: Blade Architecture') | Laravel Learning</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base: #0B0F19;
            --bg-card: rgba(17, 24, 39, 0.7);
            --border-glass: rgba(255, 255, 255, 0.08);
            --accent-primary: #6366F1;
            --accent-glow: rgba(99, 102, 241, 0.25);
            --accent-success: #10B981;
            --text-main: #F3F4F6;
            --text-muted: #9CA3AF;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-base);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
            background-image: 
                radial-gradient(circle at 15% 10%, rgba(99, 102, 241, 0.15) 0%, transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(16, 185, 129, 0.1) 0%, transparent 45%);
            background-attachment: fixed;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 1.5rem; width: 100%; }
        header {
            border-bottom: 1px solid var(--border-glass);
            backdrop-filter: blur(16px);
            background: rgba(11, 15, 25, 0.8);
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .nav-inner {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            color: var(--text-main);
            font-weight: 800;
            font-size: 1.25rem;
            letter-spacing: -0.02em;
        }
        .brand-badge {
            background: linear-gradient(135deg, #6366F1, #8B5CF6);
            padding: 0.25rem 0.6rem;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 700;
            color: white;
            box-shadow: 0 0 15px var(--accent-glow);
        }
        .nav-links { display: flex; align-items: center; gap: 1.5rem; list-style: none; }
        .nav-link { color: var(--text-muted); text-decoration: none; font-weight: 500; font-size: 0.9rem; transition: color 0.2s; }
        .nav-link:hover { color: var(--text-main); }
        .auth-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid var(--border-glass);
            background: rgba(255, 255, 255, 0.04);
        }
        main { flex: 1; padding: 2.5rem 0; }
        footer {
            border-top: 1px solid var(--border-glass);
            padding: 2rem 0;
            color: var(--text-muted);
            font-size: 0.85rem;
            text-align: center;
            backdrop-filter: blur(8px);
        }
        code, pre { font-family: 'JetBrains Mono', monospace; }
        .glass-card {
            background: var(--bg-card);
            border: 1px solid var(--border-glass);
            border-radius: 16px;
            padding: 1.75rem;
            box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.37);
            backdrop-filter: blur(12px);
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .glass-card:hover {
            border-color: rgba(99, 102, 241, 0.4);
            transform: translateY(-2px);
        }
    </style>
    @yield('styles')
</head>
<body>
    <header>
        <div class="container nav-inner">
            <a href="/" class="brand">
                <span class="brand-badge">PHASE 5</span>
                <span>Laravel E-Commerce</span>
            </a>
            <ul class="nav-links">
                <li><a href="/" class="nav-link">Blade Demo</a></li>
                <li><a href="/api/v1/health" target="_blank" class="nav-link">API Health</a></li>
                <li><a href="/api/v1/products" target="_blank" class="nav-link">JSON Products</a></li>
                <li>
                    @auth
                        <div class="auth-badge" style="border-color: rgba(16, 185, 129, 0.3); color: #34D399;">
                            <span>●</span> Authenticated as {{ auth()->user()->name }} ({{ auth()->user()->role }})
                        </div>
                    @else
                        <div class="auth-badge">
                            <span>○</span> Guest Mode (@guest directive active)
                        </div>
                    @endauth
                </li>
            </ul>
        </div>
    </header>

    <main>
        <div class="container">
            @yield('content')
        </div>
    </main>

    <footer>
        <div class="container">
            <p>Phase 5: Frontend Integration, Middleware & Sanctum Authentication — Authored by Ifti</p>
            <p style="margin-top: 0.25rem; opacity: 0.7;">PHP 8.3 &bull; Laravel 11 &bull; Blade &bull; React 18 + Vite &bull; Tailwind CSS</p>
        </div>
    </footer>
    @yield('scripts')
</body>
</html>
