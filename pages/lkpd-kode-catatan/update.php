<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include '../../config/database.php';

$kode_catatan_err = $uraian_err = $idkodelevel_err = "";
$id = $kode_catatan = $uraian = $idkodelevel = "";

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
  
    if (empty(trim($_POST["uraian"]))) {
        $uraian_err = "Uraian tidak boleh kosong.";
    } else {
        $uraian = trim($_POST["uraian"]);
    }

    if (empty(trim($_POST["kode_catatan"]))) {
        $kode_catatan_err = "Kode catatan tidak boleh kosong.";
    } else {
        $sql = "SELECT id FROM kode_catatan WHERE kode_catatan = ? AND id <> ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $param_kode_catatan_check, $param_id_check);
            $param_kode_catatan_check = trim($_POST["kode_catatan"]);
            $param_id_check = $id;

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $kode_catatan_err = "Kode catatan ini sudah terdaftar untuk data lain.";
                } else {
                    $kode_catatan = trim($_POST["kode_catatan"]);
                }
            } else {
                error_log("Error executing kode_catatan check: " . $stmt->error);
                $general_error_message = "Error: Ada masalah saat memeriksa Kode Catatan: " . $stmt->error;
            }
            $stmt->close();
        } else {
            error_log("Error preparing kode_catatan check query: " . $conn->error);
            $general_error_message = "Error: Ada masalah saat menyiapkan query Kode Catatan: " . $conn->error;
        }
    }

    if (empty(trim($_POST["id_kode_level"]))) {
        $idkodelevel_err = "Level tidak boleh kosong.";
    } elseif (!is_numeric(trim($_POST["id_kode_level"]))) {
        $idkodelevel_err = "Pilihan level tidak valid.";
    } else {
        $idkodelevel = (int)trim($_POST["id_kode_level"]);
    }

    if (empty($kode_catatan_err) && empty($uraian_err) && empty($idkodelevel_err) && empty($general_error_message)) {
        $sql = "UPDATE kode_catatan SET kode_catatan = ?, uraian = ?, id_kode_level = ? WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssii", $param_kode_catatan, $param_uraian, $param_idkodelevel, $param_id);

            $param_kode_catatan = $kode_catatan;
            $param_uraian = $uraian;
            $param_idkodelevel = $idkodelevel;
            $param_id = $id;

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
        $kode_catatan = isset($_POST["kode_catatan"]) ? trim($_POST["kode_catatan"]) : '';
        $uraian = isset($_POST["uraian"]) ? trim($_POST["uraian"]) : '';
        $idkodelevel = isset($_POST["id_kode_level"]) ? (int)trim($_POST["id_kode_level"]) : '';
    }
}

$levels = [];
$sql_levels_reget = "SELECT id, nama_level FROM kode_level ORDER BY nama_level ASC";
$result_levels_reget = $conn->query($sql_levels_reget);

if ($result_levels_reget) {
    while ($row = $result_levels_reget->fetch_assoc()) {
        $levels[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" || (!empty($general_error_message) && !empty($id))) {
    $sql_fetch = "SELECT kc.id, kc.kode_catatan, kc.uraian, kc.id_kode_level
            FROM kode_catatan kc
            WHERE kc.id = ?";
    if ($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("i", $param_id_fetch_reget);
        $param_id_fetch_reget = $id;

        if ($stmt_fetch->execute()) {
            $result_fetch = $stmt_fetch->get_result();
            if ($result_fetch->num_rows == 1) {
                $row_fetch = $result_fetch->fetch_assoc();
                if ($_SERVER["REQUEST_METHOD"] == "GET" || (!empty($general_error_message) && empty($kode_catatan))) {
                    $kode_catatan = $row_fetch["kode_catatan"];
                    $uraian = $row_fetch["uraian"];
                    $idkodelevel = $row_fetch["id_kode_level"];
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

<h2>Edit Data Kode Urut</h2>

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
        <label>Nomor Urut</label>
        <input type="text" name="kode_catatan" value="<?php echo htmlspecialchars($kode_catatan); ?>">
        <span class="error"><?php echo $kode_catatan_err; ?></span>
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
        <a href="read.php" class="back-link">Batal</a>
    </div>
</form>

<?php
$conn->close();
include '../../templates/footer.php';
?>
