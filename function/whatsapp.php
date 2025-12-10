<?php
// function/whatsapp.php - Fungsi Dummy Notifikasi WhatsApp (Harus diintegrasikan dengan API pihak ketiga seperti WA Gateway)

function sendNotification($number, $name, $action, $date, $time, $status_masuk = null) {
    // Nomor HP harus diawali 62 (contoh: 62812xxxxxx)
    // Cuplikan Logika Absen Masuk di api.php
// ...
if ($stmt_insert->execute()) {
    // Pesan otomatis dikirim DI SINI
    if (function_exists('sendNotification')) {
        sendNotification($kontak, $nama, "MASUK", $current_date, $current_time, $status_masuk); 
    }
}
// ...
    
    // Pesan default
    $message = "Assalamualaikum, Absensi $action berhasil dicatat.\n\n";
    $message .= "Nama: $name\n";
    $message .= "Tanggal: $date\n";
    $message .= "Waktu: $time\n";

    if ($action == 'MASUK' && $status_masuk) {
        $message .= "Status: $status_masuk\n";
    }

    $message .= "\nTerima kasih.";

    // --- INTEGRASI WA GATEWAY DI SINI ---
    // Di sini Anda harus menambahkan kode untuk mengirim pesan melalui API penyedia WA Gateway.
    // Contoh sederhana (tidak berfungsi tanpa API key):
    /*
    $api_url = "https://api.wagateway.com/send";
    $data = [
        'api_key' => 'YOUR_API_KEY', 
        'number' => $number,
        'message' => $message
    ];

    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => 'Content-type: application/x-www-form-urlencoded',
            'content' => http_build_query($data)
        ]
    ];
    $context  = stream_context_create($options);
    $result = file_get_contents($api_url, false, $context);
    
    if ($result === FALSE) { 
        error_log("Gagal mengirim WA melalui Gateway.");
        return false;
    }
    */

    error_log("Notifikasi WA (Dummy) dikirim ke $number untuk $name ($action)");
    return true;
}
?>                    "jam" => date('H:i')
                    );
                    
                    // Kirim notifikasi WA (jika function terdefinisi)
                    if (function_exists('sendNotification')) {
                        sendNotification($kontak, $nama, "PULANG", $current_date, $current_time);
                    }
                } else {
                    $response = array("status" => "DB_ERROR", "message" => "Gagal menyimpan data pulang.");
                }
            } else {
                // Sudah absen pulang
                $response = array("status" => "ALREADY_PULANG", "message" => "Anda sudah melakukan absen pulang hari ini.");
            }
        }

        $stmt_check->close();
    } else {
        $response = array("status" => "NOT_FOUND", "message" => "Data pengguna tidak ditemukan.");
    }
}
