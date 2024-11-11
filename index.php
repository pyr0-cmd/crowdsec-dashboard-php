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
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal pt-10">
    <!-- Navbar -->
    <?php include 'views/navbar.php'; ?>

    <!-- Main Content -->
    <div class="md:ml-64 p-4 overflow-x-hidden">
        <!-- Cards Section -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
            <!-- Decisions Card -->
            <div class="bg-white shadow-md rounded-lg p-8 text-center">
                <h2 class="text-2xl font-semibold mb-4">Decisions</h2>
                <p class="text-3xl" id="decisions_count">
                    <?php
                        $decisions = count_decisions();
                        echo "<span class='font-bold'>" . $decisions . "</span>";
                    ?>
                </p>
            </div>
            <!-- Alerts Card -->
            <div class="bg-white shadow-md rounded-lg p-8 text-center">
                <h2 class="text-2xl font-semibold mb-4">Alerts</h2>
                <p class="text-3xl" id="alerts_count">
                    <?php
                        $alerts = count_alerts();
                        echo "<span class='font-bold'>" . $alerts . "</span>";
                    ?>
                </p>
            </div>
            <!-- Bouncers Card -->
            <div class="bg-white shadow-md rounded-lg p-8 text-center">
                <h2 class="text-2xl font-semibold mb-4">Bouncers</h2>
                <p class="text-3xl" id="bouncers_count">
                    <?php
                        $bouncers = count_bouncers();
                        echo "<span class='font-bold'>" . $bouncers . "</span>";
                    ?>
                </p>
            </div>
        </div>

        <!-- System Info Section -->
        <div class="mt-12">
            <h1 class="text-3xl font-bold mb-6 text-center">System Information</h1>
            <table class="min-w-full bg-white border border-gray-300 rounded-lg">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="px-4 py-2 text-left">Metric</th>
                        <th class="px-4 py-2 text-left">Value</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                    <tr class="border-b border-gray-300">
                        <td class="px-4 py-2">Hostname</td>
                        <td class="px-4 py-2"><?php echo $system_info['hostname']; ?></td>
                    </tr>
                    <tr class="border-b border-gray-300">
                        <td class="px-4 py-2">Operating System</td>
                        <td class="px-4 py-2"><?php echo $system_info['operating_system']; ?></td>
                    </tr>
                    <tr class="border-b border-gray-300">
                        <td class="px-4 py-2">PHP Version</td>
                        <td class="px-4 py-2"><?php echo $system_info['php_version']; ?></td>
                    </tr>
                    <tr class="border-b border-gray-300">
                        <td class="px-4 py-2">Server Software</td>
                        <td class="px-4 py-2"><?php echo $system_info['server_software']; ?></td>
                    </tr>
                    <tr class="border-b border-gray-300">
                        <td class="px-4 py-2">Server IP</td>
                        <td class="px-4 py-2"><?php echo $system_info['server_ip']; ?></td>
                    </tr>
                    <tr class="border-b border-gray-300">
                        <td class="px-4 py-2">Client IP</td>
                        <td class="px-4 py-2"><?php echo $system_info['client_ip']; ?></td>
                    </tr>
                    <tr class="border-b border-gray-300">
                        <td class="px-4 py-2">CPU Load</td>
                        <td class="px-4 py-2"><?php echo $system_info['cpu_load']; ?></td>
                    </tr>
                    <tr class="border-b border-gray-300">
                        <td class="px-4 py-2">Memory Usage</td>
                        <td class="px-4 py-2"><?php echo $system_info['memory_usage']; ?></td>
                    </tr>
                    <tr class="border-b border-gray-300">
                        <td class="px-4 py-2">Total Disk Space</td>
                        <td class="px-4 py-2"><?php echo $system_info['disk_total']; ?></td>
                    </tr>
                    <tr>
                        <td class="px-4 py-2">Free Disk Space</td>
                        <td class="px-4 py-2"><?php echo $system_info['disk_free']; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
