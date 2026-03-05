<?php
require_once '../config/database.php';

$uid = $_GET['uid'] ?? '';

if (isset($_POST['simpan'])) {
    $uid_rfid = mysqli_real_escape_string($conn, $_POST['uid_rfid']);
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $peran    = $_POST['peran']; 
    $nomor_induk = mysqli_real_escape_string($conn, $_POST['identitas']);
    
    // Gunakan Nomor Induk sebagai Username & Password default agar tidak duplikat
    $username = $nomor_induk; 
    $password = password_hash($nomor_induk, PASSWORD_DEFAULT); // Password aman (terenkripsi)

    if ($peran == 'siswa') {
        $kelas = mysqli_real_escape_string($conn, $_POST['kelas']);
        $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
        
        // Query lengkap mengisi kolom identitas, nis, wa_ortu, dan username
        $query = "INSERT INTO users (uid_rfid, nama_lengkap, peran, no_hp, wa_ortu, identitas, nis, kelas, username, password) 
                  VALUES ('$uid_rfid', '$nama', '$peran', '$no_hp', '$no_hp', '$nomor_induk', '$nomor_induk', '$kelas', '$username', '$password')";
        $target = 'data_siswa.php';
    } else {
        // Untuk Guru
        $query = "INSERT INTO users (uid_rfid, nama_lengkap, peran, identitas, nip, username, password) 
                  VALUES ('$uid_rfid', '$nama', '$peran', '$nomor_induk', '$nomor_induk', '$username', '$password')";
        $target = 'data_guru.php';
    }
    
    if (mysqli_query($conn, $query)) {
        mysqli_query($conn, "DELETE FROM log_unknown WHERE uid_rfid = '$uid_rfid'");
        header("Location: $target?status=sukses");
        exit;
    } else {
        // Jika masih error, kita tampilkan keterangannya
        echo "<div style='color:red; background:white; padding:10px; border:1px solid red;'>Error: " . mysqli_error($conn) . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi Kartu | SMK NU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f0f2f5; font-family: 'Poppins', sans-serif; }
        .card { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .header-box { background: linear-gradient(135deg, #1e8449, #2ecc71); color: white; border-radius: 20px 20px 0 0; padding: 25px; }
        .uid-badge { background: #e8f5e9; color: #1b5e20; padding: 10px; border-radius: 10px; font-family: monospace; font-weight: bold; border: 1px dashed #2ecc71; }
    </style>
</head>
<body>

<div class="container mt-5" style="max-width: 600px;">
    <div class="card shadow">
        <div class="header-box text-center">
            <h4 class="fw-bold m-0 text-white">REGISTRASI KARTU</h4>
            <div class="uid-badge mt-2">ID: <?= htmlspecialchars($uid) ?></div>
        </div>
        <div class="card-body p-4">
            <form method="POST">
                <input type="hidden" name="uid_rfid" value="<?= htmlspecialchars($uid) ?>">

                <div class="mb-3">
                    <label class="fw-bold small mb-1">Pilih Peran</label>
                    <select name="peran" id="peran" class="form-select" onchange="toggleForm()" required>
                        <option value="siswa">SISWA</option>
                        <option value="guru">GURU / STAFF</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="fw-bold small mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" placeholder="Nama Lengkap" required>
                </div>

                <div class="mb-3">
                    <label class="fw-bold small mb-1" id="label_induk">NIS</label>
                    <input type="text" name="identitas" class="form-control" placeholder="Masukkan Nomor Induk" required>
                </div>

                <div id="form_siswa">
                    <div class="mb-3">
                        <label class="fw-bold small mb-1">Kelas</label>
                        <select name="kelas" class="form-select">
                            <option value="X">Kelas X</option>
                            <option value="XI">Kelas XI</option>
                            <option value="XII">Kelas XII</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="fw-bold small mb-1">WhatsApp Wali Murid (Gunakan 62...)</label>
                        <input type="text" name="no_hp" class="form-control" placeholder="Contoh: 628123456789">
                    </div>
                </div>

                <div class="d-flex gap-2 mt-4">
                    <a href="../index.php" class="btn btn-light w-100 fw-bold" style="border-radius:10px;">Batal</a>
                    <button type="submit" name="simpan" class="btn btn-success w-100 fw-bold" style="border-radius:10px;">Simpan & Aktifkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleForm() {
    var peran = document.getElementById("peran").value;
    var formSiswa = document.getElementById("form_siswa");
    var labelInduk = document.getElementById("label_induk");

    if (peran === "siswa") {
        formSiswa.style.display = "block";
        labelInduk.innerText = "NIS";
    } else {
        formSiswa.style.display = "none";
        labelInduk.innerText = "NIP";
    }
}
toggleForm(); // Jalankan saat awal loading
</script>

</body>
</html>