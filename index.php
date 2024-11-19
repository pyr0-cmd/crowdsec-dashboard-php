<?php
    include 'models/db.php';
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
    
    function get_system_info() {
        $system_info = [];

        $system_info['hostname'] = gethostname();
        $system_info['php_version'] = phpversion();
        $system_info['server_software'] = $_SERVER['SERVER_SOFTWARE'];
        $system_info['operating_system'] = PHP_OS;
        $system_info['server_ip'] = $_SERVER['SERVER_ADDR'];
        $system_info['client_ip'] = $_SERVER['REMOTE_ADDR'];
        
        if (stristr(PHP_OS, 'win')) {
            $system_info['cpu_load'] = 'Not available on Windows';
        } else {
            $load = sys_getloadavg();
            $system_info['cpu_load'] = $load[0] . ' (1 min average)';
        }

        $system_info['memory_usage'] = round(memory_get_usage() / 1024 / 1024, 2) . ' MB';

        $system_info['disk_total'] = round(disk_total_space("/") / 1024 / 1024 / 1024, 2) . ' GB';
        $system_info['disk_free'] = round(disk_free_space("/") / 1024 / 1024 / 1024, 2) . ' GB';

        return $system_info;
    }

    $system_info = get_system_info();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crowdsec</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <!-- Navbar -->
    <?php include 'views/navbar.php'; ?>

    <!-- Main Content -->
        <div class="container mt-4" style="padding: 50px;">
            <!-- Cards Section -->
            <div class="row mb-4">
                <!-- Decisions Card -->
                <div class="col-md-4">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Blocked IPs</h5>
                            <p class="display-4 fw-bold" id="decisions_count">null</p>
                        </div>
                    </div>
                </div>
                <!-- Alerts Card -->
                <div class="col-md-4">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Alerts</h5>
                            <p class="display-4 fw-bold" id="alerts_count">null</p>
                        </div>
                    </div>
                </div>
                <!-- Bouncers Card -->
                <div class="col-md-4">
                    <div class="card text-center shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">Bouncers</h5>
                            <p class="display-4" id="bouncers_count">
                                <?php
                                    $bouncers = count_bouncers();
                                    echo "<span class='fw-bold'>" . $bouncers . "</span>";
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mb-5">
                <!-- Alerts Table -->
                <div class="col-md-8">
                    <h3 class="mb-3 text-center">Alerts</h3>
                    <?php
                        include '../models/db.php';
                        $dbconn = connect_db();

                        $limit = 10;
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
                        echo '<th scope="col">ID</th>';
                        echo '<th scope="col">Scenario</th>';
                        echo '<th scope="col">Started</th>';
                        echo '<th scope="col">Source IP</th>';
                        echo '</tr>';
                        echo '</thead>';
                        echo '<tbody>';

                        while ($row = pg_fetch_assoc($rs_alerts)) {
                            echo '<tr>';
                            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['scenario']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['started_at']) . '</td>';
                            echo '<td>' . htmlspecialchars($row['source_ip']) . '</td>';
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

                <!-- System Info Section -->
                 <div class="col-md-4">
                 <h3 class="mb-3 text-center">System Information</h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>Metric</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($system_info as $key => $value): ?>
                            <tr>
                                <td><?php echo ucfirst(str_replace('_', ' ', $key)); ?></td>
                                <td><?php echo $value; ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                 </div>
                
            </div>
            
        </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ws = new WebSocket('ws://192.168.1.131:9090');

        ws.onmessage = function(event) {
            const data = JSON.parse(event.data);
            document.getElementById('decisions_count').innerText = data.decisions;
            document.getElementById('alerts_count').innerText = data.alerts;
            console.log(data.decisions);
        };
        
        ws.onopen = function() {
            console.log('Connected to WebSocket server');
        };

        ws.onclose = function() {
            console.log('Disconnected from WebSocket server');
        };
    </script>
</body>
</html>
