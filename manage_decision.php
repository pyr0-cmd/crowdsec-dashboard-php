<?php
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
    <script src="https://cdn.tailwindcss.com"></script> <!-- TailwindCSS CDN -->
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal pt-20">
    
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto p-5">

    </div>
    </div>
</body>
</html>
