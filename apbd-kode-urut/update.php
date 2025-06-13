<?php
// Aktifkan pelaporan error PHP untuk debugging (opsional di produksi, tapi bagus untuk final testing)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Mulai session di awal file
session_start();

// Sertakan file koneksi database. Pastikan TIDAK ADA spasi/baris kosong di awal file database.php
include '../config/database.php';

// Inisialisasi variabel untuk menampung pesan error
$nourut_err = $uraian_err = $idkodelevel_err = "";
// Inisialisasi variabel untuk menampung nilai dari form
$id = $nourut = $uraian = $idkodelevel = "";

// Inisialisasi variabel untuk pesan sukses atau pesan error umum
$success_message = "";
$general_error_message = ""; // Variabel baru untuk pesan error non-validasi


// --- PENTING: LOGIKA UNTUK MENENTUKAN ID YANG SEDANG DIEDIT ---
// ID dapat berasal dari GET (saat pertama kali membuka form)
// ATAU dari POST (saat form disubmit)
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);
} elseif (isset($_POST["id"]) && !empty(trim($_POST["id"]))) {
    $id = trim($_POST["id"]);
} else {
    // Jika ID tidak ada baik di GET maupun POST, redirect ke halaman utama
    header("location: ../index.php?error=no_id_found"); // Pesan error baru
    exit();
}


// --- Proses POST request saat form di-submit untuk update ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Data ID sudah diambil di blok di atas, jadi tidak perlu $_POST["id"] lagi di sini.

    // --- Validasi Uraian ---
    if (empty(trim($_POST["uraian"]))) {
        $uraian_err = "Uraian tidak boleh kosong.";
    } else {
        $uraian = trim($_POST["uraian"]);
    }

    // --- Validasi Nomor Urut ---
    if (empty(trim($_POST["nourut"]))) {
        $nourut_err = "Nomor Urut tidak boleh kosong.";
    } else {
        $sql = "SELECT id FROM kode_urut WHERE no_urut = ? AND id <> ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $param_nourut_check, $param_id_check);
            $param_nourut_check = trim($_POST["nourut"]);
            $param_id_check = $id; // Menggunakan ID yang sudah ditentukan di awal

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $nourut_err = "Nomor Urut ini sudah terdaftar untuk data lain.";
                } else {
                    $nourut = trim($_POST["nourut"]);
                }
            } else {
                error_log("Error executing no_urut check: " . $stmt->error);
                $general_error_message = "Error: Ada masalah saat memeriksa Nomor Urut: " . $stmt->error;
            }
            $stmt->close();
        } else {
            error_log("Error preparing no_urut check query: " . $conn->error);
            $general_error_message = "Error: Ada masalah saat menyiapkan query Nomor Urut: " . $conn->error;
        }
    }

    // --- Validasi ID Kode Level (dari combobox) ---
    if (empty(trim($_POST["id_kode_level"]))) {
        $idkodelevel_err = "Level tidak boleh kosong.";
    } elseif (!is_numeric(trim($_POST["id_kode_level"]))) {
        $idkodelevel_err = "Pilihan level tidak valid.";
    } else {
        $idkodelevel = (int)trim($_POST["id_kode_level"]);
    }

    // --- Proses Update Data ---
    // Jika tidak ada error validasi DAN tidak ada error umum yang fatal
    if (empty($nourut_err) && empty($uraian_err) && empty($idkodelevel_err) && empty($general_error_message)) {
        $sql = "UPDATE kode_urut SET no_urut = ?, uraian = ?, id_kode_level = ? WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssii", $param_nourut, $param_uraian, $param_idkodelevel, $param_id);

            $param_nourut = $nourut;
            $param_uraian = $uraian;
            $param_idkodelevel = $idkodelevel;
            $param_id = $id; // Menggunakan ID yang sudah ditentukan di awal

            if ($stmt->execute()) {
                $success_message = "Data berhasil diupdate!";
                // Redirect ke index.php setelah 10 detik
                // Karena kita akan me-redirect, tidak perlu mengisi ulang form
            } else {
                error_log("Error updating data: " . $stmt->error);
                $general_error_message = "Error: Gagal mengupdate data. Silakan cek log server atau pesan ini: " . $stmt->error;
            }
            $stmt->close();
        } else {
            error_log("Error preparing update query: " . $conn->error);
            $general_error_message = "Error: Ada masalah saat menyiapkan query update: " . $conn->error;
        }
    } else {
        // Jika ada error validasi, pastikan general_error_message tidak menimpa error validasi
        if (empty($general_error_message)) {
             $general_error_message = "Validasi gagal. Mohon periksa kembali input Anda.";
        }
        // Jika validasi gagal, kita perlu mengisi ulang variabel form
        // agar data yang dimasukkan pengguna tetap ada di form
        $nourut = isset($_POST["nourut"]) ? trim($_POST["nourut"]) : '';
        $uraian = isset($_POST["uraian"]) ? trim($_POST["uraian"]) : '';
        $idkodelevel = isset($_POST["id_kode_level"]) ? (int)trim($_POST["id_kode_level"]) : '';
    }
}


// --- Ambil data Level dari database untuk combobox (diulang untuk kasus validasi gagal) ---
// Ini perlu diulang karena jika POST gagal, kita tetap butuh data levels untuk combobox
$levels = [];
$sql_levels_reget = "SELECT id, nama_level FROM kode_level ORDER BY nama_level ASC";
$result_levels_reget = $conn->query($sql_levels_reget);

if ($result_levels_reget) {
    while ($row = $result_levels_reget->fetch_assoc()) {
        $levels[] = $row;
    }
}
// --- Akhir pengambilan data Level ---


// --- Ambil data kode_urut yang akan diedit dari database berdasarkan ID ---
// Ini hanya diperlukan jika ini adalah GET request awal, atau jika POST gagal
// dan kita perlu mengisi ulang form dengan data lama dari DB (selain dari POST)
// Pastikan $id sudah memiliki nilai dari GET atau POST
if ($_SERVER["REQUEST_METHOD"] == "GET" || (!empty($general_error_message) && !empty($id))) {
    $sql_fetch = "SELECT ku.id, ku.no_urut, ku.uraian, ku.id_kode_level
            FROM kode_urut ku
            WHERE ku.id = ?";
    if ($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("i", $param_id_fetch_reget);
        $param_id_fetch_reget = $id; // Menggunakan $id yang sudah ditentukan

        if ($stmt_fetch->execute()) {
            $result_fetch = $stmt_fetch->get_result();
            if ($result_fetch->num_rows == 1) {
                $row_fetch = $result_fetch->fetch_assoc();
                // Hanya isi ulang variabel form jika tidak ada data POST yang valid
                // atau jika ada error validasi dan kita perlu data asli dari DB
                if ($_SERVER["REQUEST_METHOD"] == "GET" || (!empty($general_error_message) && empty($nourut))) {
                    $nourut = $row_fetch["no_urut"];
                    $uraian = $row_fetch["uraian"];
                    $idkodelevel = $row_fetch["id_kode_level"];
                }
            } else {
                header("location: ../index.php?error=data_not_found_on_reget");
                exit();
            }
        } else {
            error_log("Error executing fetch query on reget: " . $stmt_fetch->error);
            $general_error_message = "Error: Masalah saat mengambil data asli untuk form: " . $stmt_fetch->error;
        }
        $stmt_fetch->close();
    } else {
        error_log("Error preparing fetch query on reget: " . $conn->error);
        $general_error_message = "Error: Masalah saat menyiapkan query data asli: " . $conn->error;
    }
}


// --- SERTAKAN HEADER HTML DI SINI, SETELAH SEMUA LOGIKA PEMROSESAN LENGKAP ---
include '../templates/header.php';
?>

<h2>Edit Data Kode Urut</h2>

<?php
// Tampilkan pesan sukses jika ada dan aktifkan redirect
if (!empty($success_message)): ?>
    <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $success_message; ?>
    </div>
    <script>
        // JavaScript untuk redirect setelah 10 detik
        setTimeout(function() {
            window.location.href = '../index.php';
        }, 3000); // 1000 milidetik = 1 detik
    </script>
<?php
// Tampilkan pesan error umum jika ada
elseif (!empty($general_error_message)): ?>
    <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $general_error_message; ?>
    </div>
<?php endif; ?>

<!-- Form untuk mengedit data kode_urut -->
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <!-- Hidden input untuk menyimpan ID yang sedang diedit -->
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
    <div>
        <label>Nomor Urut</label>
        <input type="text" name="nourut" value="<?php echo htmlspecialchars($nourut); ?>">
        <span class="error"><?php echo $nourut_err; ?></span>
    </div>
    <div>
        <label>Uraian</label>
        <input type="text" name="uraian" value="<?php echo htmlspecialchars($uraian); ?>">
        <span class="error"><?php echo $uraian_err; ?></span>
    </div>
    <div>
        <label>Nama Level</label>
        <select name="id_kode_level">
            <option value="">-- Pilih Level --</option>
            <?php foreach ($levels as $level): ?>
                <option value="<?php echo htmlspecialchars($level['id']); ?>"
                    <?php echo ($idkodelevel == $level['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($level['nama_level']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span class="error"><?php echo $idkodelevel_err; ?></span>
    </div>
    <div>
        <button type="submit">Update</button>
        <a href="../index.php" class="back-link">Batal</a>
    </div>
</form>

<?php
// Tutup koneksi database
$conn->close();
// Sertakan footer HTML
include '../iyatemplates/footer.php';
?>
