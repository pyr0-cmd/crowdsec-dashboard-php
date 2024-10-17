<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alerts Table</title>
    <script src="https://cdn.tailwindcss.com"></script> <!-- TailwindCSS CDN -->
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-5">
        <h1 class="text-3xl font-bold mb-5 text-center">Alerts List</h1>

        <?php
            include 'db_connect/db.php';
            $dbconn = connect_db();
            $query_alerts = 'SELECT * FROM alerts';
            $rs_alerts = pg_query($dbconn, $query_alerts);

            if (!$rs_alerts) {
                echo json_encode(['error' => 'Error executing query: ' . pg_last_error()]);
                pg_close($dbconn);
                exit();
            }

            // Start the Tailwind-styled table
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

            // Fetch and display the alert rows
            while ($row = pg_fetch_assoc($rs_alerts)) {
                echo '<tr class="border-b border-gray-300">';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['id']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['scenario']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['started_at']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['stopped_at']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['source_ip']) . '</td>';
                echo '<td class="px-4 py-2">' . htmlspecialchars($row['machines_alerts']) . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</div>';

            // Free result set and close connection
            pg_free_result($rs_alerts);
            pg_close($dbconn);
        ?>
    </div>
</body>
</html>
