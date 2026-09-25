<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - HilalEdu</title>
    <link rel="icon" href="{{ asset('images/logo_smk.png?v=' . time()) }}" type="image/png">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Tailwind CSS Play CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: false,
            },
            theme: {
                extend: {
                    colors: {
                        emerald: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
                            300: '#6ee7b7',
                            400: '#34d399',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                            950: '#022c22',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* Alpine.js cloak safeguard: prevent uninitialized components from displaying */
        [x-cloak] { display: none !important; }

        /* SVG sizing constraints: prevent unconstrained SVG from expanding full screen */
        svg { max-width: 100%; display: inline-block; vertical-align: middle; }
        svg.w-3 { width: 0.75rem !important; height: 0.75rem !important; flex-shrink: 0; }
        svg.w-3\.5 { width: 0.875rem !important; height: 0.875rem !important; flex-shrink: 0; }
        svg.w-4 { width: 1rem !important; height: 1rem !important; flex-shrink: 0; }
        svg.w-5 { width: 1.25rem !important; height: 1.25rem !important; flex-shrink: 0; }
        svg.w-6 { width: 1.5rem !important; height: 1.5rem !important; flex-shrink: 0; }
        svg.w-7 { width: 1.75rem !important; height: 1.75rem !important; flex-shrink: 0; }
        svg.w-8 { width: 2rem !important; height: 2rem !important; flex-shrink: 0; }
        svg.w-10 { width: 2.5rem !important; height: 2.5rem !important; flex-shrink: 0; }
        svg.w-12 { width: 3rem !important; height: 3rem !important; flex-shrink: 0; }
        svg.w-16 { width: 4rem !important; height: 4rem !important; flex-shrink: 0; }
        :root {
            /* Palette Seimbang: Tidak terlalu gelap & tidak terlalu silau */
            --primary: #059669;
            --primary-hover: #047857;
            --primary-light: #10b981;
            --primary-soft: #ecfdf5;
            
            --accent-amber: #d97706;
            --accent-amber-soft: #fef3c7;
            
            --bg-body: #f8fafc;        /* Slate-50: Bersih, sejuk, nyaman di mata */
            --bg-sidebar: #0f172a;     /* Slate-900: Anggun, kontras tinggi, ramah dibaca */
            --bg-sidebar-hover: rgba(255, 255, 255, 0.07);
            --bg-card: #ffffff;
            --bg-card-hover: #f1f5f9;
            
            --text-main: #1e293b;       /* Slate-800: Hitam lembut, sangat nyaman dibaca */
            --text-muted: #64748b;      /* Slate-500: Teks sekunder jelas */
            --text-light: #1e293b;      /* Safeguard fallback for legacy cards: Slate-800 */
            --text-sidebar: #94a3b8;    /* Slate-400: Teks menu sidebar */
            --text-sidebar-active: #ffffff;
            
            --border-color: #e2e8f0;    /* Border lembut */
            --border-sidebar: rgba(255, 255, 255, 0.08);
            
            --sidebar-width: 270px;
            --topbar-height: 64px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Plus Jakarta Sans', 'Poppins', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-main);
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* High Contrast Slate & Status Utilities Safeguard */
        .text-slate-900 { color: #0f172a !important; }
        .text-slate-800 { color: #1e293b !important; }
        .text-slate-700 { color: #334155 !important; }
        .text-slate-600 { color: #475569 !important; }
        .text-slate-500 { color: #64748b !important; }
        .text-slate-400 { color: #94a3b8 !important; }
        .text-emerald-700 { color: #047857 !important; }
        .text-emerald-800 { color: #065f46 !important; }
        .text-emerald-900 { color: #064e3b !important; }
        .text-rose-600 { color: #e11d48 !important; }
        .text-rose-700 { color: #be123c !important; }
        .text-amber-800 { color: #92400e !important; }
        .bg-emerald-100 { background-color: #d1fae5 !important; }
        .bg-amber-100 { background-color: #fef3c7 !important; }
        .border-emerald-300 { border-color: #6ee7b7 !important; }
        .border-amber-300 { border-color: #fcd34d !important; }

        /* ============ SIDEBAR ============ */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-sidebar);
            z-index: 1045;
            display: flex;
            flex-direction: column;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }

        .sidebar-brand {
            padding: 16px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-sidebar);
            min-height: var(--topbar-height);
            background: rgba(15, 23, 42, 0.95);
        }

        .sidebar-brand img {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            border: 1.5px solid rgba(16, 185, 129, 0.3);
            object-fit: cover;
            flex-shrink: 0;
        }

        .sidebar-brand-text {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            white-space: nowrap;
        }

        .sidebar-brand-text .brand-name {
            font-size: 1.05rem;
            font-weight: 800;
            color: #ffffff;
            line-height: 1.2;
            letter-spacing: -0.3px;
        }

        .sidebar-brand-text .brand-sub {
            font-size: 0.7rem;
            color: var(--primary-light);
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .sidebar-content {
            flex: 1;
            padding: 16px 12px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-section-title {
            font-size: 0.68rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            padding: 14px 12px 6px;
            white-space: nowrap;
        }

        .sidebar-nav {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav-item {
            margin-bottom: 2px;
        }

        .sidebar-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 9px 14px;
            color: var(--text-sidebar);
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.86rem;
            font-weight: 500;
            transition: all 0.2s ease;
            white-space: nowrap;
            width: 100%;
            background: transparent;
            border: none;
            text-align: left;
            font-family: inherit;
            cursor: pointer;
            outline: none;
        }

        .sidebar-nav-link:hover {
            background: var(--bg-sidebar-hover);
            color: #ffffff;
        }

        .sidebar-nav-link.active {
            background: var(--primary);
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.35);
        }

        .sidebar-nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
            flex-shrink: 0;
        }

        /* ============ SIDEBAR DROPDOWN SYSTEM (NO COLLISION WITH TAILWIND/BOOTSTRAP) ============ */
        .sidebar-dropdown-btn {
            width: 100%;
            background: transparent;
            border: none;
            text-align: left;
            font-family: inherit;
            cursor: pointer;
            outline: none;
        }

        .sidebar-dropdown-menu {
            display: none !important;
            overflow: hidden;
            margin: 2px 0 6px 12px;
            border-left: 2px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-dropdown-menu.show {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .sidebar-submenu {
            list-style: none !important;
            padding: 4px 0 4px 8px !important;
            margin: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 2px !important;
        }

        .sidebar-submenu li {
            list-style: none !important;
            margin: 0 !important;
            padding: 0 !important;
            display: block !important;
        }

        .sidebar-submenu li a {
            color: #94a3b8 !important;
            text-decoration: none !important;
            font-size: 0.82rem !important;
            font-weight: 500 !important;
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
            padding: 6px 12px !important;
            border-radius: 8px !important;
            transition: all 0.15s ease !important;
            white-space: nowrap !important;
        }

        .sidebar-submenu li a:hover {
            color: #ffffff !important;
            background: rgba(255, 255, 255, 0.08) !important;
            transform: translateX(3px);
        }

        .sidebar-submenu li a.active {
            color: #34d399 !important;
            background: rgba(16, 185, 129, 0.15) !important;
            font-weight: 700 !important;
        }

        .arrow-icon {
            transition: transform 0.25s ease;
            font-size: 0.75rem !important;
            width: auto !important;
            flex-shrink: 0;
        }

        .sidebar-nav-link[aria-expanded="true"] .arrow-icon,
        .sidebar-dropdown-btn[aria-expanded="true"] .arrow-icon {
            transform: rotate(180deg);
        }

        /* Generic Bootstrap Collapse Safety (Outside Sidebar) */
        .collapse:not(.show) {
            display: none !important;
        }
        .collapse.show {
            display: block !important;
            visibility: visible !important;
        }

        .sidebar-footer {
            padding: 14px;
            border-top: 1px solid var(--border-sidebar);
            background: rgba(15, 23, 42, 0.9);
        }

        .sidebar-user {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            overflow: hidden;
        }

        .sidebar-user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .sidebar-user-info {
            overflow: hidden;
            white-space: nowrap;
        }

        .sidebar-user-name {
            font-size: 0.82rem;
            font-weight: 600;
            color: #ffffff;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            font-size: 0.7rem;
            color: var(--primary-light);
            font-weight: 500;
            text-transform: capitalize;
        }

        /* ============ TOPBAR ============ */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--topbar-height);
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            transition: left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .topbar-toggle {
            background: #f1f5f9;
            border: 1px solid var(--border-color);
            color: var(--text-main);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 6px 10px;
            border-radius: 8px;
            transition: all 0.2s ease;
            display: none;
        }

        .topbar-toggle:hover {
            background: #e2e8f0;
        }

        .topbar-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-main);
            letter-spacing: -0.2px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .topbar-btn {
            background: #f8fafc;
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            font-size: 1.1rem;
            cursor: pointer;
            padding: 7px 10px;
            border-radius: 10px;
            transition: all 0.2s ease;
            position: relative;
        }

        .topbar-btn:hover {
            background: #f1f5f9;
            color: var(--text-main);
        }

        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #fef2f2;
            border: 1px solid #fee2e2;
            color: #dc2626;
            padding: 7px 14px;
            border-radius: 10px;
            font-size: 0.82rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .logout-btn:hover {
            background: #fee2e2;
            border-color: #fca5a5;
        }

        /* ============ MAIN CONTENT ============ */
        .main-content {
            margin-left: var(--sidebar-width);
            padding-top: var(--topbar-height);
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .content-area {
            padding: 24px;
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Card Global Standards */
        .card, .info-card, .stat-card {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            color: var(--text-main);
        }

        /* Standard Table Responsive Scroll */
        .table-responsive {
            border-radius: 12px;
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        table.table {
            color: var(--text-main);
            border-color: var(--border-color);
            margin-bottom: 0;
        }

        table.table thead th {
            background-color: #f8fafc;
            color: #475569;
            font-weight: 700;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 1px solid var(--border-color);
        }

        /* Global Form Controls: Clean & Light */
        input.form-control,
        select.form-control,
        select.form-select,
        textarea.form-control {
            background-color: #ffffff !important;
            border: 1px solid #cbd5e1 !important;
            color: #1e293b !important;
            border-radius: 10px !important;
            font-size: 0.88rem !important;
            padding: 8px 12px !important;
        }

        input.form-control:focus,
        select.form-select:focus,
        textarea.form-control:focus {
            border-color: var(--primary) !important;
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15) !important;
        }

        select option {
            background-color: #ffffff !important;
            color: #1e293b !important;
            padding: 8px !important;
        }

        /* Overlay Mobile */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.5);
            backdrop-filter: blur(3px);
            z-index: 1040;
        }

        /* Scrollbar */
        .sidebar-content::-webkit-scrollbar {
            width: 5px;
        }
        .sidebar-content::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-content::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 3px;
        }

        /* ============ RESPONSIVE BREAKPOINTS ============ */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar-overlay.show {
                display: block;
            }

            .topbar {
                left: 0;
                padding: 0 16px;
            }

            .topbar-toggle {
                display: block;
            }

            .main-content {
                margin-left: 0;
            }

            .content-area {
                padding: 16px;
            }
        }

        /* Alert toast */
        .alert-toast {
            position: fixed;
            top: 76px;
            right: 20px;
            z-index: 1060;
            min-width: 320px;
            max-width: 90vw;
            animation: slideInRight 0.3s ease;
        }

        @keyframes slideInRight {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* ============ TABLE CONTAINER ============ */
        .table-container {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
            overflow: hidden;
        }

        .table-container table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text-main);
        }

        .table-container table thead tr {
            background: #f8fafc;
        }

        .table-container table thead th {
            padding: 11px 16px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .table-container table tbody td {
            padding: 11px 16px;
            font-size: 0.875rem;
            color: var(--text-main);
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .table-container table tbody tr:last-child td {
            border-bottom: none;
        }

        .table-container table tbody tr:hover {
            background: #f8fafc;
        }

        /* ============ BUTTONS ============ */
        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 18px;
            background: var(--primary);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            line-height: 1.4;
        }

        .btn-primary:hover {
            background: var(--primary-hover);
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(5, 150, 105, 0.25);
        }

        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            padding: 9px 18px;
            background: #f1f5f9;
            color: var(--text-main);
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s ease;
            line-height: 1.4;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            color: var(--text-main);
        }

        .btn-success {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 9px 18px; background: #16a34a; color: #ffffff; border: none;
            border-radius: 10px; font-size: 0.85rem; font-weight: 700; cursor: pointer;
            text-decoration: none; transition: all 0.2s ease; line-height: 1.4;
        }
        .btn-success:hover { background: #15803d; color: #ffffff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(22,163,74,0.25); }

        .btn-danger {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 9px 18px; background: #dc2626; color: #ffffff; border: none;
            border-radius: 10px; font-size: 0.85rem; font-weight: 700; cursor: pointer;
            text-decoration: none; transition: all 0.2s ease; line-height: 1.4;
        }
        .btn-danger:hover { background: #b91c1c; color: #ffffff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(220,38,38,0.25); }

        .btn-warning {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 9px 18px; background: #d97706; color: #ffffff; border: none;
            border-radius: 10px; font-size: 0.85rem; font-weight: 700; cursor: pointer;
            text-decoration: none; transition: all 0.2s ease; line-height: 1.4;
        }
        .btn-warning:hover { background: #b45309; color: #ffffff; transform: translateY(-1px); }

        .btn-info {
            display: inline-flex; align-items: center; justify-content: center; gap: 6px;
            padding: 9px 18px; background: #0284c7; color: #ffffff; border: none;
            border-radius: 10px; font-size: 0.85rem; font-weight: 700; cursor: pointer;
            text-decoration: none; transition: all 0.2s ease; line-height: 1.4;
        }
        .btn-info:hover { background: #0369a1; color: #ffffff; transform: translateY(-1px); }

        /* ============ BADGE ============ */
        .badge {
            display: inline-flex; align-items: center;
            padding: 2px 10px; border-radius: 999px;
            font-size: 0.73rem; font-weight: 700; line-height: 1.5;
        }

        /* ============ STAT CARD PADDING ============ */
        .stat-card {
            padding: 20px;
        }

        /* ============ FORM TEXTAREA ============ */
        .form-textarea {
            width: 100%;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            color: #1e293b;
            border-radius: 10px;
            font-size: 0.85rem;
            padding: 8px 12px;
            resize: vertical;
        }

        .form-textarea:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
            outline: none;
        }

        @yield('dashboard-styles')
    </style>
</head>
<body>
    <!-- Sidebar Overlay Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>

    <!-- Sidebar Utama -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/logo_smk.png?v=' . time()) }}" alt="HilalEdu Logo">
            <div class="sidebar-brand-text">
                <span class="brand-name">HilalEdu</span>
                <span class="brand-sub">SMK Plus Al Hilal</span>
            </div>
        </div>

        <div class="sidebar-content">
            @if(auth()->user()->isSuperAdmin() || auth()->user()->role === 'admin' || auth()->user()->admin_role)
                {{-- ============================================================ --}}
                {{-- 1. PUSAT KENDALI (DASHBOARD)                                 --}}
                {{-- ============================================================ --}}
                <div class="sidebar-section-title">Pusat Kendali</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ auth()->user()->isSuperAdmin() ? route('superadmin.dashboard') : route(auth()->user()->admin_role . '.dashboard') }}"
                           class="sidebar-nav-link {{ request()->routeIs('superadmin.dashboard') || (auth()->user()->admin_role && request()->routeIs(auth()->user()->admin_role . '.dashboard')) ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard Utama</span>
                        </a>
                    </li>
                </ul>

                {{-- ============================================================ --}}
                {{-- 2. DATA MASTER POKOK (Fondasi Awal)                          --}}
                {{-- ============================================================ --}}
                @if(auth()->user()->isSuperAdmin())
                <div class="sidebar-section-title">Data Master Pokok</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('superadmin.master.tahun-ajaran') }}" class="sidebar-nav-link {{ request()->routeIs('superadmin.master.tahun-ajaran*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check" style="color:#10b981;"></i>
                            <span>Tahun Ajaran</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('superadmin.master.jurusan') }}" class="sidebar-nav-link {{ request()->routeIs('superadmin.master.jurusan*') ? 'active' : '' }}">
                            <i class="bi bi-diagram-3" style="color:#10b981;"></i>
                            <span>Jurusan / Keahlian</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('superadmin.master.kelas') }}" class="sidebar-nav-link {{ request()->routeIs('superadmin.master.kelas*') ? 'active' : '' }}">
                            <i class="bi bi-collection" style="color:#10b981;"></i>
                            <span>Data Kelas</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('superadmin.rombel.index') }}" class="sidebar-nav-link {{ request()->routeIs('superadmin.rombel.*') ? 'active' : '' }}">
                            <i class="bi bi-grid-3x3-gap" style="color:#10b981;"></i>
                            <span>Rombel Siswa</span>
                        </a>
                    </li>
                    {{-- Master Mapel disembunyikan sesuai permintaan agar tidak redundan dengan Kurikulum
                    <li class="sidebar-nav-item">
                        <a href="{{ route('akademik.mapel.index') }}" class="sidebar-nav-link {{ request()->routeIs('akademik.mapel.*') ? 'active' : '' }}">
                            <i class="bi bi-bookmark-check" style="color:#10b981;"></i>
                            <span>Mata Pelajaran</span>
                        </a>
                    </li>
                    --}}
                    <li class="sidebar-nav-item">
                        <a href="{{ route('superadmin.master.tugas-tambahan') }}" class="sidebar-nav-link {{ request()->routeIs('superadmin.master.tugas-tambahan*') ? 'active' : '' }}">
                            <i class="bi bi-award" style="color:#10b981;"></i>
                            <span>Tugas Tambahan Guru</span>
                        </a>
                    </li>
                </ul>

                {{-- ============================================================ --}}
                {{-- 3. DATA PERSONALIA & WARGA SEKOLAH                           --}}
                {{-- ============================================================ --}}
                <div class="sidebar-section-title">Warga Sekolah & Akun</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('superadmin.guru.index') }}" class="sidebar-nav-link {{ request()->routeIs('superadmin.guru.*') || request()->routeIs('admin.guru.*') ? 'active' : '' }}">
                            <i class="bi bi-person-workspace" style="color:#38bdf8;"></i>
                            <span>Dewan Guru</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('superadmin.tendik.index') }}" class="sidebar-nav-link {{ request()->routeIs('superadmin.tendik.*') ? 'active' : '' }}">
                            <i class="bi bi-person-badge" style="color:#38bdf8;"></i>
                            <span>Tenaga Kependidikan</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('superadmin.penugasan-admin.index') }}" class="sidebar-nav-link {{ request()->routeIs('superadmin.penugasan-admin.*') ? 'active' : '' }}">
                            <i class="bi bi-person-gear" style="color:#10b981;"></i>
                            <span>Penunjukan Admin (Guru & TU)</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('superadmin.siswa.index') }}" class="sidebar-nav-link {{ request()->routeIs('superadmin.siswa.*') || request()->routeIs('admin.siswa.*') ? 'active' : '' }}">
                            <i class="bi bi-mortarboard" style="color:#38bdf8;"></i>
                            <span>Data Siswa</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('superadmin.users.index') }}" class="sidebar-nav-link {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">
                            <i class="bi bi-people" style="color:#38bdf8;"></i>
                            <span>Semua Akun Pengguna</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('superadmin.credentials.index') }}" class="sidebar-nav-link {{ request()->routeIs('superadmin.credentials.*') ? 'active' : '' }}">
                            <i class="bi bi-key" style="color:#fbbf24;"></i>
                            <span>Kredensial & Reset Akun</span>
                        </a>
                    </li>
                </ul>
                @endif

                {{-- ============================================================ --}}
                {{-- 4. LAYANAN PEMBELAJARAN & AKADEMIK                           --}}
                {{-- ============================================================ --}}
                <div class="sidebar-section-title">Akademik & KBM</div>
                <ul class="sidebar-nav">
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->admin_role === 'akademik' || auth()->user()->role === 'guru')
                    <li class="sidebar-nav-item">
                        <button type="button" 
                                class="sidebar-nav-link sidebar-dropdown-btn {{ request()->routeIs('akademik.*') ? 'active' : '' }}" 
                                onclick="toggleSidebarDropdown('menuAkademik')" 
                                aria-expanded="{{ request()->routeIs('akademik.*') ? 'true' : 'false' }}">
                            <i class="bi bi-book-fill" style="color:#38bdf8;"></i>
                            <span>Layanan Akademik</span>
                            <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
                        </button>
                        <div class="sidebar-dropdown-menu {{ request()->routeIs('akademik.*') ? 'show' : '' }}" id="menuAkademik">
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('akademik.dashboard') }}" class="{{ request()->routeIs('akademik.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-1"></i> Dashboard Akademik</a></li>
                                <li><a href="{{ route('akademik.kalender.index') }}" class="{{ request()->routeIs('akademik.kalender.*') ? 'active' : '' }}"><i class="bi bi-calendar-event me-1"></i> Kalender Pendidikan</a></li>
                                <li><a href="{{ route('akademik.kurikulum.index') }}" class="{{ request()->routeIs('akademik.kurikulum.*') ? 'active' : '' }}"><i class="bi bi-diagram-3 me-1"></i> Kurikulum & Beban Ajar</a></li>
                                <li><a href="{{ route('akademik.jadwal.index') }}" class="{{ request()->routeIs('akademik.jadwal.index') ? 'active' : '' }}"><i class="bi bi-clock-history me-1"></i> Jadwal Pelajaran</a></li>
                                <li><a href="{{ route('akademik.jadwal.matrix') }}" class="{{ request()->routeIs('akademik.jadwal.matrix*') ? 'active' : '' }}"><i class="bi bi-grid-3x3 me-1"></i> Matriks Jadwal Resmi</a></li>
                                <li><a href="{{ route('akademik.nilai.index') }}" class="{{ request()->routeIs('akademik.nilai.*') ? 'active' : '' }}"><i class="bi bi-award me-1"></i> Rekap Nilai Siswa</a></li>
                                <li><a href="{{ route('akademik.laporan.kehadiran.index') }}" class="{{ request()->routeIs('akademik.laporan.kehadiran.*') ? 'active' : '' }}"><i class="bi bi-person-check me-1"></i> Presensi Mengajar Guru</a></li>
                                <li><a href="{{ route('presensi-harian.index') }}" class="{{ request()->routeIs('presensi-harian.*') ? 'active' : '' }}"><i class="bi bi-person-check-fill me-1"></i> Presensi Harian Siswa</a></li>
                                <li><a href="{{ route('akademik.laporan.kbm.index') }}" class="{{ request()->routeIs('akademik.laporan.kbm.*') ? 'active' : '' }}"><i class="bi bi-journal-text me-1"></i> Jurnal Realisasi KBM</a></li>
                                <li><a href="{{ route('akademik.rekap-presensi.index') }}" class="{{ request()->routeIs('akademik.rekap-presensi.*') ? 'active' : '' }}"><i class="bi bi-clipboard-data me-1"></i> Rekapitulasi Presensi Mapel</a></li>
                                <li><a href="{{ route('akademik.piket.index') }}" class="{{ request()->routeIs('akademik.piket.*') ? 'active' : '' }}"><i class="bi bi-shield-shaded me-1"></i> Jadwal Piket Sekolah</a></li>
                                <li><a href="{{ route('akademik.keluhan.index') }}" class="{{ request()->routeIs('akademik.keluhan.*') ? 'active' : '' }}"><i class="bi bi-chat-left-text me-1"></i> Suara & Evaluasi Siswa</a></li>
                                <li><a href="{{ route('akademik.pengaturan.index') }}" class="{{ request()->routeIs('akademik.pengaturan.*') ? 'active' : '' }}"><i class="bi bi-gear me-1"></i> Pengaturan Akademik</a></li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    {{-- ============================================================ --}}
                    {{-- 5. KESISWAAN & BIMBINGAN KONSELING (BK)                      --}}
                    {{-- ============================================================ --}}
                    @if(auth()->check() && auth()->user()->canAccessBk())
                    <li class="sidebar-nav-item">
                        <button type="button" 
                                class="sidebar-nav-link sidebar-dropdown-btn {{ request()->routeIs('bk.*') ? 'active' : '' }}" 
                                onclick="toggleSidebarDropdown('menuBK')" 
                                aria-expanded="{{ request()->routeIs('bk.*') ? 'true' : 'false' }}">
                            <i class="bi bi-shield-check" style="color:#f87171;"></i>
                            <span>Monitoring BK</span>
                            <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
                        </button>
                        <div class="sidebar-dropdown-menu {{ request()->routeIs('bk.*') ? 'show' : '' }}" id="menuBK">
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('bk.dashboard') }}" class="{{ request()->routeIs('bk.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-1"></i> Dashboard BK</a></li>
                                <li><a href="{{ route('bk.pelanggaran.index') }}" class="{{ request()->routeIs('bk.pelanggaran.*') ? 'active' : '' }}"><i class="bi bi-exclamation-octagon me-1"></i> Catatan Pelanggaran</a></li>
                                <li><a href="{{ route('bk.progres.index') }}" class="{{ request()->routeIs('bk.progres.*') ? 'active' : '' }}"><i class="bi bi-arrow-repeat me-1"></i> Progres Kasus & Bimbingan</a></li>
                                <li><a href="{{ route('bk.poin.index') }}" class="{{ request()->routeIs('bk.poin.*') ? 'active' : '' }}"><i class="bi bi-award me-1"></i> Akumulasi Poin Siswa</a></li>
                                <li><a href="{{ route('bk.jenis-pelanggaran.index') }}" class="{{ request()->routeIs('bk.jenis-pelanggaran.*') ? 'active' : '' }}"><i class="bi bi-tags me-1"></i> Data Pelanggaran</a></li>
                                <li><a href="{{ route('bk.laporan.index') }}" class="{{ request()->routeIs('bk.laporan.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-ruled me-1"></i> Laporan Berkala BK</a></li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    {{-- ============================================================ --}}
                    {{-- 6. PRAKTIK KERJA INDUSTRI (PRAKERIN / PKL)                   --}}
                    {{-- ============================================================ --}}
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->hasTugas('Prakerin') || auth()->user()->hasTugas('PKL') || (auth()->user()->role === 'admin' && (!auth()->user()->admin_role || auth()->user()->admin_role === 'prakerin')))
                    <li class="sidebar-nav-item">
                        <button type="button" 
                                class="sidebar-nav-link sidebar-dropdown-btn {{ request()->routeIs('prakerin.*') ? 'active' : '' }}" 
                                onclick="toggleSidebarDropdown('menuPrakerin')" 
                                aria-expanded="{{ request()->routeIs('prakerin.*') ? 'true' : 'false' }}">
                            <i class="bi bi-briefcase-fill" style="color:#fb923c;"></i>
                            <span>Prakerin / PKL</span>
                            <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
                        </button>
                        <div class="sidebar-dropdown-menu {{ request()->routeIs('prakerin.*') ? 'show' : '' }}" id="menuPrakerin">
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('prakerin.dashboard') }}" class="{{ request()->routeIs('prakerin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-1"></i> Dashboard Prakerin</a></li>
                                <li><a href="{{ route('prakerin.dudi.index') }}" class="{{ request()->routeIs('prakerin.dudi.*') ? 'active' : '' }}"><i class="bi bi-buildings me-1"></i> Mitra DU/DI Perusahaan</a></li>
                                <li><a href="{{ route('prakerin.pembimbing-dudi.index') }}" class="{{ request()->routeIs('prakerin.pembimbing-dudi.*') ? 'active' : '' }}"><i class="bi bi-person-badge me-1"></i> Instruktur DU/DI</a></li>
                                <li><a href="{{ route('prakerin.periode.index') }}" class="{{ request()->routeIs('prakerin.periode.*') ? 'active' : '' }}"><i class="bi bi-calendar-range me-1"></i> Periode Prakerin</a></li>
                                <li><a href="{{ route('prakerin.penempatan.index') }}" class="{{ request()->routeIs('prakerin.penempatan.*') ? 'active' : '' }}"><i class="bi bi-person-workspace me-1"></i> Penempatan Siswa</a></li>
                                <li><a href="{{ route('prakerin.jurnal.index') }}" class="{{ request()->routeIs('prakerin.jurnal.*') ? 'active' : '' }}"><i class="bi bi-journal-check me-1"></i> Jurnal Siswa</a></li>
                                <li><a href="{{ route('prakerin.laporan.index') }}" class="{{ request()->routeIs('prakerin.laporan.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan Nilai Prakerin</a></li>
                            </ul>
                        </div>
                    </li>
                    @endif
                </ul>

                {{-- ============================================================ --}}
                {{-- 7. LAYANAN PENDUKUNG (KEUANGAN, KOPERASI, TRACER, GAJI)     --}}
                {{-- ============================================================ --}}
                <div class="sidebar-section-title">Layanan Pendukung</div>
                <ul class="sidebar-nav">
                    @if(auth()->user()->isSuperAdmin() || auth()->user()->admin_role === 'keuangan' || (auth()->user()->role === 'admin' && (!auth()->user()->admin_role || auth()->user()->admin_role === 'keuangan')))
                    <li class="sidebar-nav-item">
                        <button type="button" 
                                class="sidebar-nav-link sidebar-dropdown-btn {{ request()->routeIs('keuangan.*') ? 'active' : '' }}" 
                                onclick="toggleSidebarDropdown('menuKeuangan')" 
                                aria-expanded="{{ request()->routeIs('keuangan.*') ? 'true' : 'false' }}">
                            <i class="bi bi-cash-stack" style="color:#34d399;"></i>
                            <span>Keuangan & SPP</span>
                            <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
                        </button>
                        <div class="sidebar-dropdown-menu {{ request()->routeIs('keuangan.*') ? 'show' : '' }}" id="menuKeuangan">
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('keuangan.dashboard') }}" class="{{ request()->routeIs('keuangan.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-1"></i> Dashboard Keuangan</a></li>
                                <li><a href="{{ route('keuangan.tagihan.index') }}" class="{{ request()->routeIs('keuangan.tagihan.*') ? 'active' : '' }}"><i class="bi bi-receipt me-1"></i> Tagihan Siswa</a></li>
                                <li><a href="{{ route('keuangan.pembayaran.index') }}" class="{{ request()->routeIs('keuangan.pembayaran.*') ? 'active' : '' }}"><i class="bi bi-credit-card me-1"></i> Data Pembayaran</a></li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->admin_role === 'koperasi' || (auth()->user()->role === 'admin' && (!auth()->user()->admin_role || auth()->user()->admin_role === 'koperasi')))
                    <li class="sidebar-nav-item">
                        <button type="button" 
                                class="sidebar-nav-link sidebar-dropdown-btn {{ request()->routeIs('koperasi.*') ? 'active' : '' }}" 
                                onclick="toggleSidebarDropdown('menuKoperasi')" 
                                aria-expanded="{{ request()->routeIs('koperasi.*') ? 'true' : 'false' }}">
                            <i class="bi bi-shop" style="color:#fbbf24;"></i>
                            <span>Koperasi Sekolah</span>
                            <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
                        </button>
                        <div class="sidebar-dropdown-menu {{ request()->routeIs('koperasi.*') ? 'show' : '' }}" id="menuKoperasi">
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('koperasi.dashboard') }}" class="{{ request()->routeIs('koperasi.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-1"></i> Dashboard Koperasi</a></li>
                                <li><a href="{{ route('koperasi.anggota.index') }}" class="{{ request()->routeIs('koperasi.anggota.*') ? 'active' : '' }}"><i class="bi bi-people me-1"></i> Anggota Koperasi</a></li>
                                <li><a href="{{ route('koperasi.transaksi.index') }}" class="{{ request()->routeIs('koperasi.transaksi.*') ? 'active' : '' }}"><i class="bi bi-cart-check me-1"></i> Data Transaksi</a></li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(auth()->user()->isSuperAdmin() || auth()->user()->admin_role === 'tracer' || (auth()->user()->role === 'admin' && (!auth()->user()->admin_role || auth()->user()->admin_role === 'tracer')))
                    <li class="sidebar-nav-item">
                        <button type="button" 
                                class="sidebar-nav-link sidebar-dropdown-btn {{ request()->routeIs('tracer.*') ? 'active' : '' }}" 
                                onclick="toggleSidebarDropdown('menuTracer')" 
                                aria-expanded="{{ request()->routeIs('tracer.*') ? 'true' : 'false' }}">
                            <i class="bi bi-mortarboard" style="color:#c084fc;"></i>
                            <span>Tracer Study (Alumni)</span>
                            <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
                        </button>
                        <div class="sidebar-dropdown-menu {{ request()->routeIs('tracer.*') ? 'show' : '' }}" id="menuTracer">
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('tracer.dashboard') }}" class="{{ request()->routeIs('tracer.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-1"></i> Dashboard Tracer</a></li>
                                <li><a href="{{ route('tracer.alumni.index') }}" class="{{ request()->routeIs('tracer.alumni.*') ? 'active' : '' }}"><i class="bi bi-person-lines-fill me-1"></i> Data Alumni</a></li>
                                <li><a href="{{ route('tracer.kuesioner.index') }}" class="{{ request()->routeIs('tracer.kuesioner.*') ? 'active' : '' }}"><i class="bi bi-question-diamond me-1"></i> Kuesioner Karir BMW</a></li>
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if(auth()->user()->isSuperAdmin())
                    <li class="sidebar-nav-item">
                        <a href="{{ route('superadmin.payroll.dashboard') }}" class="sidebar-nav-link {{ request()->routeIs('superadmin.payroll.*') ? 'active' : '' }}">
                            <i class="bi bi-cash-coin" style="color:#10b981;"></i>
                            <span>HilalPay (Penggajian)</span>
                        </a>
                    </li>
                    @endif
                </ul>

                {{-- ============================================================ --}}
                {{-- 8. PENGATURAN & SISTEM                                       --}}
                {{-- ============================================================ --}}
                @if(auth()->user()->isSuperAdmin())
                <div class="sidebar-section-title">Sistem & Pemeliharaan</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route('akademik.pengaturan.index') }}" class="sidebar-nav-link {{ request()->routeIs('akademik.pengaturan.*') ? 'active' : '' }}">
                            <i class="bi bi-gear-fill" style="color:#94a3b8;"></i>
                            <span>Pengaturan Sekolah</span>
                        </a>
                    </li>
                    <li class="sidebar-nav-item">
                        <a href="{{ route('superadmin.database.index') }}" class="sidebar-nav-link {{ request()->routeIs('superadmin.database.*') ? 'active' : '' }}">
                            <i class="bi bi-database-fill-gear" style="color:#38bdf8;"></i>
                            <span>Backup & Pulihkan Database</span>
                        </a>
                    </li>
                </ul>
                @endif
            @else
                {{-- ============================================================ --}}
                {{-- PORTAL GURU, SISWA, DAN TENDIK                               --}}
                {{-- ============================================================ --}}
                <div class="sidebar-section-title">Menu Utama</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item">
                        <a href="{{ route(auth()->user()->dashboardRoute()) }}" class="sidebar-nav-link {{ request()->routeIs('*.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard Utama</span>
                        </a>
                    </li>

                    @if(auth()->user()->role === 'guru')
                        <div class="sidebar-section-title mt-3">Tugas Akademik</div>
                        <li class="sidebar-nav-item"><a href="{{ route('guru.penugasan.index') }}" class="sidebar-nav-link {{ request()->routeIs('guru.penugasan.*') ? 'active' : '' }}"><i class="bi bi-briefcase-fill" style="color:#fb923c;"></i><span>Penugasan Guru</span></a></li>
                        <li class="sidebar-nav-item"><a href="{{ route('guru.kalender.index') }}" class="sidebar-nav-link {{ request()->routeIs('guru.kalender.*') ? 'active' : '' }}"><i class="bi bi-calendar-event" style="color:#38bdf8;"></i><span>Kalender Akademik</span></a></li>
                        <li class="sidebar-nav-item"><a href="{{ route('guru.minggu-efektif.index') }}" class="sidebar-nav-link {{ request()->routeIs('guru.minggu-efektif.*') ? 'active' : '' }}"><i class="bi bi-calendar-check" style="color:#10b981;"></i><span>Minggu Efektif</span></a></li>
                        <li class="sidebar-nav-item"><a href="{{ route('guru.rencana-pembelajaran.index') }}" class="sidebar-nav-link {{ request()->routeIs('guru.rencana-pembelajaran.*') ? 'active' : '' }}"><i class="bi bi-book" style="color:#f43f5e;"></i><span>Rencana Pembelajaran</span></a></li>
                        <li class="sidebar-nav-item"><a href="{{ route('guru.laporan-kbm.index') }}" class="sidebar-nav-link {{ request()->routeIs('guru.laporan-kbm.*') ? 'active' : '' }}"><i class="bi bi-journal-text" style="color:#8b5cf6;"></i><span>Laporan KBM</span></a></li>
                        <li class="sidebar-nav-item"><a href="{{ route('guru.absensi.index') }}" class="sidebar-nav-link {{ request()->routeIs('guru.absensi.*') ? 'active' : '' }}"><i class="bi bi-person-check" style="color:#10b981;"></i><span>Absensi Mengajar</span></a></li>
                        <li class="sidebar-nav-item"><a href="{{ route('guru.realisasi-tugas-tambahan.index') }}" class="sidebar-nav-link {{ request()->routeIs('guru.realisasi-tugas-tambahan.*') ? 'active' : '' }}"><i class="bi bi-card-checklist" style="color:#eab308;"></i><span>Realisasi Tugas Tambahan</span></a></li>
                        <li class="sidebar-nav-item"><a href="{{ route('guru.rekap-presensi.index') }}" class="sidebar-nav-link {{ request()->routeIs('guru.rekap-presensi.*') ? 'active' : '' }}"><i class="bi bi-clipboard-data" style="color:#0ea5e9;"></i><span>Rekap Presensi</span></a></li>
                        <li class="sidebar-nav-item"><a href="{{ route('guru.perangkat-ajar.index') }}" class="sidebar-nav-link {{ request()->routeIs('guru.perangkat-ajar.*') ? 'active' : '' }}"><i class="bi bi-folder" style="color:#f59e0b;"></i><span>Perangkat Ajar</span></a></li>
                        
                        <div class="sidebar-section-title mt-3">Monitoring & Pembimbingan</div>
                        @if(auth()->user()->isWaliKelas() || auth()->user()->isKaprog() || auth()->user()->isBk() || auth()->user()->isWakaKesiswaan() || auth()->user()->isWakaKurikulum() || auth()->user()->isPembinaOsis() || auth()->user()->isPetugasPiket())
                        <li class="sidebar-nav-item"><a href="{{ route('presensi-harian.index') }}" class="sidebar-nav-link {{ request()->routeIs('presensi-harian.*') ? 'active' : '' }}"><i class="bi bi-person-lines-fill" style="color:#0ea5e9;"></i><span>Monitoring Absensi</span></a></li>
                        @endif
                        @if(auth()->check() && auth()->user()->canAccessBk())
                        <li class="sidebar-nav-item">
                            <button type="button" 
                                    class="sidebar-nav-link sidebar-dropdown-btn {{ request()->routeIs('bk.*') ? 'active' : '' }}" 
                                    onclick="toggleSidebarDropdown('menuBKGuru')" 
                                    aria-expanded="{{ request()->routeIs('bk.*') ? 'true' : 'false' }}">
                                <i class="bi bi-shield-check" style="color:#f87171;"></i>
                                <span>Monitoring BK</span>
                                <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
                            </button>
                            <div class="sidebar-dropdown-menu {{ request()->routeIs('bk.*') ? 'show' : '' }}" id="menuBKGuru">
                                <ul class="sidebar-submenu">
                                    <li><a href="{{ route('bk.dashboard') }}" class="{{ request()->routeIs('bk.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-1"></i> Dashboard BK</a></li>
                                    <li><a href="{{ route('bk.pelanggaran.index') }}" class="{{ request()->routeIs('bk.pelanggaran.*') ? 'active' : '' }}"><i class="bi bi-exclamation-octagon me-1"></i> Catatan Pelanggaran</a></li>
                                    <li><a href="{{ route('bk.progres.index') }}" class="{{ request()->routeIs('bk.progres.*') ? 'active' : '' }}"><i class="bi bi-arrow-repeat me-1"></i> Progres Kasus & Bimbingan</a></li>
                                    <li><a href="{{ route('bk.poin.index') }}" class="{{ request()->routeIs('bk.poin.*') ? 'active' : '' }}"><i class="bi bi-award me-1"></i> Akumulasi Poin Siswa</a></li>
                                    <li><a href="{{ route('bk.jenis-pelanggaran.index') }}" class="{{ request()->routeIs('bk.jenis-pelanggaran.*') ? 'active' : '' }}"><i class="bi bi-tags me-1"></i> Data Pelanggaran</a></li>
                                    <li><a href="{{ route('bk.laporan.index') }}" class="{{ request()->routeIs('bk.laporan.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-ruled me-1"></i> Laporan Berkala BK</a></li>
                                </ul>
                            </div>
                        </li>
                        @endif
                        <li class="sidebar-nav-item"><a href="{{ route('prakerin.jurnal.index') }}" class="sidebar-nav-link {{ request()->routeIs('prakerin.jurnal.*') ? 'active' : '' }}"><i class="bi bi-briefcase" style="color:#fb923c;"></i><span>Jurnal Prakerin (PKL)</span></a></li>
                        <li class="sidebar-nav-item"><a href="{{ route('prakerin.laporan.index') }}" class="sidebar-nav-link {{ request()->routeIs('prakerin.laporan.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-bar-graph" style="color:#fb923c;"></i><span>Laporan Prakerin</span></a></li>

                        <div class="sidebar-section-title mt-3">Layanan Pegawai</div>
                        <li class="sidebar-nav-item"><a href="{{ route('guru.cuti.index') }}" class="sidebar-nav-link {{ request()->routeIs('guru.cuti.*') ? 'active' : '' }}"><i class="bi bi-calendar2-minus" style="color:#ef4444;"></i><span>Pengajuan Cuti</span></a></li>
                        <li class="sidebar-nav-item"><a href="{{ route('guru.kegiatan.index') }}" class="sidebar-nav-link {{ request()->routeIs('guru.kegiatan.*') ? 'active' : '' }}"><i class="bi bi-calendar-star" style="color:#8b5cf6;"></i><span>Kegiatan Sekolah</span></a></li>
                        
                        <li class="sidebar-nav-item"><a href="{{ route('guru.profile.edit') }}" class="sidebar-nav-link {{ request()->routeIs('guru.profile.*') ? 'active' : '' }}"><i class="bi bi-person-vcard" style="color:#fbbf24;"></i><span>Profil & Pendidikan</span></a></li>
                        <li class="sidebar-nav-item"><a href="{{ route('guru.payroll.index') }}" class="sidebar-nav-link {{ request()->routeIs('guru.payroll.*') ? 'active' : '' }}"><i class="bi bi-wallet2" style="color:#10b981;"></i><span>Slip Gaji & Bisyarah</span></a></li>
                    @elseif(auth()->user()->role === 'tendik')
                        <li class="sidebar-nav-item"><a href="{{ route('tendik.profile.edit') }}" class="sidebar-nav-link {{ request()->routeIs('tendik.profile.*') ? 'active' : '' }}"><i class="bi bi-person-vcard" style="color:#fbbf24;"></i><span>Profil & Biodata</span></a></li>
                        <li class="sidebar-nav-item"><a href="{{ route('tendik.payroll.index') }}" class="sidebar-nav-link {{ request()->routeIs('tendik.payroll.*') ? 'active' : '' }}"><i class="bi bi-wallet2" style="color:#10b981;"></i><span>Slip Gaji Saya</span></a></li>
                    @elseif(auth()->user()->role === 'siswa')
                        <li class="sidebar-nav-item"><a href="{{ route('siswa.profile.edit') }}" class="sidebar-nav-link {{ request()->routeIs('siswa.profile.*') ? 'active' : '' }}"><i class="bi bi-person-vcard" style="color:#38bdf8;"></i><span>Profil & Biodata</span></a></li>
                    @endif
                </ul>

                @if(!empty(auth()->user()->admin_role))
                {{-- Wewenang Admin Unit yang Ditunjuk Superadmin --}}
                <div class="sidebar-section-title" style="color: #34d399; font-weight: 800; display: flex; align-items: center; gap: 6px;">
                    <i class="bi bi-shield-lock-fill"></i>
                    <span>Admin Unit: {{ strtoupper(auth()->user()->admin_role) }}</span>
                </div>
                <ul class="sidebar-nav mb-3">
                    @if(auth()->user()->admin_role === 'akademik')
                    <li class="sidebar-nav-item">
                        <button type="button" class="sidebar-nav-link sidebar-dropdown-btn {{ request()->routeIs('akademik.*') ? 'active' : '' }}" onclick="toggleSidebarDropdown('menuStaffAkademik')" aria-expanded="{{ request()->routeIs('akademik.*') ? 'true' : 'false' }}">
                            <i class="bi bi-book-fill" style="color:#38bdf8;"></i>
                            <span>Kelola Akademik & KBM</span>
                            <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
                        </button>
                        <div class="sidebar-dropdown-menu {{ request()->routeIs('akademik.*') ? 'show' : '' }}" id="menuStaffAkademik">
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('akademik.dashboard') }}" class="{{ request()->routeIs('akademik.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-1"></i> Dashboard Akademik</a></li>
                                <li><a href="{{ route('akademik.kalender.index') }}" class="{{ request()->routeIs('akademik.kalender.*') ? 'active' : '' }}"><i class="bi bi-calendar-event me-1"></i> Kalender Pendidikan</a></li>
                                <li><a href="{{ route('akademik.kurikulum.index') }}" class="{{ request()->routeIs('akademik.kurikulum.*') ? 'active' : '' }}"><i class="bi bi-diagram-3 me-1"></i> Kurikulum & Beban Ajar</a></li>
                                <li><a href="{{ route('akademik.jadwal.index') }}" class="{{ request()->routeIs('akademik.jadwal.index') ? 'active' : '' }}"><i class="bi bi-clock-history me-1"></i> Jadwal Pelajaran</a></li>
                                <li><a href="{{ route('akademik.jadwal.matrix') }}" class="{{ request()->routeIs('akademik.jadwal.matrix*') ? 'active' : '' }}"><i class="bi bi-grid-3x3 me-1"></i> Matriks Jadwal Resmi</a></li>
                                <li><a href="{{ route('akademik.nilai.index') }}" class="{{ request()->routeIs('akademik.nilai.*') ? 'active' : '' }}"><i class="bi bi-award me-1"></i> Rekap Nilai Siswa</a></li>
                                <li><a href="{{ route('akademik.laporan.kehadiran.index') }}" class="{{ request()->routeIs('akademik.laporan.kehadiran.*') ? 'active' : '' }}"><i class="bi bi-person-check me-1"></i> Presensi Mengajar Guru</a></li>
                                <li><a href="{{ route('akademik.laporan.kbm.index') }}" class="{{ request()->routeIs('akademik.laporan.kbm.*') ? 'active' : '' }}"><i class="bi bi-journal-text me-1"></i> Jurnal Realisasi KBM</a></li>
                                <li><a href="{{ route('akademik.rekap-presensi.index') }}" class="{{ request()->routeIs('akademik.rekap-presensi.*') ? 'active' : '' }}"><i class="bi bi-clipboard-data me-1"></i> Rekapitulasi Presensi</a></li>
                            </ul>
                        </div>
                    </li>
                    @elseif(auth()->user()->admin_role === 'bk')
                    <li class="sidebar-nav-item">
                        <button type="button" class="sidebar-nav-link sidebar-dropdown-btn {{ request()->routeIs('bk.*') ? 'active' : '' }}" onclick="toggleSidebarDropdown('menuStaffBK')" aria-expanded="{{ request()->routeIs('bk.*') ? 'true' : 'false' }}">
                            <i class="bi bi-shield-check" style="color:#f87171;"></i>
                            <span>Kelola Monitoring BK</span>
                            <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
                        </button>
                        <div class="sidebar-dropdown-menu {{ request()->routeIs('bk.*') ? 'show' : '' }}" id="menuStaffBK">
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('bk.dashboard') }}" class="{{ request()->routeIs('bk.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-1"></i> Dashboard BK</a></li>
                                <li><a href="{{ route('bk.pelanggaran.index') }}" class="{{ request()->routeIs('bk.pelanggaran.*') ? 'active' : '' }}"><i class="bi bi-exclamation-octagon me-1"></i> Catatan Pelanggaran</a></li>
                                <li><a href="{{ route('bk.progres.index') }}" class="{{ request()->routeIs('bk.progres.*') ? 'active' : '' }}"><i class="bi bi-arrow-repeat me-1"></i> Progres Kasus & Bimbingan</a></li>
                                <li><a href="{{ route('bk.poin.index') }}" class="{{ request()->routeIs('bk.poin.*') ? 'active' : '' }}"><i class="bi bi-award me-1"></i> Akumulasi Poin Siswa</a></li>
                                <li><a href="{{ route('bk.jenis-pelanggaran.index') }}" class="{{ request()->routeIs('bk.jenis-pelanggaran.*') ? 'active' : '' }}"><i class="bi bi-tags me-1"></i> Data Pelanggaran</a></li>
                                <li><a href="{{ route('bk.laporan.index') }}" class="{{ request()->routeIs('bk.laporan.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-ruled me-1"></i> Laporan Berkala BK</a></li>
                            </ul>
                        </div>
                    </li>
                    @elseif(auth()->user()->admin_role === 'prakerin')
                    <li class="sidebar-nav-item">
                        <button type="button" class="sidebar-nav-link sidebar-dropdown-btn {{ request()->routeIs('prakerin.*') ? 'active' : '' }}" onclick="toggleSidebarDropdown('menuStaffPrakerin')" aria-expanded="{{ request()->routeIs('prakerin.*') ? 'true' : 'false' }}">
                            <i class="bi bi-briefcase-fill" style="color:#fb923c;"></i>
                            <span>Kelola Prakerin / PKL</span>
                            <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
                        </button>
                        <div class="sidebar-dropdown-menu {{ request()->routeIs('prakerin.*') ? 'show' : '' }}" id="menuStaffPrakerin">
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('prakerin.dashboard') }}" class="{{ request()->routeIs('prakerin.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-1"></i> Dashboard Prakerin</a></li>
                                <li><a href="{{ route('prakerin.dudi.index') }}" class="{{ request()->routeIs('prakerin.dudi.*') ? 'active' : '' }}"><i class="bi bi-buildings me-1"></i> Mitra DU/DI Perusahaan</a></li>
                                <li><a href="{{ route('prakerin.periode.index') }}" class="{{ request()->routeIs('prakerin.periode.*') ? 'active' : '' }}"><i class="bi bi-calendar-range me-1"></i> Periode Prakerin</a></li>
                                <li><a href="{{ route('prakerin.penempatan.index') }}" class="{{ request()->routeIs('prakerin.penempatan.*') ? 'active' : '' }}"><i class="bi bi-person-workspace me-1"></i> Penempatan Siswa</a></li>
                                <li><a href="{{ route('prakerin.jurnal.index') }}" class="{{ request()->routeIs('prakerin.jurnal.*') ? 'active' : '' }}"><i class="bi bi-journal-check me-1"></i> Jurnal Siswa</a></li>
                                <li><a href="{{ route('prakerin.laporan.index') }}" class="{{ request()->routeIs('prakerin.laporan.*') ? 'active' : '' }}"><i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan Nilai Prakerin</a></li>
                            </ul>
                        </div>
                    </li>
                    @elseif(auth()->user()->admin_role === 'keuangan')
                    <li class="sidebar-nav-item">
                        <button type="button" class="sidebar-nav-link sidebar-dropdown-btn {{ request()->routeIs('keuangan.*') ? 'active' : '' }}" onclick="toggleSidebarDropdown('menuStaffKeuangan')" aria-expanded="{{ request()->routeIs('keuangan.*') ? 'true' : 'false' }}">
                            <i class="bi bi-cash-stack" style="color:#34d399;"></i>
                            <span>Kelola Keuangan & SPP</span>
                            <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
                        </button>
                        <div class="sidebar-dropdown-menu {{ request()->routeIs('keuangan.*') ? 'show' : '' }}" id="menuStaffKeuangan">
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('keuangan.dashboard') }}" class="{{ request()->routeIs('keuangan.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-1"></i> Dashboard Keuangan</a></li>
                                <li><a href="{{ route('keuangan.tagihan.index') }}" class="{{ request()->routeIs('keuangan.tagihan.*') ? 'active' : '' }}"><i class="bi bi-receipt me-1"></i> Tagihan Siswa</a></li>
                                <li><a href="{{ route('keuangan.pembayaran.index') }}" class="{{ request()->routeIs('keuangan.pembayaran.*') ? 'active' : '' }}"><i class="bi bi-credit-card me-1"></i> Data Pembayaran</a></li>
                            </ul>
                        </div>
                    </li>
                    @elseif(auth()->user()->admin_role === 'koperasi')
                    <li class="sidebar-nav-item">
                        <button type="button" class="sidebar-nav-link sidebar-dropdown-btn {{ request()->routeIs('koperasi.*') ? 'active' : '' }}" onclick="toggleSidebarDropdown('menuStaffKoperasi')" aria-expanded="{{ request()->routeIs('koperasi.*') ? 'true' : 'false' }}">
                            <i class="bi bi-shop" style="color:#fbbf24;"></i>
                            <span>Kelola Koperasi Sekolah</span>
                            <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
                        </button>
                        <div class="sidebar-dropdown-menu {{ request()->routeIs('koperasi.*') ? 'show' : '' }}" id="menuStaffKoperasi">
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('koperasi.dashboard') }}" class="{{ request()->routeIs('koperasi.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-1"></i> Dashboard Koperasi</a></li>
                                <li><a href="{{ route('koperasi.anggota.index') }}" class="{{ request()->routeIs('koperasi.anggota.*') ? 'active' : '' }}"><i class="bi bi-people me-1"></i> Anggota Koperasi</a></li>
                                <li><a href="{{ route('koperasi.transaksi.index') }}" class="{{ request()->routeIs('koperasi.transaksi.*') ? 'active' : '' }}"><i class="bi bi-cart-check me-1"></i> Data Transaksi</a></li>
                            </ul>
                        </div>
                    </li>
                    @elseif(auth()->user()->admin_role === 'tracer')
                    <li class="sidebar-nav-item">
                        <button type="button" class="sidebar-nav-link sidebar-dropdown-btn {{ request()->routeIs('tracer.*') ? 'active' : '' }}" onclick="toggleSidebarDropdown('menuStaffTracer')" aria-expanded="{{ request()->routeIs('tracer.*') ? 'true' : 'false' }}">
                            <i class="bi bi-mortarboard" style="color:#c084fc;"></i>
                            <span>Kelola Tracer Study</span>
                            <i class="bi bi-chevron-down ms-auto arrow-icon"></i>
                        </button>
                        <div class="sidebar-dropdown-menu {{ request()->routeIs('tracer.*') ? 'show' : '' }}" id="menuStaffTracer">
                            <ul class="sidebar-submenu">
                                <li><a href="{{ route('tracer.dashboard') }}" class="{{ request()->routeIs('tracer.dashboard') ? 'active' : '' }}"><i class="bi bi-speedometer2 me-1"></i> Dashboard Tracer</a></li>
                                <li><a href="{{ route('tracer.alumni.index') }}" class="{{ request()->routeIs('tracer.alumni.*') ? 'active' : '' }}"><i class="bi bi-person-lines-fill me-1"></i> Data Alumni</a></li>
                                <li><a href="{{ route('tracer.kuesioner.index') }}" class="{{ request()->routeIs('tracer.kuesioner.*') ? 'active' : '' }}"><i class="bi bi-question-diamond me-1"></i> Kuesioner Karir BMW</a></li>
                            </ul>
                        </div>
                    </li>
                    @endif
                </ul>
                @endif

                {{-- Modul Layanan Khusus Siswa --}}
                @if(auth()->user()->role === 'siswa')
                <div class="sidebar-section-title">Layanan Sekolah</div>
                <ul class="sidebar-nav">
                    <li class="sidebar-nav-item"><a href="{{ route('prakerin.jurnal.index') }}" class="sidebar-nav-link {{ request()->routeIs('prakerin.jurnal.*') ? 'active' : '' }}"><i class="bi bi-briefcase" style="color:#fb923c;"></i><span>Jurnal Prakerin (PKL)</span></a></li>
                </ul>
                @endif
            @endif
        </div>

        <!-- Profil Mini di Bawah Sidebar -->
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="sidebar-user-avatar">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <div class="sidebar-user-info">
                    <div class="sidebar-user-name">{{ auth()->user()->name }}</div>
                    <div class="sidebar-user-role">{{ auth()->user()->role }}</div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Topbar Navigasi -->
    <header class="topbar">
        <div class="topbar-left">
            <button class="topbar-toggle" onclick="toggleSidebar()" id="sidebarToggle" aria-label="Buka Menu">
                <i class="bi bi-list"></i>
            </button>
            <span class="topbar-title">@yield('page-title', 'Dashboard')</span>
        </div>
        <div class="topbar-right">
            @if(auth()->user()->role === 'guru')
                <a href="{{ route('guru.profile.edit') }}" class="btn btn-sm d-inline-flex align-items-center gap-1.5" style="color:var(--text-main);background:#f8fafc;border:1px solid var(--border-color);border-radius:10px;padding:6px 12px;font-size:0.82rem;text-decoration:none;">
                    <i class="bi bi-person-circle" style="color:#10b981;"></i>
                    <span class="d-none d-sm-inline">Profil Guru</span>
                </a>
            @elseif(auth()->user()->role === 'tendik')
                <a href="{{ route('tendik.profile.edit') }}" class="btn btn-sm d-inline-flex align-items-center gap-1.5" style="color:var(--text-main);background:#f8fafc;border:1px solid var(--border-color);border-radius:10px;padding:6px 12px;font-size:0.82rem;text-decoration:none;">
                    <i class="bi bi-person-circle" style="color:#38bdf8;"></i>
                    <span class="d-none d-sm-inline">Profil Tendik</span>
                </a>
            @elseif(auth()->user()->role === 'siswa')
                <a href="{{ route('siswa.profile.edit') }}" class="btn btn-sm d-inline-flex align-items-center gap-1.5" style="color:var(--text-main);background:#f8fafc;border:1px solid var(--border-color);border-radius:10px;padding:6px 12px;font-size:0.82rem;text-decoration:none;">
                    <i class="bi bi-person-circle" style="color:#38bdf8;"></i>
                    <span class="d-none d-sm-inline">Profil Siswa</span>
                </a>
            @endif

            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="logout-btn" title="Keluar">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-sm-inline">Keluar</span>
                </button>
            </form>
        </div>
    </header>

    <!-- Konten Halaman -->
    <main class="main-content">
        <div class="content-area">
            @if(session('success'))
                <div class="alert-toast">
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm"
                         style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 12px;">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span class="fs-7 font-medium">{{ session('success') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size: 0.7rem;"></button>
                    </div>
                </div>
            @endif

            @if(session('error'))
                <div class="alert-toast">
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 shadow-sm"
                         style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 12px;">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5"></i>
                        <span class="fs-7 font-medium">{{ session('error') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" style="font-size: 0.7rem;"></button>
                    </div>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }

        function toggleSidebarDropdown(id) {
            const target = document.getElementById(id);
            if (!target) return;
            const btn = target.previousElementSibling;
            const isShown = target.classList.contains('show');
            if (isShown) {
                target.classList.remove('show');
                if (btn) btn.setAttribute('aria-expanded', 'false');
            } else {
                target.classList.add('show');
                if (btn) btn.setAttribute('aria-expanded', 'true');
            }
        }

        // Auto-hide alert toast
        setTimeout(() => {
            const toast = document.querySelector('.alert-toast');
            if (toast) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateX(40px)';
                toast.style.transition = 'all 0.4s ease';
                setTimeout(() => toast.remove(), 400);
            }
        }, 4000);
    </script>

    @yield('scripts')
    @stack('scripts')
</body>
</html>
