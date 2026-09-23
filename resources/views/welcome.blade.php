@extends('layouts.guest')

@section('title', 'HilalEdu - SMK Plus Al Hilal')

@section('guest-styles')
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    :root {
        --primary: #0ea5e9; /* Sky 500 */
        --primary-glow: rgba(14, 165, 233, 0.4);
        --secondary: #10b981; /* Emerald 500 */
        --bg-base: #020617; /* Slate 950 */
        --bg-surface: rgba(15, 23, 42, 0.6); /* Slate 900 with opacity */
        --text-main: #f8fafc; /* Slate 50 */
        --text-muted: #94a3b8; /* Slate 400 */
        --border-color: rgba(255, 255, 255, 0.1);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    body {
        background-color: var(--bg-base);
        color: var(--text-main);
        min-height: 100vh;
        overflow-x: hidden;
        position: relative;
        display: flex;
        flex-direction: column;
    }

    /* Elegant Background Glows */
    .bg-glow {
        position: fixed;
        border-radius: 50%;
        filter: blur(120px);
        z-index: -1;
        opacity: 0.5;
        animation: pulse 10s ease-in-out infinite alternate;
    }

    .glow-1 {
        top: -10%;
        left: -10%;
        width: 50vw;
        height: 50vw;
        background: radial-gradient(circle, rgba(14,165,233,0.3) 0%, rgba(2,6,23,0) 70%);
    }

    .glow-2 {
        bottom: -20%;
        right: -10%;
        width: 60vw;
        height: 60vw;
        background: radial-gradient(circle, rgba(16,185,129,0.2) 0%, rgba(2,6,23,0) 70%);
        animation-delay: -5s;
    }

    @keyframes pulse {
        0% { transform: scale(1) translate(0, 0); }
        100% { transform: scale(1.1) translate(20px, -20px); }
    }

    /* Navbar */
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1.25rem 4%;
        background: rgba(2, 6, 23, 0.7);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--border-color);
        position: sticky;
        top: 0;
        z-index: 50;
    }

    .nav-brand {
        display: flex;
        align-items: center;
        gap: 1rem;
        text-decoration: none;
        color: var(--text-main);
    }

    .nav-logo {
        width: 42px;
        height: 42px;
        border-radius: 10px;
        object-fit: cover;
    }

    .nav-title {
        display: flex;
        flex-direction: column;
    }

    .nav-title-main {
        font-size: 1.15rem;
        font-weight: 700;
        letter-spacing: -0.02em;
    }

    .nav-title-sub {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.1em;
    }

    /* Hero Section */
    .hero {
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        text-align: center;
        padding: 4rem 2rem;
        position: relative;
        z-index: 10;
    }

    .badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--border-color);
        border-radius: 100px;
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--primary);
        margin-bottom: 2rem;
        backdrop-filter: blur(10px);
    }

    .hero-title {
        font-size: clamp(3rem, 8vw, 5.5rem);
        font-weight: 800;
        line-height: 1.1;
        letter-spacing: -0.03em;
        margin-bottom: 1.5rem;
        max-width: 900px;
    }

    .text-gradient {
        background: linear-gradient(135deg, #fff 0%, #94a3b8 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    .text-accent {
        background: linear-gradient(135deg, #38bdf8 0%, #34d399 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .hero-subtitle {
        font-size: clamp(1rem, 2vw, 1.25rem);
        color: var(--text-muted);
        max-width: 600px;
        margin: 0 auto 3rem auto;
        line-height: 1.6;
    }

    .cta-group {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        padding: 1rem 2.5rem;
        border-radius: 100px;
        font-weight: 600;
        font-size: 1.05rem;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .btn-primary {
        background: var(--text-main);
        color: var(--bg-base);
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.1);
    }

    .btn-primary:hover {
        background: #e2e8f0;
        transform: translateY(-2px);
        box-shadow: 0 10px 30px rgba(255, 255, 255, 0.2);
    }

    .btn-outline {
        background: rgba(255, 255, 255, 0.05);
        color: var(--text-main);
        border: 1px solid var(--border-color);
        backdrop-filter: blur(10px);
    }

    .btn-outline:hover {
        background: rgba(255, 255, 255, 0.1);
        transform: translateY(-2px);
    }

    /* Feature Cards */
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        width: 100%;
        max-width: 1200px;
        margin: 4rem auto 2rem;
        padding: 0 2rem;
    }

    .feature-card {
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: 24px;
        padding: 2rem;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        transition: transform 0.3s ease, border-color 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }

    .feature-card:hover {
        transform: translateY(-5px);
        border-color: rgba(255, 255, 255, 0.2);
    }

    .feature-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: rgba(255, 255, 255, 0.05);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: var(--primary);
        margin-bottom: 1.5rem;
        border: 1px solid var(--border-color);
    }

    .feature-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
        color: var(--text-main);
    }

    .feature-desc {
        color: var(--text-muted);
        font-size: 0.95rem;
        line-height: 1.6;
    }

    /* Animation on load */
    .fade-in-up {
        animation: fadeInUp 1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        opacity: 0;
        transform: translateY(20px);
    }

    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }
    .delay-4 { animation-delay: 0.4s; }

    @keyframes fadeInUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>
@endsection

@section('content')
<!-- Ambient Glow Background -->
<div class="bg-glow glow-1"></div>
<div class="bg-glow glow-2"></div>

<!-- Navigation -->
<nav class="navbar">
    <a href="#" class="nav-brand">
        <img src="{{ asset('images/logo_smk.png') }}" alt="Logo SMK" class="nav-logo">
        <div class="nav-title">
            <span class="nav-title-main">SMK Plus Al Hilal</span>
            <span class="nav-title-sub">Portal Digital</span>
        </div>
    </a>
    <div class="nav-actions">
        @auth
            <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="btn btn-outline" style="padding: 0.6rem 1.5rem; font-size: 0.9rem;">
                Dashboard <i class="bi bi-arrow-right"></i>
            </a>
        @else
            <a href="{{ route('login') }}" class="btn btn-outline" style="padding: 0.6rem 1.5rem; font-size: 0.9rem;">
                Masuk <i class="bi bi-box-arrow-in-right"></i>
            </a>
        @endauth
    </div>
</nav>

<!-- Main Content -->
<main class="hero">
    <div class="badge fade-in-up">
        <i class="bi bi-rocket-takeoff-fill"></i> Sistem Informasi Manajemen Sekolah
    </div>
    
    <h1 class="hero-title fade-in-up delay-1">
        <span class="text-gradient">Selamat Datang di</span><br>
        <span class="text-accent">Portal HilalEdu</span>
    </h1>
    
    <p class="hero-subtitle fade-in-up delay-2">
        Platform ekosistem digital cerdas untuk mendukung dan mengintegrasikan seluruh kegiatan akademik, administrasi, dan bimbingan di SMK Plus Al Hilal.
    </p>
    
    <div class="cta-group fade-in-up delay-3">
        @auth
            <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="btn btn-primary">
                Akses Dashboard <i class="bi bi-arrow-right"></i>
            </a>
        @else
            <a href="{{ route('login') }}" class="btn btn-primary">
                Mulai Sesi <i class="bi bi-shield-lock"></i>
            </a>
        @endauth
        <a href="https://maps.app.goo.gl/StLDJ6QW9cA71kzg9" target="_blank" class="btn btn-outline">
            Lokasi Sekolah <i class="bi bi-geo-alt"></i>
        </a>
    </div>

    <!-- Features -->
    <div class="features-grid fade-in-up delay-4">
        <div class="feature-card">
            <div class="feature-icon" style="color: #38bdf8;">
                <i class="bi bi-laptop"></i>
            </div>
            <h3 class="feature-title">Akademik Terpadu</h3>
            <p class="feature-desc">Akses jadwal pelajaran, materi, penugasan, hingga rekapitulasi nilai secara real-time dan terstruktur.</p>
        </div>
        
        <div class="feature-card">
            <div class="feature-icon" style="color: #34d399;">
                <i class="bi bi-briefcase"></i>
            </div>
            <h3 class="feature-title">Sistem Prakerin</h3>
            <p class="feature-desc">Pemantauan kegiatan Praktik Kerja Lapangan (PKL), mulai dari jurnal harian hingga laporan evaluasi.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon" style="color: #fb923c;">
                <i class="bi bi-shield-check"></i>
            </div>
            <h3 class="feature-title">Bimbingan Karakter</h3>
            <p class="feature-desc">Pencatatan perkembangan siswa, monitoring kedisiplinan, dan layanan Bimbingan Konseling yang komprehensif.</p>
        </div>
    </div>
</main>
@endsection
