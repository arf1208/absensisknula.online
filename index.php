<?php 
require_once 'config/database.php';

// 1. Statistik & Jam Setting (TETAP SESUAI LOGIKA AWAL)
$s_guru = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE peran='Guru'");
$t_guru = mysqli_fetch_assoc($s_guru)['total'] ?? 0;
$s_siswa = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE peran='Siswa'");
$t_siswa = mysqli_fetch_assoc($s_siswa)['total'] ?? 0;

$jam_mulai_masuk = "06:00"; 
$jam_max_masuk   = "07:02"; 
$jam_pulang      = "15:00";

// 2. Logika Grafik 6 Hari Terakhir (TETAP TANPA PENGURANGAN)
$labels = []; $data_hijau = []; $data_kuning = []; $data_biru = []; $data_merah = [];
$hari_indo = ["Minggu", "Senin", "Selasa", "Rabu", "Kamis", "Jumat", "Sabtu"];

$count = 0;
$offset = 0;

while ($count < 6) {
    $tgl = date('Y-m-d', strtotime("-$offset days"));
    $day_of_week = date('w', strtotime($tgl));

    if ($day_of_week != 0 && $day_of_week != 6) {
        array_unshift($labels, $hari_indo[$day_of_week]); 
        
        $q_h = mysqli_query($conn, "SELECT COUNT(*) as tot FROM absensi WHERE DATE(waktu)='$tgl' AND status='Tepat Waktu'");
        array_unshift($data_hijau, mysqli_fetch_assoc($q_h)['tot']);
        
        $q_k = mysqli_query($conn, "SELECT COUNT(*) as tot FROM absensi WHERE DATE(waktu)='$tgl' AND status='Terlambat'");
        array_unshift($data_kuning, mysqli_fetch_assoc($q_k)['tot']);

        $q_b = mysqli_query($conn, "SELECT COUNT(*) as tot FROM absensi WHERE DATE(waktu)='$tgl' AND status='Pulang Sebelum Jam Pulang'");
        array_unshift($data_biru, mysqli_fetch_assoc($q_b)['tot']);
        
        $total_user = $t_guru + $t_siswa;
        $q_abs = mysqli_query($conn, "SELECT COUNT(DISTINCT uid_rfid) as tot FROM absensi WHERE DATE(waktu)='$tgl'");
        $hadir = mysqli_fetch_assoc($q_abs)['tot'];
        $alpha = ($total_user - $hadir < 0) ? 0 : ($total_user - $hadir);
        array_unshift($data_merah, $alpha);
        
        $count++;
    }
    $offset++;
}

function tgl_indo($tgl) {
    $bulan = [1=>"Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember"];
    return date('d', strtotime($tgl)) . " " . $bulan[(int)date('m', strtotime($tgl))] . " " . date('Y', strtotime($tgl));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | SMK NU Lamongan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --nu-emerald: #1e8449; --nu-sidebar: #0a4d3c; --nu-bg: #f8fafb; --nu-accent: #2ecc71; }
        body { background-color: var(--nu-bg); font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        
        #sidebar { width: 260px; height: 100vh; background: var(--nu-sidebar); position: fixed; transition: 0.3s; z-index: 1000; display: flex; flex-direction: column; }
        #sidebar.mini { width: 85px; }
        .nav-link { color: rgba(255,255,255,0.7); padding: 15px 25px; display: flex; align-items: center; text-decoration: none; transition: 0.3s; white-space: nowrap; }
        .nav-link:hover, .nav-link.active { background: rgba(255,255,255,0.1); color: white; border-left: 4px solid var(--nu-accent); }
        #sidebar.mini .nav-link span, #sidebar.mini .unknown-section { display: none; }
        .nav-link.text-danger:hover { background: rgba(231, 76, 60, 0.1); border-left-color: #e74c3c; }

        .unknown-section { padding: 15px; border-top: 1px solid rgba(255,255,255,0.1); margin-top: 10px; flex-grow: 1; overflow-y: auto; }
        
        /* FIX BACKGROUND KOTAK KARTU BELUM TERDAFTAR */
        .unknown-card { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 12px; margin-bottom: 12px; color: white; }
        
        #content { width: calc(100% - 260px); margin-left: 260px; transition: 0.3s; padding: 30px; min-height: 100vh; display: flex; flex-direction: column; }
        #content.expand { width: calc(100% - 85px); margin-left: 85px; }
        
        .hero-banner { background: linear-gradient(135deg, #0a4d3c, #1e8449); border-radius: 25px; padding: 35px; color: white; margin-bottom: 30px; position: relative; }

        /* JAM OPERASIONAL POJOK KANAN (WARNA SOLID TETAP UTUH) */
        .op-pill { padding: 15px; border-radius: 20px; min-width: 110px; box-shadow: 0 4px 15px rgba(0,0,0,0.2); border: none; text-align: center; color: white !important; }
        .op-pill.mulai { background: #0061f2 !important; }
        .op-pill.maks { background: #f4b400 !important; color: #212529 !important; }
        .op-pill.maks * { color: #212529 !important; }
        .op-pill.pulang { background: #f85a40 !important; }
        .op-pill small { font-weight: 800; font-size: 10px; display: block; margin-bottom: 5px; opacity: 0.9; }
        .op-pill h4 { margin: 0; font-weight: 800; color: white; font-size: 24px; }

        /* ANIMASI KALENDER JAM DI HERO */
        .calendar-clock { display: flex; gap: 10px; }
        .flip-card { background: white; padding: 15px; border-radius: 12px; min-width: 90px; text-align: center; position: relative; box-shadow: 0 10px 20px rgba(0,0,0,0.2); transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1); }
        .flip-card.jam { border-bottom: 5px solid #3498db; color: #3498db; }
        .flip-card.menit { border-bottom: 5px solid #f1c40f; color: #f1c40f; }
        .flip-card.detik { border-bottom: 5px solid #e74c3c; color: #e74c3c; }
        .flip-card h2 { font-size: 45px; font-weight: 900; margin: 0; font-family: 'Poppins'; }
        .flip-card small { color: #2d3436; font-weight: 700; font-size: 10px; text-transform: uppercase; }
        
        /* Animasi ganti halaman sederhana */
        .animate-flip { animation: flipEffect 0.5s ease-in-out; }
        @keyframes flipEffect { 0% { transform: rotateX(0deg); } 50% { transform: rotateX(-90deg); } 100% { transform: rotateX(0deg); } }

        .blink { animation: blinker 1.5s linear infinite; } @keyframes blinker { 50% { opacity: 0; } }
        .footer-text { margin-top: auto; padding: 25px 0; text-align: center; color: #b2bec3; font-size: 12px; font-weight: 500; }
    </style>
</head>
<body>

<div id="sidebar">
    <div class="p-4 text-center"><img src="https://i.postimg.cc/kVS9X4gM/download.png" width="55"></div>
    <nav style="display: flex; flex-direction: column; height: calc(100vh - 160px);">
        <div class="menu-items">
            <a href="index.php" class="nav-link active"><i class="fas fa-th-large me-3"></i><span>Dashboard</span></a>
            <a href="modules/data_siswa.php" class="nav-link"><i class="fas fa-user-graduate me-3"></i><span>Data Siswa</span></a>
            <a href="modules/data_guru.php" class="nav-link"><i class="fas fa-user-tie me-3"></i><span>Data Guru</span></a>
            <a href="modules/laporan.php" class="nav-link"><i class="fas fa-file-excel me-3"></i><span>Laporan</span></a>
            <a href="modules/pengaturan.php" class="nav-link"><i class="fas fa-cog me-3"></i><span>Pengaturan</span></a>
            <a href="logout.php" class="nav-link text-danger mt-2" onclick="return confirm('Apakah Anda yakin ingin logout?')">
                <i class="fas fa-sign-out-alt me-3"></i><span>Logout</span>
            </a>
        </div>
        <div class="unknown-section">
            <div id="list-unknown"></div>
        </div>
    </nav>
    <div class="p-3 border-top border-white border-opacity-10 text-center">
        <button id="sidebarCollapse" class="btn text-white w-100"><i class="fas fa-chevron-left" id="toggleIcon"></i></button>
    </div>
</div>

<div id="content">
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 style="font-weight:800; font-size: 32px;"><span style="color:var(--nu-emerald)">SMK NU</span> LAMONGAN</h1>
            <small class="text-muted fw-bold">SISTEM MONITORING ABSENSI REAL-TIME</small>
        </div>
        
        <div class="d-flex gap-3">
            <div class="op-pill mulai">
                <small>ABSEN MASUK</small>
                <h4><?= $jam_mulai_masuk ?></h4>
                <span style="font-size:9px; color:rgba(255,255,255,0.8)">Waktu mulai scan</span>
            </div>
            <div class="op-pill maks">
                <small>BATAS MASUK</small>
                <h4><?= $jam_max_masuk ?></h4>
                <span style="font-size:9px; color:rgba(0,0,0,0.6)">Mulai terlambat</span>
            </div>
            <div class="op-pill pulang">
                <small>JAM PULANG</small>
                <h4><?= $jam_pulang ?></h4>
                <span style="font-size:9px; color:rgba(255,255,255,0.8)">Waktu pulang</span>
            </div>
        </div>
    </div>

    <div class="hero-banner shadow-sm d-flex justify-content-between align-items-center">
        <div>
            <span class="badge bg-white text-success px-3 rounded-pill mb-2">SERVER CONNECTED</span>
            <h2 class="fw-bold m-0">DIGITAL PRESENCE DASHBOARD</h2>
            <p class="m-0 opacity-75">Panel Kendali Presensi Utama SMK NU Lamongan</p>
        </div>

        <div class="text-center">
            <div class="calendar-clock mb-2">
                <div class="flip-card jam" id="card-hr">
                    <small>Jam</small>
                    <h2 id="hr">00</h2>
                </div>
                <div class="flip-card menit" id="card-mn">
                    <small>Menit</small>
                    <h2 id="mn">00</h2>
                </div>
                <div class="flip-card detik" id="card-sc">
                    <small>Detik</small>
                    <h2 id="sc">00</h2>
                </div>
            </div>
            <div class="fw-bold text-white mt-1" style="letter-spacing: 2px; font-size: 15px;">
                <i class="far fa-calendar-alt me-2"></i><?= tgl_indo(date('Y-m-d')) ?>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="bg-white p-4 rounded-4 shadow-sm h-100">
                <h6 class="fw-bold mb-4"><i class="fas fa-chart-line me-2 text-success"></i>STATISTIK 6 HARI TERAKHIR</h6>
                <div style="height: 350px;"><canvas id="absensiChart"></canvas></div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="bg-white p-4 rounded-4 shadow-sm mb-4 border-start border-4 border-success">
                <small class="text-muted fw-bold d-block mb-1">TOTAL USER AKTIF</small>
                <h2 class="fw-bold m-0"><?= $t_guru + $t_siswa ?></h2>
            </div>
            <div class="bg-white p-4 rounded-4 shadow-sm border-top border-4 border-danger">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h6 class="fw-bold m-0">LIVE MONITOR</h6>
                    <span class="badge bg-danger blink">LIVE</span>
                </div>
                <div id="live-scan-container"></div>
            </div>
        </div>
    </div>

    <div class="footer-text">
        <p>COPYRIGHT © 2026 ARIEF RAHMAN. ALL RIGHTS RESERVED.</p>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Sidebar logic
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('content');
    const toggleIcon = document.getElementById('toggleIcon');
    document.getElementById('sidebarCollapse').addEventListener('click', function() {
        sidebar.classList.toggle('mini');
        content.classList.toggle('expand');
        toggleIcon.classList.toggle('fa-chevron-right');
    });

    // Clock Animasi Kalender Berganti Halaman
    let lastHr, lastMn, lastSc;
    setInterval(() => {
        const now = new Date();
        const hr = String(now.getHours()).padStart(2, '0');
        const mn = String(now.getMinutes()).padStart(2, '0');
        const sc = String(now.getSeconds()).padStart(2, '0');

        if(hr !== lastHr) { 
            document.getElementById('hr').innerText = hr;
            document.getElementById('card-hr').classList.add('animate-flip');
            setTimeout(() => document.getElementById('card-hr').classList.remove('animate-flip'), 500);
            lastHr = hr;
        }
        if(mn !== lastMn) { 
            document.getElementById('mn').innerText = mn;
            document.getElementById('card-mn').classList.add('animate-flip');
            setTimeout(() => document.getElementById('card-mn').classList.remove('animate-flip'), 500);
            lastMn = mn;
        }
        if(sc !== lastSc) { 
            document.getElementById('sc').innerText = sc;
            document.getElementById('card-sc').classList.add('animate-flip');
            setTimeout(() => document.getElementById('card-sc').classList.remove('animate-flip'), 500);
            lastSc = sc;
        }
    }, 1000);

    // Chart logic
    new Chart(document.getElementById('absensiChart'), {
        type: 'bar',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [
                { label: 'Tepat Waktu', data: <?= json_encode($data_hijau) ?>, backgroundColor: '#2ecc71', borderRadius: 5 },
                { label: 'Terlambat', data: <?= json_encode($data_kuning) ?>, backgroundColor: '#f1c40f', borderRadius: 5 },
                { label: 'Pulang Awal', data: <?= json_encode($data_biru) ?>, backgroundColor: '#3498db', borderRadius: 5 },
                { label: 'Alpha', data: <?= json_encode($data_merah) ?>, backgroundColor: '#e74c3c', borderRadius: 5 }
            ]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // AJAX real-time
    function loadRealtime() {
        fetch('modules/get_live_scan.php').then(r => r.text()).then(h => { document.getElementById('live-scan-container').innerHTML = h; });
        fetch('modules/get_unknown_cards.php').then(r => r.text()).then(h => { document.getElementById('list-unknown').innerHTML = h; });
    }
    setInterval(loadRealtime, 2000);
</script>
</body>
</html>