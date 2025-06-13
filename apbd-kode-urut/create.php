<?php
// Mulai session di awal file (masih diperlukan jika ada logika session lain,
// tapi untuk pesan sukses di halaman ini tidak lagi utama)
session_start();

// Sertakan file koneksi database
include '../config/database.php';
// Sertakan header HTML
include '../templates/header.php';

// Inisialisasi variabel untuk menampung pesan error
$nourut_err = $uraian_err = $idkodelevel_err = "";
// Inisialisasi variabel untuk menampung nilai dari form
$nourut = $uraian = $idkodelevel = "";

// Inisialisasi variabel untuk pesan sukses
$success_message = "";

// --- Ambil data Level dari database untuk combobox ---
$levels = []; // Array untuk menyimpan data level
$sql_levels = "SELECT id, nama_level, akronim FROM kode_level ORDER BY id ASC";
$result_levels = $conn->query($sql_levels);

if ($result_levels) {
    while ($row = $result_levels->fetch_assoc()) {
        $levels[] = $row;
    }
} else {
    echo "Gagal mengambil data level: " . $conn->error;
}
// --- Akhir pengambilan data Level ---

// Periksa apakah request adalah POST (saat form disubmit)
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validasi uraian
    if (empty(trim($_POST["uraian"]))) {
        $uraian_err = "Uraian tidak boleh kosong.";
    } else {
        $uraian = trim($_POST["uraian"]);
    }

    // Validasi Nomor Urut
    if (empty(trim($_POST["no_urut"]))) {
        $nourut_err = "Nomor Urut tidak boleh kosong.";
    } else {
        // Cek apakah Nomor Urut sudah ada
        $sql = "SELECT id FROM kode_urut WHERE no_urut = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_nourut_check);
            $param_nourut_check = trim($_POST["no_urut"]);

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $nourut_err = "Nomor Urut ini sudah terdaftar.";
                } else {
                    $nourut = trim($_POST["no_urut"]);
                }
            } else {
                echo "Ada yang salah saat memeriksa Nomor Urut. Silakan coba lagi nanti.";
            }
            $stmt->close();
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


    // --- Proses Penyimpanan Data ---
    // Jika tidak ada error validasi, masukkan data ke database
    if (empty($nourut_err) && empty($uraian_err) && empty($idkodelevel_err)) {
        // Query INSERT ke tabel kode_urut
        $sql = "INSERT INTO kode_urut (no_urut, uraian, id_kode_level) VALUES (?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            // Bind parameter ke statement yang disiapkan
            $stmt->bind_param("ssi", $param_nourut, $param_uraian, $param_idkodelevel);

            // Set parameter
            $param_nourut = $nourut;
            $param_uraian = $uraian;
            $param_idkodelevel = $idkodelevel;

            // Jalankan statement
            if ($stmt->execute()) {
                // Set pesan sukses
                $success_message = "Data berhasil disimpan!";
                // Kosongkan form setelah sukses
                $nourut = $uraian = $idkodelevel = "";
                // Namun, untuk redirect otomatis, mengosongkan form tidak terlalu relevan
                // karena halaman akan segera dialihkan.
            } else {
                // Pesan error jika eksekusi statement gagal saat menyimpan
                echo "Ada yang salah saat menyimpan data. Silakan coba lagi nanti: ". $stmt->error;
            }
            // Tutup statement
            $stmt->close();
        }
    }
}
?>

<h2>Tambah Data</h2>

<?php
// Tampilkan pesan sukses jika ada
if (!empty($success_message)): ?>
    <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $success_message; ?>
    </div>
    <script>
        // JavaScript untuk redirect setelah 10 detik
        setTimeout(function() {
            window.location.href = '../index.php';
        }, 2000); // 1000 milidetik = 1 detik
    </script>
<?php endif; ?>

<!-- Form untuk menambah data kode_urut -->
<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div>
        <label>Nomor Urut</label>
        <input type="text" name="no_urut" value="<?php echo htmlspecialchars($nourut); ?>">
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
        <button type="submit">Simpan</button>
        <a href="index.php" class="back-link">Batal</a>
    </div>
</form>

<?php
// Tutup koneksi database
$conn->close();
// Sertakan footer HTML
include '../templates/footer.php';
?>