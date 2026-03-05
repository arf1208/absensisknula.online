<?php 
require_once '../config/database.php';

// --- LOGIKA HAPUS ---
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    mysqli_query($conn, "DELETE FROM users WHERE id='$id' AND peran='siswa'");
    header("Location: data_siswa.php?pesan=dihapus");
    exit();
}

// --- LOGIKA TAMBAH ---
if (isset($_POST['tambah_siswa'])) {
    $nama    = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nis     = mysqli_real_escape_string($conn, $_POST['nis']);
    $kelas   = mysqli_real_escape_string($conn, $_POST['kelas']);
    $wa_ortu = mysqli_real_escape_string($conn, $_POST['wa_ortu']);
    $uid     = mysqli_real_escape_string($conn, $_POST['uid_rfid']);
    
    $q_tambah = "INSERT INTO users (nama_lengkap, nis, kelas, wa_ortu, uid_rfid, peran) 
                 VALUES ('$nama', '$nis', '$kelas', '$wa_ortu', '$uid', 'siswa')";
    
    if(mysqli_query($conn, $q_tambah)) {
        header("Location: data_siswa.php?pesan=ditambah");
        exit();
    } else {
        die("Gagal Tambah: " . mysqli_error($conn));
    }
}

// --- LOGIKA EDIT ---
if (isset($_POST['edit_siswa'])) {
    $id      = mysqli_real_escape_string($conn, $_POST['id_edit']);
    $nama    = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nis     = mysqli_real_escape_string($conn, $_POST['nis']);
    $kelas   = mysqli_real_escape_string($conn, $_POST['kelas']);
    $wa_ortu = mysqli_real_escape_string($conn, $_POST['wa_ortu']);
    $uid     = mysqli_real_escape_string($conn, $_POST['uid_rfid']);
    
    $q_edit = "UPDATE users SET 
               nama_lengkap='$nama', 
               nis='$nis', 
               kelas='$kelas', 
               wa_ortu='$wa_ortu', 
               uid_rfid='$uid' 
               WHERE id='$id'";
              
    if(mysqli_query($conn, $q_edit)) {
        header("Location: data_siswa.php?pesan=diubah");
        exit();
    } else {
        die("Gagal Update: " . mysqli_error($conn));
    }
}

$siswa = mysqli_query($conn, "SELECT * FROM users WHERE peran='siswa' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Siswa | SMK NU Lamongan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8fafb; font-family: 'Poppins', sans-serif; padding: 30px; }
        .card-nu { background: white; border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); padding: 25px; }
        .btn-nu { background: #1e8449; color: white; border-radius: 8px; font-weight: 600; }
        .table thead { background: #0a4d3c; color: white; }
        .badge-wa { background: #e8f5e9; color: #2e7d32; padding: 5px 10px; border-radius: 5px; font-size: 12px; font-weight: bold; border: 1px solid #c3e6cb; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0 text-success">DATA SISWA</h2>
            <p class="text-muted small">Manajemen NIS, Kelas, dan WhatsApp Wali Murid.</p>
        </div>
        <a href="../index.php" class="btn btn-outline-secondary rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i>Dashboard</a>
    </div>

    <div class="card-nu mb-4">
        <h5 class="fw-bold mb-3 text-success">Tambah Siswa Baru</h5>
        <form method="POST" class="row g-2">
            <div class="col-md-2"><input type="text" name="nama_lengkap" class="form-control" placeholder="Nama" required></div>
            <div class="col-md-2"><input type="text" name="nis" class="form-control" placeholder="NIS" required></div>
            <div class="col-md-2"><input type="text" name="kelas" class="form-control" placeholder="Kelas" required></div>
            <div class="col-md-2"><input type="text" name="wa_ortu" class="form-control" placeholder="WA (628...)" required></div>
            <div class="col-md-2"><input type="text" name="uid_rfid" class="form-control" placeholder="UID RFID" required></div>
            <div class="col-md-2"><button type="submit" name="tambah_siswa" class="btn btn-nu w-100">Simpan</button></div>
        </form>
    </div>

    <div class="card-nu text-center">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>NIS</th>
                    <th>Nama</th>
                    <th>Kelas</th>
                    <th>WhatsApp Wali</th>
                    <th>RFID</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($siswa)): ?>
                <tr>
                    <td><?= $row['nis'] ?></td>
                    <td class="fw-bold text-start"><?= $row['nama_lengkap'] ?></td>
                    <td><span class="badge bg-secondary"><?= $row['kelas'] ?></span></td>
                    <td><span class="badge-wa"><i class="fab fa-whatsapp me-1"></i><?= $row['wa_ortu'] ?></span></td>
                    <td><code class="fw-bold"><?= $row['uid_rfid'] ?></code></td>
                    <td>
                        <button class="btn btn-warning btn-sm text-white px-3" 
                                onclick="bukaEdit('<?= $row['id'] ?>','<?= $row['nama_lengkap'] ?>','<?= $row['nis'] ?>','<?= $row['kelas'] ?>','<?= $row['wa_ortu'] ?>','<?= $row['uid_rfid'] ?>')"
                                data-bs-toggle="modal" data-bs-target="#modalEdit">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <a href="?hapus=<?= $row['id'] ?>" class="btn btn-danger btn-sm px-3" onclick="return confirm('Hapus?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="modalEdit" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold">Edit Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_edit" id="id_edit">
                    <div class="mb-3"><label class="small fw-bold">Nama</label><input type="text" name="nama_lengkap" id="nama_edit" class="form-control" required></div>
                    <div class="row">
                        <div class="col-6 mb-3"><label class="small fw-bold">NIS</label><input type="text" name="nis" id="nis_edit" class="form-control" required></div>
                        <div class="col-6 mb-3"><label class="small fw-bold">Kelas</label><input type="text" name="kelas" id="kelas_edit" class="form-control" required></div>
                    </div>
                    <div class="mb-3"><label class="small fw-bold">WhatsApp Wali</label><input type="text" name="wa_ortu" id="wa_edit" class="form-control" required></div>
                    <div class="mb-3"><label class="small fw-bold">RFID</label><input type="text" name="uid_rfid" id="uid_edit" class="form-control" required></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="edit_siswa" class="btn btn-warning text-white px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function bukaEdit(id, nama, nis, kelas, wa, uid) {
        document.getElementById('id_edit').value = id;
        document.getElementById('nama_edit').value = nama;
        document.getElementById('nis_edit').value = nis;
        document.getElementById('kelas_edit').value = kelas;
        document.getElementById('wa_edit').value = wa;
        document.getElementById('uid_edit').value = uid;
    }
</script>
</body>
</html>
