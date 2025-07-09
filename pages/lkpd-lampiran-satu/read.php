<?php
session_start();

include '../../config/database.php';
include '../../templates/header.php';

$selected_year = '';
// Check if a year is selected from the dropdown
if (isset($_GET['tahun']) && $_GET['tahun'] != '') {
    $selected_year = $_GET['tahun'];
}

// Build the SQL query
$sql = "SELECT lk.id, lk.tahun_lkpd, kc.kode_catatan, kc.uraian, lk.jumlah_anggaran, lk.jumlah_realisasi, (lk.jumlah_realisasi/lk.jumlah_anggaran)*100 AS persentase 
        FROM lkpd_apbd_lampiran_1 AS lk
        INNER JOIN kode_catatan AS kc 
        ON lk.id_kode_catatan=kc.id";

// Add WHERE clause if a year is selected
if ($selected_year != '') {
    $sql .= " WHERE lk.tahun_lkpd = ?";
}

$sql .= " ORDER BY lk.tahun_lkpd, kc.kode_catatan DESC";

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

<h2 style="text-align: center;">Lampiran I - LKPD</h2>

<div class="button-group">
    <button><a href="../../index.php">Home</a></button>
    <button><a href="create.php">Tambah Data</a></button>
</div>

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
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th style="display: none;">ID</th>
                    <th>Tahun</th>
                    <th>Catatan</th>
                    <th>Uraian</th>
                    <th style="text-align: center;">Anggaran</th>
                    <th style="text-align: center;">Realisasi</th>
                    <th style="text-align: center;">Persentase</th>
                    <th style="text-align: center;">Sisa Anggaran</th>
                    <th style="text-align: center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = $result->fetch_assoc()):
                ?>
                    <tr>
                        <td style="display: none;"><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['tahun_lkpd']); ?></td>
                        <td><?php echo htmlspecialchars($row['kode_catatan']); ?></td>
                        <td><?php echo htmlspecialchars($row['uraian']); ?></td>
                        <td style="text-align:right"><?php echo htmlspecialchars(number_format($row['jumlah_anggaran'] ?? 0, 2, ',', '.')); ?></td>
                        <td style="text-align:right"><?php echo htmlspecialchars(number_format($row['jumlah_realisasi'] ?? 0, 2, ',', '.')); ?></td>
                        <td style="text-align:right"><?php echo htmlspecialchars(number_format($row['persentase'] ?? 0, 2, ',', '.')) . '%'; ?></td>
                        <td style="text-align:right"><?php echo htmlspecialchars(number_format($row['jumlah_anggaran'] - $row['jumlah_realisasi'], 2, ',', '.')); ?></td>
                        <td class="actions">
                            <a href="update.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="edit">Edit</a>
                            <a href="delete.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="delete"
                                onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>Belum ada data.</p>
    <?php endif; ?>
    </div>

    <?php
    $stmt->close();
    $conn->close();
    include '../../templates/footer.php';
    ?>