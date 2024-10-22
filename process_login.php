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

    // Prepare the SQL query to find the user with the given username
    $query = "SELECT * FROM users WHERE username = $1";
    $result = pg_query_params($dbconn, $query, array($username));

    if (!$result) {
        echo "Error executing query: " . pg_last_error($dbconn);
        pg_close($dbconn);
        exit();
    }

    // Check if a user with the given username exists
    if (pg_num_rows($result) == 1) {
        $user = pg_fetch_assoc($result);

        // Verify the entered password against the stored hashed password
        if (password_verify($password, $user['password'])) {
            // Password is correct, create a session for the user
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Redirect to dashboard page
            header("Location: index.php");
            exit();
        } else {
            // Password is incorrect
            echo "Invalid username or password!";
        }
    } else {
        // Username not found
        echo "Invalid username or password!";
    }

    // Free result and close connection
    pg_free_result($result);
    pg_close($dbconn);
}
?>
