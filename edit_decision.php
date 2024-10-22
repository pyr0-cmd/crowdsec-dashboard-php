<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

include 'db_connect/db.php';
$dbconn = connect_db();

// Initialize variables
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$error = '';
$success = '';

// Fetch existing record
if ($id > 0) {
    $fetch_query = "SELECT * FROM decisions WHERE id = $1";
    $result = pg_query_params($dbconn, $fetch_query, [$id]);

    if ($result && pg_num_rows($result) > 0) {
        $decision = pg_fetch_assoc($result);
    } else {
        $error = "Record not found.";
    }
} else {
    $error = "Invalid record ID.";
}

// Handle form submission for update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    $scenario = pg_escape_string($dbconn, $_POST['scenario']);
    $until = $_POST['until']; // Assuming 'until' is a datetime-local input
    $type = pg_escape_string($dbconn, $_POST['type']);
    $value = pg_escape_string($dbconn, $_POST['value']);

    // Get current timestamp for updated_at
    $current_time = date('Y-m-d H:i:s');

    // Prepare and execute the UPDATE query
    $update_query = "UPDATE decisions SET scenario = $1, until = $2, type = $3, value = $4, updated_at = $5 WHERE id = $6";
    $update_result = pg_query_params($dbconn, $update_query, [
        $scenario,
        $until,
        $type,
        $value,
        $current_time,
        $id
    ]);

    if ($update_result) {
        $success = "Record updated successfully.";
        // Fetch the updated record
        $result = pg_query_params($dbconn, $fetch_query, [$id]);
        $decision = pg_fetch_assoc($result);
    } else {
        $error = "Error updating record: " . pg_last_error($dbconn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Decision</title>
    <script src="https://cdn.tailwindcss.com"></script> <!-- TailwindCSS CDN -->
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal pt-20">
    
    <?php include 'navbar.php'; ?>

    <div class="container mx-auto p-5">
        <a href="index.php" class="text-blue-500 mb-4 inline-block">← Back to Dashboard</a>

        <?php if ($error): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($decision) && $id > 0): ?>
            <h2 class="text-2xl font-bold mb-4">Edit Decision ID: <?= htmlspecialchars($decision['id']) ?></h2>
            <form method="POST" class="mb-5">
                <!-- Origin and Scope are not editable as they are default values -->
                <input type="hidden" name="origin" value="<?= htmlspecialchars($decision['origin']) ?>">
                <input type="hidden" name="scope" value="<?= htmlspecialchars($decision['scope']) ?>">
                
                <div class="mb-2">
                    <label class="block text-gray-700">Scenario:</label>
                    <input type="text" name="scenario" value="<?= htmlspecialchars($decision['scenario']) ?>" required class="border p-2 w-full">
                </div>
                <div class="mb-2">
                    <label class="block text-gray-700">Until:</label>
                    <input type="datetime-local" name="until" value="<?= date('Y-m-d\TH:i', strtotime($decision['until'])) ?>" required class="border p-2 w-full">
                </div>
                <div class="mb-2">
                    <label class="block text-gray-700">Type:</label>
                    <input type="text" name="type" value="<?= htmlspecialchars($decision['type']) ?>" required class="border p-2 w-full">
                </div>
                <div class="mb-2">
                    <label class="block text-gray-700">Value:</label>
                    <input type="text" name="value" value="<?= htmlspecialchars($decision['value']) ?>" required class="border p-2 w-full">
                </div>
                <button type="submit" name="update" class="bg-green-500 text-white px-4 py-2 rounded">Update Decision</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// Free result and close connection
if (isset($result)) {
    pg_free_result($result);
}
pg_close($dbconn);
?>
