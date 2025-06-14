<?php
session_start();

include '../config/database.php';
include '../templates/header.php';

$uraian_err = $akronim_err = "";
$uraian = $akronim = "";

$success_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["uraian"]))) {
        $uraian_err = "<p class='error-message'>Uraian tidak boleh kosong.</p>";
    } else {
        $sql = "SELECT id FROM jenis_apbd WHERE uraian = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_uraian_check);
            $param_uraian_check = trim($_POST["uraian"]);

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $uraian_err = "<p class='error-message'>Uraian ini sudah terdaftar.</p>";
                } else {
                    $uraian = trim($_POST["uraian"]);
                }
            } else {
                echo "<p class='error-message'>Ada yang salah saat memeriksa Uraian. Silakan coba lagi nanti.</p>";
            }
            $stmt->close();
        }
    }

    if (empty(trim($_POST["akronim"]))) {
        $akronim_err = "<p class='error-message'>Akronim tidak boleh kosong.</p>";
    } else {
        $akronim = trim($_POST["akronim"]);
    }

    if (empty($uraian_err) && empty($akronim_err)) {
        $sql = "INSERT INTO jenis_apbd (uraian, akronim) VALUES (?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ss", $param_uraian, $param_akronim);

            $param_uraian = $uraian;
            $param_akronim = $akronim;

            if ($stmt->execute()) {
                $success_message = "Data berhasil disimpan!";
                $uraian = $akronim = "";
            } else {
                echo "<p class='error-message'>Ada yang salah saat menyimpan data. Silakan coba lagi nanti: </p>". $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<h2>Tambah Data Jenis APBD</h2>

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
        <input type="text" name="uraian" value="<?php echo htmlspecialchars($uraian); ?>">
        <span class="error"><?php echo $uraian_err; ?></span>
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
include '../templates/footer.php';
?>