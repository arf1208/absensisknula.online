<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi | SMK NU Lamongan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --nu-bright: #2ecc71; 
            --nu-sidebar: #0a4d3c; /* Hijau nyambung dengan tema */
            --nu-bg: #f8fafc;
        }
        body { background-color: var(--nu-bg); font-family: 'Poppins', sans-serif; overflow-x: hidden; }
        
        /* Sidebar Hijau Emerald */
        #sidebar { 
            width: 280px; height: 100vh; background: var(--nu-sidebar); 
            position: fixed; transition: all 0.3s; z-index: 1000;
            box-shadow: 4px 0 15px rgba(0,0,0,0.1);
        }
        #sidebar.active { margin-left: -280px; }
        
        .sidebar-header { padding: 40px 20px; text-align: center; }
        .sidebar-header img { width: 80px; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2)); }

        .nav-link { 
            color: rgba(255,255,255,0.7); padding: 14px 25px; margin: 5px 15px;
            border-radius: 12px; font-size: 14px; transition: 0.3s;
        }
        .nav-link:hover, .nav-link.active { 
            background: rgba(255,255,255,0.15); color: white; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .menu-label { color: rgba(255,255,255,0.4); font-size: 11px; text-transform: uppercase; margin: 25px 30px 10px; font-weight: 600; }

        #content { width: calc(100% - 280px); margin-left: 280px; transition: all 0.3s; padding: 40px; }
        #content.active { width: 100%; margin-left: 0; }
        
        .btn-toggle { background: white; border: none; width: 45px; height: 45px; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
