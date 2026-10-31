<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sesi Berakhir - HilalEdu</title>
    <link rel="icon" href="{{ asset('images/logo_smk.png') }}" type="image/png">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background-color: #0f172a;
            color: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        /* Ambient Glow */
        body::before {
            content: '';
            position: absolute;
            top: -20%;
            left: -20%;
            width: 140%;
            height: 140%;
            background: radial-gradient(circle at 30% 40%, rgba(16, 185, 129, 0.15) 0%, transparent 40%),
                        radial-gradient(circle at 70% 60%, rgba(59, 130, 246, 0.12) 0%, transparent 40%);
            pointer-events: none;
            z-index: 0;
        }

        .error-card {
            position: relative;
            z-index: 1;
            background: rgba(30, 41, 59, 0.7);
            border: 1px solid rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-radius: 24px;
            padding: 48px 36px;
            max-width: 480px;
            width: 100%;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-wrap {
            margin-bottom: 24px;
            display: inline-block;
        }

        .logo-wrap img {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            padding: 6px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .badge-expired {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(245, 158, 11, 0.15);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fbbf24;
            padding: 6px 14px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 600;
            margin-bottom: 20px;
            letter-spacing: 0.5px;
        }

        h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 12px;
            line-height: 1.3;
        }

        p {
            font-size: 0.95rem;
            color: #94a3b8;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .btn-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .btn-action {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 14px 24px;
            border-radius: 14px;
            font-size: 0.95rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(16, 185, 129, 0.4);
            color: white;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: #cbd5e1;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.12);
            color: white;
            transform: translateY(-2px);
        }

        .redirect-notice {
            margin-top: 24px;
            font-size: 0.85rem;
            color: #64748b;
        }

        .countdown-num {
            color: #34d399;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="logo-wrap">
            <img src="{{ asset('images/logo_smk.png') }}" alt="Logo SMK Plus Al Hilal">
        </div>

        <div>
            <span class="badge-expired">
                <i class="bi bi-clock-history"></i> Error 419 • Halaman Kedaluwarsa
            </span>
        </div>

        <h1>Sesi Anda Telah Berakhir</h1>
        
        <p>
            Halaman ini sudah tidak aktif dalam waktu lama demi menjaga keamanan akun Anda. Silakan masuk kembali untuk melanjutkan aktivitas.
        </p>

        <div class="btn-group">
            <a href="{{ route('login') }}" class="btn-action btn-primary">
                <i class="bi bi-box-arrow-in-right"></i>
                <span>Masuk Kembali Sekarang</span>
            </a>
            <button onclick="window.location.reload()" class="btn-action btn-secondary">
                <i class="bi bi-arrow-clockwise"></i>
                <span>Muat Ulang Halaman</span>
            </button>
        </div>

        <div class="redirect-notice">
            Otomatis dialihkan ke halaman login dalam <span id="countdown" class="countdown-num">5</span> detik...
        </div>
    </div>

    <script>
        let timeLeft = 5;
        const countdownEl = document.getElementById('countdown');
        const timer = setInterval(() => {
            timeLeft--;
            if (countdownEl) countdownEl.textContent = timeLeft;
            if (timeLeft <= 0) {
                clearInterval(timer);
                window.location.href = "{{ route('login') }}";
            }
        }, 1000);
    </script>
</body>
</html>
