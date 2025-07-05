<?php
//include 'config/database.php';

$selected_year = '';
// Check if a year is selected from the dropdown
if (isset($_GET['tahun']) && $_GET['tahun'] != '') {
    $selected_year = $_GET['tahun'];
}

$sql = "
SELECT a.tahun_lkpd AS tahun, a.kode AS kinerja, a.anggaran AS target, a.realisasi AS realisasi, (a.realisasi/a.anggaran)*100 AS ketercapaian
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
	#Efektifitas PAD        = realisasi PAD / Target PAD
	SELECT tahun_lkpd, 'Efektifitas PAD' AS kode,
	(SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_realisasi ELSE 0 END)/SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_anggaran ELSE 0 END))*100 AS anggaran, 
	(SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_realisasi ELSE 0 END)/SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_anggaran ELSE 0 END))*100 AS realisasi 
	FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id` GROUP BY tahun_lkpd, kode
) AS a
LEFT JOIN kode_catatan AS kc ON a.kode = kc.`kode_catatan`
WHERE kode IN ('Derajat Desentralisasi','Ketergantungan Keuangan','Kemandirian Keuangan','Efektifitas PAD')
";
    
// Add WHERE clause if a year is selected
if ($selected_year != '') {
    $sql .= " HAVING tahun = ?";
}

$sql .= " ORDER BY a.tahun_lkpd, a.kode ASC";


// Prepare the statement
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind parameter if a year is selected
if ($selected_year != '') {
    $stmt->bind_param("s", $selected_year);
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

<!-- Year Filter Form -->
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
                <th style="text-align: center;">Ketercapaian</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch_assoc()):
            ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['tahun']); ?></td>
                    <td><?php echo htmlspecialchars($row['kinerja']); ?></td>
                    <td style="text-align:right"><?php echo htmlspecialchars(number_format($row['target']??0, 2, ',', '.')); ?></td>
                    <td style="text-align:right"><?php echo htmlspecialchars(number_format($row['realisasi']??0, 2, ',', '.')); ?></td>
                    <td style="text-align:right"><?php echo htmlspecialchars(number_format($row['ketercapaian']??0, 2, ',', '.')); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Belum ada data.</p>
<?php endif; ?>

<?php
$stmt->close();
$conn->close();
?>
