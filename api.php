<?php
/**
 * API SISTEM ABSENSI SMK NU LAMONGAN
 * Versi: Final Revisi (Limit 2x Scan + Pulang Sebelum Jam Pulang)
 */

// 1. PENGATURAN WAKTU & HEADER
date_default_timezone_set('Asia/Jakarta');
header('Content-Type: application/json');

// 2. KONEKSI DATABASE
if (file_exists('config/database.php')) {
    require_once 'config/database.php';
} else {
    die(json_encode(["status" => "error", "message" => "File config/database.php tidak ditemukan"]));
}

/**
 * FUNGSI KIRIM WHATSAPP FONNTE
 */
function kirimNotifikasiWA($target, $pesan) {
    $token = "A8m4iU1P2f15yB4wcsWf"; 
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => "https://api.fonnte.com/send",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_POSTFIELDS => array(
            'target' => $target,
            'message' => $pesan,
            'countryCode' => '62', 
        ),
        CURLOPT_HTTPHEADER => array(
            "Authorization: $token"
        ),
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    return $response;
}

// 3. MENERIMA DATA DARI ESP32
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (isset($data['uid'])) {
    
    // --- CEK STATUS REMOTE ON/OFF ---
    $cek_sistem = mysqli_query($conn, "SELECT sys_on FROM pengaturan_sistem WHERE id = 1 LIMIT 1");
    $sys = mysqli_fetch_assoc($cek_sistem);
    $is_online = ($sys['sys_on'] == 1) ? true : false;

    $uid = mysqli_real_escape_string($conn, $data['uid']);
    $waktu_lengkap = date('Y-m-d H:i:s');
    $tgl_sekarang_db = date('Y-m-d');
    $jam_cek = (int)date('H'); 
    $jam_menit = date('H:i');
    $tgl_format_indo = date('d-m-Y');

    // --- LOGIKA SALAM WAKTU ---
    if ($jam_cek >= 3 && $jam_cek < 11) { $salam_waktu = "Selamat Pagi"; }
    elseif ($jam_cek >= 11 && $jam_cek < 15) { $salam_waktu = "Selamat Siang"; }
    elseif ($jam_cek >= 15 && $jam_cek < 18) { $salam_waktu = "Selamat Sore"; }
    else { $salam_waktu = "Selamat Malam"; }

    // 4. CEK DATA KARTU DI DATABASE
    $user_query = mysqli_query($conn, "SELECT nama_lengkap, wa_ortu, peran FROM users WHERE uid_rfid = '$uid' LIMIT 1");

    if (mysqli_num_rows($user_query) > 0) {
        $user_data = mysqli_fetch_assoc($user_query);
        $nama_lengkap = $user_data['nama_lengkap'];
        $nomor_wa     = $user_data['wa_ortu'];
        $peran        = $user_data['peran'];

        // --- PROTEKSI JIKA SISTEM "OFF" ---
        if (!$is_online) {
            echo json_encode(["status" => "off", "sys_on" => false, "message" => "SISTEM MATI"]);
            exit;
        }

        // --- CEK LIMIT SCAN (MAKSIMAL 2 KALI SEHARI) ---
        $cek_absen_hari_ini = mysqli_query($conn, "SELECT COUNT(*) as total FROM absensi WHERE uid_rfid = '$uid' AND DATE(waktu) = '$tgl_sekarang_db'");
        $data_count = mysqli_fetch_assoc($cek_absen_hari_ini);
        
        if ($data_count['total'] >= 2) {
            echo json_encode([
                "status" => "error",
                "sys_on" => true,
                "nama" => $nama_lengkap,
                "message" => "LIMIT SCAN 2X"
            ]);
            exit;
        }

        // --- LOGIKA PENENTUAN STATUS REVISI ---
        if ($jam_menit >= '06:00' && $jam_menit <= '07:02') {
            $status = 'Tepat Waktu';
        } elseif ($jam_menit > '07:02' && $jam_menit < '13:00') {
            $status = 'Terlambat';
        } elseif ($jam_menit >= '13:00' && $jam_menit < '15:00') {
            $status = 'Pulang Sebelum Jam Pulang';
        } elseif ($jam_menit >= '15:00') {
            $status = 'Pulang';
        } else {
            $status = 'Belum Waktunya';
        }

        // 5. SIMPAN KE DATABASE
        $insert_query = "INSERT INTO absensi (uid_rfid, status, waktu) VALUES ('$uid', '$status', '$waktu_lengkap')";
        
        if (mysqli_query($conn, $insert_query)) {
            
            // --- KIRIM NOTIFIKASI WHATSAPP FONNTE ---
            if (!empty($nomor_wa) && strtolower($peran) == 'siswa') {
                $pesanWA  = "Assalamualaikum Wr. Wb.\n";
                $pesanWA .= "$salam_waktu, Ayah/Bunda Ananda *$nama_lengkap*.\n\n";
                $pesanWA .= "Menginfokan bahwa Ananda telah melakukan presensi digital dengan status: *$status*.\n\n";
                $pesanWA .= "📅 *Tanggal:* $tgl_format_indo\n";
                $pesanWA .= "⏰ *Waktu:* $jam_menit WIB\n\n";
                $pesanWA .= "Wassalamualaikum Wr. Wb.\n";
                $pesanWA .= "— *SMK NU LAMONGAN* —";

                kirimNotifikasiWA($nomor_wa, $pesanWA);
            }

            // Respon Sukses ke ESP32
            echo json_encode([
                "status" => "success",
                "sys_on" => true,
                "nama" => $nama_lengkap,
                "keterangan" => $status,
                "jam" => $jam_menit
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Gagal simpan database"]);
        }

    } else {
        // --- LOG KARTU TIDAK DIKENAL ---
        $log_unknown = "INSERT INTO log_unknown (uid_rfid, waktu) VALUES ('$uid', '$waktu_lengkap') 
                        ON DUPLICATE KEY UPDATE waktu = '$waktu_lengkap'";
        mysqli_query($conn, $log_unknown);

        http_response_code(404);
        echo json_encode([
            "status" => "not_found",
            "sys_on" => $is_online,
            "message" => "Kartu Belum Terdaftar",
            "uid" => $uid
        ]);
    }
} else {
    http_response_code(400);
    echo json_encode(["status" => "invalid", "message" => "Data tidak lengkap"]);
}
?>