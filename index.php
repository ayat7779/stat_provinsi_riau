<link rel="stylesheet" href="templates/css/style.css">

<?php
session_start();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Tentukan judul halaman jika diperlukan
$pageTitle = "Manajemen Statistik Riau";

include_once 'config/database.php';
include_once 'templates/header.php';
include_once 'templates/menu.php';

$page = isset($_GET['page']) ? $_GET['page'] : 'apbd'; 

switch ($page) {
    case 'apbd':
        include_once 'views/apbd.php'; 
        break; 
    case 'lkpd_apbd': 
        include_once 'views/lkpd_apbd.php';
        break;
    case 'kinerja_keuangan': 
        include_once 'views/kinerja_keuangan.php';
        break;
    case 'kinerja_keuangan_detail': 
        include_once 'views/kinerja_keuangan_detail.php';
        break;
    default:
        include_once 'views/apbd.php';
        break;
}

include_once 'templates/footer.php';
