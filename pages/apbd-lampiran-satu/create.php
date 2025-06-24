<?php
session_start();

include '../../config/database.php';
include '../../templates/header.php';

$tahun_apbd_err = $id_kode_urut_err = $id_jenis_apbd_err = $jumlah_anggaran_err = $jumlah_perubahan_err = $kodeurut_err = "";
$tahun_apbd = $id_kode_urut = $id_jenis_apbd = $jumlah_anggaran = $jumlah_perubahan = $kodeurut = "";

$success_message = "";

// Ambil data nomor urut dari database untuk dropdown
$kdurut = [];
$sql_kdurut = "SELECT * FROM kode_urut WHERE id_kode_level = 3 ORDER BY no_urut ASC";
$result_kdurut = $conn->query($sql_kdurut);

if ($result_kdurut) {
    while ($row = $result_kdurut->fetch_assoc()) {
        $kdurut[] = $row;
    }
} else {
    echo "Gagal mengambil data urutan: " . $conn->error;
}

// Ambil data jenis APBD dari database untuk dropdown
$kdjenisapbd = [];
$sql_kdjenisapbd = "SELECT * FROM jenis_apbd ORDER BY id ASC";
$result_kdjenisapbd = $conn->query($sql_kdjenisapbd);

if ($result_kdjenisapbd) {
    while ($row = $result_kdjenisapbd->fetch_assoc()) {
        $kdjenisapbd[] = $row;
    }
} else {
    echo "Gagal mengambil data Jenis APBD: " . $conn->error;
}

// Proses form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    //vALIDASI INPUT TAHUN APBD
    if (empty(trim($_POST["tahun_apbd"]))) {
        $tahun_apbd_err = "<p class='error-message'>Tahun APBD tidak boleh kosong.</p>";
    } else {
        $tahun_apbd = trim($_POST["tahun_apbd"]);
    }

    // Validasi input nomor urut
    if (empty(trim($_POST["id_kode_urut"]))) {
        $id_kode_urut_err = "<p class='error-message'>id Nomor Urut tidak boleh kosong.</p>";
    } elseif (!is_numeric(trim($_POST["id_kode_urut"]))) {
        $id_kode_urut_err = "<p class='error-message'>Pilihan nomor urut tidak valid.</p>";
    } else {
        $id_kode_urut = (int)trim($_POST["id_kode_urut"]);
    }

    // Validasi input jumlah anggaran
    if (trim($_POST["jumlah_anggaran"]) === "") {
        $jumlah_anggaran_err = "<p class='error-message'>Jumlah anggaran tidak boleh kosong.</p>";
    } elseif (!is_numeric(trim($_POST["jumlah_anggaran"]))) {
        $jumlah_anggaran_err = "<p class='error-message'>Jumlah anggaran harus berupa angka.</p>";
    } else {
        $jumlah_anggaran = (float)trim($_POST["jumlah_anggaran"]);
    }

    // Validasi input jumlah perubahan
    if (trim($_POST["jumlah_perubahan"]) === "") {
        $jumlah_perubahan_err = "<p class='error-message'>Jumlah anggaran perubahan tidak boleh kosong.</p>";
    } elseif (!is_numeric(trim($_POST["jumlah_perubahan"]))) {
        $jumlah_perubahan_err = "<p class='error-message'>Jumlah anggaran perubahan harus berupa angka.</p>";
    } else {
        $jumlah_perubahan = (float)trim($_POST["jumlah_perubahan"]);
    }

    // Validasi input jenis APBD
    if (empty(trim($_POST["id_jenis_apbd"]))) {
        $id_jenis_apbd_err = "<p class='error-message'>Jenis APBD tidak boleh kosong.</p>";
    } elseif (!is_numeric(trim($_POST["id_jenis_apbd"]))) {
        $id_jenis_apbd_err = "<p class='error-message'>Pilihan jenis APBD tidak valid.</p>";
    } else {
        $id_jenis_apbd = (int)trim($_POST["id_jenis_apbd"]);
    }

    if (empty($tahun_apbd_err) && empty($id_kode_urut_err) && empty($jumlah_anggaran_err) && empty($jumlah_perubahan_err) && empty($id_jenis_apbd_err)) {
        $sql = "INSERT INTO apbd_lampiran_1 (tahun, id_kode_urut, jumlah_anggaran, jumlah_perubahan, id_jenis_apbd) VALUES (?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("siddi", $param_tahun_apbd, $param_id_kode_urut, $param_jumlah_anggaran, $param_jumlah_perubahan, $param_id_jenis_apbd);

            $param_tahun_apbd = $tahun_apbd;
            $param_id_kode_urut = $id_kode_urut;
            $param_jumlah_anggaran = $jumlah_anggaran;
            $param_jumlah_perubahan = $jumlah_perubahan;
            $param_id_jenis_apbd = $id_jenis_apbd;

            if ($stmt->execute()) {
                $success_message = "Data berhasil disimpan!";
                $tahun_apbd = $id_kode_urut = $jumlah_anggaran = $jumlah_perubahan = $id_jenis_apbd = "";
            } else {
                echo "<p class='error-message'>Ada yang salah saat menyimpan data. Silakan coba lagi nanti: </p>" . $stmt->error;
            }
            $stmt->close();
        }
    }
}
?>

<h2>Tambah Data APBD</h2>

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
<?php endif; ?>

<form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <div class="custom-select-wrapper">
        <label for="my-dropdown-custom">Jenis APBD</label>
        <div class="custom-select-wrapper">
            <select name="id_jenis_apbd">
                <option value="">-- Pilih Jenis --</option>
                <?php foreach ($kdjenisapbd as $jenis): ?>
                    <option value="<?php echo htmlspecialchars($jenis['id']); ?>"
                        <?php echo ($id_jenis_apbd == $jenis['id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($jenis['uraian']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <span class="error"><?php echo $id_jenis_apbd_err; ?></span>
    </div>
    <div>
        <label>Tahun APBD</label>
        <input type="text" name="tahun_apbd" value="<?php echo htmlspecialchars($tahun_apbd); ?>">
        <span class="error"><?php echo $tahun_apbd_err; ?></span>
    </div>
    <div>
        <label for="custom-select-wrapper">Uraian</label>
        <select name="id_kode_urut">
            <option value="">-- Pilih Akun --</option>
            <?php foreach ($kdurut as $urut): ?>
                <option value="<?php echo htmlspecialchars($urut['id']); ?>"
                    <?php echo ($id_kode_urut == $urut['id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($urut['no_urut']) . " - "; ?>
                    <?php echo htmlspecialchars($urut['uraian']); ?>
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
        <label>Jumlah Anggaran Perubahan</label>
        <input type="text" name="jumlah_perubahan" value="<?php echo htmlspecialchars($jumlah_perubahan); ?>">
        <span class="error"><?php echo $jumlah_perubahan_err; ?></span>
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