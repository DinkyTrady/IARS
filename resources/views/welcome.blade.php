<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>IARS — Sistem Informasi Reservasi Ruangan Kampus</title>
    <meta name="description" content="Sistem Informasi Reservasi Ruangan Kampus berbasis web. Pesan ruangan, cek jadwal, dan hindari konflik secara otomatis dengan teknologi Genetic Algorithm.">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-light: #dbeafe;
            --accent: #7c3aed;
            --text: #111827;
            --text-muted: #6b7280;
            --bg: #ffffff;
            --bg-alt: #f8fafc;
            --border: #e5e7eb;
            --radius: 12px;
        }
        body {
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background: var(--bg);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        a { color: inherit; text-decoration: none; }

        /* Nav */
        nav {
            position: sticky; top: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 5%; height: 64px;
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border);
        }
        .nav-brand { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 1.2rem; color: var(--primary); }
        .nav-brand span { background: var(--primary); color: white; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1rem; }
        .nav-links { display: flex; align-items: center; gap: 8px; }
        .btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 20px; border-radius: 8px; font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all 0.15s; border: none; }
        .btn-ghost { color: var(--text); background: transparent; }
        .btn-ghost:hover { background: var(--bg-alt); }
        .btn-primary { color: white; background: var(--primary); }
        .btn-primary:hover { background: var(--primary-dark); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,0.4); }
        .btn-lg { padding: 14px 32px; font-size: 1rem; border-radius: 12px; }
        .btn-outline { border: 2px solid var(--border); color: var(--text); background: white; }
        .btn-outline:hover { border-color: var(--primary); color: var(--primary); }

        /* Hero */
        .hero {
            min-height: 90vh;
            display: flex; align-items: center;
            background: linear-gradient(135deg, #eff6ff 0%, #f5f3ff 50%, #fdf2f8 100%);
            position: relative; overflow: hidden;
        }
        .hero-bg {
            position: absolute; inset: 0;
            background-image: radial-gradient(circle at 20% 50%, rgba(37,99,235,0.08) 0%, transparent 60%),
                              radial-gradient(circle at 80% 20%, rgba(124,58,237,0.08) 0%, transparent 60%),
                              radial-gradient(circle at 60% 80%, rgba(219,39,119,0.06) 0%, transparent 60%);
        }
        .hero-content { position: relative; max-width: 1200px; margin: 0 auto; padding: 80px 5%; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; }
        .hero-badge { display: inline-flex; align-items: center; gap: 6px; background: var(--primary-light); color: var(--primary); padding: 6px 14px; border-radius: 999px; font-size: 0.8rem; font-weight: 600; margin-bottom: 20px; }
        .hero-title { font-size: clamp(2rem, 4vw, 3.2rem); font-weight: 900; line-height: 1.15; color: var(--text); margin-bottom: 20px; }
        .hero-title .highlight { color: var(--primary); position: relative; }
        .hero-desc { font-size: 1.1rem; color: var(--text-muted); margin-bottom: 36px; max-width: 480px; }
        .hero-actions { display: flex; gap: 12px; flex-wrap: wrap; }
        .hero-visual { background: white; border-radius: 20px; padding: 28px; box-shadow: 0 20px 60px rgba(0,0,0,0.12); border: 1px solid var(--border); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
        .card-title { font-weight: 700; font-size: 0.95rem; }
        .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--primary); }
        .room-card { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border: 1px solid var(--border); border-radius: 10px; margin-bottom: 10px; transition: all 0.2s; }
        .room-card:hover { border-color: var(--primary); background: var(--primary-light); }
        .room-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
        .room-info { flex: 1; }
        .room-name { font-weight: 600; font-size: 0.875rem; }
        .room-detail { font-size: 0.75rem; color: var(--text-muted); }
        .badge { padding: 3px 10px; border-radius: 999px; font-size: 0.72rem; font-weight: 600; }
        .badge-green { background: #dcfce7; color: #16a34a; }
        .badge-red { background: #fee2e2; color: #dc2626; }
        .badge-blue { background: var(--primary-light); color: var(--primary); }

        /* Stats row */
        .stats-row { display: flex; gap: 16px; margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border); }
        .stat-item { flex: 1; text-align: center; }
        .stat-num { font-size: 1.5rem; font-weight: 800; color: var(--primary); }
        .stat-label { font-size: 0.72rem; color: var(--text-muted); }

        /* Features */
        .section { padding: 80px 5%; max-width: 1200px; margin: 0 auto; }
        .section-header { text-align: center; margin-bottom: 52px; }
        .section-badge { display: inline-block; background: var(--primary-light); color: var(--primary); padding: 4px 14px; border-radius: 999px; font-size: 0.78rem; font-weight: 600; margin-bottom: 12px; }
        .section-title { font-size: clamp(1.6rem, 3vw, 2.4rem); font-weight: 800; margin-bottom: 12px; }
        .section-desc { font-size: 1rem; color: var(--text-muted); max-width: 520px; margin: 0 auto; }
        .features-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 24px; }
        .feature-card { padding: 28px; border: 1px solid var(--border); border-radius: var(--radius); background: white; transition: all 0.2s; }
        .feature-card:hover { border-color: var(--primary); box-shadow: 0 8px 30px rgba(37,99,235,0.1); transform: translateY(-2px); }
        .feature-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; margin-bottom: 16px; }
        .feature-title { font-weight: 700; font-size: 1rem; margin-bottom: 8px; }
        .feature-desc { font-size: 0.875rem; color: var(--text-muted); line-height: 1.6; }

        /* How it works */
        .how-section { background: var(--bg-alt); padding: 80px 5%; }
        .how-inner { max-width: 1200px; margin: 0 auto; }
        .steps-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 32px; }
        .step { text-align: center; }
        .step-num { width: 52px; height: 52px; border-radius: 50%; background: var(--primary); color: white; font-size: 1.2rem; font-weight: 800; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        .step-title { font-weight: 700; margin-bottom: 8px; }
        .step-desc { font-size: 0.875rem; color: var(--text-muted); }

        /* GA Section */
        .ga-section { padding: 80px 5%; max-width: 1200px; margin: 0 auto; }
        .ga-card { background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%); border-radius: 20px; padding: 48px; color: white; display: grid; grid-template-columns: 1fr auto; gap: 40px; align-items: center; }
        .ga-title { font-size: 1.8rem; font-weight: 800; margin-bottom: 12px; }
        .ga-desc { font-size: 0.95rem; opacity: 0.85; max-width: 480px; line-height: 1.7; margin-bottom: 24px; }
        .ga-steps { display: flex; flex-direction: column; gap: 10px; }
        .ga-step { display: flex; align-items: center; gap: 10px; font-size: 0.875rem; opacity: 0.9; }
        .ga-step-dot { width: 8px; height: 8px; border-radius: 50%; background: rgba(255,255,255,0.6); flex-shrink: 0; }
        .ga-badge { background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.3); border-radius: 12px; padding: 24px; text-align: center; white-space: nowrap; }
        .ga-badge-num { font-size: 2.5rem; font-weight: 900; }
        .ga-badge-label { font-size: 0.8rem; opacity: 0.8; }

        /* CTA */
        .cta-section { padding: 80px 5%; text-align: center; background: var(--text); color: white; }
        .cta-title { font-size: clamp(1.6rem, 3vw, 2.4rem); font-weight: 800; margin-bottom: 12px; }
        .cta-desc { color: rgba(255,255,255,0.7); margin-bottom: 32px; font-size: 1rem; }

        /* Footer */
        footer { padding: 24px 5%; border-top: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; font-size: 0.85rem; color: var(--text-muted); }

        @media (max-width: 768px) {
            .hero-content { grid-template-columns: 1fr; }
            .hero-visual { display: none; }
            .ga-card { grid-template-columns: 1fr; }
            .ga-badge { display: none; }
            nav .nav-links .btn-ghost { display: none; }
        }
    </style>
</head>
<body>

<!-- Navigation -->
<nav>
    <div class="nav-brand">
        <span class="flex items-center justify-center bg-blue-600 rounded-lg p-1.5 shadow-sm shadow-blue-500/20">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="white" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
            </svg>
        </span>
        <span class="font-black tracking-tight text-blue-600">IARS</span>
    </div>
    <div class="nav-links">
        @if (Route::has('login'))
            @auth
                <a href="{{ route('dashboard') }}" class="btn btn-ghost">Dashboard</a>
            @else
                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-ghost">Daftar</a>
                @endif
                <a href="{{ route('login') }}" class="btn btn-primary">Masuk</a>
            @endauth
        @endif
    </div>
</nav>

<!-- Hero -->
<section class="hero">
    <div class="hero-bg"></div>
    <div class="hero-content">
        <div>
            <div class="hero-badge">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9 18.75l-.813-2.846a4.5 4.5 0 0 0-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 0 0 3.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 0 0 3.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 0 0-3.09 3.09ZM18.259 8.715 18 9.75l-.259-1.035a3.375 3.375 0 0 0-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 0 0 2.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 0 0 2.456 2.456L21.75 6l-1.035.259a3.375 3.375 0 0 0-2.456 2.456ZM16.894 20.567 16.5 21.75l-.394-1.183a2.25 2.25 0 0 0-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 0 0 1.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 0 0 1.423 1.423l1.183.394-1.183.394a2.25 2.25 0 0 0-1.423 1.423Z" />
                </svg>
                Sistem Reservasi Cerdas
            </div>
            <h1 class="hero-title">
                Pesan Ruangan Kampus<br>
                <span class="highlight">Lebih Mudah & Cerdas</span>
            </h1>
            <p class="hero-desc">
                Platform digital terintegrasi untuk reservasi ruangan, validasi konflik otomatis,
                dan optimasi jadwal akademik menggunakan Genetic Algorithm.
            </p>
            <div class="hero-actions">
                @auth
                    <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
                        Buka Dashboard →
                    </a>
                @else
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="btn btn-primary btn-lg">
                            Mulai Sekarang →
                        </a>
                    @endif
                    <a href="{{ route('login') }}" class="btn btn-outline btn-lg">
                        Masuk ke Sistem
                    </a>
                @endauth
            </div>
        </div>

        <!-- Hero Visual (Mock UI) -->
        <div class="hero-visual">
            <div class="card-header">
                <div class="card-title">Daftar Ruangan</div>
                <div class="dot"></div>
            </div>
            <div class="room-card">
                <div class="room-icon" style="background:#dbeafe">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-blue-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A48.36 48.36 0 0 0 12 9.75c-2.551 0-5.056.2-7.5.582V21M3 21h18M12 6.75h.008v.008H12V6.75Z" />
                    </svg>
                </div>
                <div class="room-info">
                    <div class="room-name">Ruang Teori A</div>
                    <div class="room-detail">Gedung A · Lantai 2 · 40 orang</div>
                </div>
                <span class="badge badge-green">Tersedia</span>
            </div>
            <div class="room-card">
                <div class="room-icon" style="background:#ede9fe">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-indigo-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 0 1-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0 1 15 18.257V17.25m6-12V15a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 15V5.25m18 0A2.25 2.25 0 0 0 18.75 3H5.25A2.25 2.25 0 0 0 3 5.25m18 0V12a2.25 2.25 0 0 1-2.25 2.25H5.25A2.25 2.25 0 0 1 3 12V5.25" />
                    </svg>
                </div>
                <div class="room-info">
                    <div class="room-name">Lab Komputer 1</div>
                    <div class="room-detail">Gedung B · Lantai 1 · 35 orang</div>
                </div>
                <span class="badge badge-red">Terpakai</span>
            </div>
            <div class="room-card">
                <div class="room-icon" style="background:#fce7f3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-pink-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.425 4.708 5.25 5.25 0 0 0 2.262 6.44l.35.204a2.25 2.25 0 0 1 1.296 2.038v.227a.75.75 0 0 0 1.5 0V17.11a2.25 2.25 0 0 1 1.296-2.038l.35-.204a5.25 5.25 0 0 0 2.262-6.44 50.627 50.627 0 0 0-2.425-4.708l-.124-.224A1.125 1.125 0 0 0 12 3.75a1.125 1.125 0 0 0-1.033.682l-.124.224Z" />
                    </svg>
                </div>
                <div class="room-info">
                    <div class="room-name">Aula Serbaguna</div>
                    <div class="room-detail">Gedung C · Lantai 1 · 120 orang</div>
                </div>
                <span class="badge badge-green">Tersedia</span>
            </div>
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-num">12</div>
                    <div class="stat-label">Ruangan</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">8</div>
                    <div class="stat-label">Tersedia</div>
                </div>
                <div class="stat-item">
                    <div class="stat-num">0</div>
                    <div class="stat-label">Konflik</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features -->
<div style="padding: 80px 0; background: white;">
    <div class="section">
        <div class="section-header">
            <div class="section-badge">Fitur Utama</div>
            <h2 class="section-title">Semua yang Anda Butuhkan</h2>
            <p class="section-desc">Sistem lengkap yang mengintegrasikan reservasi, validasi konflik, dan optimasi jadwal dalam satu platform.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon" style="background:#dbeafe">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-blue-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                </div>
                <div class="feature-title">Reservasi Ruangan</div>
                <div class="feature-desc">Ajukan pemesanan ruangan dengan mudah. Pilih ruangan, tentukan waktu, dan tunggu persetujuan admin.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background:#dcfce7">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-green-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <div class="feature-title">Validasi Konflik Otomatis</div>
                <div class="feature-desc">Sistem mendeteksi dan mencegah konflik jadwal secara real-time, baik dengan reservasi lain maupun jadwal akademik.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background:#fce7f3">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-pink-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.755 10.04a.75.75 0 0 1 .634.934l-.004.013a2.25 2.25 0 0 1-.581 1.054l-.454.455a2.25 2.25 0 0 0 0 3.182l.454.454a2.25 2.25 0 0 1 .581 1.055l.004.013a.75.75 0 1 1-1.436.48l-.004-.013a3.75 3.75 0 0 0-.968-1.758l-.454-.454a3.75 3.75 0 0 1 0-5.304l.454-.454a3.75 3.75 0 0 0 .968-1.759l.004-.012a.75.75 0 0 1 .934-.634Zm14.49 0a.75.75 0 0 0-.634.934l.004.013c.056.17.152.326.28.46l.454.455a2.25 2.25 0 0 1 0 3.182l-.454.454a2.25 2.25 0 0 0-.581 1.055l-.004.013a.75.75 0 1 0 1.436.48l.004-.013a3.75 3.75 0 0 1 .968-1.758l.454-.454a3.75 3.75 0 0 0 0-5.304l-.454-.454a3.75 3.75 0 0 1-.968-1.759l-.004-.012a.75.75 0 0 0-.934-.634ZM9 12a3 3 0 1 1 6 0 3 3 0 0 1-6 0Z" />
                    </svg>
                </div>
                <div class="feature-title">Genetic Algorithm</div>
                <div class="feature-desc">Optimasi jadwal perkuliahan otomatis menggunakan GA untuk meminimalkan konflik ruangan dan dosen.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background:#fef3c7">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-amber-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                    </svg>
                </div>
                <div class="feature-title">Dashboard Real-time</div>
                <div class="feature-desc">Pantau status reservasi, ketersediaan ruangan, dan statistik penggunaan secara real-time.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background:#ede9fe">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-indigo-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                    </svg>
                </div>
                <div class="feature-title">Multi-role Access</div>
                <div class="feature-desc">Dua level akses: Admin (Pengelola) untuk manajemen penuh, dan Pengguna untuk reservasi dan melihat jadwal.</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon" style="background:#f0fdf4">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6 text-green-600">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 2.25h.008v.008H12v-.008ZM9.75 15h.008v.008H9.75V15Zm0 2.25h.008v.008H9.75v-.008ZM7.5 15h.008v.008H7.5V15Zm0 2.25h.008v.008H7.5v-.008Zm6.75-4.5h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V15Zm0 2.25h.008v.008h-.008v-.008Zm2.25-4.5h.008v.008H16.5v-.008Zm0 2.25h.008v.008H16.5V15Z" />
                    </svg>
                </div>
                <div class="feature-title">Jadwal Akademik</div>
                <div class="feature-desc">Lihat jadwal perkuliahan resmi yang telah disusun otomatis berdasarkan mata kuliah, dosen, dan ruangan.</div>
            </div>
        </div>
    </div>
</div>

<!-- How it works -->
<div class="how-section">
    <div class="how-inner">
        <div class="section-header">
            <div class="section-badge">Cara Kerja</div>
            <h2 class="section-title">Mudah dalam 4 Langkah</h2>
            <p class="section-desc">Proses reservasi yang simpel dan transparan untuk semua pengguna.</p>
        </div>
        <div class="steps-grid">
            <div class="step">
                <div class="step-num">1</div>
                <div class="step-title">Daftar & Masuk</div>
                <div class="step-desc">Buat akun atau masuk ke sistem dengan email kampus Anda.</div>
            </div>
            <div class="step">
                <div class="step-num">2</div>
                <div class="step-title">Pilih Ruangan</div>
                <div class="step-desc">Pilih ruangan yang tersedia dan sesuai dengan kapasitas kegiatan.</div>
            </div>
            <div class="step">
                <div class="step-num">3</div>
                <div class="step-title">Ajukan Reservasi</div>
                <div class="step-desc">Isi detail kegiatan dan sistem akan validasi konflik secara otomatis.</div>
            </div>
            <div class="step">
                <div class="step-num">4</div>
                <div class="step-title">Tunggu Persetujuan</div>
                <div class="step-desc">Admin akan menyetujui atau menolak dengan catatan yang jelas.</div>
            </div>
        </div>
    </div>
</div>

<!-- GA Section -->
<div class="ga-section">
    <div class="ga-card">
        <div>
            <div class="ga-title">Optimasi dengan Genetic Algorithm</div>
            <p class="ga-desc">
                Sistem menggunakan Genetic Algorithm untuk secara otomatis menyusun jadwal perkuliahan yang optimal.
                Algoritma mengevaluasi ratusan kombinasi untuk menemukan solusi tanpa konflik.
            </p>
            <div class="ga-steps">
                <div class="ga-step"><div class="ga-step-dot"></div>Inisialisasi populasi jadwal awal</div>
                <div class="ga-step"><div class="ga-step-dot"></div>Evaluasi nilai fitness setiap solusi</div>
                <div class="ga-step"><div class="ga-step-dot"></div>Seleksi, crossover, dan mutasi</div>
                <div class="ga-step"><div class="ga-step-dot"></div>Iterasi hingga jadwal optimal tercapai</div>
            </div>
        </div>
        <div class="ga-badge">
            <div class="ga-badge-num">0</div>
            <div class="ga-badge-label">Konflik Jadwal</div>
            <div style="margin-top: 16px; font-size: 0.8rem; opacity: 0.7;">Hasil Optimasi GA</div>
        </div>
    </div>
</div>

<!-- CTA -->
<div class="cta-section">
    <h2 class="cta-title">Mulai Gunakan IARS Sekarang</h2>
    <p class="cta-desc">Bergabung dengan sistem reservasi kampus yang modern dan efisien.</p>
    @auth
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-lg">
            Buka Dashboard →
        </a>
    @else
        <div style="display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg">Daftar Sekarang</a>
            @endif
            <a href="{{ route('login') }}" class="btn" style="background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3); padding: 14px 32px; border-radius: 12px; font-size: 1rem; font-weight: 600;">Masuk</a>
        </div>
    @endauth
</div>

<!-- Footer -->
<footer>
    <div>© {{ date('Y') }} IARS — Sistem Informasi Reservasi Ruangan Kampus</div>
    <div>Dikembangkan untuk UNESA · Laravel 12 + Livewire 4</div>
</footer>

</body>
</html>
