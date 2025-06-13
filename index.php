<?php
session_start();
include 'config/database.php';
include 'templates/header.php';
include 'templates/menu.php';
?>

<h1>HALAMAN UTAMA</h1>


<?php
$conn->close();
include 'templates/footer.php';
?>
