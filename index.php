<?php
    include 'db_connect/db.php';
    session_start();
    if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crowdsec Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal pt-20">
    <!-- Navbar -->
    <?php include 'navbar.php'; ?>

    <!-- Main Content -->
    <div class="flex justify-center items-start h-screen p-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12 w-2/3">
            <!-- Decisions Card -->
            <div class="bg-white shadow-md rounded-lg p-8 transform scale-110 text-center">
                <h2 class="text-2xl font-semibold mb-4">Decisions</h2>
                <p class="text-3xl">
                    <?php
                        $decisions = count_decisions();
                        echo "<span class='font-bold'>" . $decisions . "</span>";
                    ?>
                </p>
            </div>
            <!-- Alerts Card -->
            <div class="bg-white shadow-md rounded-lg p-8 transform scale-110 text-center">
                <h2 class="text-2xl font-semibold mb-4">Alerts</h2>
                <p class="text-3xl">
                    <?php
                        $alerts = count_alerts();
                        echo "<span class='font-bold'>" . $alerts . "</span>";
                    ?>
                </p>
            </div>
            <!-- Bouncers Card -->
            <div class="bg-white shadow-md rounded-lg p-8 transform scale-110 text-center">
                <h2 class="text-2xl font-semibold mb-4">Bouncers</h2>
                <p class="text-3xl">
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
