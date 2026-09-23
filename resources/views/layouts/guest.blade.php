<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="HilalEdu - Platform digital terpadu SMK Plus Al Hilal yang menghubungkan ruang belajar, pendidik, dan siswa secara cerdas dan modern">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'HilalEdu - Platform Digital SMK Plus Al Hilal')</title>
    <link rel="icon" href="{{ asset('images/logo_smk.png?v=' . time()) }}" type="image/png">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --primary-dark: #0d3b1e;
            --primary: #1a5632;
            --primary-light: #2d8a4e;
            --primary-lighter: #3daa64;
            --accent-gold: #d4a843;
            --accent-gold-light: #e8c96a;
            --bg-dark: #0a0f0d;
            --bg-card: rgba(255, 255, 255, 0.05);
            --text-light: #f0f4f1;
            --text-muted: #8a9b8f;
            --glass-bg: rgba(26, 86, 50, 0.12);
            --glass-border: rgba(212, 168, 67, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-dark);
            color: var(--text-light);
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Animated background */
        .bg-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
        }

        .bg-pattern::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 20% 50%, rgba(26, 86, 50, 0.3) 0%, transparent 50%),
                        radial-gradient(circle at 80% 20%, rgba(212, 168, 67, 0.15) 0%, transparent 40%),
                        radial-gradient(circle at 50% 80%, rgba(45, 138, 78, 0.2) 0%, transparent 50%);
            animation: bgFloat 20s ease-in-out infinite;
        }

        @keyframes bgFloat {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            33% { transform: translate(-2%, -1%) rotate(1deg); }
            66% { transform: translate(1%, 2%) rotate(-1deg); }
        }

        /* Geometric patterns */
        .geo-pattern {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            opacity: 0.03;
            background-image:
                linear-gradient(30deg, var(--accent-gold) 12%, transparent 12.5%, transparent 87%, var(--accent-gold) 87.5%, var(--accent-gold)),
                linear-gradient(150deg, var(--accent-gold) 12%, transparent 12.5%, transparent 87%, var(--accent-gold) 87.5%, var(--accent-gold)),
                linear-gradient(30deg, var(--accent-gold) 12%, transparent 12.5%, transparent 87%, var(--accent-gold) 87.5%, var(--accent-gold)),
                linear-gradient(150deg, var(--accent-gold) 12%, transparent 12.5%, transparent 87%, var(--accent-gold) 87.5%, var(--accent-gold)),
                linear-gradient(60deg, rgba(212, 168, 67, 0.5) 25%, transparent 25.5%, transparent 75%, rgba(212, 168, 67, 0.5) 75%, rgba(212, 168, 67, 0.5)),
                linear-gradient(60deg, rgba(212, 168, 67, 0.5) 25%, transparent 25.5%, transparent 75%, rgba(212, 168, 67, 0.5) 75%, rgba(212, 168, 67, 0.5));
            background-size: 80px 140px;
            background-position: 0 0, 0 0, 40px 70px, 40px 70px, 0 0, 40px 70px;
        }

        .content-wrapper {
            position: relative;
            z-index: 1;
        }

        /* Glass card */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .glass-card:hover {
            border-color: rgba(212, 168, 67, 0.4);
            box-shadow: 0 8px 40px rgba(26, 86, 50, 0.3);
            transform: translateY(-2px);
        }

        /* Floating particles */
        .particle {
            position: fixed;
            border-radius: 50%;
            pointer-events: none;
            z-index: 0;
        }

        .particle-1 {
            width: 4px;
            height: 4px;
            background: var(--accent-gold);
            top: 20%;
            left: 10%;
            animation: particleFloat 6s ease-in-out infinite;
            opacity: 0.6;
        }

        .particle-2 {
            width: 6px;
            height: 6px;
            background: var(--primary-light);
            top: 60%;
            right: 15%;
            animation: particleFloat 8s ease-in-out infinite reverse;
            opacity: 0.4;
        }

        .particle-3 {
            width: 3px;
            height: 3px;
            background: var(--accent-gold-light);
            top: 40%;
            left: 80%;
            animation: particleFloat 7s ease-in-out infinite 2s;
            opacity: 0.5;
        }

        .particle-4 {
            width: 5px;
            height: 5px;
            background: var(--primary-lighter);
            bottom: 30%;
            left: 20%;
            animation: particleFloat 9s ease-in-out infinite 1s;
            opacity: 0.3;
        }

        @keyframes particleFloat {
            0%, 100% { transform: translateY(0) translateX(0); }
            25% { transform: translateY(-30px) translateX(15px); }
            50% { transform: translateY(-10px) translateX(-10px); }
            75% { transform: translateY(-40px) translateX(5px); }
        }

        /* Button styles */
        .btn-hilal {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-hilal::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            transition: left 0.5s ease;
        }

        .btn-hilal:hover::before {
            left: 100%;
        }

        .btn-hilal:hover {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary-lighter) 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(26, 86, 50, 0.4);
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--accent-gold) 0%, var(--accent-gold-light) 100%);
            color: var(--primary-dark);
            border: none;
            padding: 12px 32px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-gold:hover {
            color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(212, 168, 67, 0.4);
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: var(--bg-dark);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--primary-light);
        }

        @yield('guest-styles')
    </style>
</head>
<body>
    <div class="bg-pattern"></div>
    <div class="geo-pattern"></div>
    <div class="particle particle-1"></div>
    <div class="particle particle-2"></div>
    <div class="particle particle-3"></div>
    <div class="particle particle-4"></div>

    <div class="content-wrapper">
        @yield('content')
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @yield('scripts')
</body>
</html>
