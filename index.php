<?php
include 'config/database.php';
include 'templates/header.php';

$sql = "SELECT ku.no_urut, ku.uraian, kl.nama_level 
        FROM kode_urut ku
        INNER JOIN kode_level kl 
        ON ku.id_kode_level = kl.id
        ORDER BY no_urut";
$result = $conn->query($sql);
?>

<h2>Daftar Kode Urut APBD</h2>

<?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Uraian</th>
                <th>level</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['no_urut']; ?></td>
                    <td><?php echo $row['uraian']; ?></td>
                    <td><?php echo $row['nama_level']; ?></td>
                    <td class="actions">
                        <a href="update.php?id=<?php echo $row['id']; ?>" class="edit">Edit</a>
                        <a href="delete.php?id=<?php echo $row['id']; ?>" class="delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
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
include 'templates/footer.php';
?>