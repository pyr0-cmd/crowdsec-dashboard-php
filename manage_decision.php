<?php 
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    include 'db_connect/db.php';
    $dbconn = connect_db();

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['create'])) {
            $scenario = pg_escape_string($dbconn, $_POST['scenario']);
            $type = pg_escape_string($dbconn, $POST['type']);
            $value = pg_escape_string($dbconn, $_POST['value']);

            $origin = 'admin';
            $scope  = 'Ip';

            $current_time = date('Y-m-d H:i:s');

            $insert_query = "INSERT INTO decisions (origin, scenario, created_at, updated_at, until, type, value, scope) 
                            VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";
            
            $result = pg_query_params($dbconn, $insert_query, [
                $origin,
                $scenario,
                $current_time,
                $current_time,
                $_POST['until'],
                $type,
                $value,
                $scope
            ]);

            
            if (!$result) {
                $error = "Error adding record: " . pg_last_error($dbconn);
            }

        } elseif (isset($_POST['delete'])) {
            $id = intval($_POST['id']);
            $delete_query = "DELETE FROM decisions WHERE id = $1";
            $result = pg_query_params($dbconn, $delete_query, [$id]);

            if (!$result) {
                $error = "Error deleting record: " . pg_last_error($dbconn);
            }
        }
    }
?>
<!-- My front-end skill is SUCK T-T, so i used chatGPT -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crowdsec Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script> <!-- TailwindCSS CDN -->
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal pt-20">
    
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto p-5">
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Create new record form -->
        <h2 class="text-2xl font-bold mb-4">Add New Decision</h2>
        <form method="POST" class="mb-5">
            <!-- Hidden or default fields -->
            <input type="hidden" name="origin" value="admin">
            <input type="hidden" name="scope" value="Ip">
            <!-- 'created_at' and 'updated_at' are handled in the backend -->

            <input type="text" name="scenario" placeholder="Scenario" required class="border p-2 mb-2 w-full">
            <input type="datetime-local" name="until" placeholder="Until" required class="border p-2 mb-2 w-full">
            <input type="text" name="type" placeholder="Type" required class="border p-2 mb-2 w-full">
            <input type="text" name="value" placeholder="Value" required class="border p-2 mb-2 w-full">
            <button type="submit" name="create" class="bg-blue-500 text-white px-4 py-2 rounded">Add Decision</button>
        </form>

        <!-- Table of records -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-300 rounded-lg">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">ID</th>
                        <th class="px-4 py-2 text-left">Origin</th>
                        <th class="px-4 py-2 text-left">Scenario</th>
                        <th class="px-4 py-2 text-left">Created At</th>
                        <th class="px-4 py-2 text-left">Updated At</th>
                        <th class="px-4 py-2 text-left">Until</th>
                        <th class="px-4 py-2 text-left">Type</th>
                        <th class="px-4 py-2 text-left">Value</th>
                        <th class="px-4 py-2 text-left">Scope</th>
                        <th class="px-4 py-2 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <?php while ($row = pg_fetch_assoc($rs_decisions)): ?>
                    <tr class="border-b border-gray-300">
                        <td class="px-4 py-2"><?= htmlspecialchars($row['id']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['origin']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['scenario']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['created_at']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['updated_at']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['until']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['type']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['value']) ?></td>
                        <td class="px-4 py-2"><?= htmlspecialchars($row['scope']) ?></td>
                        <td class="px-4 py-2 flex space-x-2">
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this record?');">
                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                <button type="submit" name="delete" class="bg-red-500 text-white px-2 py-1 rounded">Delete</button>
                            </form>
                            <a href="edit_decision.php?id=<?= $row['id'] ?>" class="bg-green-500 text-white px-2 py-1 rounded">Edit</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination controls -->
        <div class="mt-5 flex justify-center">
            <?php
            $page_range = 5;
            $start_page = max(1, $page - floor($page_range / 2));
            $end_page = min($total_pages, $start_page + $page_range - 1);

            if ($end_page - $start_page < $page_range - 1) {
                $start_page = max(1, $end_page - $page_range + 1);
            }
            ?>

            <?php if ($page > 1): ?>
                <a href="?page=<?= $page - 1 ?>" class="bg-gray-800 text-white px-3 py-1 rounded mr-2">Previous</a>
            <?php endif; ?>

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

<?php
// Free result and close connection
if (isset($rs_decisions)) {
    pg_free_result($rs_decisions);
}
pg_close($dbconn);
?>
