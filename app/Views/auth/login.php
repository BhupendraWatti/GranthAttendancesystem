<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sign in to AttendPro Attendance Management System">
    <title>Sign In — AttendPro</title>
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <div class="login-page">
        <div class="login-card">
            <div class="login-brand">
                <div class="login-icon"><img src="/assets/img/image-1776985023902.png" alt="Logo" style="max-width: 48px; border-radius: 50%;" onerror="this.style.display='none'"></div>
                <h1>Granth Infotech</h1>
                <p>Attendance Management System</p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert--danger">
                    ❌ <?= esc(session()->getFlashdata('error')) ?>
                    <button class="alert-close">&times;</button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('info')): ?>
                <div class="alert alert--info">
                    ℹ️ <?= esc(session()->getFlashdata('info')) ?>
                    <button class="alert-close">&times;</button>
                </div>
            <?php endif; ?>

            <form action="/login" method="POST" id="login-form">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-control"
                        placeholder="Enter your username"
                        required
                        autocomplete="username"
                        value="<?= esc(old('username') ?? '') ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        required
                        autocomplete="current-password"
                    >
                </div>

                <button type="submit" class="btn btn--primary btn--block btn--lg" id="login-btn">
                    Sign In
                </button>
            </form>

            <div class="login-footer">
                Authenticates via eTimeOffice cloud credentials.
            </div>
        </div>
    </div>

    <script>
        // Simple form submission feedback
        document.getElementById('login-form').addEventListener('submit', function () {
            var btn = document.getElementById('login-btn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner"></span> Signing in...';
        });

        // Auto-dismiss alerts
        document.querySelectorAll('.alert').forEach(function(el) {
            setTimeout(function() {
                el.style.opacity = '0';
                setTimeout(function() { el.remove(); }, 400);
            }, 5000);
            var close = el.querySelector('.alert-close');
            if (close) close.addEventListener('click', function() {
                el.style.opacity = '0';
                setTimeout(function() { el.remove(); }, 300);
            });
        });
    </script>
    <link rel="stylesheet" href="/assets/css/app.css">
</body>
</html>
