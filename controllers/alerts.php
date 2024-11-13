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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    
    <?php include '../views/navbar.php'; ?>

    <div class="container mt-4" style="padding-top: 50px;">

        <?php
            include '../models/db.php';
            $dbconn = connect_db();

            $limit = 15; 
            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
            $offset = ($page - 1) * $limit;

            $total_alerts_query = "SELECT COUNT(*) AS total FROM alerts WHERE scenario LIKE 'crowdsecurity/%'";
            $total_alerts_result = pg_query($dbconn, $total_alerts_query);
            $total_alerts_row = pg_fetch_assoc($total_alerts_result);
            $total_alerts = intval($total_alerts_row['total']);

            $total_pages = ceil($total_alerts / $limit);

            $query_alerts = "SELECT * FROM alerts WHERE scenario LIKE 'crowdsecurity/%' LIMIT $limit OFFSET $offset";
            $rs_alerts = pg_query($dbconn, $query_alerts);

            if (!$rs_alerts) {
                echo json_encode(['error' => 'Error executing query: ' . pg_last_error()]);
                pg_close($dbconn);
                exit();
            }

            echo '<div class="table-responsive">';
            echo '<table class="table table-bordered table-hover">';
            echo '<thead class="table-dark">';
            echo '<tr>';
            echo '<th scope="col">Alert ID</th>';
            echo '<th scope="col">Scenario</th>';
            echo '<th scope="col">Started</th>';
            echo '<th scope="col">Stopped</th>';
            echo '<th scope="col">Source IP</th>';
            echo '<th scope="col">Target</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while ($row = pg_fetch_assoc($rs_alerts)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                echo '<td>' . htmlspecialchars($row['scenario']) . '</td>';
                echo '<td>' . htmlspecialchars($row['started_at']) . '</td>';
                echo '<td>' . htmlspecialchars($row['stopped_at']) . '</td>';
                echo '<td>' . htmlspecialchars($row['source_ip']) . '</td>';
                echo '<td>' . htmlspecialchars($row['machine_alerts']) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            pg_free_result($rs_alerts);
            pg_close($dbconn);
        ?>

         <!-- Pagination controls -->
         <nav class="mt-4">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>">Previous</a>
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
                    <li class="page-item"><a class="page-link" href="?page=1">1</a></li>
                    <?php if ($start_page > 2): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                <?php endif; ?>

                <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($end_page < $total_pages): ?>
                    <?php if ($end_page < $total_pages - 1): ?>
                        <li class="page-item disabled"><span class="page-link">...</span></li>
                    <?php endif; ?>
                    <li class="page-item"><a class="page-link" href="?page=<?= $total_pages ?>"><?= $total_pages ?></a></li>
                <?php endif; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>">Next</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
