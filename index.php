<?php
// Mulai session di awal file (penting jika Anda menggunakan session untuk pesan sukses atau lainnya)
session_start();

// Sertakan file koneksi database
include 'config/database.php';
// Sertakan header HTML
include 'templates/header.php';

// Ambil data kode_urut dari database, join dengan kode_level.
// PENTING: Pastikan ku.id (ID dari tabel kode_urut) juga dipilih di sini.
$sql = "SELECT ku.id, ku.no_urut, ku.uraian, kl.nama_level
        FROM kode_urut ku
        INNER JOIN kode_level kl
        ON ku.id_kode_level = kl.id
        ORDER BY ku.no_urut ASC"; // Menggunakan ku.no_urut untuk pengurutan
$result = $conn->query($sql);
?>
        <nav>
            <a href="index.php">Home</a>
            <a href="apbd-kode-urut/create.php">Tambah Data</a>
        </nav>
        <hr>

<h2>Daftar Kode Urut APBD</h2>

<?php
// Cek apakah ada pesan sukses di session (dari create.php atau update.php)
if (isset($_SESSION['message'])): ?>
    <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $_SESSION['message']; ?>
    </div>
<?php
    // Hapus pesan dari session agar tidak muncul lagi setelah refresh
    unset($_SESSION['message']);
endif;

// Cek apakah ada pesan error dari redirect (misalnya dari update.php)
if (isset($_GET['error'])): ?>
    <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        Error: <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>


<?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th style="display: none;">ID</th> <!-- Kolom ID disembunyikan dengan CSS -->
                <th>Kode</th>
                <th>Uraian</th>
                <th>Level</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td style="display: none;"><?php echo htmlspecialchars($row['id']); ?></td>
                    <td><?php echo htmlspecialchars($row['no_urut']); ?></td>
                    <td><?php echo htmlspecialchars($row['uraian']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_level']); ?></td>
                    <td class="actions">
                        <!-- PENTING: Pastikan $row['id'] memiliki nilai di sini -->
                        <a href="apbd-kode-urut/update.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="edit">Edit</a>
                        <a href="apbd-kode-urut/delete.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
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
