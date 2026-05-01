<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sign in to Granth Infotech Attendance Management System">
    <title>Sign In — Granth Infotech Admin</title>

    <!-- Premium Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Premium CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/premium.css') ?>">

    <style>
        /* Login-specific styles */
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            position: relative;
            overflow: hidden;
        }

        .login-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: float 20s ease-in-out infinite;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 48px;
            width: 100%;
            max-width: 480px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.3);
            position: relative;
            z-index: 1;
            animation: slideInUp 0.6s ease;
        }

        .login-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .login-logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            font-weight: 700;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            animation: float 3s ease-in-out infinite;
        }

        .login-title {
            font-family: 'Playfair Display', Georgia, serif;
            font-size: 2rem;
            font-weight: 700;
            color: #1a1a2e;
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: #6c757d;
            font-size: 1rem;
        }

        .login-form {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

        .form-group {
            position: relative;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            font-size: 0.875rem;
            color: #1a1a2e;
        }

        .form-input {
            width: 100%;
            padding: 16px 20px;
            font-family: 'Manrope', sans-serif;
            font-size: 1rem;
            border: 2px solid #e9ecef;
            border-radius: 12px;
            background: white;
            color: #1a1a2e;
            transition: all 0.3s ease;
        }

        .form-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
        }

        .form-input::placeholder {
            color: #adb5bd;
        }

        .form-icon {
            position: absolute;
            right: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #adb5bd;
            font-size: 1.25rem;
            pointer-events: none;
        }

        .login-button {
            width: 100%;
            padding: 16px 32px;
            font-family: 'Manrope', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            color: white;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            position: relative;
            overflow: hidden;
        }

        .login-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .login-button:hover::before {
            left: 100%;
        }

        .login-button:active {
            transform: translateY(0);
        }

        .login-button:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .login-footer {
            text-align: center;
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e9ecef;
            color: #6c757d;
            font-size: 0.875rem;
        }

        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .login-footer a:hover {
            text-decoration: underline;
        }

        .alert {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            animation: slideInDown 0.3s ease;
        }

        .alert--danger {
            background: rgba(214, 48, 49, 0.1);
            color: #d63031;
            border: 1px solid rgba(214, 48, 49, 0.2);
        }

        .alert--info {
            background: rgba(9, 132, 227, 0.1);
            color: #0984e3;
            border: 1px solid rgba(9, 132, 227, 0.2);
        }

        .alert-icon {
            font-size: 1.5rem;
        }

        .alert-close {
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: inherit;
            opacity: 0.7;
            transition: opacity 0.2s ease;
            margin-left: auto;
        }

        .alert-close:hover {
            opacity: 1;
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes slideInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive */
        @media (max-width: 640px) {
            .login-card {
                padding: 32px 24px;
                margin: 16px;
            }

            .login-title {
                font-size: 1.5rem;
            }

            .form-input {
                padding: 14px 16px;
            }

            .login-button {
                padding: 14px 24px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">G</div>
                <h1 class="login-title">Granth Infotech</h1>
                <p class="login-subtitle">Attendance Management System</p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert--danger">
                    <span class="alert-icon">⚠️</span>
                    <span><?= esc(session()->getFlashdata('error')) ?></span>
                    <button class="alert-close">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('info')): ?>
                <div class="alert alert--info">
                    <span class="alert-icon">ℹ️</span>
                    <span><?= esc(session()->getFlashdata('info')) ?></span>
                    <button class="alert-close">&times;</button>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('login') ?>" method="POST" id="login-form" class="login-form">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" id="username" name="username" class="form-input"
                        placeholder="Enter your username" required autocomplete="username"
                        value="<?= esc(old('username') ?? '') ?>">
                    <span class="form-icon">👤</span>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-input"
                        placeholder="Enter your password" required autocomplete="current-password">
                    <span class="form-icon">🔒</span>
                </div>

                <button type="submit" class="login-button" id="login-btn">
                    <span class="button-text">Sign In</span>
                </button>
            </form>

            <div class="login-footer">
                <p>Authenticates via eTimeOffice cloud credentials.</p>
            </div>
        </div>
    </div>

    <script>
        // Form submission feedback
        document.getElementById('login-form').addEventListener('submit', function () {
            var btn = document.getElementById('login-btn');
            var btnText = btn.querySelector('.button-text');

            btn.disabled = true;
            btnText.innerHTML = '<span class="spinner"></span> Signing in...';
        });

        // Auto-dismiss alerts
        document.querySelectorAll('.alert').forEach(function (el) {
            setTimeout(function () {
                el.style.opacity = '0';
                setTimeout(function () { el.remove(); }, 400);
            }, 5000);

            var close = el.querySelector('.alert-close');
            if (close) {
                close.addEventListener('click', function () {
                    el.style.opacity = '0';
                    setTimeout(function () { el.remove(); }, 300);
                });
            }
        });

        // Input focus effects
        document.querySelectorAll('.form-input').forEach(function (input) {
            input.addEventListener('focus', function () {
                this.parentElement.querySelector('.form-icon').style.color = '#667eea';
            });

            input.addEventListener('blur', function () {
                this.parentElement.querySelector('.form-icon').style.color = '#adb5bd';
            });
        });
    </script>
</body>

</html>