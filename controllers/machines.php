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
    <script src="https://cdn.tailwindcss.com"></script> <!-- TailwindCSS CDN -->
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal pt-10">
    
    <?php include '../views/navbar.php'; ?>

    <div class="md:ml-64 p-4 overflow-x-hidden">

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

            echo '<div class="overflow-x-auto">';
            echo '<table class="min-w-full bg-white border border-gray-300 rounded-lg">';
            echo '<thead class="bg-gray-800 text-white">';
            echo '<tr>';
            echo '<th class="px-4 py-2 text-left">Name</th>';
            echo '<th class="px-4 py-2 text-left">IP Address</th>';
            echo '<th class="px-4 py-2 text-left">Status</th>';
            echo '<th class="px-4 py-2 text-left">OS Version</th>';
            echo '<th class="px-4 py-2 text-left">Version</th>';
            echo '<th class="px-4 py-2 text-left">Last Update</th>';

            echo '</tr>';
            echo '</thead>';
            echo '<tbody class="text-gray-700">';

            while ($row = pg_fetch_assoc($rs_machines)) {
                echo '<tr class="border-b border-gray-300">';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['machine_id']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['ip_address']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['is_validated']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['osname']) . ' ' . htmlspecialchars($row['osversion']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['version']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['last_heartbeat']) . '</td>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            pg_free_result($rs_machines);
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
