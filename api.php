<?php
// api.php - Endpoint untuk komunikasi ESP32
header('Content-Type: application/json');

// SOLUSI FATAL ERROR PATH: Menggunakan __DIR__
include __DIR__ . '/config/db_connect.php'; 
include __DIR__ . '/function/whatsapp.php';

$response = array();

// --- Cek Hari Libur ---
function isHoliday($conn) {
    $today = date('Y-m-d');
    $sql = "SELECT keterangan FROM hari_libur WHERE tanggal = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// --- Logika Utama ---
if (isset($_GET['rfid_uid']) && !empty($_GET['rfid_uid'])) {
    $rfid_uid = strtoupper(trim($_GET['rfid_uid']));
    $current_time = date('H:i:s');
    $current_date = date('Y-m-d');

    // 1. Cek Hari Libur
    if (isHoliday($conn)) {
        $response = array("status" => "SYSTEM_HOLIDAY", "message" => "Hari libur, absensi dinonaktifkan.");
        echo json_encode($response);
        exit();
    }

    // 2. Cari UID di tabel siswa/pegawai
    $user_data = null;
    $user_type = null;
    $table_name = '';
    
    // Cek di tabel siswa
    $stmt_s = $conn->prepare("SELECT id, nama, jam_masuk_standar, no_wali_murid AS kontak FROM siswa WHERE rfid_uid = ?");
    $stmt_s->bind_param("s", $rfid_uid);
    $stmt_s->execute();
    $result_s = $stmt_s->get_result();
    
    if ($result_s->num_rows == 1) {
        $user_data = $result_s->fetch_assoc();
        $user_type = 'siswa';
        $table_name = 'siswa';
    } else {
        // Cek di tabel pegawai
        $stmt_p = $conn->prepare("SELECT id, nama, jam_masuk_standar, no_hp AS kontak FROM pegawai WHERE rfid_uid = ?");
        $stmt_p->bind_param("s", $rfid_uid);
        $stmt_p->execute();
        $result_p = $stmt_p->get_result();

        if ($result_p->num_rows == 1) {
            $user_data = $result_p->fetch_assoc();
            $user_type = 'pegawai';
            $table_name = 'pegawai';
        }
    }

    // 3. Proses Absensi
    if ($user_data) {
        $user_id = $user_data['id'];
        $nama = $user_data['nama'];
        $jam_standar = $user_data['jam_masuk_standar'];
        $kontak = $user_data['kontak'];

        // Cek status hari ini
        $sql_check = "SELECT id, jam_masuk, jam_pulang, status_masuk FROM absensi WHERE tanggal = ? AND user_id = ? AND user_type = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("sis", $current_date, $user_id, $user_type);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();

        if ($result_check->num_rows == 0) {
            // A. Belum Absen Masuk -> Lakukan Absen Masuk
            $status_masuk = ($current_time > $jam_standar) ? 'Terlambat' : 'Tepat Waktu';
            
            $sql_insert = "INSERT INTO absensi (user_id, user_type, tanggal, jam_masuk, status_masuk) VALUES (?, ?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("issss", $user_id, $user_type, $current_date, $current_time, $status_masuk);

            if ($stmt_insert->execute()) {
                $response = array(
                    "status" => "SUCCESS",
                    "action" => "MASUK",
                    "nama" => $nama,
                    "jam" => date('H:i'),
                    "status_masuk" => $status_masuk
                );
                
                // Kirim notifikasi WA (jika function terdefinisi)
                if (function_exists('sendNotification')) {
                    sendNotification($kontak, $nama, "MASUK", $current_date, $current_time, $status_masuk);
                }
            } else {
                $response = array("status" => "DB_ERROR", "message" => "Gagal menyimpan data masuk.");
            }
        
        } else {
            $row_check = $result_check->fetch_assoc();
            
            // B. Sudah Absen Masuk, Cek Absen Pulang
            if (!empty($row_check['jam_masuk']) && empty($row_check['jam_pulang'])) {
                // Lakukan Absen Pulang
                $absensi_id = $row_check['id'];
                $sql_update = "UPDATE absensi SET jam_pulang = ? WHERE id = ?";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bind_param("si", $current_time, $absensi_id);

                if ($stmt_update->execute()) {
                    $response = array(
                        "status" => "SUCCESS",
                        "action" => "PULANG",
                        "nama" => $nama,
                        "jam" => date('H:i')
                    );
                    
                    // Kirim notifikasi WA
                    if (function_exists('sendNotification')) {
                        sendNotification($kontak, $nama, "PULANG", $current_date, $current_time);
                    }
                } else {
                    $response = array("status" => "DB_ERROR", "message" => "Gagal menyimpan data pulang.");
                }

            } else {
                // C. Sudah Absen Pulang
                $response = array("status" => "ALREADY_OUT", "nama" => $nama, "message" => "Anda sudah absen pulang hari ini.");
            }
        }
    } else {
        // 4. Kartu Invalid
        $sql_invalid = "INSERT INTO invalid_cards (rfid_uid, tanggal, jam) VALUES (?, ?, ?)";
        $stmt_invalid = $conn->prepare($sql_invalid);
        $stmt_invalid->bind_param("sss", $rfid_uid, $current_date, $current_time);
        $stmt_invalid->execute();

        $response = array("status" => "INVALID", "message" => "Kartu tidak terdaftar.");
    }

} else {
    $response = array("status" => "ERROR", "message" => "UID RFID tidak ditemukan.");
}

echo json_encode($response);
?>
