<?php
// views/dashboard.php
// Header include (Keluar dari folder views, masuk ke partials)
include 'partials/header.php'; 

// Ambil total data
$total_siswa = $conn->query("SELECT COUNT(*) FROM siswa")->fetch_row()[0];
$total_guru = $conn->query("SELECT COUNT(*) FROM guru")->fetch_row()[0];
$total_absensi = $conn->query("SELECT COUNT(*) FROM absensi WHERE tanggal = CURDATE()")->fetch_row()[0];

// Ambil data absensi hari ini (terlambat vs tepat waktu)
$data_absensi_hari_ini = $conn->query("SELECT status_masuk, COUNT(*) as count FROM absensi WHERE tanggal = CURDATE() GROUP BY status_masuk");
$statistik_absensi = [
    'Tepat Waktu' => 0,
    'Terlambat' => 0
];
while ($row = $data_absensi_hari_ini->fetch_assoc()) {
    $statistik_absensi[$row['status_masuk']] = $row['count'];
}

?>

<div class="card">
    <h2>📊 Dashboard Admin</h2>
    <p>Selamat datang, <?php echo $_SESSION['username']; ?>. Ini adalah ringkasan sistem absensi Anda.</p>
</div>

<div style="display: flex; gap: 20px;">
    <div class="card" style="flex: 1;">
        <h2>📝 Data Master</h2>
        <div style="display: flex; justify-content: space-around; text-align: center;">
            <div>
                <h3>Siswa</h3>
                <p style="font-size: 2em; color: var(--color-primary);"><?php echo $total_siswa; ?></p>
                <a href="data_user.php?role=siswa" class="btn-primary">Lihat Detail</a>
            </div>
            <div>
                <h3>Guru</h3>
                <p style="font-size: 2em; color: var(--color-success);"><?php echo $total_guru; ?></p>
                <a href="data_user.php?role=pegawai" class="btn-primary">Lihat Detail</a>
            </div>
        </div>
    </div>

    <div class="card" style="flex: 1;">
        <h2>⏰ Absensi Hari Ini (<?php echo date('d-m-Y'); ?>)</h2>
        <div style="display: flex; justify-content: space-around; text-align: center;">
            <div>
                <h3>Total Absen</h3>
                <p style="font-size: 2em; color: var(--color-secondary);"><?php echo $total_absensi; ?></p>
            </div>
            <div>
                <h3>Tepat Waktu</h3>
                <p style="font-size: 2em; color: var(--color-success);"><?php echo $statistik_absensi['Tepat Waktu']; ?></p>
            </div>
            <div>
                <h3>Terlambat</h3>
                <p style="font-size: 2em; color: var(--color-danger);"><?php echo $statistik_absensi['Terlambat']; ?></p>
            </div>
        </div>
        <div style="text-align: center; margin-top: 20px;">
            <a href="data_absensi.php" class="btn-primary">Lihat Semua Absensi Hari Ini</a>
        </div>
    </div>
</div>

<?php
// Footer include
include 'partials/footer.php';
?>
