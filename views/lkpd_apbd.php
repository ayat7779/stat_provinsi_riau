<?php
//include 'config/database.php';

$selected_year = '';
// Check if a year is selected from the dropdown
if (isset($_GET['tahun_lkpd']) && $_GET['tahun_lkpd'] != '') {
    $selected_year = $_GET['tahun_lkpd'];
}

$where_clause = "";
$param_count = 0; // Initialize a counter for parameters
if ($selected_year != '') {
    $where_clause = " WHERE tahun_lkpd = ?";
    // Explicitly set the count to 18 based on manual inspection of UNION ALL blocks.
    // This value *must* exactly match the number of times `tahun_lkpd = ?` appears in the final SQL string.
    $param_count = 18;
}

// Prepare the SQL query
$sql = "SELECT a.tahun_lkpd AS tahun, a.kode, kc.uraian, a.anggaran, a.realisasi, 
CASE WHEN a.anggaran = 0 THEN 0 ELSE (a.anggaran-a.realisasi) END AS sisa_anggaran, 
CASE WHEN a.anggaran = 0 THEN 0 ELSE (a.realisasi/a.anggaran)*100 END AS persentase, kl.nama_level, kc.id_kode_level
FROM (
	-- AKUN
	SELECT tahun_lkpd, LEFT(kode_catatan,2) AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_realisasi) AS realisasi 
        FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id`
	GROUP BY tahun_lkpd, kode
	UNION ALL
	-- KELOMPOK
	SELECT tahun_lkpd, LEFT(kode_catatan,4) AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_realisasi) AS realisasi  
        FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id`
	GROUP BY tahun_lkpd, kode 
	UNION ALL
	-- JENIS
	SELECT tahun_lkpd, kode_catatan AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_realisasi) AS realisasi  
        FROM lkpd_apbd_lampiran_1 la INNER JOIN kode_catatan kc ON la.`id_kode_catatan`=kc.`id`
	GROUP BY tahun_lkpd, kode
	UNION ALL
	-- Jumlah PAD
        SELECT tahun_lkpd, '1.1111' AS kode, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- Jumlah TRANSFER DANA PERIMBANGAN
        SELECT tahun_lkpd, '1.2222' AS kode, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.2.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.2.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- JUMLAH PENDAPATAN TRANSFER PEMERINTAH PUSAT-LAINNYA
        SELECT tahun_lkpd, '1.3333' AS kode, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.3.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.3.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- JUMLAH PENDAPATAN TRANSFER
        SELECT tahun_lkpd, '1.3334' AS kode, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.2.') THEN la.jumlah_anggaran ELSE 0 END)
		    +SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.3.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran,
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.2.') THEN la.jumlah_realisasi ELSE 0 END) 
		    +SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.3.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- JUMLAH LAIN-LAIN PENDAPATAN DAERAH YANG SAH
        SELECT tahun_lkpd, '1.4444' AS kode, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.4.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.4.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- JUMLAH PENDAPATAN
        SELECT tahun_lkpd, '1.5555' AS kode, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_anggaran ELSE 0 END)		    
		    +SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.2.') THEN la.jumlah_anggaran ELSE 0 END)
		    +SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.3.') THEN la.jumlah_anggaran ELSE 0 END)
		    +SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.4.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran,
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.1.') THEN la.jumlah_realisasi ELSE 0 END)		    
		    +SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.2.') THEN la.jumlah_realisasi ELSE 0 END) 
		    +SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.3.') THEN la.jumlah_realisasi ELSE 0 END)
		    +SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '1.4.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- Jumlah Belanja Operasi
        SELECT tahun_lkpd, '2.1111' AS kode, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '2.1.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '2.1.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- Jumlah Belanja Modal
        SELECT tahun_lkpd, '2.2222' AS kode, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '2.2.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '2.2.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- Jumlah Belanja Tak terduga
        SELECT tahun_lkpd, '2.3333' AS kode, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '2.3.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '2.3.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- JUMLAH TRANSFER-BAGI HASIL PENDAPATAN KE KAB/KOTA
        SELECT tahun_lkpd, '3.1111' AS kode, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '3.1.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '3.1.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- JUMLAH TRANSFER-BANTUAN KEUANGAN
        SELECT tahun_lkpd, '3.2222' AS kode, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '3.2.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '3.2.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- JUMLAH TRANSFER
        SELECT tahun_lkpd, '3.2223' AS kode, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '3.1.') THEN la.jumlah_anggaran ELSE 0 END)		    
		    +SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '3.2.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '3.1.') THEN la.jumlah_realisasi ELSE 0 END)
		    +SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '3.2.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- Jumlah Belanja dan Transfer
        SELECT tahun_lkpd, '3.2224' AS kode, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '2.') THEN la.jumlah_anggaran ELSE 0 END)
		    +SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '3.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '2.') THEN la.jumlah_realisasi ELSE 0 END)
		    +SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '3.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- Surplus/Defisit
	SELECT tahun_lkpd, '3.2225' AS kode, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '1.') THEN la.jumlah_anggaran ELSE 0 END)-
            (SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '2.') THEN la.jumlah_anggaran ELSE 0 END)+
            SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '3.') THEN la.jumlah_anggaran ELSE 0 END)) AS anggaran, 
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '1.') THEN la.jumlah_realisasi ELSE 0 END)-
            (SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '2.') THEN la.jumlah_realisasi ELSE 0 END)+
            SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '3.') THEN la.jumlah_realisasi ELSE 0 END)) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- JUMLAH PENERIMAAN PEMBIAYAAN		    
	SELECT tahun_lkpd, '4.1111' AS kode,
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.1.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran,
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.1.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- JUMLAH PENGELUARAN PEMBIAYAAN		    
	SELECT tahun_lkpd, '4.2222' AS kode,
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.2.') THEN la.jumlah_anggaran ELSE 0 END) AS anggaran,
		    SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.2.') THEN la.jumlah_realisasi ELSE 0 END) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
		    UNION ALL
	-- Pembiayaan Netto		    
	SELECT tahun_lkpd, '4.2223' AS kode,
		    (SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.1.') THEN la.jumlah_anggaran ELSE 0 END)-
            SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.2.') THEN la.jumlah_anggaran ELSE 0 END)) AS anggaran,
		    (SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.1.') THEN la.jumlah_realisasi ELSE 0 END)-
            SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.2.') THEN la.jumlah_realisasi ELSE 0 END)) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
	UNION ALL
	-- SILPA
	SELECT tahun_lkpd, '4.2224' AS kode,
			(SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '1.') THEN la.jumlah_anggaran ELSE 0 END)-
            (SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '2.') THEN la.jumlah_anggaran ELSE 0 END)+
            SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '3.') THEN la.jumlah_anggaran ELSE 0 END)))+ 
		    (SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.1.') THEN la.jumlah_anggaran ELSE 0 END)-
            SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.2.') THEN la.jumlah_anggaran ELSE 0 END)) AS anggaran,
		    (SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '1.') THEN la.jumlah_realisasi ELSE 0 END)-
            (SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '2.') THEN la.jumlah_realisasi ELSE 0 END)+
            SUM(CASE WHEN (LEFT(kc.kode_catatan,2)= '3.') THEN la.jumlah_realisasi ELSE 0 END)))+
		    (SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.1.') THEN la.jumlah_realisasi ELSE 0 END)-
            SUM(CASE WHEN (LEFT(kc.kode_catatan,4)= '4.2.') THEN la.jumlah_realisasi ELSE 0 END)) AS realisasi 
		    FROM lkpd_apbd_lampiran_1 la 
		    INNER JOIN kode_catatan kc 
		    ON la.`id_kode_catatan`=kc.`id`
		    GROUP BY tahun_lkpd, kode
            ) AS a
            LEFT JOIN kode_catatan AS kc ON a.kode = kc.`kode_catatan`
            LEFT JOIN kode_level AS kl ON kl.id = kc.id_kode_level
            ". $where_clause ."
            ORDER BY a.tahun_lkpd, a.kode ASC";

// --- PENANGANAN BINDING PARAMETER ---
if ($selected_year != '') {
    // --- IMPORTANT: Calculate the actual number of '?' in the SQL query ---
    // This is the most robust way to ensure the count is correct.
    $actual_param_count = substr_count($sql, '?');

    // Make sure our expected param_count matches the actual count.
    // If you always intend to have 18, then perhaps your SQL needs trimming.
    // But if you've added more blocks, $actual_param_count is king.
    // Given the previous error showed 18 binds and 21 subqueries,
    // let's assume the actual count of '?' in the constructed $sql is the source of truth.
    if ($actual_param_count === 0) {
        // This case should ideally not happen if $selected_year is set
        // and $where_clause is being appended.
        // It might indicate an issue if $where_clause is empty for some reason.
        // Or if you only want to bind if the count is > 0
    }

    $types = str_repeat("s", $actual_param_count); // Use the dynamically counted parameters

    // Create an array to hold references to the parameter
    $bind_params = [];
    $bind_params[] = $types; // First argument is the types string

    // Loop to add $selected_year by reference for each placeholder
    for ($i = 0; $i < $actual_param_count; $i++) { // Use actual_param_count here
        $bind_params[] = &$selected_year; // Pass by reference
    }

    // Prepare the statement (should be done before binding)
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing statement for LKPD APBD: " . $conn->error);
        die("Error preparing statement for LKPD APBD: " . $conn->error);
    }

    // Call bind_param using call_user_func_array with references
    $bind_success = call_user_func_array([$stmt, 'bind_param'], $bind_params);

    if (!$bind_success) {
        error_log("Error binding parameters for LKPD APBD: " . $stmt->error);
        die("Error binding parameters for LKPD APBD: " . $stmt->error);
    }
} else {
    // If no year is selected, prepare the statement without binding parameters
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        error_log("Error preparing statement for LKPD APBD (no year selected): " . $conn->error);
        die("Error preparing statement for LKPD APBD (no year selected): " . $conn->error);
    }
}

// Execute the statement
$execute_success = $stmt->execute();
if (!$execute_success) {
    error_log("Error executing statement for LKPD APBD: " . $stmt->error);
    die("Error executing statement for LKPD APBD: " . $stmt->error);
}
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

<h2 style="text-align: center;">Lampiran I - LKPD</h2>

<form method="GET" action="index.php">
    <label for="tahun_lkpd">Filter Tahun:</label>
    <select name="tahun_lkpd" id="tahun_lkpd" onchange="this.form.submit()">
        <option value="">-- Semua Tahun --</option>
        <?php foreach ($available_years as $year): ?>
            <option value="<?php echo htmlspecialchars($year); ?>"
                <?php echo ($selected_year == $year) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($year); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="hidden" name="page" value="lkpd_apbd">
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
    <div>
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
                    <th style="text-align: center;">Nama Level</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = $result->fetch_assoc()):
                    $is_level_1_or_2 = ($row['id_kode_level'] == 1 || $row['id_kode_level'] == 2);

                    $tahun_display          = htmlspecialchars($row['tahun']);
                    $kode_display           = htmlspecialchars($row['kode']);
                    $uraian_display         = htmlspecialchars($row['uraian']);
                    $anggaran_display       = htmlspecialchars(number_format($row['anggaran'] ?? 0, 2, ',', '.'));
                    $realisasi_display      = htmlspecialchars(number_format($row['realisasi'] ?? 0, 2, ',', '.'));
                    $sisaanggaran_display   = htmlspecialchars(number_format($row['sisa_anggaran'] ?? 0, 2, ',', '.'));
                    $persentase_display     = htmlspecialchars(number_format($row['persentase'] ?? 0, 2, ',', '.')) . '%';
                    $namalevel_display      = htmlspecialchars($row['nama_level']);

                    if ($is_level_1_or_2) {
                        $tahun_display          = '<strong>' . $tahun_display . '</strong>';
                        $kode_display           = '<strong>' . $kode_display . '</strong>';
                        $uraian_display         = '<strong>' . $uraian_display . '</strong>';
                        $anggaran_display       = '<strong>' . $anggaran_display . '</strong>';
                        $realisasi_display      = '<strong>' . $realisasi_display . '</strong>';
                        $sisaanggaran_display   = '<strong>' . $sisaanggaran_display . '</strong>';
                        $persentase_display     = '<strong>' . $persentase_display . '</strong>';
                        $namalevel_display      = '<strong>' . $namalevel_display . '</strong>';
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
                        <td><?php echo $namalevel_display; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>Belum ada data.</p>
<?php endif; ?>

<?php
if (isset($stmt) && $stmt instanceof mysqli_stmt) {
    $stmt->close();
}
//$conn->close();
?>