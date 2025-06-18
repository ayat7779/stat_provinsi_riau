<?php
include 'config/database.php';

$selected_year = '';
// Check if a year is selected from the dropdown
if (isset($_GET['tahun']) && $_GET['tahun'] != '') {
    $selected_year = $_GET['tahun'];
}

/** 
// Build the SQL query
$sql = " SELECT kc.id_kode_level, tahun_lkpd, kode, uraian, anggaran, realisasi, (realisasi/anggaran)*100 AS persentase FROM (
            SELECT tahun_lkpd, LEFT(kode_catatan,2) AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_realisasi) AS realisasi FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id`
            GROUP BY tahun_lkpd, kode
            UNION ALL
            SELECT tahun_lkpd, LEFT(kode_catatan,4) AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_realisasi) AS realisasi  FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id`
            GROUP BY tahun_lkpd, kode 
            UNION ALL
            SELECT tahun_lkpd, kode_catatan AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_realisasi) AS realisasi  FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id`
            GROUP BY tahun_lkpd, kode
            ) AS a
            INNER JOIN kode_catatan AS kc ON a.kode = kc.`kode_catatan`";

// Add WHERE clause if a year is selected
if ($selected_year != '') {
    $sql .= " WHERE a.tahun_lkpd = ?";
}

$sql .= " ORDER BY a.tahun_lkpd, a.kode ASC";
*/

$sql = "SELECT kc.id_kode_level, tahun_lkpd, kode, uraian, anggaran, realisasi, CASE WHEN a.anggaran = 0 THEN 0 ELSE (a.anggaran-a.realisasi) END AS sisa_anggaran, CASE WHEN a.anggaran = 0 THEN 0 ELSE (a.realisasi/a.anggaran)*100 END AS persentase FROM (
		    SELECT tahun_lkpd, LEFT(kode_catatan,2) AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_realisasi) AS realisasi FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
		    SELECT tahun_lkpd, LEFT(kode_catatan,4) AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_realisasi) AS realisasi  FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode 
		    UNION ALL
		    SELECT tahun_lkpd, kode_catatan AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_realisasi) AS realisasi  FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
                    SELECT tahun_lkpd, 
		           '333330' AS kode, 
		           SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '2.') THEN la.jumlah_anggaran ELSE 0 END)+SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '3.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran, 
		           SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '2.') THEN la.jumlah_realisasi ELSE 0 END)+SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '3.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
		    SELECT tahun_lkpd, 
		           '333331' AS kode, 
		           SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '1.') THEN la.jumlah_anggaran ELSE 0 END)-(SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '2.') THEN la.jumlah_anggaran ELSE 0 END)+SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '3.') THEN la.jumlah_anggaran ELSE 0 END)) AS anggaran, 
		           SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '1.') THEN la.jumlah_realisasi ELSE 0 END)-(SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '2.') THEN la.jumlah_realisasi ELSE 0 END)+SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '3.') THEN la.jumlah_realisasi ELSE 0 END)) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
		    SELECT tahun_lkpd, 
		           '444441' AS kode,
			   (SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '1.') THEN la.jumlah_anggaran ELSE 0 END)-(SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '2.') THEN la.jumlah_anggaran ELSE 0 END)+SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '3.') THEN la.jumlah_anggaran ELSE 0 END)))+ 
		           (SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.1.') THEN la.jumlah_anggaran ELSE 0 END)-SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.2.') THEN la.jumlah_anggaran ELSE 0 END)) AS anggaran,
		           (SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '1.') THEN la.jumlah_realisasi ELSE 0 END)-(SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '2.') THEN la.jumlah_realisasi ELSE 0 END)+SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '3.') THEN la.jumlah_realisasi ELSE 0 END)))+
		           (SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.1.') THEN la.jumlah_realisasi ELSE 0 END)-SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.2.') THEN la.jumlah_realisasi ELSE 0 END)) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
		    SELECT tahun_lkpd, 
		           '444440' AS kode,
		           (SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.1.') THEN la.jumlah_anggaran ELSE 0 END)-SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.2.') THEN la.jumlah_anggaran ELSE 0 END)) AS anggaran,
		           (SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.1.') THEN la.jumlah_realisasi ELSE 0 END)-SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.2.') THEN la.jumlah_realisasi ELSE 0 END)) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode     
            ) AS a
            LEFT JOIN kode_catatan AS kc ON a.kode = kc.`kode_catatan`";
    
// Add WHERE clause if a year is selected
if ($selected_year != '') {
    $sql .= " WHERE a.tahun_lkpd = ?";
}

$sql .= " ORDER BY a.tahun_lkpd, a.kode ASC";


// Prepare the statement
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

// Bind parameter if a year is selected
if ($selected_year != '') {
    $stmt->bind_param("s", $selected_year); // "s" for string, adjust if tahun_lkpd is integer
}

// Execute the statement
$stmt->execute();
$result = $stmt->get_result();

// --- Get unique years for the dropdown ---
$years_query = "SELECT DISTINCT tahun_lkpd FROM lkpd_apbd_lampiran_1 ORDER BY tahun_lkpd DESC";
$years_result = $conn->query($years_query);
$available_years = [];
if ($years_result->num_rows > 0) {
    while ($row = $years_result->fetch_assoc()) {
        $available_years[] = $row['tahun_lkpd'];
    }
}
?>

<h2>Daftar LKPD APBD</h2>

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
                <th style="display: none;">ID</th>
                <th style="text-align: center;">Tahun</th>
                <th style="text-align: center;">Kode</th>
                <th style="text-align: center;">Uraian</th>
                <th style="text-align: center;">Anggaran</th>
                <th style="text-align: center;">Realisasi</th>
                <th style="text-align: center;">Persentase</th>
                <th style="text-align: center;">Sisa Anggaran</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch_assoc()):
                $is_level_1_or_2 = ($row['id_kode_level'] == 1 || $row['id_kode_level'] == 2);

                $tahun_display = htmlspecialchars($row['tahun_lkpd']);
                $kode_display = htmlspecialchars($row['kode']);
                $uraian_display = htmlspecialchars($row['uraian']);
                $anggaran_display = htmlspecialchars(number_format($row['anggaran']??0, 2, ',', '.'));
                $realisasi_display = htmlspecialchars(number_format($row['realisasi']??0, 2, ',', '.'));
                $sisaanggaran_display = htmlspecialchars(number_format($row['sisa_anggaran']??0, 2, ',', '.'));
                $persentase_display = htmlspecialchars(number_format($row['persentase']??0, 2, ',', '.')) . '%';

                if ($is_level_1_or_2) {
                    $tahun_display = '<strong>' . $tahun_display . '</strong>';
                    $kode_display = '<strong>' . $kode_display . '</strong>';
                    $uraian_display = '<strong>' . $uraian_display . '</strong>';
                    $anggaran_display = '<strong>' . $anggaran_display . '</strong>';
                    $realisasi_display = '<strong>' . $realisasi_display . '</strong>';
                    $sisaanggaran_display = '<strong>' . $sisaanggaran_display . '</strong>';
                    $persentase_display = '<strong>' . $persentase_display . '</strong>';
                }
            ?>
                <tr>
                    <td style="display: none;"><?php echo htmlspecialchars($row['id_kode_level']); ?></td>
                    <td><?php echo $tahun_display; ?></td>
                    <td><?php echo $kode_display; ?></td>
                    <td><?php echo $uraian_display; ?></td>
                    <td style="text-align:right"><?php echo $anggaran_display; ?></td>
                    <td style="text-align:right"><?php echo $realisasi_display; ?></td>
                    <td style="text-align:right"><?php echo $persentase_display; ?></td>
                    <td style="text-align:right"><?php echo $sisaanggaran_display; ?></td>
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
