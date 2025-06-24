<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include '../../config/database.php';

$tahun_lkpd_err = $id_kode_catatan_err = $jumlah_anggaran_err = $jumlah_realisasi_err = "";
$id = $tahun_lkpd = $id_kode_catatan = $jumlah_anggaran = $jumlah_realisasi = "";

$success_message = "";
$general_error_message = "";

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);
} elseif (isset($_POST["id"]) && !empty(trim($_POST["id"]))) {
    $id = trim($_POST["id"]);
} else {
    header("location: ../../index.php?error=no_id_found");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["tahun_lkpd"]))) {
        $tahun_lkpd_err = "<p class='error-message'>Tahun LKPD tidak boleh kosong.</p>";
    } else {
        $tahun_lkpd = trim($_POST["tahun_lkpd"]);
    }

    if (trim($_POST["id_kode_catatan"]) === "") { 
        $id_kode_catatan_err = "<p class='error-message'>Pilihan kode catatan tidak boleh kosong.</p>";
    } elseif (!is_numeric(trim($_POST["id_kode_catatan"]))) {
        $id_kode_catatan_err = "<p class='error-message'>Pilihan kode catatan tidak valid.</p>";
    } else {
        $id_kode_catatan = (int)trim($_POST["id_kode_catatan"]);
    }

    if (trim($_POST["jumlah_anggaran"]) === "") {
        $jumlah_anggaran_err = "<p class='error-message'>Jumlah anggaran tidak boleh kosong.</p>";
    } elseif (!is_numeric(trim($_POST["jumlah_anggaran"]))) {
        $jumlah_anggaran_err = "<p class='error-message'>Jumlah anggaran harus berupa angka.</p>";
    } else {
        $jumlah_anggaran = (float)trim($_POST["jumlah_anggaran"]);
    }

    if (trim($_POST["jumlah_realisasi"]) === "") {
        $jumlah_realisasi_err = "<p class='error-message'>Jumlah realisasi tidak boleh kosong.</p>";
    } elseif (!is_numeric(trim($_POST["jumlah_realisasi"]))) {
        $jumlah_realisasi_err = "<p class='error-message'>Jumlah realisasi harus berupa angka.</p>";
    } else {
        $jumlah_realisasi = (float)trim($_POST["jumlah_realisasi"]);
    }

    if (empty($tahun_lkpd_err) && empty($id_kode_catatan_err) && empty($jumlah_anggaran_err) && empty($jumlah_realisasi_err)) {
        $sql = "UPDATE lkpd_apbd_lampiran_1
                SET tahun_lkpd = ?, id_kode_catatan = ?, jumlah_anggaran = ?, jumlah_realisasi = ?
                WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("siddi", $param_tahun_lkpd, $param_id_kode_catatan, $param_jumlah_anggaran, $param_jumlah_realisasi, $id);

            $param_tahun_lkpd = $tahun_lkpd;
            $param_id_kode_catatan = $id_kode_catatan;
            $param_jumlah_anggaran = $jumlah_anggaran;
            $param_jumlah_realisasi = $jumlah_realisasi;

            if ($stmt->execute()) {
                $success_message = "Data berhasil diupdate!";
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
        if (empty($general_error_message)) {
             $general_error_message = "Validasi gagal. Mohon periksa kembali input Anda.";
        }
        // Retain values if validation fails for the form
        $tahun_lkpd = isset($_POST["tahun_lkpd"]) ? trim($_POST["tahun_lkpd"]) : '';
        $id_kode_catatan = isset($_POST["id_kode_catatan"]) ? (int)trim($_POST["id_kode_catatan"]) : '';
        $jumlah_anggaran = isset($_POST["jumlah_anggaran"]) ? (float)trim($_POST["jumlah_anggaran"]) : '';
        $jumlah_realisasi = isset($_POST["jumlah_realisasi"]) ? (float)trim($_POST["jumlah_realisasi"]) : '';
    }
}

// Fetch `kode_catatan` for the dropdown (this part is correct for the dropdown)
$kode_catatan_options = [];
$sql_kode_catatan = "SELECT id, kode_catatan, uraian, id_kode_level 
                     FROM kode_catatan
                     WHERE id_kode_level = 3
                     ORDER BY kode_catatan ASC";
$result_kode_catatan = $conn->query($sql_kode_catatan);

if ($result_kode_catatan) {
    while ($row = $result_kode_catatan->fetch_assoc()) {
        $kode_catatan_options[] = $row;
    }
} else {
    error_log("Error fetching kode_catatan options: " . $conn->error);
    $general_error_message = "Error: Gagal memuat pilihan Kode Catatan: " . $conn->error;
}

if ($_SERVER["REQUEST_METHOD"] == "GET" || (!empty($general_error_message) && !empty($id))) {
    $sql_fetch = "SELECT tahun_lkpd, id_kode_catatan, jumlah_anggaran, jumlah_realisasi
                  FROM lkpd_apbd_lampiran_1
                  WHERE id = ?";
    if ($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("i", $param_id_fetch);
        $param_id_fetch = $id;

        if ($stmt_fetch->execute()) {
            $result_fetch = $stmt_fetch->get_result();
            if ($result_fetch->num_rows == 1) {
                $row_fetch = $result_fetch->fetch_assoc();
                if ($_SERVER["REQUEST_METHOD"] == "GET" || empty($tahun_lkpd)) {
                    $tahun_lkpd = $row_fetch["tahun_lkpd"];
                    $id_kode_catatan = $row_fetch["id_kode_catatan"];
                    $jumlah_anggaran = $row_fetch["jumlah_anggaran"];
                    $jumlah_realisasi = $row_fetch["jumlah_realisasi"];
                }
            } else {
                header("location: ../../index.php?error=data_not_found_on_reget");
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

include '../../templates/header.php';
?>

<h2>Edit Data LKPD APBD Lampiran 1</h2>

<?php
if (!empty($success_message)): ?>
    <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $success_message; ?>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = 'read.php';
        }, 3000);
    </script>
<?php
elseif (!empty($general_error_message)): ?>
    <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $general_error_message; ?>
    </div>
<?php endif; ?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($id); ?>">
    <div>
        <label>Tahun LKPD</label>
        <input type="text" name="tahun_lkpd" value="<?php echo htmlspecialchars($tahun_lkpd); ?>">
        <span class="error"><?php echo $tahun_lkpd_err; ?></span>
    </div>
    <div>
        <label>Kode Catatan</label>
        <select name="id_kode_catatan">
            <option value="">-- Pilih Kode Catatan --</option>
            <?php foreach ($kode_catatan_options as $option): ?>
                <option value="<?php echo htmlspecialchars($option['id']); ?>"
                    <?php echo ($id_kode_catatan == $option['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($option['kode_catatan'] . ' - ' . $option['uraian']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span class="error"><?php echo $id_kode_catatan_err; ?></span>
    </div>
    <div>
        <label>Jumlah Anggaran</label>
        <input type="text" name="jumlah_anggaran" value="<?php echo htmlspecialchars($jumlah_anggaran); ?>">
        <span class="error"><?php echo $jumlah_anggaran_err; ?></span>
    </div>
    <div>
        <label>Jumlah Realisasi</label>
        <input type="text" name="jumlah_realisasi" value="<?php echo htmlspecialchars($jumlah_realisasi); ?>">
        <span class="error"><?php echo $jumlah_realisasi_err; ?></span>
    </div>
    <div>
        <button type="submit">Update</button>
        <a href="read.php" class="back-link">Batal</a>
    </div>
</form>

<?php
$conn->close();
include '../../templates/footer.php';
?>