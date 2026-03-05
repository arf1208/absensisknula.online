<?php
// Sesuaikan dengan data di hPanel > Databases > MySQL Databases
$host = "localhost";
$user = "u469804088_system_absensi"; // Sesuai screenshot Anda
$pass = "~Skanula$111"; 
$db   = "u469804088_DBabsensi"; // Sesuai nama database di Hostinger

$conn = mysqli_connect($host, $user, $pass, $db);

if (!$conn) {
    die("Koneksi Gagal: " . mysqli_connect_error());
}
?>
