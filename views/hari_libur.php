<?php
// views/hari_libur.php
include 'partials/header.php';

$message = '';
$message_type = '';

// Proses Tambah/Hapus Hari Libur
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['add_libur'])) {
        $tanggal = $_POST['tanggal'];
        $keterangan = $_POST['keterangan'];

        $stmt = $conn->prepare("INSERT INTO hari_libur (tanggal, keterangan) VALUES (?, ?)");
        $stmt->bind_param("ss", $tanggal, $keterangan);
        if ($stmt->execute()) {
            $message = "Hari libur berhasil ditambahkan.";
            $message_type = 'success';
        } else {
            $message = "Gagal menambahkan hari libur. " . $conn->error;
            $message_type = 'danger';
        }
        $stmt->close();

    } elseif (isset($_POST['delete_libur'])) {
        $id = $_POST['libur_id'];
        
        $stmt = $conn->prepare("DELETE FROM hari_libur WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $message = "Hari libur berhasil dihapus.";
            $message_type = 'success';
        } else {
            $message = "Gagal menghapus hari libur. " . $conn->error;
            $message_type = 'danger';
        }
        $stmt->close();
    }
}

// Ambil data hari libur
$result = $conn->query("SELECT id, tanggal, keterangan FROM hari_libur ORDER BY tanggal DESC");
?>

<div class="card">
    <h2>📅 Manajemen Hari Libur</h2>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?>" style="padding: 10px; border-radius: 4px; margin-bottom: 15px; background-color: <?php echo $message_type == 'success' ? '#d4edda' : '#f8d7da'; ?>; color: <?php echo $message_type == 'success' ? '#155724' : '#721c24'; ?>;">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <div style="display: flex; gap: 30px;">
        <div style="flex: 1;">
            <h3>Tambah Hari Libur Baru</h3>
            <form method="POST" action="hari_libur.php" class="card" style="padding: 20px;">
                <input type="hidden" name="add_libur" value="1">
                <div class="form-group">
                    <label for="tanggal">Tanggal</label>
                    <input type="date" id="tanggal" name="tanggal" required>
                </div>
                <div class="form-group">
                    <label for="keterangan">Keterangan</label>
                    <input type="text" id="keterangan" name="keterangan" required>
                </div>
                <button type="submit" class="btn-primary">Simpan Hari Libur</button>
            </form>
        </div>

        <div style="flex: 2;">
            <h3>Daftar Hari Libur</h3>
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Keterangan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; if ($result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo date('d-m-Y', strtotime($row['tanggal'])); ?></td>
                        <td><?php echo htmlspecialchars($row['keterangan']); ?></td>
                        <td>
                            <form method="POST" action="hari_libur.php" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus libur ini?');">
                                <input type="hidden" name="libur_id" value="<?php echo $row['id']; ?>">
                                <input type="hidden" name="delete_libur" value="1">
                                <button type="submit" class="btn-danger">Hapus</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="4" style="text-align: center;">Tidak ada hari libur yang terdaftar.</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
include 'partials/footer.php';
?>
