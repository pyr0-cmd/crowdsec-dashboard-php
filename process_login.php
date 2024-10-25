<?php

session_start();
include 'db_connect/db.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validate user input
    if (empty($username) || empty($password)) {
        echo "Please enter both username and password.";
        exit();
    }

    $dbconn = connect_db();

    $query = "SELECT * FROM users WHERE username = $1";
    $result = pg_query_params($dbconn, $query, array($username));

    if (!$result) {
        echo "Error executing query: " . pg_last_error($dbconn);
        pg_close($dbconn);
        exit();
    }

    if (pg_num_rows($result) == 1) {
        $user = pg_fetch_assoc($result);

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            header("Location: index.php");
            exit();
        } else {
            echo "Invalid username or password!";
        }
    } else {
        echo "Invalid username or password!";
    }

    pg_free_result($result);
    pg_close($dbconn);
}
?>
