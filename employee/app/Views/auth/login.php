<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Granth. — Corporate Access</title>

    <!-- Professional Typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@700;800&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --color-bg: #FFFFFF;
            --color-primary: #0F172A;
            --color-accent: #2563EB;
            --color-border: #E2E8F0;
            --color-text: #1E293B;
            --color-text-dim: #64748B;
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

        .login-layout {
            display: flex;
            width: 100%;
            max-width: 1100px;
            height: 640px;
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--color-border);
            margin: 2rem;
        }

        /* Branding Side */
        .login-brand {
            flex: 1;
            background: var(--color-primary);
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .login-brand::after {
            content: '';
            position: absolute;
            bottom: -10%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: var(--color-accent);
            filter: blur(120px);
            opacity: 0.15;
        }

        .brand-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: white;
            font-family: 'Manrope', sans-serif;
            font-weight: 800;
            font-size: 1.5rem;
            letter-spacing: -0.02em;
        }

        .brand-logo .dot {
            width: 10px;
            height: 10px;
            background: var(--color-accent);
            border-radius: 50%;
        }

        .brand-content h1 {
            color: white;
            font-family: 'Manrope', sans-serif;
            font-size: 2.25rem;
            line-height: 1.2;
            margin-bottom: 1.5rem;
        }

        .brand-content p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 1rem;
            line-height: 1.6;
        }

        /* Form Side */
        .login-form-container {
            width: 460px;
            padding: 4rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-header {
            margin-bottom: 2.5rem;
        }

        .form-header h2 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .form-header p {
            color: var(--color-text-dim);
            font-size: 0.875rem;
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
        }

        input:focus {
            outline: none;
            border-color: var(--color-accent);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
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
            margin-top: 1rem;
        }

        .btn-primary {
            background: var(--color-accent);
            color: white;
        }

        .btn-primary:hover {
            background: #1D4ED8;
        }

        .btn-secondary {
            background: #F1F5F9;
            color: var(--color-text);
            border: 1px solid var(--color-border);
        }

        .btn-secondary:hover {
            background: #E2E8F0;
        }

        #msg {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.8125rem;
            display: none;
        }

        .msg-success {
            background: #DCFCE7;
            color: #166534;
            border: 1px solid #BBF7D0;
        }

        .msg-error {
            background: #FEE2E2;
            color: #991B1B;
            border: 1px solid #FECACA;
        }

        @media (max-width: 900px) {
            .login-layout {
                height: auto;
                flex-direction: column;
            }

            .login-brand {
                display: none;
            }

            .login-form-container {
                width: 100%;
                padding: 3rem 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="login-layout">
        <div class="login-brand">
            <div class="brand-logo">
                <div class="dot"></div>
                Granth.
            </div>
            <div class="brand-content">
                <h1>Manage your work presence with precision.</h1>
                <p>Enterprise-grade attendance and personal management for high-performance teams.</p>
            </div>
            <div style="color: rgba(255, 255, 255, 0.3); font-size: 0.75rem; font-weight: 500;">
                &copy; 2026 Granth Infotech
            </div>
        </div>

        <div class="login-form-container">
            <div class="form-header">
                <h2>Corporate Sign In</h2>
                <p>Enter your identity details to continue.</p>
            </div>

            <div id="msg"></div>

            <div class="form-group">
                <label>Email Address</label>
                <input id="email" type="email" placeholder="name@company.com" autocomplete="email" />
            </div>

            <button id="sendOtp" class="btn btn-primary">Request Access Key</button>

            <div style="height: 1px; background: var(--color-border); margin: 2rem 0; position: relative;">
                <span
                    style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: white; padding: 0 1rem; color: var(--color-text-dim); font-size: 0.75rem; font-weight: 600;">VERIFICATION</span>
            </div>

            <div class="form-group">
                <label>One-Time Key</label>
                <input id="otp" type="text" inputmode="numeric" maxlength="6" placeholder="0 0 0 0 0 0"
                    style="letter-spacing: 0.5em; text-align: center;" />
            </div>

            <button id="verifyOtp" class="btn btn-secondary">Authenticate</button>
        </div>
    </div>

    <script>
        const msg = document.getElementById('msg');
        const emailEl = document.getElementById('email');
        const otpEl = document.getElementById('otp');

        function setMsg(text, isError = false) {
            msg.textContent = text;
            msg.className = isError ? 'msg-error' : 'msg-success';
            msg.style.display = 'block';
            setTimeout(() => { msg.style.display = 'none'; }, 5000);
        }

        async function post(url, payload) {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams(payload)
            });
            return { res, json: await res.json().catch(() => ({})) };
        }

        document.getElementById('sendOtp').addEventListener('click', async () => {
            if (!emailEl.value) return setMsg('Please enter your email', true);
            const btn = document.getElementById('sendOtp');
            const originalText = btn.textContent;
            btn.textContent = 'Verifying...';
            btn.disabled = true;

            const { res, json } = await post('<?= site_url('auth/send-otp') ?>', { email: emailEl.value });
            btn.textContent = originalText;
            btn.disabled = false;

            if (!res.ok) return setMsg(json.message || 'Verification failed', true);

            if (json.devOtp) {
                otpEl.value = json.devOtp;
                setMsg('Access key generated for development environment.');
            } else {
                setMsg('Access key has been dispatched to your email.');
            }
            otpEl.focus();
        });

        document.getElementById('verifyOtp').addEventListener('click', async () => {
            if (!emailEl.value || !otpEl.value) return setMsg('Missing access key', true);
            const btn = document.getElementById('verifyOtp');
            btn.textContent = 'Authenticating...';
            btn.disabled = true;

            const { res, json } = await post('<?= site_url('auth/verify-otp') ?>', {
                email: emailEl.value,
                otp: otpEl.value
            });

            if (!res.ok) {
                btn.textContent = 'Authenticate';
                btn.disabled = false;
                return setMsg(json.message || 'Authentication failed', true);
            }

            setMsg('Access Granted. Entering portal...');
            setTimeout(() => { window.location.href = json.redirect || '<?= site_url('/') ?>'; }, 800);
        });
    </script>
</body>

</html>