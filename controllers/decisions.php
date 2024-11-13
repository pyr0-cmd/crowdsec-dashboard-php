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

    <div class="container mt-4">

        <?php
            include '../models/db.php';
            $dbconn = connect_db();

            $limit = 15; 
            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
            $offset = ($page - 1) * $limit;

            $total_decisions_query = "SELECT COUNT(*) AS total FROM decisions";
            $total_decisions_result = pg_query($dbconn, $total_decisions_query);
            $total_decisions_row = pg_fetch_assoc($total_decisions_result);
            $total_decisions = intval($total_decisions_row['total']);

            $total_pages = ceil($total_decisions / $limit);

            $query_decisions = "SELECT * FROM decisions LIMIT $limit OFFSET $offset";
            $rs_decisions = pg_query($dbconn, $query_decisions);

            if (!$rs_decisions) {
                echo json_encode(['error' => 'Error executing query: ' . pg_last_error()]);
                pg_close($dbconn);
                exit();
            }

            echo '<div class="table-responsive">';
            echo '<table class="table table-bordered table-hover">';
            echo '<thead class="table-dark">';
            echo '<tr>';
            echo '<th>ID</th>';
            echo '<th>Origin</th>';
            echo '<th>Scenario</th>';
            echo '<th>Created At</th>';
            echo '<th>Updated At</th>';
            echo '<th>Until</th>';
            echo '<th>Type</th>';
            echo '<th>Value</th>';
            echo '<th>Scope</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            while ($row = pg_fetch_assoc($rs_decisions)) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                echo '<td>' . htmlspecialchars($row['origin']) . '</td>';
                echo '<td>' . htmlspecialchars($row['scenario']) . '</td>';
                echo '<td>' . htmlspecialchars($row['created_at']) . '</td>';
                echo '<td>' . htmlspecialchars($row['updated_at']) . '</td>';
                echo '<td>' . htmlspecialchars($row['until']) . '</td>';
                echo '<td>' . htmlspecialchars($row['type']) . '</td>';
                echo '<td>' . htmlspecialchars($row['value']) . '</td>';
                echo '<td>' . htmlspecialchars($row['scope']) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            pg_free_result($rs_decisions);
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
