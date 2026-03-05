<?php
/**
 * AJAX Service: Get Unknown Cards (Final Version)
 * SMK NU Lamongan - Jalur Registrasi Cepat
 */

require_once '../config/database.php';

// Mengambil data kartu asing dalam 1 menit terakhir
$query = "SELECT * FROM log_unknown 
          WHERE waktu >= DATE_SUB(NOW(), INTERVAL 1 MINUTE) 
          ORDER BY waktu DESC 
          LIMIT 3";

$q = mysqli_query($conn, $query);

if (mysqli_num_rows($q) > 0) {
    while ($r = mysqli_fetch_assoc($q)) {
        // UID RFID dibersihkan untuk keamanan URL
        $uid_clean = htmlspecialchars($r['uid_rfid']);
        
        echo '<div class="unknown-card">
                <div class="d-flex flex-column">
                    <small class="text-white-50" style="font-size: 9px;">Terdeteksi Alat:</small>
                    <span class="unknown-uid">ID: ' . $uid_clean . '</span>
                    
                    <a href="modules/registrasi_cepat.php?uid=' . $uid_clean . '" class="btn-daftarkan text-center mt-1">
                        <i class="fas fa-bolt me-1"></i> DAFTARKAN SEGERA
                    </a>
                </div>
              </div>';
    }
} else { 
    // Tampilan default jika tidak ada scan baru
    echo '<div class="text-center p-3 border border-white border-opacity-10 rounded-3" style="border-style: dashed !important;">
            <i class="fas fa-id-card text-white-50 mb-2" style="font-size: 18px;"></i>
            <div class="text-white-50" style="font-size: 10px; line-height: 1.4;">
                Menunggu scan<br>kartu baru...
            </div>
          </div>'; 
}
?>