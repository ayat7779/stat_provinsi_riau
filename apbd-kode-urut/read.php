<?php
// Memulai session di awal file (penting untuk pesan sukses/error)
session_start();

// Menyertakan file koneksi database
include 'config/database.php';
// Menyertakan header HTML
include 'templates/header.php';

// Query SQL untuk mengambil data kode_urut beserta nama level dari tabel kode_level
// Menggunakan JOIN untuk menggabungkan dua tabel berdasarkan id_kode_level
// Memilih id dari kode_urut (ku.id) untuk keperluan edit dan hapus
$sql = "SELECT ku.id, ku.no_urut, ku.uraian, kl.nama_level
        FROM kode_urut ku
        INNER JOIN kode_level kl
        ON ku.id_kode_level = kl.id
        ORDER BY ku.no_urut DESC"; // Mengurutkan berdasarkan nomor urut secara descending
$result = $conn->query($sql); // Menjalankan query

?>

<h2>Daftar Kode Urut APBD</h2>

<?php
// Memeriksa apakah ada pesan sukses yang disimpan dalam session
if (isset($_SESSION['message'])): ?>
    <div style="background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        <?php echo $_SESSION['message']; ?>
    </div>
<?php
    // Menghapus pesan dari session agar tidak muncul lagi setelah refresh halaman
    unset($_SESSION['message']);
endif;

// Memeriksa apakah ada pesan error yang dikirimkan melalui URL (misalnya dari update.php atau delete.php)
if (isset($_GET['error'])): ?>
    <div style="background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
        Error: <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>

<?php
// Memeriksa apakah ada baris data yang ditemukan dari query
if ($result->num_rows > 0):
?>
    <table>
        <thead>
            <tr>
                <th style="display: none;">ID</th> <!-- Kolom ID disembunyikan menggunakan CSS -->
                <th>Kode</th>
                <th>Uraian</th>
                <th>Level</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Melakukan looping untuk menampilkan setiap baris data
            while ($row = $result->fetch_assoc()):
            ?>
                <tr>
                    <td style="display: none;"><?php echo htmlspecialchars($row['id']); ?></td> <!-- Nilai ID disembunyikan -->
                    <td><?php echo htmlspecialchars($row['no_urut']); ?></td>
                    <td><?php echo htmlspecialchars($row['uraian']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_level']); ?></td>
                    <td class="actions">
                        <!-- Tautan untuk mengedit data, mengirim ID melalui parameter URL -->
                        <a href="update.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="edit">Edit</a>
                        <!-- Tautan untuk menghapus data, mengirim ID melalui parameter URL dan konfirmasi JavaScript -->
                        <a href="delete.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="delete" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <!-- Pesan jika tidak ada data ditemukan di database -->
    <p>Belum ada data.</p>
<?php endif; ?>

<?php
// Menutup koneksi database
$conn->close();
// Menyertakan footer HTML
include 'templates/footer.php';
?>
