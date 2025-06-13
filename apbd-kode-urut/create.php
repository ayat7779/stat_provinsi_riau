<?php
session_start();

include '../config/database.php';
include '../templates/header.php';

$nourut_err = $uraian_err = $idkodelevel_err = "";
$nourut = $uraian = $idkodelevel = "";

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
        $uraian_err = "Uraian tidak boleh kosong.";
    } else {
        $uraian = trim($_POST["uraian"]);
    }

    if (empty(trim($_POST["no_urut"]))) {
        $nourut_err = "Nomor Urut tidak boleh kosong.";
    } else {
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

    if (empty(trim($_POST["id_kode_level"]))) {
        $idkodelevel_err = "Level tidak boleh kosong.";
    } elseif (!is_numeric(trim($_POST["id_kode_level"]))) {
        $idkodelevel_err = "Pilihan level tidak valid.";
    } else {
        $idkodelevel = (int)trim($_POST["id_kode_level"]);
    }

    if (empty($nourut_err) && empty($uraian_err) && empty($idkodelevel_err)) {
        $sql = "INSERT INTO kode_urut (no_urut, uraian, id_kode_level) VALUES (?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $param_nourut, $param_uraian, $param_idkodelevel);

            $param_nourut = $nourut;
            $param_uraian = $uraian;
            $param_idkodelevel = $idkodelevel;

            if ($stmt->execute()) {
                $success_message = "Data berhasil disimpan!";
                $nourut = $uraian = $idkodelevel = "";
            } else {
                echo "Ada yang salah saat menyimpan data. Silakan coba lagi nanti: ". $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<h2>Tambah Data</h2>

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
include '../templates/footer.php';
?>