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
        <h1 class="text-3xl font-bold mb-5 text-center">decisions List</h1>

        <?php
            include 'db_connect/db.php';
            $dbconn = connect_db();

            // Pagination settings
            $limit = 15; 
            $page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
            $offset = ($page - 1) * $limit; // Calculate offset

            // Get total number of decisions
            $total_decisions_query = "SELECT COUNT(*) AS total FROM decisions WHERE origin != 'CAPI'";
            $total_decisions_result = pg_query($dbconn, $total_decisions_query);
            $total_decisions_row = pg_fetch_assoc($total_decisions_result);
            $total_decisions = intval($total_decisions_row['total']);

            // Calculate total pages
            $total_pages = ceil($total_decisions / $limit);

            // Fetch paginated decisions
            $query_decisions = "SELECT * FROM decisions WHERE origin != 'CAPI' LIMIT $limit OFFSET $offset";
            $rs_decisions = pg_query($dbconn, $query_decisions);

            if (!$rs_decisions) {
                echo json_encode(['error' => 'Error executing query: ' . pg_last_error()]);
                pg_close($dbconn);
                exit();
            }

            echo '<div class="overflow-x-auto">';
            echo '<table class="min-w-full bg-white border border-gray-300 rounded-lg">';
            echo '<thead class="bg-gray-800 text-white">';
            echo '<tr>';
            echo '<th class="px-4 py-2 text-left">ID</th>';
            echo '<th class="px-4 py-2 text-left">Origin</th>';
            echo '<th class="px-4 py-2 text-left">Scenarios</th>';
            echo '<th class="px-4 py-2 text-left">Create at</th>';
            echo '<th class="px-4 py-2 text-left">Updated at</th>';
            echo '<th class="px-4 py-2 text-left">Until</th>';
            echo '<th class="px-4 py-2 text-left">Type</th>';
            echo '<th class="px-4 py-2 text-left">Value</th>';
            echo '<th class="px-4 py-2 text-left">Scope</th>';
            echo '<th class="px-4 py-2 text-left">Alert Decisions</th>';

            echo '</tr>';
            echo '</thead>';
            echo '<tbody class="text-gray-700">';

            while ($row = pg_fetch_assoc($rs_decisions)) {
                echo '<tr class="border-b border-gray-300">';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['id']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['origin']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['scenario']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['created_at']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['updated_at']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['until']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['type']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['value']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['scope']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['alert_decisions']) . '</td>';

                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            pg_free_result($rs_decisions);
            pg_close($dbconn);
        ?>

       <!-- Pagination controls -->
        <div class="mt-5 flex justify-center">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="bg-gray-800 text-white px-3 py-1 rounded mr-2">Previous</a>
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
