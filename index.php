<?php
    include 'db_connect/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crowdsec Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">
    <!-- Navbar -->
    <nav class="bg-blue-600 p-4">
        <div class="container mx-auto flex justify-between items-center">
            <div class="text-white text-xl font-bold">Crowdsec Dashboard</div>
            <ul class="flex space-x-4">
                <li><a href="#" class="text-white hover:text-blue-200">Home</a></li>
                <li><a href="#" class="text-white hover:text-blue-200">Decisions</a></li>
                <li><a href="#" class="text-white hover:text-blue-200">Alerts</a></li>
                <li><a href="#" class="text-white hover:text-blue-200">Settings</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="flex justify-start items-start h-screen p-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 w-2/3">
            <!-- Decisions Card -->
            <div class="bg-white shadow-md rounded-lg p-8 transform scale-110">
                <h2 class="text-3xl font-semibold mb-4">Decisions</h2>
                <p class="text-2xl">
                    <?php
                        $decisions = count_decisions();
                        echo "<span class='font-bold'>" . $decisions . "</span>";
                    ?>
                </p>
            </div>
            <!-- Alerts Card -->
            <div class="bg-white shadow-md rounded-lg p-8 transform scale-110">
                <h2 class="text-3xl font-semibold mb-4">Alerts</h2>
                <p class="text-2xl">
                    <?php
                        $alerts = count_alerts();
                        echo "<span class='font-bold'>" . $alerts . "</span>";
                    ?>
                </p>
            </div>
            <!-- Bouncers Card -->
            <div class="bg-white shadow-md rounded-lg p-8 transform scale-110">
                <h2 class="text-3xl font-semibold mb-4">Bouncers</h2>
                <p class="text-2xl">
                    <?php
                        $bouncers = count_bouncers();
                        echo "<span class='font-bold'>" . $bouncers . "</span>";
                    ?>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
