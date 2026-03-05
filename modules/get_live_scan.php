<?php
// 1. TAMBAHKAN INI AGAR JAM SINKRON DENGAN INDONESIA
date_default_timezone_set('Asia/Jakarta');

require_once '../config/database.php';

// Ambil 5 data absensi terbaru
$query = "SELECT a.*, u.nama_lengkap 
          FROM absensi a 
          LEFT JOIN users u ON a.uid_rfid = u.uid_rfid 
          ORDER BY a.waktu DESC 
          LIMIT 5";

$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $nama = $row['nama_lengkap'] ?? "Unknown Card";
        
        // Memformat jam agar sesuai dengan kolom 'waktu' di database
        $waktu = date('H:i:s', strtotime($row['waktu']));
        
        $is_registered = isset($row['nama_lengkap']);
        
        // Tentukan kelas CSS
        $class = $is_registered ? "scan-registered" : "scan-unknown";
        $icon = $is_registered ? "fa-check-circle text-success" : "fa-exclamation-triangle text-danger";
        
        // Warna badge status
        $status_bg = 'bg-success';
        if ($row['status'] == 'Terlambat') $status_bg = 'bg-warning text-dark';
        if ($row['status'] == 'Pulang') $status_bg = 'bg-primary';

        echo '
        <div class="scan-item ' . $class . '" style="border-left: 4px solid ' . ($is_registered ? '#2ecc71' : '#e74c3c') . '; background: #f8fafb; padding: 10px; margin-bottom: 10px; border-radius: 8px;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas ' . $icon . ' me-2"></i>
                    <strong style="font-size: 13px;">' . htmlspecialchars($nama) . '</strong>
                </div>
                <small class="text-muted" style="font-weight: 600;">' . $waktu . '</small>
            </div>
            <div class="mt-1">
                <span class="badge ' . $status_bg . '" style="font-size: 9px; padding: 4px 8px;">' . strtoupper($row['status']) . '</span>
                <small class="text-muted ms-1" style="font-size: 10px;">ID: ' . $row['uid_rfid'] . '</small>
            </div>
        </div>';
    }
} else {
    echo '<div class="text-center py-4 text-muted small">Belum ada aktivitas scan hari ini.</div>';
}
?>