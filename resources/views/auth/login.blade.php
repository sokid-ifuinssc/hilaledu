@extends('layouts.guest')

@section('title', 'Login - HilalEdu')

@section('guest-styles')
body {
    background-color: #0f172a; /* Slate 900 base */
}

.split-layout {
    display: flex;
    min-height: 100vh;
    width: 100%;
    overflow: hidden;
}

/* Left side: Image and Branding */
.split-image-side {
    flex: 1.2;
    position: relative;
    display: none;
    background-image: url('{{ asset('images/gedung_tkro.png') }}');
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

@media (min-width: 992px) {
    .split-image-side {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 60px;
    }
}

.split-image-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.9) 0%, rgba(15, 23, 42, 0.4) 50%, rgba(15, 23, 42, 0.1) 100%);
    z-index: 1;
}

.split-image-content {
    position: relative;
    z-index: 2;
    color: white;
    max-width: 500px;
    animation: fadeInLeft 1s ease;
}

.split-image-content .logo-large {
    width: 90px;
    height: 90px;
    border-radius: 24px;
    border: 2px solid rgba(255,255,255,0.2);
    padding: 4px;
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(10px);
    margin-bottom: 24px;
}

.split-image-content h1 {
    font-size: 2.5rem;
    font-weight: 800;
    line-height: 1.2;
    margin-bottom: 16px;
    background: linear-gradient(to right, #ffffff, #cbd5e1);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.split-image-content p {
    font-size: 1.1rem;
    color: #cbd5e1;
    line-height: 1.6;
}

/* Right side: Form */
.split-form-side {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 40px 20px;
    background: #0f172a;
    position: relative;
}

.split-form-side::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle at center, rgba(30, 64, 175, 0.15) 0%, transparent 50%);
    pointer-events: none;
    z-index: 0;
}

.login-wrapper {
    width: 100%;
    max-width: 440px;
    z-index: 1;
    animation: fadeInRight 0.8s ease;
}

.login-card {
    background: rgba(30, 41, 59, 0.7);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.05);
    border-radius: 24px;
    padding: 48px 40px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
}

.login-logo-mobile {
    display: none;
    text-align: center;
    margin-bottom: 32px;
}

@media (max-width: 991px) {
    .login-logo-mobile {
        display: block;
    }
}

.login-logo-mobile img {
    width: 72px;
    height: 72px;
    border-radius: 18px;
    margin-bottom: 16px;
}

.login-header {
    text-align: center;
    margin-bottom: 36px;
}

.login-header h2 {
    font-size: 1.75rem;
    font-weight: 700;
    color: white;
    margin-bottom: 8px;
}

.login-header p {
    color: #94a3b8;
    font-size: 0.95rem;
}

.form-floating-custom {
    position: relative;
    margin-bottom: 24px;
}

.form-floating-custom .form-icon {
    position: absolute;
    left: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #64748b;
    z-index: 3;
    font-size: 1.2rem;
    transition: color 0.3s ease;
}

.form-floating-custom input {
    width: 100%;
    background: rgba(15, 23, 42, 0.6);
    border: 1px solid rgba(255, 255, 255, 0.1);
    color: white;
    padding: 16px 20px 16px 52px;
    border-radius: 16px;
    font-size: 1rem;
    transition: all 0.3s ease;
    outline: none;
}

.form-floating-custom input:focus {
    border-color: #3b82f6;
    background: rgba(15, 23, 42, 0.8);
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
}

.form-floating-custom input:focus ~ .form-icon {
    color: #3b82f6;
}

.password-toggle {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    z-index: 3;
    font-size: 1.2rem;
    transition: color 0.3s ease;
}

.password-toggle:hover {
    color: white;
}

.form-check-custom {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 32px;
}

.form-check-label {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #94a3b8;
    font-size: 0.9rem;
    cursor: pointer;
}

.form-check-label input {
    width: 18px;
    height: 18px;
    accent-color: #3b82f6;
    cursor: pointer;
    border-radius: 4px;
}

.forgot-password {
    color: #3b82f6;
    font-size: 0.9rem;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.2s;
}

.forgot-password:hover {
    color: #60a5fa;
    text-decoration: underline;
}

.btn-login {
    width: 100%;
    padding: 16px;
    font-size: 1.05rem;
    font-weight: 600;
    border-radius: 16px;
    background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
    color: white;
    border: none;
    transition: all 0.3s ease;
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.btn-login:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);
}

.btn-login:active {
    transform: translateY(0);
}

.ecosystem-badges {
    display: flex;
    justify-content: center;
    flex-wrap: wrap;
    gap: 8px;
    margin-top: 32px;
    padding-top: 24px;
    border-top: 1px solid rgba(255,255,255,0.05);
}

.badge-module {
    font-size: 0.75rem;
    padding: 6px 12px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
}

.badge-akademik { color: #8b5cf6; background: rgba(139, 92, 246, 0.1); border: 1px solid rgba(139, 92, 246, 0.2); }
.badge-tracer { color: #f59e0b; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); }
.badge-bk { color: #10b981; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); }
.badge-prakerin { color: #3b82f6; background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.2); }

@keyframes fadeInLeft {
    from { opacity: 0; transform: translateX(-30px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes fadeInRight {
    from { opacity: 0; transform: translateX(30px); }
    to { opacity: 1; transform: translateX(0); }
}

.alert-error {
    background: rgba(239, 68, 68, 0.1);
    border: 1px solid rgba(239, 68, 68, 0.2);
    color: #f87171;
    border-radius: 12px;
    padding: 14px 16px;
    font-size: 0.9rem;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
}
@endsection

@section('content')
<div class="split-layout">
    
    <!-- Left Side: Image -->
    <div class="split-image-side">
        <div class="split-image-overlay"></div>
        <div class="split-image-content">
            <img src="{{ asset('images/logo_smk.png') }}" alt="Logo" class="logo-large shadow-lg">
            <h1>HilalEdu</h1>
            <p>Platform digital yang menghubungkan seluruh ruang belajar dan ekosistem pendidikan SMK Plus Al Hilal secara terpadu, inovatif, dan interaktif.</p>
        </div>
    </div>

    <!-- Right Side: Form -->
    <div class="split-form-side">
        <div class="login-wrapper">
            
            <!-- Mobile Logo -->
            <div class="login-logo-mobile">
                <img src="{{ asset('images/logo_smk.png') }}" alt="Logo SMK Plus Al Hilal" class="shadow-lg border border-slate-700">
            </div>

            <div class="login-card">
                <div class="login-header">
                    <h2>Selamat Datang</h2>
                    <div class="text-xs font-semibold text-emerald-400 tracking-wider uppercase mb-1">HilalEdu - Portal Akses Terpadu</div>
                    <p>Silakan masuk ke akun Anda</p>
                </div>

                @if(request('app'))
                    <div class="mb-6 p-3 rounded-xl text-sm text-center" style="background: rgba(59,130,246,0.1); border: 1px solid rgba(59,130,246,0.2); color: #60a5fa;">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Anda diarahkan untuk masuk ke aplikasi <strong>{{ ucfirst(str_replace(['-', '_'], ' ', request('app'))) }}</strong>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert-error">
                        <i class="bi bi-exclamation-triangle-fill text-lg"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.process') }}">
                    @csrf

                    <div class="form-floating-custom">
                        <i class="bi bi-person-fill form-icon"></i>
                        <input type="text" name="email" id="email" placeholder="Email atau Username"
                               value="{{ old('email') }}" required autofocus autocomplete="username">
                    </div>

                    <div class="form-floating-custom">
                        <i class="bi bi-lock-fill form-icon"></i>
                        <input type="password" name="password" id="password" placeholder="Kata Sandi"
                               required autocomplete="current-password">
                        <button type="button" class="password-toggle" onclick="togglePassword()" id="toggleBtn" title="Tampilkan Kata Sandi">
                            <i class="bi bi-eye-fill" id="toggleIcon"></i>
                        </button>
                    </div>

                    <div class="form-check-custom">
                        <label class="form-check-label">
                            <input type="checkbox" name="remember" id="remember">
                            <span>Ingat sesi saya</span>
                        </label>
                        <a href="#" class="forgot-password">Lupa sandi?</a>
                    </div>

                    <button type="submit" class="btn-login">
                        <span>Masuk Sekarang</span>
                        <i class="bi bi-arrow-right-short text-xl"></i>
                    </button>
                </form>

                <div class="ecosystem-badges">
                    <span class="badge-module badge-akademik"><i class="bi bi-journal-bookmark-fill"></i> Akademik</span>
                    <span class="badge-module badge-bk"><i class="bi bi-heart-pulse-fill"></i> Konseling</span>
                    <span class="badge-module badge-prakerin"><i class="bi bi-briefcase-fill"></i> Prakerin</span>
                    <span class="badge-module badge-tracer"><i class="bi bi-compass-fill"></i> Tracer Study</span>
                </div>
            </div>
            
            <div class="text-center mt-8 text-slate-500 text-sm">
                &copy; {{ date('Y') }} SMK Plus Al Hilal. Hak Cipta Dilindungi.
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.getElementById('toggleIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.classList.remove('bi-eye-fill');
            toggleIcon.classList.add('bi-eye-slash-fill');
        } else {
            passwordInput.type = 'password';
            toggleIcon.classList.remove('bi-eye-slash-fill');
            toggleIcon.classList.add('bi-eye-fill');
        }
    }
</script>
@endsection
