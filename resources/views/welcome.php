<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome — Micro</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #0f172a;
            color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
        .card {
            text-align: center;
            padding: 3rem 4rem;
            background: #1e293b;
            border-radius: 1rem;
            border: 1px solid #334155;
            max-width: 520px;
            width: 90%;
        }
        h1 { font-size: 2.25rem; font-weight: 700; letter-spacing: -.02em; color: #f8fafc; }
        h1 span { color: #38bdf8; }
        p  { margin-top: .75rem; color: #94a3b8; font-size: .95rem; line-height: 1.6; }
        .badge {
            display: inline-block;
            margin-top: 1.5rem;
            padding: .35rem .85rem;
            border-radius: 9999px;
            background: #0ea5e9;
            color: #fff;
            font-size: .8rem;
            font-weight: 600;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .links { margin-top: 2rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; }
        .links a {
            color: #38bdf8;
            text-decoration: none;
            font-size: .9rem;
            border-bottom: 1px solid transparent;
            transition: border-color .15s;
        }
        .links a:hover { border-color: #38bdf8; }
    </style>
</head>
<body>
    <div class="card">
        <h1>Welcome to <span>Micro</span></h1>
        <p>A lightweight, expressive PHP framework for building web applications with clean, modern PHP 8.2+ syntax.</p>
        <div class="badge">PHP <?= PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION ?></div>
        <div class="links">
            <a href="https://github.com/grayphp/micro#readme" target="_blank" rel="noopener">Documentation</a>
            <a href="https://github.com/grayphp/micro" target="_blank" rel="noopener">GitHub</a>
        </div>
    </div>
</body>
</html>
