<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include '../config/database.php';

$tahun_apbd_err = $id_kode_urut_err = $id_jenis_apbd_err = $jumlah_anggaran_err = "";
$tahun_apbd = $id_kode_urut = $id_jenis_apbd = $jumlah_anggaran = "";

$success_message = "";
$general_error_message = "";

// Check if 'id' is set in GET or POST request
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);
} elseif (isset($_POST["id"]) && !empty(trim($_POST["id"]))) {
    $id = trim($_POST["id"]);
} else {
    header("location: ../index.php?error=no_id_found");
    exit();
}

// Initialize variables for form fields
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //VALIDASI INPUT TAHUN APBD
    if (empty(trim($_POST["tahun_apbd"]))) {
        $tahun_apbd_err = "<p class='error-message'>Tahun APBD tidak boleh kosong.</p>";
    } else {
        $tahun_apbd = trim($_POST["tahun_apbd"]);
    }

    // Validasi input kode urut
    if (trim($_POST["id_kode_urut"]) === "") { 
        $id_kode_urut_err = "<p class='error-message'>Pilihan kode urut tidak boleh kosong.</p>";
    } elseif (!is_numeric(trim($_POST["id_kode_urut"]))) {
        $id_kode_urut_err = "<p class='error-message'>Pilihan kode urut tidak valid.</p>";
    } else {
        $id_kode_urut = (int)trim($_POST["id_kode_urut"]);
    }

    // Validasi input Jumlah Anggaran
    if (trim($_POST["jumlah_anggaran"]) === "") {
        $jumlah_anggaran_err = "<p class='error-message'>Jumlah anggaran tidak boleh kosong.</p>";
    } elseif (!is_numeric(trim($_POST["jumlah_anggaran"]))) {
        $jumlah_anggaran_err = "<p class='error-message'>Jumlah anggaran harus berupa angka.</p>";
    } else {
        $jumlah_anggaran = (float)trim($_POST["jumlah_anggaran"]);
    }

    // Validasi input kode jenis
    if (trim($_POST["id_jenis_apbd"]) === "") { 
        $id_jenis_apbd_err = "<p class='error-message'>Pilihan Jenis APBD tidak boleh kosong.</p>";
    } elseif (!is_numeric(trim($_POST["id_jenis_apbd"]))) {
        $id_jenis_apbd_err = "<p class='error-message'>Pilihan kode jenis tidak valid.</p>";
    } else {
        $id_jenis_apbd = (int)trim($_POST["id_jenis_apbd"]);
    }

    if (empty($tahun_apbd_err) && empty($id_kode_urut_err) && empty($jumlah_anggaran_err) && empty($id_jenis_apbd_err)) {
        $sql = "UPDATE apbd_lampiran_1
                SET tahun = ?, id_kode_urut = ?, jumlah = ?, id_jenis_apbd = ?
                WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sidii", $param_tahun_apbd, $param_id_kode_urut, $param_jumlah_anggaran, $param_id_jenis_apbd, $id);

            $param_tahun_apbd = $tahun_apbd;
            $param_id_kode_urut = $id_kode_urut;
            $param_jumlah_anggaran = $jumlah_anggaran;
            $param_id_jenis_apbd = $id_jenis_apbd;

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
        $tahun_apbd = isset($_POST["tahun_apbd"]) ? trim($_POST["tahun_apbd"]) : '';
        $id_kode_urut = isset($_POST["id_kode_urut"]) ? (int)trim($_POST["id_kode_urut"]) : '';
        $jumlah_anggaran = isset($_POST["jumlah_anggaran"]) ? (float)trim($_POST["jumlah_anggaran"]) : '';
        $id_jenis_apbd = isset($_POST["id_jenis_apbd"]) ? (int)trim($_POST["id_jenis_apbd"]) : '';
    }
}

// Fetch `kode_catatan` for the dropdown (this part is correct for the dropdown)
$kode_urut_options = [];
$sql_kode_urut = "SELECT * FROM kode_urut WHERE id_kode_level = 3 ORDER BY no_urut ASC";
$result_kode_urut = $conn->query($sql_kode_urut);

if ($result_kode_urut) {
    while ($row = $result_kode_urut->fetch_assoc()) {
        $kode_urut_options[] = $row;
    }
} else {
    error_log("Error fetching kode_catatan options: " . $conn->error);
    $general_error_message = "Error: Gagal memuat pilihan Kode Catatan: " . $conn->error;
}

// Fetch `kode_jenis apbd` for the dropdown (this part is correct for the dropdown)
$id_jenis_apbd_options = [];
$sql_id_jenis_apbd = "SELECT * FROM jenis_apbd ORDER BY id ASC";
$result_id_jenis_apbd = $conn->query($sql_id_jenis_apbd);

if ($result_id_jenis_apbd) {
    while ($row = $result_id_jenis_apbd->fetch_assoc()) {
        $id_jenis_apbd_options[] = $row;
    }
} else {
    error_log("Error fetching kode_catatan options: " . $conn->error);
    $general_error_message = "Error: Gagal memuat pilihan Kode Catatan: " . $conn->error;
}


if ($_SERVER["REQUEST_METHOD"] == "GET" || (!empty($general_error_message) && !empty($id))) {
    
    $sql_fetch = "SELECT * FROM apbd_lampiran_1 WHERE id = ?";
    if ($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("i", $param_id_fetch);
        $param_id_fetch = $id;

        if ($stmt_fetch->execute()) {
            $result_fetch = $stmt_fetch->get_result();
            if ($result_fetch->num_rows == 1) {
                $row_fetch = $result_fetch->fetch_assoc();
                if ($_SERVER["REQUEST_METHOD"] == "GET" || empty($tahun_lkpd)) {
                    $tahun_apbd = $row_fetch["tahun"];
                    $id_kode_urut = $row_fetch["id_kode_urut"];
                    $jumlah_anggaran = $row_fetch["jumlah"];
                    $id_jenis_apbd = $row_fetch["id_jenis_apbd"];
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

include '../templates/header.php';
?>

<h2>Edit Data APBD Lampiran 1</h2>

<?php
if (!empty($success_message)): ?>
    <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $success_message; ?>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = 'read.php';
        }, 1000);
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
        <label>Tahun APBD</label>
        <input type="text" name="tahun_apbd" value="<?php echo htmlspecialchars($tahun_apbd); ?>">
        <span class="error"><?php echo $tahun_apbd_err; ?></span>
    </div>
    <div>
        <label>Kode Akun</label>
        <select name="id_kode_urut">
            <option value="">-- Pilih Akun --</option>
            <?php foreach ($kode_urut_options as $option): ?>
                <option value="<?php echo htmlspecialchars($option['id']); ?>"
                    <?php echo ($id_kode_urut == $option['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($option['no_urut'] . ' - ' . $option['uraian']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span class="error"><?php echo $id_kode_urut_err; ?></span>
    </div>
    <div>
        <label>Jumlah Anggaran</label>
        <input type="text" name="jumlah_anggaran" value="<?php echo htmlspecialchars($jumlah_anggaran); ?>">
        <span class="error"><?php echo $jumlah_anggaran_err; ?></span>
    </div>
    <div>
        <label>Jenis APBD</label>
        <select name="id_jenis_apbd">
            <option value="">-- Pilih Jenis APBD --</option>
            <?php foreach ($id_jenis_apbd_options as $options): ?>
                <option value="<?php echo htmlspecialchars($options['id']); ?>"
                    <?php echo ($id_jenis_apbd == $options['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($options['uraian']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <span class="error"><?php echo $id_jenis_apbd_err; ?></span>
    </div>
    <div>
        <button type="submit">Update</button>
        <a href="read.php" class="back-link">Batal</a>
    </div>
</form>

<?php
$conn->close();
include '../templates/footer.php';
?>