<?php
// Mulai session di awal file (untuk pesan sukses)
session_start();

// Aktifkan pelaporan error PHP untuk debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Sertakan file koneksi database
include 'config/database.php';

// Periksa apakah ID diterima melalui parameter GET dan tidak kosong
if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    // Ambil nilai ID dan bersihkan
    $id = trim($_GET["id"]);

    // Siapkan pernyataan DELETE
    $sql = "DELETE FROM kode_urut WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        // Bind parameter ID ke pernyataan yang disiapkan
        // 'i' menunjukkan bahwa parameter adalah integer
        $stmt->bind_param("i", $param_id);

        // Set nilai parameter
        $param_id = $id;

        // Jalankan pernyataan
        if ($stmt->execute()) {
            // Set pesan sukses di session
            $_SESSION['message'] = "Data berhasil dihapus!";
            // Redirect ke halaman utama setelah sukses
            header("location: index.php");
            exit(); // Pastikan script berhenti setelah redirect
        } else {
            // Jika eksekusi gagal, set pesan error
            error_log("Error executing delete query: " . $stmt->error);
            $_SESSION['message'] = "Error: Gagal menghapus data. Silakan coba lagi nanti atau cek log server.";
            header("location: index.php"); // Redirect kembali meskipun ada error
            exit();
        }
        // Tutup statement
        $stmt->close();
    } else {
        // Jika prepared statement gagal disiapkan
        error_log("Error preparing delete query: " . $conn->error);
        $_SESSION['message'] = "Error: Ada masalah saat menyiapkan query hapus.";
        header("location: index.php");
        exit();
    }
} else {
    // Jika ID tidak disediakan atau kosong, redirect ke halaman utama dengan pesan error
    $_SESSION['message'] = "Error: ID data tidak ditemukan untuk dihapus.";
    header("location: index.php");
    exit();
}

// Tutup koneksi database
$conn->close();
?>
