<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include '../../config/database.php';

$nourut_err = $uraian_err = $idkodelevel_err = $akronim_err = "";
$id = $nourut = $uraian = $idkodelevel = $akronim = "";

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

// Check if the ID is a valid integer
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate nomor urut
    if (empty(trim($_POST["nourut"]))) {
        $nourut_err = "Nomor Urut tidak boleh kosong.";
    } else {
        $sql = "SELECT id FROM kode_urut_13 WHERE no_urut = ? AND id <> ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $param_nourut_check, $param_id_check);
            $param_nourut_check = trim($_POST["nourut"]);
            $param_id_check = $id;

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

    // Validate uraian
    if (empty(trim($_POST["uraian"]))) {
        $uraian_err = "Uraian tidak boleh kosong.";
    } else {
        $uraian = trim($_POST["uraian"]);
    }

    // Validate akronim
    if (empty(trim($_POST["akronim"]))) {
        $akronim_err = "Akronim tidak boleh kosong.";
    } else {
        $akronim = trim($_POST["akronim"]);
    }

    // Validate level
    if (empty(trim($_POST["id_kode_level"]))) {
        $idkodelevel_err = "Level tidak boleh kosong.";
    } elseif (!is_numeric(trim($_POST["id_kode_level"]))) {
        $idkodelevel_err = "Pilihan level tidak valid.";
    } else {
        $idkodelevel = (int)trim($_POST["id_kode_level"]);
    }

    // Check for errors before updating the database
    if (empty($nourut_err) && empty($uraian_err) && empty($akronim_err) && empty($idkodelevel_err) && empty($general_error_message)) {
        $sql = "UPDATE kode_urut_13 SET no_urut = ?, uraian = ?, akronim = ?, id_kode_level = ? WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssii", $param_nourut, $param_uraian, $param_akronim, $param_idkodelevel, $param_id);

            $param_nourut = $nourut;
            $param_uraian = $uraian;
            $param_akronim = $akronim;
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
        $nourut = isset($_POST["nourut"]) ? trim($_POST["nourut"]) : '';
        $uraian = isset($_POST["uraian"]) ? trim($_POST["uraian"]) : '';
        $akronim = isset($_POST["akronim"]) ? trim($_POST["akronim"]) : '';
        $idkodelevel = isset($_POST["id_kode_level"]) ? (int)trim($_POST["id_kode_level"]) : '';
    }
}
// Fetch all levels from the database
$levels = [];
$sql_levels_reget = "SELECT id, nama_level FROM kode_level ORDER BY id ASC";
$result_levels_reget = $conn->query($sql_levels_reget);
// Check if the query was successful
if ($result_levels_reget) {
    while ($row = $result_levels_reget->fetch_assoc()) {
        $levels[] = $row;
    }
}
// If the levels array is empty, set an error message
if ($_SERVER["REQUEST_METHOD"] == "GET" || (!empty($general_error_message) && !empty($id))) {
    $sql_fetch = "SELECT ku.id, ku.no_urut, ku.uraian, ku.akronim, ku.id_kode_level
            FROM kode_urut_13 ku
            WHERE ku.id = ?";
    if ($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("i", $param_id_fetch_reget);
        $param_id_fetch_reget = $id;

        if ($stmt_fetch->execute()) {
            $result_fetch = $stmt_fetch->get_result();
            if ($result_fetch->num_rows == 1) {
                $row_fetch = $result_fetch->fetch_assoc();
                if ($_SERVER["REQUEST_METHOD"] == "GET" || (!empty($general_error_message) && empty($nourut))) {
                    $nourut = $row_fetch["no_urut"];
                    $uraian = $row_fetch["uraian"];
                    $akronim = $row_fetch["akronim"];
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
        <label>Akronim</label>
        <input type="text" name="akronim" value="<?php echo htmlspecialchars($akronim); ?>">
        <span class="error"><?php echo $akronim_err; ?></span>
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