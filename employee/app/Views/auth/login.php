<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Employee Login - GranthInfotech Attendance</title>
  <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet"/>
</head>
<body class="bg-[#F5F2EA] font-['Manrope'] text-[#3A3A3A] min-h-screen flex items-center justify-center p-6">
  <div class="w-full max-w-md bg-white rounded-lg border border-[#E5E1D5] shadow-[0px_40px_40px_rgba(58,58,58,0.04)] p-6">
    <h1 class="text-2xl font-extrabold tracking-tight">GranthInfotech Attendance</h1>
    <p class="text-sm text-[#3A3A3A]/70 mt-2">Login with OTP</p>

    <div class="mt-6 space-y-4">
      <div>
        <label class="block text-xs font-semibold tracking-wider uppercase text-[#3A3A3A]/60">Email</label>
        <input id="email" type="email" class="mt-2 w-full rounded-lg border border-[#E5E1D5] bg-white px-4 py-3 text-sm focus:outline-none focus:ring-1 focus:ring-[#3A3A3A]" placeholder="you@company.com"/>
      </div>

      <button id="sendOtp" class="w-full bg-[#EB5C49] text-white py-3 rounded-lg font-semibold hover:bg-[#d65241] transition-colors">
        Send OTP
      </button>

      <div>
        <label class="block text-xs font-semibold tracking-wider uppercase text-[#3A3A3A]/60">OTP</label>
        <input id="otp" type="text" inputmode="numeric" maxlength="6" class="mt-2 w-full rounded-lg border border-[#E5E1D5] bg-white px-4 py-3 text-sm focus:outline-none focus:ring-1 focus:ring-[#3A3A3A]" placeholder="6-digit OTP"/>
      </div>

      <button id="verifyOtp" class="w-full bg-[#3A3A3A] text-white py-3 rounded-lg font-semibold hover:bg-[#2d2d2d] transition-colors">
        Verify & Continue
      </button>

      <p id="msg" class="text-sm text-[#3A3A3A]/70"></p>
    </div>
  </div>

  <script>
    const msg = document.getElementById('msg');
    const emailEl = document.getElementById('email');
    const otpEl = document.getElementById('otp');

    function setMsg(text, isError=false) {
      msg.textContent = text;
      msg.className = isError ? 'text-sm text-red-700' : 'text-sm text-emerald-700';
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
      setMsg('Sending OTP...');
      const { res, json } = await post('<?= site_url('auth/send-otp') ?>', { email: emailEl.value });
      if (!res.ok) return setMsg(json.message || 'Failed to send OTP', true);
      if (json.devOtp) {
        otpEl.value = json.devOtp;
        setMsg('OTP generated for localhost testing and prefilled.');
      } else {
        setMsg('OTP sent to your email.');
      }
    });

    document.getElementById('verifyOtp').addEventListener('click', async () => {
      setMsg('Verifying OTP...');
      const { res, json } = await post('<?= site_url('auth/verify-otp') ?>', { email: emailEl.value, otp: otpEl.value });
      if (!res.ok) return setMsg(json.message || 'Invalid OTP', true);
      window.location.href = (json.redirect || '<?= site_url('/') ?>');
    });
  </script>
</body>
</html>