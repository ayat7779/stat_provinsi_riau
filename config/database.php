<?php
$host = 'localhost'; 
$user = 'root';      
$pass = '';          
$db_name = 'stat_provinsi_riau'; 

// Buat koneksi
$conn = new mysqli($host, $user, $pass, $db_name);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>