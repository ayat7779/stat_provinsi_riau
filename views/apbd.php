<?php
//include 'config/database.php';

$selected_year = '';
// Check if a year is selected from the dropdown
if (isset($_GET['tahun']) && $_GET['tahun'] != '') {
    $selected_year = $_GET['tahun'];
}

$sql = "SELECT tahun, kode, b.uraian, anggaran, perubahan,   
    IFNULL(ROUND((perubahan - anggaran), 2), 0) AS bertambah_berkurang, 
    IFNULL(ROUND(((perubahan - anggaran) / anggaran) * 100, 2), 0) AS persentase, 
    c.akronim AS jenis_apbd,
    b.id_kode_level, nama_level
FROM (
    -- AKUN
    SELECT tahun, LEFT(no_urut,2) AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_perubahan) AS perubahan, id_jenis_apbd
    FROM apbd_lampiran_1 AS a
    INNER JOIN kode_urut AS b ON a.id_kode_urut=b.id
    INNER JOIN jenis_apbd AS c ON a.id_jenis_apbd=c.id
    GROUP BY tahun, kode, id_jenis_apbd
    UNION ALL
    -- KELOMPOK
    SELECT tahun, LEFT(no_urut,4) AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_perubahan) AS perubahan, id_jenis_apbd
    FROM apbd_lampiran_1 AS a
    INNER JOIN kode_urut AS b ON a.id_kode_urut=b.id
    INNER JOIN jenis_apbd AS c ON a.id_jenis_apbd=c.id
    GROUP BY tahun, kode, id_jenis_apbd
    UNION ALL
    -- JENIS
    SELECT tahun, no_urut AS kode, SUM(jumlah_anggaran) AS anggaran, SUM(jumlah_perubahan) AS perubahan, id_jenis_apbd
    FROM apbd_lampiran_1 AS a
    INNER JOIN kode_urut AS b ON a.id_kode_urut=b.id
    INNER JOIN jenis_apbd AS c ON a.id_jenis_apbd=c.id
    GROUP BY tahun, kode, id_jenis_apbd
    UNION ALL
    -- surplus defisit
    SELECT tahun, '5.5.' AS kode, 
    SUM(CASE WHEN (LEFT(b.no_urut,2)= '4.') THEN a.jumlah_anggaran ELSE 0 END)
    -SUM(CASE WHEN (LEFT(b.no_urut,2)= '5.') THEN a.jumlah_anggaran ELSE 0 END)AS anggaran, 
    SUM(CASE WHEN (LEFT(b.no_urut,2)= '4.') THEN a.jumlah_perubahan ELSE 0 END)
    -SUM(CASE WHEN (LEFT(b.no_urut,2)= '5.') THEN a.jumlah_perubahan ELSE 0 END) AS perubahan,
    id_jenis_apbd 
    FROM apbd_lampiran_1 AS a 
    INNER JOIN kode_urut AS b ON a.id_kode_urut=b.id
    INNER JOIN jenis_apbd AS c ON a.id_jenis_apbd=c.id
    GROUP BY tahun, kode, id_jenis_apbd
    UNION ALL
    -- pembiayaan neto
    SELECT tahun, '6.2.9' AS kode, 
    SUM(CASE WHEN (LEFT(b.no_urut,4)= '6.1.') THEN a.jumlah_anggaran ELSE 0 END)
    -SUM(CASE WHEN (LEFT(b.no_urut,4)= '6.2.') THEN a.jumlah_anggaran ELSE 0 END)AS anggaran, 
    SUM(CASE WHEN (LEFT(b.no_urut,4)= '6.1.') THEN a.jumlah_perubahan ELSE 0 END)
    -SUM(CASE WHEN (LEFT(b.no_urut,4)= '6.2.') THEN a.jumlah_perubahan ELSE 0 END) AS perubahan,
    id_jenis_apbd 
    FROM apbd_lampiran_1 AS a 
    INNER JOIN kode_urut AS b ON a.id_kode_urut=b.id
    INNER JOIN jenis_apbd AS c ON a.id_jenis_apbd=c.id
    GROUP BY tahun, kode, id_jenis_apbd
    UNION ALL
    -- SILPA
    SELECT tahun, '6.3.' AS kode, 
    (SUM(CASE WHEN (LEFT(b.no_urut,2)= '4.') THEN a.jumlah_anggaran ELSE 0 END)
    -SUM(CASE WHEN (LEFT(b.no_urut,2)= '5.') THEN a.jumlah_anggaran ELSE 0 END))+
    (SUM(CASE WHEN (LEFT(b.no_urut,4)= '6.1.') THEN a.jumlah_anggaran ELSE 0 END)
    -SUM(CASE WHEN (LEFT(b.no_urut,4)= '6.2.') THEN a.jumlah_anggaran ELSE 0 END)) AS anggaran, 
    (SUM(CASE WHEN (LEFT(b.no_urut,2)= '4.') THEN a.jumlah_perubahan ELSE 0 END)
    -SUM(CASE WHEN (LEFT(b.no_urut,2)= '5.') THEN a.jumlah_perubahan ELSE 0 END))+
    (SUM(CASE WHEN (LEFT(b.no_urut,4)= '6.1.') THEN a.jumlah_perubahan ELSE 0 END)
    -SUM(CASE WHEN (LEFT(b.no_urut,4)= '6.2.') THEN a.jumlah_perubahan ELSE 0 END)) AS perubahan,
    id_jenis_apbd 
    FROM apbd_lampiran_1 AS a 
    INNER JOIN kode_urut AS b ON a.id_kode_urut=b.id
    INNER JOIN jenis_apbd AS c ON a.id_jenis_apbd=c.id
    GROUP BY tahun, kode, id_jenis_apbd
) AS a
INNER JOIN kode_urut AS b ON a.kode = b.`no_urut`
INNER JOIN jenis_apbd AS c ON a.id_jenis_apbd = c.`id`
LEFT JOIN kode_level AS d ON b.id_kode_level = d.`id`
WHERE b.id_kode_level IN (1, 2, 3)"; // Filter for specific levels

// Add WHERE clause if a year is selected
if ($selected_year != '') {
    $sql .= " AND a.tahun = ?";
}

$sql .= " ORDER BY tahun, b.no_urut  ASC";


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
$years_query = "SELECT DISTINCT tahun FROM apbd_lampiran_1 ORDER BY tahun DESC";
$years_result = $conn->query($years_query);
$available_years = [];
if ($years_result->num_rows > 0) {
    while ($row = $years_result->fetch_assoc()) {
        $available_years[] = $row['tahun'];
    }
}
?>

<h2 style="text-align: center;">Lampiran I - APBD</h2>

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
    <div>
        <table>
            <thead>
                <tr>
                    <th style="display: none;">ID</th>
                    <th style="text-align: center;">Tahun</th>
                    <th style="text-align: center;">Kode</th>
                    <th style="text-align: center;">Uraian</th>
                    <th style="text-align: center;">Anggaran</th>
                    <th style="text-align: center;">Perubahan</th>
                    <th style="text-align: center;">Bertambah/Berkurang</th>
                    <th style="text-align: center;">Persentase</th>
                    <th style="text-align: center;">Level</th>
                    <th style="text-align: center;">Jenis APBD</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = $result->fetch_assoc()):
                    $is_level_1_or_2 = ($row['id_kode_level'] == 1 || $row['id_kode_level'] == 2);

                    $tahun_display = htmlspecialchars($row['tahun']);
                    $kode_display = htmlspecialchars($row['kode']);
                    $uraian_display = htmlspecialchars($row['uraian']);
                    $anggaran_display = htmlspecialchars(number_format($row['anggaran'] ?? 0, 2, ',', '.'));
                    $perubahan_display = htmlspecialchars(number_format($row['perubahan'] ?? 0, 2, ',', '.'));
                    $bertambahberkurang_display = htmlspecialchars(number_format($row['bertambah_berkurang'] ?? 0, 2, ',', '.'));
                    $persentase_display = htmlspecialchars(number_format($row['persentase'] ?? 0, 2, ',', '.')) . '%';
                    $namalevel_display = htmlspecialchars($row['nama_level']);
                    $jenisapbd_display = htmlspecialchars($row['jenis_apbd']);

                    if ($is_level_1_or_2) {
                        $tahun_display = '<strong>' . $tahun_display . '</strong>';
                        $kode_display = '<strong>' . $kode_display . '</strong>';
                        $uraian_display = '<strong>' . $uraian_display . '</strong>';
                        $anggaran_display = '<strong>' . $anggaran_display . '</strong>';
                        $perubahan_display = '<strong>' . $perubahan_display . '</strong>';
                        $bertambahberkurang_display = '<strong>' . $bertambahberkurang_display . '</strong>';
                        $persentase_display = '<strong>' . $persentase_display . '</strong>';
                        $namalevel_display = '<strong>' . $namalevel_display . '</strong>';
                        $jenisapbd_display = '<strong>' . $jenisapbd_display . '</strong>';
                    }
                ?>
                    <tr>
                        <td style="display: none;"><?php echo htmlspecialchars($row['id_kode_level']); ?></td>
                        <td><?php echo $tahun_display; ?></td>
                        <td><?php echo $kode_display; ?></td>
                        <td><?php echo $uraian_display; ?></td>
                        <td style="text-align:right"><?php echo $anggaran_display; ?></td>
                        <td style="text-align:right"><?php echo $perubahan_display; ?></td>
                        <td style="text-align:right"><?php echo $bertambahberkurang_display; ?></td>
                        <td style="text-align:right"><?php echo $persentase_display; ?></td>
                        <td style="text-align:left"><?php echo $namalevel_display; ?></td>
                        <td style="text-align:right"><?php echo $jenisapbd_display; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php else: ?>
    <p>Belum ada data.</p>
<?php endif; ?>

<?php
$stmt->close();
$conn->close();
?>