<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ip = escapeshellarg($_POST['ip']);
    $duration = escapeshellarg($_POST['duration']);
    $reason = escapeshellarg($_POST['reason']);
    
    $command = "sudo -u www-data cscli decisions add --ip $ip --duration $duration --reason $reason 2>&1";
    
    $output = [];
    $return_var = 0;
    exec($command, $output, $return_var);
    
    if ($return_var === 0) {
        echo "Decision added successfully:<br>";
        echo implode("<br>", $output);
    } else {
        echo "Failed to add decision. Error code: $return_var<br>";
        echo implode("<br>", $output);
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Add Decision</title>
</head>
<body>
    <h2>Add CrowdSec Decision</h2>
    <form method="post" action="">
        <label for="ip">IP Address:</label>
        <input type="text" id="ip" name="ip" required><br><br>
        
        <label for="duration">Duration:</label>
        <input type="text" id="duration" name="duration" required><br><br>
        
        <label for="reason">Reason:</label>
        <input type="text" id="reason" name="reason" required><br><br>
        
        <input type="submit" value="Submit">
    </form>
</body>
</html>
