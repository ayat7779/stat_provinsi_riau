<?php
session_start();

include '../../config/database.php';
include '../../templates/header.php';

// Initialize variables for error messages and form data
$nourut_err = $uraian_err = $akronim_err = $idkodelevel_err = "";
$nourut = $uraian = $akronim = $idkodelevel ="";

$success_message = "";

// Fetch all levels from the database
$levels = [];
$sql_levels = "SELECT id, nama_level, akronim FROM kode_level ORDER BY id ASC";
$result_levels = $conn->query($sql_levels);

// Check if the query was successful
if ($result_levels) {
    while ($row = $result_levels->fetch_assoc()) {
        $levels[] = $row;
    }
} else {
    echo "Gagal mengambil data level: " . $conn->error;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //Validate Nomor Urut
    if (empty(trim($_POST["no_urut"]))) {
        $nourut_err = "<p class='error-message'>Nomor Urut tidak boleh kosong.</p>";
    } else {
        $sql = "SELECT id FROM kode_urut_13 WHERE no_urut = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_nourut_check);
            $param_nourut_check = trim($_POST["no_urut"]);

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $nourut_err = "<p class='error-message'>Nomor Urut ini sudah terdaftar.</p>";
                } else {
                    $nourut = trim($_POST["no_urut"]);
                }
            } else {
                echo "<p class='error-message'>Ada yang salah saat memeriksa Nomor Urut. Silakan coba lagi nanti.</p>";
            }
            $stmt->close();
        }
    }
    //Validate Uraian
    if (empty(trim($_POST["uraian"]))) {
        $uraian_err = "<p class='error-message'>Uraian tidak boleh kosong.</p>";
    } else {
        $uraian = trim($_POST["uraian"]);
    }
    //Validate Akronim
    if (empty(trim($_POST["akronim"]))) {
        $akronim_err = "<p class='error-message'>Akronim tidak boleh kosong.</p>";
    } else {
        $akronim = trim($_POST["akronim"]);
    }
    //Validate Level
    if (empty(trim($_POST["id_kode_level"]))) {
        $idkodelevel_err = "<p class='error-message'>Level tidak boleh kosong.</p>";
    } elseif (!is_numeric(trim($_POST["id_kode_level"]))) {
        $idkodelevel_err = "Pilihan level tidak valid.";
    } else {
        $idkodelevel = (int)trim($_POST["id_kode_level"]);
    }
    //Check for errors before inserting into database
    if (empty($nourut_err) && empty($uraian_err) && empty($akronim_err) && empty($idkodelevel_err)) {
        $sql = "INSERT INTO kode_urut_13 (no_urut, uraian, akronim, id_kode_level) VALUES (?, ?, ?, ?)"; 
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssi", $param_nourut, $param_uraian, $param_akronim, $param_idkodelevel);

            $param_nourut = $nourut;
            $param_uraian = $uraian;
            $param_akronim = $akronim;
            $param_idkodelevel = $idkodelevel;

            if ($stmt->execute()) {
                $success_message = "Data berhasil disimpan!";
                $nourut = $uraian = $akronim = $idkodelevel = "";
            } else {
                echo "<p class='error-message'>Ada yang salah saat menyimpan data. Silakan coba lagi nanti: </p>" . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<h2>Tambah Data Kode Urut APBD</h2>

<?php
if (!empty($success_message)): ?>
    <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $success_message; ?>
    </div>
    <script>
        setTimeout(function() {
            window.location.href = 'read.php';
        }, 2000);
    </script>
<?php endif; ?>

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
        <button type="submit">Simpan</button>
        <a href="read.php" class="back-link">Batal</a>
    </div>
</form>

<?php
$conn->close();
include '../../templates/footer.php';
?>