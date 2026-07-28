<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#3d5ee1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <title>{{ config('app.name', 'Laravel') }} - Offline</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f4f7fb;
            --panel: #ffffff;
            --text: #111827;
            --muted: #6b7280;
            --accent: #3d5ee1;
            --accent-soft: rgba(61, 94, 225, 0.12);
            --border: rgba(17, 24, 39, 0.08);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top, rgba(61, 94, 225, 0.14), transparent 34%),
                linear-gradient(180deg, #ffffff 0%, var(--bg) 100%);
            color: var(--text);
            padding: 24px;
        }

        .offline-shell {
            width: min(100%, 640px);
            background: var(--panel);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 32px;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.12);
        }

        .offline-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 14px;
            border-radius: 999px;
            background: var(--accent-soft);
            color: var(--accent);
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 24px;
        }

        .offline-badge::before {
            content: '';
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: var(--accent);
        }

        h1 {
            margin: 0 0 12px;
            font-size: clamp(32px, 5vw, 48px);
            line-height: 1.05;
            letter-spacing: -0.03em;
        }

        p {
            margin: 0 0 16px;
            color: var(--muted);
            font-size: 16px;
            line-height: 1.7;
        }

        .offline-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 28px;
        }

        .btn {
            appearance: none;
            border: 0;
            border-radius: 14px;
            padding: 14px 18px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 48px;
        }

        .btn-primary {
            background: var(--accent);
            color: #fff;
        }

        .btn-secondary {
            background: #eef2ff;
            color: #1f2937;
        }

        .offline-note {
            margin-top: 24px;
            font-size: 14px;
            color: var(--muted);
            padding-top: 18px;
            border-top: 1px solid var(--border);
        }
    </style>
</head>
<body>
    <main class="offline-shell" role="main">
        <div class="offline-badge">You are offline</div>
        <h1>{{ config('app.name', 'Laravel') }} is still available from your device.</h1>
        <p>
            The app could not reach the server just now, but your last visited pages and cached resources
            are still available. Reconnect to continue working normally.
        </p>

        <div class="offline-actions">
            <button class="btn btn-primary" type="button" onclick="window.location.reload()">Try again</button>
            <a class="btn btn-secondary" href="{{ route('dashboard') }}">Go to dashboard</a>
        </div>

        <div class="offline-note">
            Once the connection returns, the app will continue to use the online Laravel flow as usual.
        </div>
    </main>
</body>
</html>
