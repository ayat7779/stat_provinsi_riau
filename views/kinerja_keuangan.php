<?php
// Pastikan koneksi database sudah di-include, misalnya dari index.php atau langsung di sini
// include 'config/database.php'; // Uncomment this line if not already included

$selected_year = '';
$selected_kinerja = ''; // Variabel baru untuk menyimpan kinerja yang dipilih

// Check if a year is selected from the dropdown
if (isset($_GET['tahun']) && $_GET['tahun'] != '') {
    $selected_year = $_GET['tahun'];
}

// Check if a performance type is selected from the dropdown
if (isset($_GET['kinerja']) && $_GET['kinerja'] != '') {
    $selected_kinerja = $_GET['kinerja'];
}

// Definisikan daftar kinerja keuangan yang tersedia
// Ini harus sesuai dengan string 'kode' di bagian UNION ALL query Anda
$available_kinerja = [
    'Derajat Desentralisasi',
    'Ketergantungan Keuangan',
    'Kemandirian Keuangan',
    'Efektifitas PAD'
];

$sql = "
SELECT a.tahun_lkpd AS tahun, a.kode AS kinerja, a.anggaran AS anggaran, a.realisasi AS realisasi
FROM (
    #Akun
    SELECT tahun_lkpd, LEFT(kode_catatan,2) AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_realisasi) AS realisasi
        FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id` GROUP BY tahun_lkpd, kode
    UNION ALL
    #Kelompok
    SELECT tahun_lkpd, LEFT(kode_catatan,4) AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_realisasi) AS realisasi
        FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id` GROUP BY tahun_lkpd, kode
    UNION ALL
    #Jenis
    SELECT tahun_lkpd, kode_catatan AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_realisasi) AS realisasi
        FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id` GROUP BY tahun_lkpd, kode
    UNION ALL
    #Derajat Desentralisasi = PAD / Total Pendapatan Daerah
    SELECT tahun_lkpd, 'Derajat Desentralisasi' AS kode,
    (SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_anggaran ELSE 0 END)/SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '1.') THEN la.jumlah_anggaran ELSE 0 END))*100 AS anggaran,
    (SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_realisasi ELSE 0 END)/SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '1.') THEN la.jumlah_realisasi ELSE 0 END))*100 AS realisasi
    FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id` GROUP BY tahun_lkpd, kode
    UNION ALL
    #Ketergantungan Keuangan= Pendapatan Transfer / Total Pendapatan
    SELECT tahun_lkpd, 'Ketergantungan Keuangan' AS kode,
    ((SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.2.') THEN la.jumlah_anggaran ELSE 0 END)+SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.3.') THEN la.jumlah_anggaran ELSE 0 END))/SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '1.') THEN la.jumlah_anggaran ELSE 0 END))*100 AS anggaran,
    ((SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.2.') THEN la.jumlah_realisasi ELSE 0 END)+SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.3.') THEN la.jumlah_realisasi ELSE 0 END))/SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '1.') THEN la.jumlah_realisasi ELSE 0 END))*100 AS realisasi
    FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id` GROUP BY tahun_lkpd, kode
    UNION ALL
    #Kemandirian Keuangan = PAD / (Transfer Pusat + Provinsi + Pinjaman)
    SELECT tahun_lkpd, 'Kemandirian Keuangan' AS kode,
    (SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_anggaran ELSE 0 END)/(SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.2.') THEN la.jumlah_anggaran ELSE 0 END)+SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.3.') THEN la.jumlah_anggaran ELSE 0 END)))*100 AS anggaran,
    (SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_realisasi ELSE 0 END)/(SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.2.') THEN la.jumlah_realisasi ELSE 0 END)+SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.3.') THEN la.jumlah_realisasi ELSE 0 END)))*100 AS realisasi
    FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id` GROUP BY tahun_lkpd, kode
    UNION ALL
    #Efektifitas PAD         = realisasi PAD / Target PAD
    SELECT tahun_lkpd, 'Efektifitas PAD' AS kode,
    (SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_realisasi ELSE 0 END)/SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_anggaran ELSE 0 END))*100 AS anggaran,
    (SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_realisasi ELSE 0 END)/SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_anggaran ELSE 0 END))*100 AS realisasi
    FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id` GROUP BY tahun_lkpd, kode
) AS a
LEFT JOIN kode_catatan AS kc ON a.kode = kc.`kode_catatan`
-- Modifikasi bagian WHERE untuk kondisi awal
WHERE a.kode IN ('Derajat Desentralisasi','Ketergantungan Keuangan','Kemandirian Keuangan','Efektifitas PAD')
";

// Tambahkan kondisi WHERE untuk kinerja yang dipilih
if ($selected_kinerja != '') {
    // Karena 'kode' sudah ada di kondisi WHERE utama, kita akan menambahkannya dengan AND
    // Pastikan tidak ada konflik dengan kondisi 'IN' jika 'selected_kinerja' adalah salah satu dari daftar tersebut
    // Cara paling aman adalah membuat array dinamis untuk WHERE IN
    $where_conditions = ["a.kode IN ('" . implode("','", $available_kinerja) . "')"];
    $where_conditions[] = "a.kode = ?"; // Tambahkan kondisi spesifik untuk kinerja yang dipilih
    $sql .= " AND " . implode(" AND ", $where_conditions);
} else {
     // Jika tidak ada kinerja yang dipilih, tetap tampilkan semua yang ada di available_kinerja
    $sql .= " AND a.kode IN ('" . implode("','", $available_kinerja) . "')";
}

// Add WHERE clause if a year is selected
if ($selected_year != '') {
    // Gunakan HAVING karena 'tahun' adalah alias dari kolom agregat
    $sql .= " HAVING tahun = ?";
}

$sql .= " ORDER BY a.tahun_lkpd, a.kode ASC";


// Prepare the statement
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind parameter(s)
$bind_types = "";
$bind_params = [];

if ($selected_kinerja != '') {
    $bind_types .= "s";
    $bind_params[] = $selected_kinerja;
}

if ($selected_year != '') {
    $bind_types .= "s";
    $bind_params[] = $selected_year;
}

// Hanya panggil bind_param jika ada parameter untuk di-bind
if (!empty($bind_params)) {
    $stmt->bind_param($bind_types, ...$bind_params);
}


// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// --- Get unique years for the dropdown ---
$years_query = "SELECT DISTINCT tahun_lkpd AS tahun FROM lkpd_apbd_lampiran_1 ORDER BY tahun_lkpd DESC";
$years_result = $conn->query($years_query);
$available_years = [];
if ($years_result->num_rows > 0) {
    while ($row = $years_result->fetch_assoc()) {
        $available_years[] = $row['tahun'];
    }
}
?>

<h2>KINERJA KEUANGAN PROVINSI RIAU</h2>

<form method="GET" action="">
    <label for="tahun">Filter Tahun:</label>
    <select name="tahun" id="tahun" onchange="this.form.submit()">
        <option value="">-- Semua Tahun --</option>
        <?php foreach ($available_years as $year): ?>
            <option value="<?php echo htmlspecialchars($year); ?>"
                    <?php echo ($selected_year == $year) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($year); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <label for="kinerja">Filter Kinerja:</label>
    <select name="kinerja" id="kinerja" onchange="this.form.submit()">
        <option value="">-- Semua Kinerja --</option>
        <?php foreach ($available_kinerja as $kinerja_item): ?>
            <option value="<?php echo htmlspecialchars($kinerja_item); ?>"
                    <?php echo ($selected_kinerja == $kinerja_item) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($kinerja_item); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <input type="hidden" name="page" value="kinerja_keuangan">

    <noscript><button type="submit">Terapkan Filter</button></noscript>
</form>

<?php
if (isset($_SESSION['message'])): ?>
    <div class="message-box message-success">
        <?php echo $_SESSION['message']; ?>
    </div>
<?php
    unset($_SESSION['message']);
endif;

if (isset($_GET['error'])): ?>
    <div class="message-box message-error">
        Error: <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>

<?php
if ($result->num_rows > 0):
?>
    <table>
        <thead>
            <tr>
                <th style="text-align: center;">Tahun</th>
                <th style="text-align: center;">Kinerja</th>
                <th style="text-align: center;">Target</th> 
                <th style="text-align: center;">Realisasi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['tahun']); ?></td>
                    <td><?php echo htmlspecialchars($row['kinerja']); ?></td>
                    <td style="text-align:right"><?php echo htmlspecialchars(number_format($row['anggaran']??0, 2, ',', '.')); ?></td>
                    <td style="text-align:right"><?php echo htmlspecialchars(number_format($row['realisasi']??0, 2, ',', '.')); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Belum ada data untuk kriteria yang dipilih.</p>
<?php endif; ?>

<?php
$stmt->close();
// $conn->close(); // Jangan tutup koneksi jika ini adalah bagian dari aplikasi yang lebih besar (misal: header/footer akan menggunakannya)
?>