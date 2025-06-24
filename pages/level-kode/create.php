<?php
session_start();

include '../../config/database.php';
include '../../templates/header.php';

$nama_level_err = $akronim_err = "";
$nama_level = $akronim = "";

$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["nama_level"]))) {
        $nama_level_err = "<p class='error-message'>nama_level tidak boleh kosong.</p>";
    } else {
        $sql = "SELECT id FROM kode_level WHERE nama_level = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_nama_level_check);
            $param_nama_level_check = trim($_POST["nama_level"]);

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $nama_level_err = "<p class='error-message'>nama_level ini sudah terdaftar.</p>";
                } else {
                    $nama_level = trim($_POST["nama_level"]);
                }
            } else {
                echo "<p class='error-message'>Ada yang salah saat memeriksa nama_level. Silakan coba lagi nanti.</p>";
            }
            $stmt->close();
        }
    }

    if (empty(trim($_POST["akronim"]))) {
        $akronim_err = "<p class='error-message'>Akronim tidak boleh kosong.</p>";
    } else {
        $akronim = trim($_POST["akronim"]);
    }

    if (empty($nama_level_err) && empty($akronim_err)) {
        $sql = "INSERT INTO kode_level (nama_level, akronim) VALUES (?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $param_nama_level, $param_akronim);

            $param_nama_level = $nama_level;
            $param_akronim = $akronim;

            if ($stmt->execute()) {
                $success_message = "Data berhasil disimpan!";
                $nama_level = $akronim = "";
            } else {
                echo "<p class='error-message'>Ada yang salah saat menyimpan data. Silakan coba lagi nanti: </p>". $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<h2>Tambah Data Kode Level</h2>

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
        <label>Uraian</label>
        <input type="text" name="nama_level" value="<?php echo htmlspecialchars($nama_level); ?>">
        <span class="error"><?php echo $nama_level_err; ?></span>
    </div>
    <div>
        <label>Akronim</label>
        <input type="text" name="akronim" value="<?php echo htmlspecialchars($akronim); ?>">
        <span class="error"><?php echo $akronim_err; ?></span>
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