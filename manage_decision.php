<?php 
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    include 'db_connect/db.php';
    $dbconn = connect_db();

    if($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['create'])) {
            $scenario = pg_escape_string($dbconn, $_POST['scenario']);
            $type = pg_escape_string($dbconn, $POST['type']);
            $value = pg_escape_string($dbconn, $_POST['value']);

            $origin = 'admin';
            $scope  = 'Ip';

            $current_time = date('Y-m-d H:i:s');

            $insert_query = "INSERT INTO decisions (origin, scenario, created_at, updated_at, until, type, value, scope) 
                            VALUES ($1, $2, $3, $4, $5, $6, $7, $8)";
            
            $result = pg_query_params($dbconn, $insert_query, [
                $origin,
                $scenario,
                $current_time,
                $current_time,
                $_POST['until'],
                $type,
                $value,
                $scope
            ]);

            
            if (!$result) {
                $error = "Error adding record: " . pg_last_error($dbconn);
            }

        } elseif (isset($_POST['delete'])) {
            $id = intval($_POST['id']);
            $delete_query = "DELETE FROM decisions WHERE id = $1";
            $result = pg_query_params($dbconn, $delete_query, [$id]);

            if (!$result) {
                $error = "Error deleting record: " . pg_last_error($dbconn);
            }
        }
    }
?>