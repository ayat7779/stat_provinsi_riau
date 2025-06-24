<?php
session_start();

include '../../config/database.php';
include '../../templates/header.php';

$sql = "SELECT * FROM kode_level ORDER BY id";
$result = $conn->query($sql);

?>

<h2>Daftar Level Kode</h2>
<div class="button-group">
    <button><a href="../../index.php">Home</a></button>
    <button><a href="create.php">Tambah Data</a></button>
</div>

<?php
if (isset($_SESSION['message'])): ?>
    <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $_SESSION['message']; ?>
    </div>
<?php
    unset($_SESSION['message']);
endif;

if (isset($_GET['error'])): ?>
    <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
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
                <th>Nama Level</th>
                <th>Akronim</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = $result->fetch_assoc()):
            ?>
                <tr>
                    <td style="display: none;"><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_level']); ?></td>
                    <td><?php echo htmlspecialchars($row['akronim']); ?></td>
                    <td class="actions">
                        <a href="update.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="edit">Edit</a>
                        <a href="delete.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Belum ada data.</p>
<?php endif; ?>

<?php
$conn->close();
include '../../templates/footer.php';
?>
