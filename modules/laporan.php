<?php
date_default_timezone_set('Asia/Jakarta');
require_once '../config/database.php';

// LOGIKA EKSPOR EXCEL
if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    header("Content-type: application/vnd-ms-excel");
    header("Content-Disposition: attachment; filename=Laporan_Presensi_" . date('Ymd_His') . ".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
}

$tgl_mulai = $_GET['tgl_mulai'] ?? date('Y-m-01');
$tgl_selesai = $_GET['tgl_selesai'] ?? date('Y-m-d');

// Query menggunakan tabel 'absensi' sesuai database Anda
$query_string = "SELECT a.*, u.nama_lengkap, u.peran 
                 FROM absensi a 
                 JOIN users u ON a.uid_rfid = u.uid_rfid 
                 WHERE DATE(a.waktu) BETWEEN '$tgl_mulai' AND '$tgl_selesai' 
                 ORDER BY a.waktu DESC";
$sql = mysqli_query($conn, $query_string);
?>

<?php if (!isset($_GET['export'])): ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekap Absensi | SMK NU Lamongan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #f4f7f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .table thead { background: #2ecc71; color: white; }
    </style>
</head>
<body class="p-4">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-success"><i class="fas fa-file-invoice me-2"></i>REKAPITULASI ABSENSI</h3>
            <a href="../index.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
        </div>

        <div class="card p-4 mb-4">
            <form method="GET" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small fw-bold">DARI TANGGAL</label>
                    <input type="date" name="tgl_mulai" class="form-control" value="<?= $tgl_mulai ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold">SAMPAI TANGGAL</label>
                    <input type="date" name="tgl_selesai" class="form-control" value="<?= $tgl_selesai ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary px-4 me-2"><i class="fas fa-filter me-2"></i>Filter</button>
                    <a href="?export=excel&tgl_mulai=<?= $tgl_mulai ?>&tgl_selesai=<?= $tgl_selesai ?>" class="btn btn-success px-4">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </a>
                </div>
            </form>
        </div>
<?php endif; ?>

        <div class="card overflow-hidden">
            <table class="table table-hover mb-0" border="<?= isset($_GET['export']) ? '1' : '0' ?>">
                <thead>
                    <tr>
                        <th class="py-3">No</th>
                        <th>Tanggal & Waktu</th>
                        <th>Nama Lengkap</th>
                        <th>Status</th>
                        <th>ID Kartu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $no = 1;
                    while($row = mysqli_fetch_assoc($sql)): 
                        $warna_status = ($row['status'] == 'Terlambat') ? 'text-danger' : 'text-success';
                    ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= date('d/m/Y H:i:s', strtotime($row['waktu'])) ?></td>
                        <td class="fw-bold"><?= htmlspecialchars($row['nama_lengkap']) ?></td>
                        <td class="<?= $warna_status ?> fw-bold"><?= strtoupper($row['status']) ?></td>
                        <td class="text-muted small"><?= $row['uid_rfid'] ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
<?php if (!isset($_GET['export'])): ?>
    </div>
</body>
</html>
<?php endif; ?>