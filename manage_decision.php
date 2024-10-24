<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect/db.php';
$dbconn = connect_db();

$status_message = '';
$status_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $ip = escapeshellarg($_POST['ip']);
        $duration = "5m";  
        $reason = escapeshellarg($_POST['reason']);

        $add_command = "sudo -u www-data cscli decisions add --ip $ip --duration $duration --reason $reason 2>&1";

        $output = [];
        $return_var = 0;
        exec($add_command, $output, $return_var); 

        if ($return_var === 0) {
            $status_message = "Decision added successfully.";
            $status_type = 'success';
        } else {
            $status_message = "Failed to add decision. Error code: $return_var.";
            $status_type = 'error';
        }
    } elseif (isset($_POST['delete'])) {
        $ip = escapeshellarg($_POST['ip']);  

        $delete_command = "sudo -u www-data cscli decisions delete --ip $ip 2>&1";  

        $output = [];
        $return_var = 0;
        exec($delete_command, $output, $return_var);  

        if ($return_var === 0) {
            $status_message = "Decision deleted successfully.";
            $status_type = 'success';
        } else {
            $status_message = "Failed to delete decision. Error code: $return_var.";
            $status_type = 'error';
        }
        $id = intval($_POST['id']);
        $delete_query = "DELETE FROM decisions WHERE id = $1";
        $result = pg_query_params($dbconn, $delete_query, [$id]);

        if (!$result) {
            $error = "Error deleting record: " . pg_last_error($dbconn);
        }
    }
}

    // Pagination settings
    $limit = 15;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;

    // Fetch the total number of decisions
    $total_decisions_query = "SELECT COUNT(*) AS total FROM decisions WHERE origin != 'CAPI'";
    $total_decisions_result = pg_query($dbconn, $total_decisions_query);

    if (!$total_decisions_result) {
        $error = "Error fetching total records: " . pg_last_error($dbconn);
    } else {
        $total_decisions_row = pg_fetch_assoc($total_decisions_result);
        $total_decisions = intval($total_decisions_row['total']);
        $total_pages = ceil($total_decisions / $limit);
    }

    // Fetch paginated decisions
    $query_decisions = "SELECT * FROM decisions WHERE origin != 'CAPI' ORDER BY id DESC LIMIT $1 OFFSET $2";
    $rs_decisions = pg_query_params($dbconn, $query_decisions, [$limit, $offset]);

    if (!$rs_decisions) {
        $error = "Error fetching records: " . pg_last_error($dbconn);
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crowdsec Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script> <!-- TailwindCSS CDN -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal pt-10">

    <?php include 'navbar.php'; ?>

    <div class="md:ml-64 p-4 overflow-x-hidden">
        <!-- Display error if any -->
        <?php if (isset($error)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Create new record form -->
        <h2 class="text-2xl font-bold mb-4">Chặn IP</h2>
        <form method="POST" class="mb-5">
            <div class="grid md:grid-cols-2 md:gap-6">
                <div class="relative z-0 w-full mb-5 group">
                    <h3>Lý do </h3>
                    <input type="text" name="reason" placeholder="Reason" required class="border p-2 mb-2 w-full">
                    <h3>Địa chỉ IP </h3>
                    <input type="text" name="ip" placeholder="192.168.x.x, 172.16.x.x,..." required class="border p-2 mb-2 w-full">
                </div>
            </div>
            <button type="submit" name="create" class="bg-blue-500 text-white px-4 py-2 rounded"><i class="fa-solid fa-square-plus"></i> Thêm</button>
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
                    <?php if (isset($rs_decisions)): ?>
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
                                    <input type="hidden" name="ip" value="<?= $row['value'] ?>">
                                    <button type="submit" name="delete" class="bg-red-500 text-white px-2 py-1 rounded"><i class="fa-regular fa-trash-can"></i></button>
                                </form>
                                <a href="edit_decision.php?id=<?= $row['id'] ?>" class="bg-green-500 text-white px-2 py-1 rounded"><i class="fa-regular fa-pen-to-square"></i></a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination controls -->
        <div class="mt-5 flex justify-center">
            <!-- Pagination logic here -->
        </div>
    </div>

    <!-- JavaScript to show alert -->
    <script>
        // Display alert if status message exists
        const statusMessage = '<?= addslashes($status_message) ?>';
        const statusType = '<?= addslashes($status_type) ?>';
        
        if (statusMessage) {
            if (statusType === 'success') {
                alert(statusMessage);  // For success
            } else if (statusType === 'error') {
                alert('Error: ' + statusMessage);  // For error
            }
        }
    </script>

</body>
</html>

<?php
// Free result and close connection
if (isset($rs_decisions)) {
    pg_free_result($rs_decisions);
}
pg_close($dbconn);
?>
