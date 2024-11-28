<?php 
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include '../models/db.php';
$dbconn = connect_db();

$status_message = '';
$status_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create'])) {
        $ip = escapeshellarg($_POST['ip']);
        $duration = "5m";  
        $reason = escapeshellarg($_POST['reason']);

        $add_command = "sudo -u www-data sudo cscli decisions add --ip $ip --duration $duration --reason $reason 2>&1";

        $output = [];
        $return_var = 0;
        exec($add_command, $output, $return_var); 

        if ($return_var === 0) {
            $status_message = "Decision added successfully.";
            $status_type = 'success';
        } else {
            $status_message = "Failed to add decision. Error code: $return_var.";
            $status_type = 'error';
            var_dump($output);
        }
    } elseif (isset($_POST['delete'])) {
        $ip = escapeshellarg($_POST['ip']);
        $id = intval($_POST['id']);
        $delete_command = "sudo -u www-data sudo cscli decisions delete --ip $ip 2>&1";  

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

        $delete_query = "DELETE FROM decisions WHERE id = $1";
        $result = pg_query_params($dbconn, $delete_query, [$id]);

        if (!$result) {
            $error = "Error deleting record: " . pg_last_error($dbconn);
        }
    }
}

$limit = 8;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $limit;

$total_decisions_query = "SELECT COUNT(*) AS total FROM decisions";
$total_decisions_result = pg_query($dbconn, $total_decisions_query);

if (!$total_decisions_result) {
    $error = "Error fetching total records: " . pg_last_error($dbconn);
} else {
    $total_decisions_row = pg_fetch_assoc($total_decisions_result);
    $total_decisions = intval($total_decisions_row['total']);
    $total_pages = ceil($total_decisions / $limit);
}

$query_decisions = "SELECT * FROM decisions ORDER BY id DESC LIMIT $1 OFFSET $2";
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" integrity="sha512-Kc323vGBEqzTmouAECnVceyQqyqdsSiqLQISBL29aUW4U/M7pSPA/gEUZQqv1cwx4OnYxTxve5UMg5GT6L4JJg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>
<body class="bg-light pt-5">

    <?php include '../views/navbar.php'; ?>

    <div class="container mt-4" style="padding-top: 50px;">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <h2 class="mb-4">Block IP</h2>
        <form method="POST" class="mb-5">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="reason" class="form-label">Reason</label>
                    <input type="text" name="reason" class="form-control" id="reason" placeholder="Reason" required>
                </div>
                <div class="col-md-6">
                    <label for="ip" class="form-label">IP Address</label>
                    <input type="text" name="ip" class="form-control" id="ip" placeholder="192.168.x.x, 172.16.x.x,..." required>
                </div>
            </div>
            <button type="submit" name="create" class="btn btn-primary mt-3"><i class="fa-solid fa-square-plus"></i> Add</button>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Origin</th>
                        <th>Scenario</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Until</th>
                        <th>Type</th>
                        <th>Source IP</th>
                        <th>Scope</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($rs_decisions)): ?>
                        <?php while ($row = pg_fetch_assoc($rs_decisions)): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['origin']) ?></td>
                            <td><?= htmlspecialchars($row['scenario']) ?></td>
                            <td><?= htmlspecialchars($row['created_at']) ?></td>
                            <td><?= htmlspecialchars($row['updated_at']) ?></td>
                            <td><?= htmlspecialchars($row['until']) ?></td>
                            <td><?= htmlspecialchars($row['type']) ?></td>
                            <td><?= htmlspecialchars($row['value']) ?></td>
                            <td><?= htmlspecialchars($row['scope']) ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this record?');" class="d-inline">
                                    <input type="hidden" name="ip" value="<?= $row['value'] ?>">
                                    <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                    <button type="submit" name="delete" class="btn btn-danger"><i class="fa-regular fa-trash-can"></i></button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3 d-flex justify-content-center">
        <nav class="mt-2">
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const statusMessage = '<?= addslashes($status_message) ?>';
        const statusType = '<?= addslashes($status_type) ?>';

        if (statusMessage) {
            if (statusType === 'success') {
                alert(statusMessage);  
            } else if (statusType === 'error') {
                alert('Error: ' + statusMessage); 
            }
        }
    </script>
</body>
</html>

<?php
if (isset($rs_decisions)) {
    pg_free_result($rs_decisions);
}
pg_close($dbconn);
?>
