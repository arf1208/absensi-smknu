<?php
// views/data_user.php
include 'partials/header.php';

$role = isset($_GET['role']) && in_array($_GET['role'], ['siswa', 'Guru']) ? $_GET['role'] : 'siswa';
$table = $role;
$title = ($role == 'siswa') ? 'Siswa' : 'Guru';

// Ambil data
$result = $conn->query("SELECT * FROM $table ORDER BY id DESC");

// Proses Hapus
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete'])) {
    $id = $_POST['id'];
    
    // Hapus data pengguna
    $stmt_user = $conn->prepare("DELETE FROM $table WHERE id = ?");
    $stmt_user->bind_param("i", $id);
    $stmt_user->execute();
    
    // Hapus data absensi terkait
    $stmt_absensi = $conn->prepare("DELETE FROM absensi WHERE user_id = ? AND user_type = ?");
    $stmt_absensi->bind_param("is", $id, $role);
    $stmt_absensi->execute();

    header("Location: data_user.php?role=$role&msg=deleted");
    exit();
}
?>

<div class="card">
    <h2>🧑‍🎓 Manajemen Data <?php echo $title; ?></h2>
    
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'success'): ?>
        <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
            Data berhasil disimpan/diperbarui.
        </div>
    <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 15px;">
            Data berhasil dihapus.
        </div>
    <?php endif; ?>

    <div style="margin-bottom: 20px;">
        <a href="form_user.php?role=<?php echo $role; ?>" class="btn-primary">Tambah <?php echo $title; ?> Baru</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>RFID UID</th>
                <th>Jam Masuk Standar</th>
                <th>Kontak <?php echo ($role == 'siswa') ? 'Wali' : 'HP'; ?></th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $no = 1; if ($result->num_rows > 0): while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $no++; ?></td>
                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                <td><?php echo htmlspecialchars($row['rfid_uid']); ?></td>
                <td><?php echo htmlspecialchars($row['jam_masuk_standar']); ?></td>
                <td><?php echo htmlspecialchars($row[($role == 'siswa' ? 'no_wali_murid' : 'no_hp')]); ?></td>
                <td>
                    <a href="form_user.php?role=<?php echo $role; ?>&id=<?php echo $row['id']; ?>" class="btn-primary" style="background-color: #ffc107; color: black; padding: 5px 10px;">Edit</a>
                    
                    <form method="POST" action="data_user.php?role=<?php echo $role; ?>" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus data <?php echo htmlspecialchars($row['nama']); ?>?');">
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <input type="hidden" name="delete" value="1">
                        <button type="submit" class="btn-danger" style="padding: 5px 10px;">Hapus</button>
                    </form>
                </td>
            </tr>
            <?php endwhile; else: ?>
            <tr>
                <td colspan="6" style="text-align: center;">Tidak ada data <?php echo $title; ?> yang terdaftar.</td>
            </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php
include 'partials/footer.php';
?>
