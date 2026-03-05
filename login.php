<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$error_msg = "";

if (isset($_POST['login'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];

    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['login'] = true;
        $_SESSION['nama'] = "Administrator";
        $_SESSION['role'] = "admin";
        header("Location: index.php");
        exit;
    } else {
        $query = "SELECT * FROM users WHERE username = '$username' AND peran != 'siswa'";
        $result = mysqli_query($conn, $query);
        
        if (mysqli_num_rows($result) === 1) {
            $row = mysqli_fetch_assoc($result);
            if (password_verify($password, $row['password'])) {
                $_SESSION['login'] = true;
                $_SESSION['nama'] = $row['nama_lengkap'];
                $_SESSION['role'] = $row['peran'];
                header("Location: index.php");
                exit;
            } else {
                $error_msg = "Password salah!";
            }
        } else {
            $error_msg = "Username tidak terdaftar!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Digital System Absensi SMK NU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-color: #0b1e1e; 
            --accent-green: #00ffaa;
            --card-bg: #ffffff;
            --input-fill: #f0f4f4;
            --text-dark: #1a2a2a;
        }

        body {
            background: var(--bg-color);
            background: radial-gradient(circle at center, #133a3a 0%, #0b1e1e 100%);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            margin: 0;
            color: white;
            overflow: hidden; /* Mencegah scrollbar muncul */
        }

        .login-wrapper {
            width: 100%;
            max-width: 400px; /* Diperkecil dari 450px */
            padding: 10px;
            text-align: center;
            transform: scale(0.9); /* Mengecilkan seluruh elemen secara proporsional */
        }

        /* Header */
        .logo-container {
            margin-bottom: 15px;
        }

        .logo-circle {
            width: 110px; /* Diperkecil dari 150px */
            height: 110px;
            background: white;
            border-radius: 50%;
            padding: 8px;
            display: inline-block;
            box-shadow: 0 0 20px rgba(0, 255, 170, 0.2);
        }

        .header-title h1 {
            font-weight: 800;
            font-size: 20px; /* Diperkecil */
            letter-spacing: 1px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .header-title h1 span {
            color: var(--accent-green);
        }

        .badge-school {
            display: inline-flex;
            align-items: center;
            background: rgba(0, 255, 170, 0.1);
            color: var(--accent-green);
            border: 1px solid rgba(0, 255, 170, 0.3);
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 25px;
        }

        /* Card Styling */
        .login-card {
            background: var(--card-bg);
            border-radius: 30px;
            padding: 30px 35px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            text-align: left;
        }

        .login-card h2 {
            font-weight: 700;
            font-size: 24px;
            color: var(--text-dark);
            margin-bottom: 3px;
        }

        .login-card p.subtitle {
            color: #7a8a8a;
            font-size: 13px;
            margin-bottom: 25px;
        }

        /* Form Styling */
        .label-text {
            display: block;
            font-size: 10px;
            font-weight: 800;
            color: #a0b0b0;
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .input-group-custom {
            position: relative;
            margin-bottom: 15px;
        }

        .input-group-custom i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0b0b0;
            font-size: 14px;
        }

        .form-control-custom {
            background-color: var(--input-fill);
            border: 2px solid transparent;
            border-radius: 15px;
            padding: 12px 12px 12px 50px;
            width: 100%;
            font-size: 14px;
            color: var(--text-dark);
        }

        .btn-submit {
            background: #0d1b1b;
            color: white;
            border: none;
            border-radius: 20px;
            padding: 14px;
            width: 100%;
            font-weight: 700;
            font-size: 13px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 10px;
            text-transform: uppercase;
        }

        .footer-text {
            margin-top: 25px;
            font-size: 10px;
            color: rgba(255,255,255,0.2);
            font-weight: 600;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="logo-container">
        <img src="https://i.postimg.cc/kVS9X4gM/download.png" alt="Logo SMK NU" class="logo-circle">
    </div>

    <div class="header-title">
        <h1>DIGITAL SYSTEM <span>ABSENSI</span></h1>
        <div class="badge-school">
            <i class="fas fa-check-shield me-2"></i> SMK NU LAMONGAN
        </div>
    </div>

    <div class="login-card">
        <h2>Login</h2>
        <p class="subtitle">Masuk ke Panel Kontrol Utama.</p>

        <?php if ($error_msg !== ""): ?>
            <div class="alert alert-danger py-2 px-3 mb-3" style="border-radius: 10px; font-size: 12px;">
                 <?= $error_msg ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST">
            <label class="label-text">Username</label>
            <div class="input-group-custom">
                <i class="far fa-user"></i>
                <input type="text" name="username" class="form-control-custom" placeholder="Username" required autocomplete="off">
            </div>
            
            <label class="label-text">Password</label>
            <div class="input-group-custom">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" class="form-control-custom" placeholder="••••••••" required>
            </div>
            
            <button type="submit" name="login" class="btn-submit">
                MASUK KE SYSTEM <i class="fas fa-arrow-right"></i>
            </button>
        </form>
    </div>

    <div class="footer-text">
        COPYRIGHT © 2026 SMK NU LAMONGAN
    </div>
</div>

</body>
</html>