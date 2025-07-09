<?php
session_start();

include '../../config/database.php';
include '../../templates/header.php';

$selected_year = '';
// Check if a year is selected from the dropdown
if (isset($_GET['tahun']) && $_GET['tahun'] != '') {
    $selected_year = $_GET['tahun'];
}

$where_clause = "";
if ($selected_year != '') {
    $where_clause = " WHERE tahun= ?";
}

// Build the SQL query
$sql = "SELECT a.id AS id, a.tahun AS tahun, b.no_urut AS kode, b.uraian AS nama_kode, jumlah_anggaran, jumlah_perubahan, c.nama_level AS level, d.akronim AS jenis_apbd  
        FROM apbd_lampiran_1 AS a 
        LEFT JOIN kode_urut AS b ON a.id_kode_urut = b.id
        LEFT JOIN kode_level AS c ON c.id = b.id_kode_level
        LEFT JOIN jenis_apbd AS d ON d.id = a.id_jenis_apbd
        " . $where_clause . "
        ORDER BY tahun, kode DESC";

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
    <table>
        <thead>
            <tr>
                <th style="display: none;">ID</th>
                <th>Tahun</th>
                <th>Kode</th>
                <th>Nama Kode</th>
                <th style="text-align: center;">Anggaran</th>
                <th style="text-align: center;">Perubahan</th>
                <th style="text-align: center;">Selisih</th>
                <th style="text-align: center;">Tahapan</th>
                <th style="text-align: center;">Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch_assoc()):
            ?>
                <tr>
                    <td style="display: none;"><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['tahun']); ?></td>
                    <td><?php echo htmlspecialchars($row['kode']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_kode']); ?></td>
                    <td style="text-align:right"><?php echo htmlspecialchars(number_format($row['jumlah_anggaran'] ?? 0, 2, ',', '.')); ?></td>
                    <td style="text-align:right"><?php echo htmlspecialchars(number_format($row['jumlah_perubahan'] ?? 0, 2, ',', '.')); ?></td>
                    <td style="text-align:right"><?php echo htmlspecialchars(number_format(($row['jumlah_perubahan'] - $row['jumlah_anggaran']) ?? 0, 2, ',', '.')); ?></td>
                    <td><?php echo htmlspecialchars($row['jenis_apbd']); ?></td>
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

<?php
$stmt->close();
$conn->close();
include '../../templates/footer.php';
?>