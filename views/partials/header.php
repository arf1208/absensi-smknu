<?php
// partials/header.php
session_start();
// SOLUSI FATAL ERROR: Koneksi database sekarang berada dua tingkat di atas
include '../config/db_connect.php'; 

// Cek status login
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Absensi RFID</title>
    <link rel="stylesheet" href="../assets/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Absensi RFID SMK NU</h1>
            <nav>
                <a href="dashboard.php" class="nav-link">Dashboard</a>
                <div class="dropdown">
                    <button class="nav-link dropbtn">Data Master</button>
                    <div class="dropdown-content">
                        <a href="data_user.php?role=siswa">Data Siswa</a>
                        <a href="data_user.php?role=guru">Data Guru</a>
                        <a href="hari_libur.php">Hari Libur</a>
                    </div>
                </div>
                <a href="data_absensi.php" class="nav-link">Data Absensi</a>
                <a href="../logout.php" class="nav-link logout-btn" onclick="return confirm('Apakah Anda yakin ingin keluar?');">Logout (<?php echo $_SESSION['username']; ?>)</a>
            </nav>
        </header>
        <main>
