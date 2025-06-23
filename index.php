                    <ul>
                        <li><a href="index.php?page=apbd">Data APBD</a></li>
                        <li><a href="index.php?page=lkpd_apbd">Data LKPD APBD</a></li>
                    </ul>
                    <?php
                    session_start();

                    ini_set('display_errors', 1);
                    ini_set('display_startup_errors', 1);
                    error_reporting(E_ALL);

                    include 'config/database.php';
                    include 'templates/header.php';
                    include 'templates/menu.php';

                    $page = isset($_POST['page']) ? $_POST['page'] : (isset($_GET['page']) ? $_GET['page'] : 'apbd');

                    switch ($page) {
                        case 'apbd':
                            include 'views/apbd.php';
                            break;
                        case 'lkpd_apbd':
                            include 'views/lkpd_apbd.php';
                            break;
                        default:
                            include 'views/apbd.php';
                            break;
                    }
                    
                    include 'templates/footer.php';
