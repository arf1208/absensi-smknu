<?php
// config/db_connect.php
$servername = "localhost";
$username = "root"; // Laragon default
$password = ""; // Laragon default
$dbname = "db_absensi_smknu";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
// echo "Koneksi berhasil"; // Bisa dihapus setelah dipastikan berhasil
?>
