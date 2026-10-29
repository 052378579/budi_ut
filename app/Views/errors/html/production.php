<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Sistem Dalam Pemeliharaan - UT Tes Formatif</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons 1.11 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Google Fonts Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: #0f172a url('/img/perbaikan.jpg') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            margin: 0;
            padding-bottom: 7vh;
            position: relative;
            color: #ffffff;
        }

        /* Vignette overlay lembut untuk meredam kontras latar belakang ekstrem */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0.2) 0%, rgba(15, 23, 42, 0.7) 100%);
            z-index: 1;
            pointer-events: none;
        }

        .maintenance-container {
            position: relative;
            z-index: 2;
            width: 92%;
            max-width: 700px;
            padding: 2.5rem 2.5rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            background: rgba(15, 23, 42, 0.50); /* Dark Slate 900 kontras tinggi */
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
            border-radius: 24px;
            box-shadow: 0 25px 60px -15px rgba(0, 0, 0, 0.75), 0 0 35px rgba(0, 77, 161, 0.25);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .logo-ut {
            max-height: 44px;
            width: auto;
            margin-bottom: 1rem;
            filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.4));
        }

        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1.25rem;
            background: rgba(255, 204, 0, 0.15);
            border: 1px solid rgba(255, 204, 0, 0.45);
            border-radius: 50px;
            color: #FFCC00;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 1.25rem;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .badge-status i {
            animation: spin-gear 6s linear infinite;
            display: inline-block;
        }

        @keyframes spin-gear {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .title {
            color: #ffffff;
            font-weight: 800;
            font-size: 2.5rem;
            margin-bottom: 0.85rem;
            letter-spacing: -0.5px;
            text-shadow: 0 2px 12px rgba(0, 0, 0, 0.6);
        }

        .subtitle {
            color: #cbd5e1; /* Slate 300 - Sangat kontras dan nyaman di mata */
            line-height: 1.75;
            margin-bottom: 1.75rem;
            font-size: 1.02rem;
            max-width: 800px;
            font-weight: 400;
        }

        .subtitle .line-text {
            display: block;
        }

        @media (min-width: 768px) {
            .subtitle .line-text {
                white-space: nowrap;
            }
        }

        .buttons-group {
            display: flex;
            gap: 1.2rem;
            justify-content: center;
            width: 100%;
        }

        .btn-custom {
            padding: 0.85rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.25s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.6rem;
        }

        /* Tombol Sekunder - Light Glass */
        .btn-glass {
            background-color: rgba(255, 255, 255, 0.12);
            color: #ffffff;
            border: 1px solid rgba(255, 255, 255, 0.35);
        }
        .btn-glass:hover {
            background-color: rgba(255, 255, 255, 0.25);
            color: #ffffff;
            border-color: rgba(255, 255, 255, 0.6);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
        }

        /* Tombol Utama - Kuning UT */
        .btn-yellow {
            background-color: #FFCC00;
            color: #0f172a;
            border: 1px solid #FFCC00;
            font-weight: 700;
            box-shadow: 0 4px 18px rgba(255, 204, 0, 0.35);
        }
        .btn-yellow:hover {
            background-color: #eab308;
            border-color: #eab308;
            color: #0f172a;
            transform: translateY(-2px);
            box-shadow: 0 6px 24px rgba(255, 204, 0, 0.55);
        }

        @media (max-width: 768px) {
            body { padding-bottom: 3vh; }
            .title { font-size: 1.85rem; }
            .subtitle { font-size: 0.95rem; line-height: 1.6; }
            .buttons-group { flex-direction: column; width: 100%; gap: 0.85rem; }
            .btn-custom { width: 100%; }
            .maintenance-container { padding: 2rem 1.25rem; }
            .logo-ut { max-height: 38px; }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <!-- Logo Resmi UT 
        <img src="/img/Logo_UT_white.png" alt="Logo Universitas Terbuka" class="logo-ut">
        -->
        <!-- Status Badge -->
        <div class="badge-status">
            <i class="bi bi-gear-fill"></i> Pemeliharaan Sistem Berkala
        </div>

        <!-- Judul & Deskripsi -->
        <h1 class="title">Sedang Pemeliharaan</h1>
        <p class="subtitle">
            <span class="line-text">Sistem saat ini dalam proses peningkatan performa dan pemeliharaan layanan.</span>
            <span class="line-text">Mohon maaf atas ketidaknyamanan Anda. Kami akan segera kembali beroperasi.</span>
        </p>

        <!-- Tombol Aksi -->
        <div class="buttons-group">
            <a href="javascript:window.location.reload()" class="btn-custom btn-glass">
                <i class="bi bi-arrow-clockwise"></i> Cek Status Layanan
            </a>
            <a href="https://wa.me/6281313428000" target="_blank" class="btn-custom btn-yellow">
                <i class="bi bi-whatsapp"></i> Hubungi Dukungan
            </a>
        </div>
    </div>
</body>
</html>
