<?php
session_start();

include '../../config/database.php';
include '../../templates/header.php';

$kodecatatan_err = $uraian_err = $idkodelevel_err = "";
$kodecatatan = $uraian = $idkodelevel = "";

$success_message = "";

$levels = [];
$sql_levels = "SELECT id, nama_level, akronim FROM kode_level ORDER BY id ASC";
$result_levels = $conn->query($sql_levels);

if ($result_levels) {
    while ($row = $result_levels->fetch_assoc()) {
        $levels[] = $row;
    }
} else {
    echo "Gagal mengambil data level: " . $conn->error;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["uraian"]))) {
        $uraian_err = "<p class='error-message'>Uraian tidak boleh kosong.</p>";
    } else {
        $uraian = trim($_POST["uraian"]);
    }

    if (empty(trim($_POST["kode_catatan"]))) {
        $kodecatatan_err = "<p class='error-message'>Kode catatan tidak boleh kosong.</p>";
    } else {
        $sql = "SELECT id FROM kode_catatan WHERE kode_catatan = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_kodecatatan_check);
            $param_kodecatatan_check = trim($_POST["kode_catatan"]);

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $kodecatatan_err = "<p class='error-message'>Kode catatan ini sudah terdaftar.</p>";
                } else {
                    $kodecatatan = trim($_POST["kode_catatan"]);
                }
            } else {
                echo "<p class='error-message'>Ada yang salah saat memeriksa kode catatan. Silakan coba lagi nanti.</p>";
            }
            $stmt->close();
        }
    }

    if (empty(trim($_POST["id_kode_level"]))) {
        $idkodelevel_err = "<p class='error-message'>Level tidak boleh kosong.</p>";
    } elseif (!is_numeric(trim($_POST["id_kode_level"]))) {
        $idkodelevel_err = "<p class='error-message'>Pilihan level tidak valid.</p>";
    } else {
        $idkodelevel = (int)trim($_POST["id_kode_level"]);
    }

    if (empty($kodecatatan_err) && empty($uraian_err) && empty($idkodelevel_err)) {
        $sql = "INSERT INTO kode_catatan (kode_catatan, uraian, id_kode_level) VALUES (?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $param_kodecatatan, $param_uraian, $param_idkodelevel);

            $param_kodecatatan = $kodecatatan;
            $param_uraian = $uraian;
            $param_idkodelevel = $idkodelevel;

            if ($stmt->execute()) {
                $success_message = "Data berhasil disimpan!";
                $kodecatatan = $uraian = $idkodelevel = "";
            } else {
                echo "<p class='error-message'>Ada yang salah saat menyimpan data. Silakan coba lagi nanti: </p>". $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<h2>Tambah Data Kode Catatan</h2>

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
        <label>Kode Catatan</label>
        <input type="text" name="kode_catatan" value="<?php echo htmlspecialchars($kodecatatan); ?>">
        <span class="error"><?php echo $kodecatatan_err; ?></span>
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
        <a href="read.php" class="back-link">Batal</a>
    </div>
</form>

<?php
$conn->close();
include '../../templates/footer.php';
?>