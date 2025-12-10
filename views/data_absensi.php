<?php
// views/data_absensi.php
include 'partials/header.php';

// Filter
$filter_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$filter_role = isset($_GET['role']) && in_array($_GET['role'], ['siswa', 'guru']) ? $_GET['role'] : '';

// Query Absensi
$sql = "SELECT 
            a.tanggal, 
            a.jam_masuk, 
            a.jam_pulang, 
            a.status_masuk,
            a.user_type,
            CASE 
                WHEN a.user_type = 'siswa' THEN s.nama 
                WHEN a.user_type = 'guru' THEN p.nama
            END as nama
        FROM absensi a
        LEFT JOIN siswa s ON a.user_id = s.id AND a.user_type = 'siswa'
        LEFT JOIN guru p ON a.user_id = p.id AND a.user_type = 'guru'
        WHERE a.tanggal = ? ";

if ($filter_role) {
    $sql .= " AND a.user_type = ?";
}

$sql .= " ORDER BY a.jam_masuk DESC";

$stmt = $conn->prepare($sql);

if ($filter_role) {
    $stmt->bind_param("ss", $filter_date, $filter_role);
} else {
    $stmt->bind_param("s", $filter_date);
}

$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<div class="card">
    <h2>📋 Data Absensi</h2>
    
    <div style="margin-bottom: 20px;">
        <form method="GET" action="data_absensi.php" style="display: flex; gap: 10px; align-items: center;">
            <label for="date">Tanggal:</label>
            <input type="date" id="date" name="date" value="<?php echo $filter_date; ?>" class="form-control">

            <label for="role">Filter:</label>
            <select id="role" name="role" class="form-control">
                <option value="">Semua</option>
                <option value="siswa" <?php echo ($filter_role == 'siswa' ? 'selected' : ''); ?>>Siswa</option>
                <option value="guru" <?php echo ($filter_role == 'guru' ? 'selected' : ''); ?>>Guru</option>
            </select>
            
            <button type="submit" class="btn-primary">Filter</button>
            <a href="data_absensi.php" class="btn-primary" style="background-color: var(--color-secondary);">Reset</a>
        </form>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Nama</th>
                <th>Tipe</th>
                <th>Jam Masuk</th>
                <th>Status Masuk</th>
                <th>Jam Pulang</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                <td><?php echo ucfirst($row['user_type']); ?></td>
                <td><?php echo htmlspecialchars($row['jam_masuk']); ?></td>
                <td style="color: <?php echo $row['status_masuk'] == 'Terlambat' ? 'var(--color-danger)' : 'var(--color-success)'; ?>; font-weight: bold;">
                    <?php echo htmlspecialchars($row['status_masuk']); ?>
                </td>
                <td><?php echo $row['jam_pulang'] ? htmlspecialchars($row['jam_pulang']) : '-'; ?></td>
            </tr>
            <?php endwhile; ?>
            <?php if ($result->num_rows == 0): ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Tidak ada data absensi untuk tanggal ini.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
include 'partials/footer.php';
?> 
