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
                            <h5 class="card-title">Decisions</h5>
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

            <!-- System Info Section -->
            <h2 class="mb-4 text-center">System Information</h2>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ws = new WebSocket('ws://192.168.56.129:9090');

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
