<!doctype html>
<html lang="id">
  <head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover"/>
    <meta http-equiv="X-UA-Compatible" content="ie=edge"/>
    <title>Login KOPKARTEX</title>

    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}"/>
    <link href="{{ asset('tabler/dist/css/tabler.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('tabler/dist/css/tabler-flags.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('tabler/dist/css/tabler-payments.min.css') }}" rel="stylesheet"/>
    <link href="{{ asset('tabler/dist/css/tabler-vendors.min.css') }}" rel="stylesheet"/>
    <style>
      @import url('https://rsms.me/inter/inter.css');

      :root {
        --tblr-font-sans-serif: 'Inter Var', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
        --brand: #0f6fcf;
        --brand-dark: #0b4f9a;
        --brand-soft: #eaf4ff;
        --ink: #102033;
        --muted: #63758d;
        --line: #d9e7f7;
      }

      * {
        box-sizing: border-box;
      }

      body {
        min-height: 100vh;
        color: var(--ink);
        font-feature-settings: "cv03", "cv04", "cv11";
        background:
          radial-gradient(circle at 8% 12%, rgba(15, 111, 207, .16), transparent 30%),
          radial-gradient(circle at 88% 8%, rgba(56, 189, 248, .18), transparent 28%),
          linear-gradient(145deg, #f7fbff 0%, #eef6ff 45%, #ffffff 100%);
      }

      .login-shell {
        min-height: 100vh;
        display: grid;
        place-items: center;
        padding: 28px 16px;
      }

      .login-panel {
        width: min(1080px, 100%);
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(390px, .95fr);
        overflow: hidden;
        border: 1px solid rgba(217, 231, 247, .9);
        border-radius: 22px;
        background: rgba(255, 255, 255, .82);
        box-shadow: 0 24px 70px rgba(15, 64, 118, .16);
        backdrop-filter: blur(16px);
      }

      .brand-side {
        position: relative;
        min-height: 630px;
        padding: 42px;
        color: #fff;
        background:
          linear-gradient(145deg, rgba(11, 79, 154, .96), rgba(15, 111, 207, .94)),
          url("{{ asset('tabler/static/photos/blue-sofa-with-pillows-in-a-designer-living-room-interior.jpg') }}");
        background-size: cover;
        background-position: center;
      }

      .brand-side::before {
        content: "";
        position: absolute;
        inset: 0;
        background:
          linear-gradient(180deg, rgba(4, 26, 52, .05), rgba(4, 26, 52, .22)),
          repeating-linear-gradient(135deg, rgba(255, 255, 255, .08) 0 1px, transparent 1px 18px);
        pointer-events: none;
      }

      .brand-content {
        position: relative;
        z-index: 1;
        height: 100%;
        display: flex;
        flex-direction: column;
      }

      .brand-mark {
        display: flex;
        align-items: center;
        gap: 14px;
      }

      .brand-mark img {
        width: 64px;
        height: 64px;
        object-fit: contain;
        padding: 8px;
        border-radius: 18px;
        background: #fff;
        box-shadow: 0 14px 30px rgba(0, 0, 0, .18);
      }

      .brand-title {
        margin: 0;
        font-size: clamp(2.1rem, 4vw, 3.65rem);
        line-height: 1;
        font-weight: 850;
        letter-spacing: 0;
      }

      .brand-subtitle {
        max-width: 510px;
        margin: 24px 0 0;
        color: rgba(255, 255, 255, .82);
        font-size: 1.05rem;
        line-height: 1.65;
      }

      .brand-highlight {
        margin-top: auto;
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
      }

      .highlight-item {
        min-height: 118px;
        padding: 18px;
        border: 1px solid rgba(255, 255, 255, .22);
        border-radius: 16px;
        background: rgba(255, 255, 255, .12);
      }

      .highlight-value {
        display: block;
        font-size: 1.5rem;
        font-weight: 850;
        line-height: 1;
      }

      .highlight-label {
        display: block;
        margin-top: 10px;
        color: rgba(255, 255, 255, .78);
        font-size: .86rem;
        line-height: 1.35;
      }

      .form-side {
        display: flex;
        align-items: center;
        padding: 44px;
        background: linear-gradient(180deg, rgba(255, 255, 255, .96), rgba(248, 251, 255, .98));
      }

      .form-wrap {
        width: 100%;
      }

      .mobile-logo {
        display: none;
      }

      .login-eyebrow {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 16px;
        padding: 7px 12px;
        border: 1px solid var(--line);
        border-radius: 999px;
        color: var(--brand-dark);
        background: var(--brand-soft);
        font-size: .78rem;
        font-weight: 750;
        text-transform: uppercase;
        letter-spacing: .08em;
      }

      .login-title {
        margin: 0;
        font-size: clamp(1.8rem, 4vw, 2.55rem);
        line-height: 1.08;
        font-weight: 850;
        letter-spacing: 0;
      }

      .login-copy {
        margin: 12px 0 28px;
        color: var(--muted);
        line-height: 1.65;
      }

      .auth-alert {
        border: 0;
        border-radius: 14px;
      }

      .form-label {
        color: #1e334f;
        font-weight: 700;
      }

      .form-control {
        min-height: 52px;
        border-color: var(--line);
        border-radius: 13px;
        color: var(--ink);
        background: #fff;
      }

      .form-control:focus {
        border-color: rgba(15, 111, 207, .6);
        box-shadow: 0 0 0 .25rem rgba(15, 111, 207, .12);
      }

      .input-group-modern {
        position: relative;
      }

      .input-group-modern .form-control {
        padding-left: 48px;
      }

      .input-icon {
        position: absolute;
        z-index: 3;
        top: 50%;
        left: 16px;
        width: 20px;
        height: 20px;
        color: #7a90aa;
        transform: translateY(-50%);
        pointer-events: none;
      }

      .password-toggle {
        position: absolute;
        z-index: 4;
        top: 50%;
        right: 10px;
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 0;
        border-radius: 11px;
        color: #60758e;
        background: transparent;
        transform: translateY(-50%);
      }

      .password-toggle:hover,
      .password-toggle:focus {
        color: var(--brand-dark);
        background: var(--brand-soft);
      }

      .password-field {
        padding-right: 56px;
      }

      .login-button {
        min-height: 54px;
        border: 0;
        border-radius: 14px;
        background: linear-gradient(135deg, var(--brand), var(--brand-dark));
        box-shadow: 0 14px 28px rgba(15, 111, 207, .24);
        font-weight: 800;
      }

      .login-button:hover,
      .login-button:focus {
        background: linear-gradient(135deg, #177fe8, #0b4f9a);
      }

      .support-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 14px;
        margin: 18px 0 0;
        color: var(--muted);
        font-size: .88rem;
      }

      .support-row a {
        color: var(--brand-dark);
        font-weight: 700;
        text-decoration: none;
      }

      .partner-footer {
        margin-top: 34px;
        padding-top: 22px;
        border-top: 1px solid var(--line);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        color: var(--muted);
        font-size: .84rem;
      }

      .partner-footer img {
        height: 38px;
        width: auto;
      }

      @media (max-width: 960px) {
        .login-panel {
          max-width: 560px;
          grid-template-columns: 1fr;
        }

        .brand-side {
          display: none;
        }

        .form-side {
          padding: 34px 24px;
        }

        .mobile-logo {
          display: flex;
          align-items: center;
          gap: 12px;
          margin-bottom: 26px;
        }

        .mobile-logo img {
          width: 56px;
          height: 56px;
          object-fit: contain;
          padding: 7px;
          border-radius: 16px;
          background: #fff;
          border: 1px solid var(--line);
        }
      }

      @media (max-width: 480px) {
        .login-shell {
          padding: 0;
          place-items: stretch;
        }

        .login-panel {
          min-height: 100vh;
          border: 0;
          border-radius: 0;
          box-shadow: none;
        }

        .form-side {
          padding: 28px 18px;
        }

        .support-row,
        .partner-footer {
          align-items: flex-start;
          flex-direction: column;
        }
      }
    </style>
  </head>
  <body>
    <script src="{{ asset('tabler/dist/js/demo-theme.min.js') }}"></script>

    <main class="login-shell">
      <section class="login-panel" aria-label="Login KOPKARTEX">
        <aside class="brand-side">
          <div class="brand-content">
            <div class="brand-mark">
              <img src="{{ asset('logo.png') }}" alt="Logo KOPKARTEX">
              <div>
                <div class="fw-bold fs-3 lh-1">KOPKARTEX</div>
                <div class="opacity-75 mt-1">Koperasi Karyawan</div>
              </div>
            </div>

            <div class="mt-6">
              <p class="login-eyebrow text-white border-white border-opacity-25 bg-white bg-opacity-10">Admin Panel</p>
              <h1 class="brand-title">Kelola operasional koperasi dengan lebih rapi.</h1>
              <p class="brand-subtitle">
                Masuk untuk mengakses transaksi, simpan pinjam, persediaan, laporan, dan pengaturan layanan anggota.
              </p>
            </div>

            <div class="brand-highlight">
              <div class="highlight-item">
                <span class="highlight-value">24/7</span>
                <span class="highlight-label">Akses data operasional</span>
              </div>
              <div class="highlight-item">
                <span class="highlight-value">Real</span>
                <span class="highlight-label">Monitoring transaksi</span>
              </div>
              <div class="highlight-item">
                <span class="highlight-value">Aman</span>
                <span class="highlight-label">Area khusus pengguna</span>
              </div>
            </div>
          </div>
        </aside>

        <div class="form-side">
          <div class="form-wrap">
            <div class="mobile-logo">
              <img src="{{ asset('logo.png') }}" alt="Logo KOPKARTEX">
              <div>
                <div class="fw-bold fs-3 lh-1">KOPKARTEX</div>
                <div class="text-muted">Admin Panel</div>
              </div>
            </div>

            <span class="login-eyebrow">
              <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-tabler icon-tabler-shield-lock" width="18" height="18" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M12 3a12 12 0 0 0 8.5 3a12 12 0 0 1 -8.5 15a12 12 0 0 1 -8.5 -15a12 12 0 0 0 8.5 -3" />
                <path d="M12 11m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                <path d="M12 12l0 2.5" />
              </svg>
              Secure Access
            </span>
            <h2 class="login-title">Selamat datang</h2>
            <p class="login-copy">Gunakan akun anda untuk login ke system.</p>

            @if (session('status'))
              <div class="alert alert-success auth-alert">
                {{ session('status') }}
              </div>
            @endif

            @if ($errors->any())
              <div class="alert alert-danger auth-alert">
                <ul class="mb-0 ps-3">
                  @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                  @endforeach
                </ul>
              </div>
            @endif

            <form method="POST" action="{{ route('login') }}" autocomplete="off" id="loginForm">
              @csrf
              <div class="mb-3">
                <label class="form-label" for="username">Username</label>
                <div class="input-group-modern">
                  <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
                    <path d="M6 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
                  </svg>
                  <input type="text" id="username" name="username" class="form-control" placeholder="Masukkan username"
                    value="{{ old('username') }}" required autofocus autocomplete="username">
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label" for="password">Password</label>
                <div class="input-group-modern">
                  <svg xmlns="http://www.w3.org/2000/svg" class="input-icon" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M5 13a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v6a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z" />
                    <path d="M11 16a1 1 0 1 0 2 0a1 1 0 0 0 -2 0" />
                    <path d="M8 11v-4a4 4 0 1 1 8 0v4" />
                  </svg>
                  <input type="password" id="password" name="password" class="form-control password-field"
                    placeholder="Masukkan password" required autocomplete="current-password">
                  <button type="button" class="password-toggle" title="Tampilkan password" id="toggle-password" aria-label="Tampilkan password">
                    <svg xmlns="http://www.w3.org/2000/svg" class="icon icon-eye" width="22" height="22" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                      <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                      <path d="M10 12a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" />
                      <path d="M21 12c-2.4 4 -5.4 6 -9 6c-3.6 0 -6.6 -2 -9 -6c2.4 -4 5.4 -6 9 -6c3.6 0 6.6 2 9 6" />
                    </svg>
                  </button>
                </div>
              </div>

              <button type="submit" id="loginBtn" class="btn btn-primary login-button w-100">
                LOGIN
              </button>
            </form>

            <div class="support-row">
              <span>KOPKARTEX &copy; {{ date('Y') }}</span>
              @if (Route::has('password.request'))
                <a href="{{ route('password.request') }}">Lupa password?</a>
              @endif
            </div>

            <div class="partner-footer">
              <div>
                <div class="fw-bold text-dark">PartnerInCode Project</div>
                <div>Modern cooperative system</div>
              </div>
              <img src="{{ asset('piclogo.png') }}" alt="PartnerInCode">
            </div>
          </div>
        </div>
      </section>
    </main>

    <script src="{{ asset('tabler/dist/js/tabler.min.js') }}" defer></script>
    <script>
      const togglePassword = document.getElementById('toggle-password');
      const passwordInput = document.getElementById('password');
      const loginForm = document.getElementById('loginForm');
      const loginBtn = document.getElementById('loginBtn');

      if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
          const isHidden = passwordInput.type === 'password';
          passwordInput.type = isHidden ? 'text' : 'password';
          this.setAttribute('title', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
          this.setAttribute('aria-label', isHidden ? 'Sembunyikan password' : 'Tampilkan password');
        });
      }

      if (loginForm && loginBtn) {
        loginForm.addEventListener('submit', function () {
          loginBtn.disabled = true;
          loginBtn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            Memproses...
          `;
        });
      }
    </script>
  </body>
</html>
