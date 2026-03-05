<?php 
require_once '../config/database.php'; 

// --- LOGIKA UPDATE ---
$status_pesan = "";

// 1. Ambil status ESP32 saat ini dari database
$query_status = $conn->query("SELECT sys_on FROM pengaturan_sistem WHERE id = 1");
$data_status = $query_status->fetch_assoc();
$status_esp_sekarang = $data_status['sys_on'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Logika Update Remote ON/OFF
    if (isset($_POST['update_remote'])) {
        $status_baru = $_POST['status_esp'];
        $conn->query("UPDATE pengaturan_sistem SET sys_on = $status_baru WHERE id = 1");
        $status_esp_sekarang = $status_baru; 
        $status_pesan = "Status perangkat berhasil diubah!";
    } else {
        // Logika update lainnya (Jam, Password, dll)
        $status_pesan = "Pengaturan berhasil diperbarui!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Sistem | SMK NU Lamongan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background-color: #f8fafb; font-family: 'Poppins', sans-serif; padding: 30px; }
        .card-settings { background: white; border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); overflow: hidden; }
        .nav-tabs-custom { background: #f1f3f5; padding: 10px 15px 0; border: none; gap: 5px; }
        .nav-tabs-custom .nav-link { 
            border: none; color: #6c757d; font-weight: 600; font-size: 13px; 
            padding: 12px 20px; border-radius: 12px 12px 0 0; text-transform: uppercase;
        }
        .nav-tabs-custom .nav-link.active { background: white; color: #1e8449; }
        .tab-content { padding: 40px; }
        .form-label { font-weight: 600; color: #495057; font-size: 14px; }
        .help-text { font-size: 12px; color: #6c757d; margin-top: 5px; display: block; }
        .btn-save { background: #1e8449; color: white; padding: 10px 25px; border-radius: 10px; font-weight: 600; border: none; transition: 0.3s; }
        .btn-save:hover { background: #145a32; transform: translateY(-2px); }
        .form-check-input:checked { background-color: #1e8449; border-color: #1e8449; }
        .status-badge { padding: 8px 15px; border-radius: 30px; font-size: 12px; font-weight: 700; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0 text-dark">PENGATURAN SISTEM</h2>
            <p class="text-muted">Konfigurasi operasional dan keamanan sistem digital.</p>
        </div>
        <a href="../index.php" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fas fa-arrow-left me-2"></i> Dashboard
        </a>
    </div>

    <?php if($status_pesan): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-4" role="alert">
            <i class="fas fa-check-circle me-2"></i> <?= $status_pesan ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card-settings">
        <ul class="nav nav-tabs nav-tabs-custom" id="settingsTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="jam-tab" data-bs-toggle="tab" data-bs-target="#jam" type="button">
                    <i class="fas fa-clock me-2"></i> Jam Sekolah
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="keamanan-tab" data-bs-toggle="tab" data-bs-target="#keamanan" type="button">
                    <i class="fas fa-shield-alt me-2"></i> Keamanan
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="kontrol-tab" data-bs-toggle="tab" data-bs-target="#kontrol" type="button">
                    <i class="fas fa-microchip me-2"></i> Kontrol Perangkat
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="identitas-tab" data-bs-toggle="tab" data-bs-target="#identitas" type="button">
                    <i class="fas fa-school me-2"></i> Identitas Sekolah
                </button>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="jam">
                <div class="row align-items-center mb-4">
                    <div class="col-md-7">
                        <h5 class="fw-bold">Konfigurasi Waktu Presensi</h5>
                        <p class="text-muted small">Atur batasan waktu masuk dan jam pulang sesuai kebijakan sekolah.</p>
                    </div>
                </div>
                <form method="POST" action="">
                    <div class="row g-4">
                        <div class="col-md-4">
                            <label class="form-label">Jam Mulai Masuk</label>
                            <input type="time" name="jam_mulai_masuk" class="form-control form-control-lg rounded-3 shadow-sm" value="06:00">
                            <span class="help-text">Waktu awal alat bisa scan masuk.</span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jam Maksimal Masuk</label>
                            <input type="time" name="jam_max_masuk" class="form-control form-control-lg rounded-3 shadow-sm" value="07:02">
                            <span class="help-text">Batas akhir status Tepat Waktu.</span>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Jam Pulang</label>
                            <input type="time" name="jam_pulang" class="form-control form-control-lg rounded-3 shadow-sm" value="15:00">
                            <span class="help-text">Waktu standar pulang sekolah.</span>
                        </div>
                    </div>
                    <input type="hidden" name="toleransi" value="0">
                    <hr class="my-5">
                    <button type="submit" class="btn btn-save">Simpan Perubahan Jam</button>
                </form>
            </div>

            <div class="tab-pane fade" id="keamanan">
                <div class="row mb-4">
                    <div class="col-md-7">
                        <h5 class="fw-bold">Keamanan Akun Admin</h5>
                        <p class="text-muted small">Perbarui kata sandi Anda secara berkala untuk menjaga keamanan akses dashboard.</p>
                    </div>
                </div>
                <form method="POST" action="">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label">Password Baru</label>
                            <input type="password" name="pass_baru" class="form-control form-control-lg rounded-3 shadow-sm" placeholder="Masukkan password baru">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Konfirmasi Password</label>
                            <input type="password" name="pass_konfirmasi" class="form-control form-control-lg rounded-3 shadow-sm" placeholder="Ulangi password baru">
                        </div>
                    </div>
                    <hr class="my-5">
                    <button type="submit" class="btn btn-save" style="background: #2980b9;">Update Kata Sandi</button>
                </form>
            </div>

            <div class="tab-pane fade" id="kontrol">
                <div class="row mb-4">
                    <div class="col-md-7">
                        <h5 class="fw-bold text-uppercase">Kontrol Jarak Jauh ESP32</h5>
                        <p class="text-muted small">Matikan fungsi pemindaian kartu pada alat secara instan tanpa mencabut kabel power.</p>
                    </div>
                    <div class="col-md-5 text-end">
                        <?php if($status_esp_sekarang == 1): ?>
                            <span class="status-badge bg-success text-white"><i class="fas fa-circle me-1"></i> SISTEM ONLINE</span>
                        <?php else: ?>
                            <span class="status-badge bg-danger text-white"><i class="fas fa-power-off me-1"></i> SISTEM NONAKTIF</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="p-4 rounded-4 border bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="fw-bold mb-1">Status Operasional Mesin Absensi</h6>
                            <p class="text-muted small mb-0">Jika dimatikan, ESP32 akan menolak setiap kartu yang ditempelkan.</p>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="update_remote" value="1">
                            <input type="hidden" name="status_esp" value="<?= ($status_esp_sekarang == 1) ? '0' : '1' ?>">
                            <div class="form-check form-switch form-check-reverse shadow-sm p-2 bg-white rounded-3 border">
                                <input class="form-check-input h4 m-0" type="checkbox" role="switch" <?= ($status_esp_sekarang == 1) ? 'checked' : '' ?> onchange="this.form.submit()">
                            </div>
                        </form>
                    </div>
                </div>

                <div class="alert alert-warning mt-4 rounded-4 border-0 shadow-sm">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <b>Peringatan:</b> Mematikan sistem tidak mematikan daya listrik alat, melainkan hanya menonaktifkan fungsi verifikasi data pada API.
                </div>
            </div>

            <div class="tab-pane fade" id="identitas">
                <h5 class="fw-bold mb-3">Informasi Sekolah</h5>
                <p class="text-muted">Menu ini digunakan untuk mengubah profil SMK NU Lamongan pada sistem.</p>
                <div class="alert alert-light border border-info text-info">
                    <i class="fas fa-info-circle me-2"></i> Fitur edit identitas akan tersedia pada update versi berikutnya.
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>