<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include '../config/database.php';

if (isset($_GET["id"]) && !empty(trim($_GET["id"]))) {
    $id = trim($_GET["id"]);

    $sql = "DELETE FROM lkpd_apbd_lampiran_1 WHERE id = ?";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $param_id);

        $param_id = $id;

        if ($stmt->execute()) {
            $_SESSION['message'] = "Data berhasil dihapus!";            
            header("location: read.php");
            exit();
        } else {
            error_log("Error executing delete query: " . $stmt->error);
            $_SESSION['message'] = "Error: Gagal menghapus data. Silakan coba lagi nanti atau cek log server.";
            header("location: read.php");
            exit();
        }
        $stmt->close();
    } else {
        error_log("Error preparing delete query: " . $conn->error);
        $_SESSION['message'] = "Error: Ada masalah saat menyiapkan query hapus.";
        header("location: read.php");
        exit();
    }
} else {
    $_SESSION['message'] = "Error: ID data tidak ditemukan untuk dihapus.";
    header("location: read.php");
    exit();
}

$conn->close();
?>