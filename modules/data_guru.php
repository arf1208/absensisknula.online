<?php 
require_once '../config/database.php';

// --- LOGIKA HAPUS ---
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    mysqli_query($conn, "DELETE FROM users WHERE id='$id' AND peran='guru'");
    header("Location: data_guru.php?pesan=dihapus");
    exit();
}

// --- LOGIKA TAMBAH ---
if (isset($_POST['tambah_guru'])) {
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nip  = mysqli_real_escape_string($conn, $_POST['nip']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $uid  = mysqli_real_escape_string($conn, $_POST['uid_rfid']);
    
    mysqli_query($conn, "INSERT INTO users (nama_lengkap, nip, jabatan, uid_rfid, peran) VALUES ('$nama', '$nip', '$jabatan', '$uid', 'guru')");
    header("Location: data_guru.php?pesan=ditambah");
    exit();
}

// --- LOGIKA EDIT ---
if (isset($_POST['edit_guru'])) {
    $id = mysqli_real_escape_string($conn, $_POST['id_edit']);
    $nama = mysqli_real_escape_string($conn, $_POST['nama_lengkap']);
    $nip  = mysqli_real_escape_string($conn, $_POST['nip']);
    $jabatan = mysqli_real_escape_string($conn, $_POST['jabatan']);
    $uid  = mysqli_real_escape_string($conn, $_POST['uid_rfid']);
    
    $query = "UPDATE users SET nama_lengkap='$nama', nip='$nip', jabatan='$jabatan', uid_rfid='$uid' WHERE id='$id'";
    if(mysqli_query($conn, $query)) {
        header("Location: data_guru.php?pesan=diubah");
        exit();
    } else {
        die("Gagal Update: " . mysqli_error($conn));
    }
}

$guru = mysqli_query($conn, "SELECT * FROM users WHERE peran='guru' ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Guru | SMK NU Lamongan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f8fafb; font-family: 'Poppins', sans-serif; padding: 30px; }
        .card-nu { background: white; border-radius: 15px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); padding: 25px; }
        .btn-nu { background: #1e8449; color: white; border-radius: 8px; font-weight: 600; }
        .table thead { background: #0a4d3c; color: white; }
    </style>
</head>
<body>

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">DATA GURU</h2>
            <p class="text-muted">Kelola NIP, Jabatan, dan Kartu RFID.</p>
        </div>
        <a href="../index.php" class="btn btn-outline-secondary rounded-pill px-4"><i class="fas fa-arrow-left me-2"></i>Dashboard</a>
    </div>

    <div class="card-nu mb-4">
        <h5 class="fw-bold mb-3">Tambah Guru</h5>
        <form method="POST" class="row g-2">
            <div class="col-md-3"><input type="text" name="nama_lengkap" class="form-control" placeholder="Nama Guru" required></div>
            <div class="col-md-2"><input type="text" name="nip" class="form-control" placeholder="NIP" required></div>
            <div class="col-md-3"><input type="text" name="jabatan" class="form-control" placeholder="Jabatan" required></div>
            <div class="col-md-2"><input type="text" name="uid_rfid" class="form-control" placeholder="UID RFID" required></div>
            <div class="col-md-2"><button type="submit" name="tambah_guru" class="btn btn-nu w-100">Simpan</button></div>
        </form>
    </div>

    <div class="card-nu text-center">
        <table class="table table-hover align-middle">
            <thead>
                <tr>
                    <th>NIP</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>UID RFID</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($guru)): ?>
                <tr>
                    <td><?= $row['nip'] ?></td>
                    <td class="fw-bold"><?= $row['nama_lengkap'] ?></td>
                    <td><?= $row['jabatan'] ?></td>
                    <td><code class="text-success fw-bold"><?= $row['uid_rfid'] ?></code></td>
                    <td>
                        <button class="btn btn-warning btn-sm text-white px-3" 
                                onclick="fillEdit('<?= $row['id'] ?>', '<?= $row['nama_lengkap'] ?>', '<?= $row['nip'] ?>', '<?= $row['jabatan'] ?>', '<?= $row['uid_rfid'] ?>')"
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
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header border-0">
                <h5 class="fw-bold">Edit Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_edit" id="id_edit">
                    <div class="mb-3"><label class="small fw-bold">Nama</label><input type="text" name="nama_lengkap" id="nama_edit" class="form-control" required></div>
                    <div class="mb-3"><label class="small fw-bold">NIP</label><input type="text" name="nip" id="nip_edit" class="form-control" required></div>
                    <div class="mb-3"><label class="small fw-bold">Jabatan</label><input type="text" name="jabatan" id="jabatan_edit" class="form-control" required></div>
                    <div class="mb-3"><label class="small fw-bold">UID RFID</label><input type="text" name="uid_rfid" id="uid_edit" class="form-control" required></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" name="edit_guru" class="btn btn-warning text-white px-4">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function fillEdit(id, nama, nip, jabatan, uid) {
        document.getElementById('id_edit').value = id;
        document.getElementById('nama_edit').value = nama;
        document.getElementById('nip_edit').value = nip;
        document.getElementById('jabatan_edit').value = jabatan;
        document.getElementById('uid_edit').value = uid;
    }
</script>
</body>
</html>
