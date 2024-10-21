<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crowdsec Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script> <!-- TailwindCSS CDN -->
</head>
<body class="bg-gray-100">
    
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto p-5">
        <h1 class="text-3xl font-bold mb-5 text-center">Alerts List</h1>

        <?php
            include 'db_connect/db.php';
            $dbconn = connect_db();

            // Pagination settings
            $limit = 15; 
            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
            $offset = ($page - 1) * $limit; // Calculate offset

            // Get total number of alerts
            $total_alerts_query = "SELECT COUNT(*) AS total FROM alerts WHERE scenario LIKE 'crowdsecurity/%'";
            $total_alerts_result = pg_query($dbconn, $total_alerts_query);
            $total_alerts_row = pg_fetch_assoc($total_alerts_result);
            $total_alerts = intval($total_alerts_row['total']);

            // Calculate total pages
            $total_pages = ceil($total_alerts / $limit);

            // Fetch paginated alerts
            $query_alerts = "SELECT * FROM alerts WHERE scenario LIKE 'crowdsecurity/%' LIMIT $limit OFFSET $offset";
            $rs_alerts = pg_query($dbconn, $query_alerts);

            if (!$rs_alerts) {
                echo json_encode(['error' => 'Error executing query: ' . pg_last_error()]);
                pg_close($dbconn);
                exit();
            }

            echo '<div class="overflow-x-auto">';
            echo '<table class="min-w-full bg-white border border-gray-300 rounded-lg">';
            echo '<thead class="bg-gray-800 text-white">';
            echo '<tr>';
            echo '<th class="px-4 py-2 text-left">Alert ID</th>';
            echo '<th class="px-4 py-2 text-left">Scenario</th>';
            echo '<th class="px-4 py-2 text-left">Started</th>';
            echo '<th class="px-4 py-2 text-left">Stopped</th>';
            echo '<th class="px-4 py-2 text-left">Source IP</th>';
            echo '<th class="px-4 py-2 text-left">Target</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody class="text-gray-700">';

            while ($row = pg_fetch_assoc($rs_alerts)) {
                echo '<tr class="border-b border-gray-300">';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['id']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['scenario']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['started_at']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['stopped_at']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['source_ip']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['machine_alerts']) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            pg_free_result($rs_alerts);
            pg_close($dbconn);
        ?>

         <!-- Pagination controls -->
         <div class="mt-5 flex justify-center">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="bg-gray-800 text-white px-3 py-1 rounded mr-2">Previous</a>
            <?php endif; ?>

            <?php
            // Range of pages to show around the current page
            $page_range = 5;
            $start_page = max(1, $page - floor($page_range / 2));
            $end_page = min($total_pages, $start_page + $page_range - 1);

            // Ensure the range always displays $page_range number of pages
            if ($end_page - $start_page < $page_range - 1) {
                $start_page = max(1, $end_page - $page_range + 1);
            }
            ?>

            <?php if ($start_page > 1): ?>
                <a href="?page=1" class="px-3 py-1 bg-gray-200 text-gray-800 rounded mx-1">1</a>
                <?php if ($start_page > 2): ?>
                    <span class="px-3 py-1 text-gray-600">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                <a href="?page=<?= $i ?>" class="px-3 py-1 <?= ($i == $page) ? 'bg-gray-600 text-white' : 'bg-gray-200 text-gray-800' ?> rounded mx-1"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($end_page < $total_pages): ?>
                <?php if ($end_page < $total_pages - 1): ?>
                    <span class="px-3 py-1 text-gray-600">...</span>
                <?php endif; ?>
                <a href="?page=<?= $total_pages ?>" class="px-3 py-1 bg-gray-200 text-gray-800 rounded mx-1"><?= $total_pages ?></a>
            <?php endif; ?>

            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page + 1 ?>" class="bg-gray-800 text-white px-3 py-1 rounded ml-2">Next</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
