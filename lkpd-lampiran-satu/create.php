<?php
session_start();

include '../config/database.php';
include '../templates/header.php';

$tahun_lkpd_err = $id_kode_catatan_err = $jumlah_anggaran_err = $jumlah_realisasi_err = "";
$tahun_lkpd = $id_kode_catatan = $jumlah_anggaran = $jumlah_realisasi ="";

$success_message = "";

$kdcatatan = [];
$sql_kdcatatan = "SELECT * 
                  FROM kode_catatan 
                  WHERE id_kode_level = 3
                  ORDER BY id ASC";
$result_kdcatatan = $conn->query($sql_kdcatatan);

if ($result_kdcatatan) {
    while ($row = $result_kdcatatan->fetch_assoc()) {
        $kdcatatan[] = $row;
    }
} else {
    echo "Gagal mengambil data Catatan: " . $conn->error;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["tahun_lkpd"]))) {
        $tahun_lkpd_err = "<p class='error-message'>Tahun LKPD tidak boleh kosong.</p>";
    } else {
        $tahun_lkpd = trim($_POST["tahun_lkpd"]);
    }

    if (empty(trim($_POST["id_kode_catatan"]))) {
        $id_kode_catatan_err = "<p class='error-message'>id kode catatan tidak boleh kosong.</p>";
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
        $jumlah_anggaran = (int)trim($_POST["jumlah_anggaran"]);
    }

    if (trim($_POST["jumlah_realisasi"]) === "") {
        $jumlah_realisasi_err = "<p class='error-message'>Jumlah realisasi tidak boleh kosong.</p>";
    } elseif (!is_numeric(trim($_POST["jumlah_realisasi"]))) {
        $jumlah_realisasi_err = "<p class='error-message'>Jumlah realisasi harus berupa angka.</p>";
    } else {
        $jumlah_realisasi = (int)trim($_POST["jumlah_realisasi"]);
    }

    if (empty($tahun_lkpd_err) && empty($id_kode_catatan_err) && empty($jumlah_anggaran_err) && empty($jumlah_realisasi_err)) {
        $sql = "INSERT INTO lkpd_apbd_lampiran_1 (tahun_lkpd, id_kode_catatan, jumlah_anggaran, jumlah_realisasi) VALUES (?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("siii", $param_tahun_lkpd, $param_id_kode_catatan, $param_jumlah_anggaran, $param_jumlah_realisasi);

            $param_tahun_lkpd = $tahun_lkpd;
            $param_id_kode_catatan = $id_kode_catatan;
            $param_jumlah_anggaran = $jumlah_anggaran;
            $param_jumlah_realisasi = $jumlah_realisasi;

            if ($stmt->execute()) {
                $success_message = "Data berhasil disimpan!";
                $tahun_lkpd = $id_kode_catatan = $jumlah_anggaran = $jumlah_realisasi = "";
            } else {
                echo "<p class='error-message'>Ada yang salah saat menyimpan data. Silakan coba lagi nanti: </p>". $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<h2>Tambah Data LKPD APBD</h2>

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
        <label>Tahun LKPD</label>
        <input type="text" name="tahun_lkpd" value="<?php echo htmlspecialchars($tahun_lkpd); ?>">
        <span class="error"><?php echo $tahun_lkpd_err; ?></span>
    </div>
    <div>
        <label>Uraian Catatan</label>
        <select name="id_kode_catatan">
            <option value="">-- Pilih Catatan --</option>
            <?php foreach ($kdcatatan as $catatan): ?>
                <option value="<?php echo htmlspecialchars($catatan['id']); ?>"
                    <?php echo ($id_kode_catatan == $catatan['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($catatan['kode_catatan']). " - ";?>
                    <?php echo htmlspecialchars($catatan['uraian']); ?>
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
    <div>
        <button type="submit">Simpan</button>
        <a href="read.php" class="back-link">Batal</a>
    </div>
</form>

<?php
$conn->close();
include '../templates/footer.php';
?>