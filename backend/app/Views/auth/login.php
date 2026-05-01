<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>personal Admin — Secure Login</title>

    <!-- Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@700;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --color-bg: #F8FAFC;
            --color-primary: #0F172A;
            --color-accent: #4F46E5;
            --color-text: #1E293B;
            --color-text-dim: #64748B;
            --color-border: #E2E8F0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: var(--color-bg);
            color: var(--color-text);
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            -webkit-font-smoothing: antialiased;
        }

        .login-wrap {
            width: 100%;
            max-width: 440px;
            padding: 2rem;
            animation: fadeIn 0.6s ease-out;
        }

        .login-card {
            background: #FFFFFF;
            padding: 3rem;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--color-border);
        }

        .header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .logo-box {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 48px;
            height: 48px;
            background: var(--color-primary);
            color: white;
            border-radius: 12px;
            font-family: 'Manrope', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .title {
            font-family: 'Manrope', sans-serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--color-primary);
            letter-spacing: -0.02em;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            font-size: 0.875rem;
            color: var(--color-text-dim);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-size: 0.8125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--color-text);
        }

        input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--color-border);
            border-radius: 8px;
            font-size: 0.9375rem;
            font-family: inherit;
            transition: all 0.2s;
            background: #F9FAFB;
        }

        input:focus {
            outline: none;
            border-color: var(--color-accent);
            background: #FFFFFF;
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }

        .btn {
            width: 100%;
            padding: 0.875rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s;
            background: var(--color-primary);
            color: white;
            margin-top: 1rem;
        }

        .btn:hover {
            background: #1E293B;
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.2);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.8125rem;
            text-align: center;
        }

        .alert-error {
            background: #FEF2F2;
            color: #991B1B;
            border: 1px solid #FEE2E2;
        }

        .alert-info {
            background: #F0F9FF;
            color: #075985;
            border: 1px solid #E0F2FE;
        }

        .footer {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.75rem;
            color: var(--color-text-dim);
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>

<body>
    <div class="login-wrap">
        <div class="login-card">
            <div class="header">
                <div class="logo-box">G</div>
                <h1 class="title">personal Admin</h1>
                <p class="subtitle">System Management Interface</p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error">
                    <?= esc(session()->getFlashdata('error')) ?>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('login') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label>Administrator Username</label>
                    <input type="text" name="username" required autocomplete="username"
                        value="<?= esc(old('username') ?? '') ?>" placeholder="Enter username">
                </div>

                <div class="form-group">
                    <label>Security Password</label>
                    <input type="password" name="password" required autocomplete="current-password"
                        placeholder="••••••••">
                </div>

                <button type="submit" class="btn">Access Terminal</button>
            </form>
        </div>

        <div class="footer">
            &copy; 2026 Granth Infotech. Operational Security Active.
        </div>
    </div>
</body>

</html>