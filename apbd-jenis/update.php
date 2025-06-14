<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

include '../config/database.php';

$uraian_err = $akronim_err = "";
$id = $uraian = $akronim = "";

$success_message = "";
$general_error_message = "";

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);
} elseif (isset($_POST["id"]) && !empty(trim($_POST["id"]))) {
    $id = trim($_POST["id"]);
} else {
    header("location: ../index.php?error=no_id_found");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  
    if (empty(trim($_POST["uraian"]))) {
        $uraian_err = "Uraian tidak boleh kosong.";
    } else {
        $sql = "SELECT id FROM jenis_apbd WHERE uraian = ? AND id <> ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $param_uraian_check, $param_id_check);
            $param_uraian_check = trim($_POST["uraian"]);
            $param_id_check = $id;

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $uraian_err = "Uraian ini sudah terdaftar untuk data lain.";
                } else {
                    $uraian = trim($_POST["uraian"]);
                }
            } else {
                error_log("Error executing uraian check: " . $stmt->error);
                $general_error_message = "Error: Ada masalah saat memeriksa uraian: " . $stmt->error;
            }
            $stmt->close();
        } else {
            error_log("Error preparing uraian check query: " . $conn->error);
            $general_error_message = "Error: Ada masalah saat menyiapkan query uraian: " . $conn->error;
        }
    }

    if (empty(trim($_POST["akronim"]))) {
        $akronim_err = "Akronim tidak boleh kosong.";
    } else {
        $akronim = trim($_POST["akronim"]);
    }

    if (empty($uraian_err) && empty($akronim_err) && empty($general_error_message)) {
        $sql = "UPDATE jenis_apbd SET uraian = ?, akronim = ? WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $param_uraian, $param_akronim, $param_id);

            $param_uraian = $uraian;
            $param_akronim = $akronim;
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
        $uraian = isset($_POST["uraian"]) ? trim($_POST["uraian"]) : '';
        $akronim = isset($_POST["akronim"]) ? trim($_POST["akronim"]) : '';
    }
}

if ($_SERVER["REQUEST_METHOD"] == "GET" || (!empty($general_error_message) && !empty($id))) {
    $sql_fetch = "SELECT * FROM jenis_apbd WHERE id = ?";
    if ($stmt_fetch = $conn->prepare($sql_fetch)) {
        $stmt_fetch->bind_param("i", $param_id_fetch_reget);
        $param_id_fetch_reget = $id;

        if ($stmt_fetch->execute()) {
            $result_fetch = $stmt_fetch->get_result();
            if ($result_fetch->num_rows == 1) {
                $row_fetch = $result_fetch->fetch_assoc();
                if ($_SERVER["REQUEST_METHOD"] == "GET" || (!empty($general_error_message) && empty($kode_catatan))) {
                    $uraian = $row_fetch["uraian"];
                    $akronim = $row_fetch["akronim"];
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
        <button type="submit">Update</button>
        <a href="read.php" class="back-link">Batal</a>
    </div>
</form>

<?php
$conn->close();
include '../templates/footer.php';
?>
