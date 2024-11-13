<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crowdsec Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap CSS -->
</head>
<body class="bg-light">
    
    <?php include '../views/navbar.php'; ?>

    <div class="container mt-4">
        <?php
            include '../models/db.php';
            $dbconn = connect_db();

            $limit = 15; 
            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
            $offset = ($page - 1) * $limit;

            $total_machines_query = "SELECT COUNT(*) AS total FROM machines";
            $total_machines_result = pg_query($dbconn, $total_machines_query);
            $total_machines_row = pg_fetch_assoc($total_machines_result);
            $total_machines = intval($total_machines_row['total']);

            $total_pages = ceil($total_machines / $limit);

            $query_machines = "SELECT * FROM machines";
            $rs_machines = pg_query($dbconn, $query_machines);

            if (!$rs_machines) {
                echo json_encode(['error' => 'Error executing query: ' . pg_last_error()]);
                pg_close($dbconn);
                exit();
            }

            echo '<div class="table-responsive">';
            echo '<table class="table table-bordered table-hover">';
            echo '<thead class="table-dark">';
            echo '<tr>';
            echo '<th>Name</th>';
            echo '<th>IP Address</th>';
            echo '<th>Status</th>';
            echo '<th>OS Version</th>';
            echo '<th>Version</th>';
            echo '<th>Last Update</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while ($row = pg_fetch_assoc($rs_machines)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['machine_id']) . '</td>';
                echo '<td>' . htmlspecialchars($row['ip_address']) . '</td>';
                echo '<td>' . htmlspecialchars($row['is_validated']) . '</td>';
                echo '<td>' . htmlspecialchars($row['osname']) . ' ' . htmlspecialchars($row['osversion']) . '</td>';
                echo '<td>' . htmlspecialchars($row['version']) . '</td>';
                echo '<td>' . htmlspecialchars($row['last_heartbeat']) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            pg_free_result($rs_machines);
            pg_close($dbconn);
        ?>

        <!-- Pagination controls -->
        <nav aria-label="Page navigation" class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a href="?page=<?= $page - 1 ?>" class="page-link">Previous</a>
                    </li>
                <?php endif; ?>

                <?php
                $page_range = 5;
                $start_page = max(1, $page - floor($page_range / 2));
                $end_page = min($total_pages, $start_page + $page_range - 1);

                if ($end_page - $start_page < $page_range - 1) {
                    $start_page = max(1, $end_page - $page_range + 1);
                }
                ?>

                <?php if ($start_page > 1): ?>
                    <li class="page-item"><a href="?page=1" class="page-link">1</a></li>
                    <?php if ($start_page > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a href="?page=<?= $i ?>" class="page-link"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a href="?page=<?= $total_pages ?>" class="page-link"><?= $total_pages ?></a></li>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a href="?page=<?= $page + 1 ?>" class="page-link">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
