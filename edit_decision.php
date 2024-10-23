<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    include 'db_connect/db.php';
    $dbconn = connect_db();

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $error = '';
    $success = '';

    if($id > 0){
        $fetch_query = "SELECT * FROM decisions WHERE id = $1";
        $result = pg_query_params($dbconn, $fetch_query, [$id]);

        if ($result && pg_num_rows($result) > 0) {
            $decision = pg_fetch_assoc($result);
        } else {
            $error = "Record not found!!!.";
        }
    } else {
        $error = "Invalid record ID!!!.";
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
        $scenario = pg_escape_string($dbconn, $_POST['scenario']);
        $until = $_POST['until'];
        $type = pg_escape_string($dbconn, $_POST['type']);
        $value = pg_escape_string($dbconn, $_POST['value']);
        
        $current_time = date('Y-m-d H:i:s');
        $update_query = "UPDATE decisions SET scenario = $1, until = $2, type = $3, value = $4, updated_at = $5 WHERE id = $6";
        $update_result = pg_query_params($dbconn, $update_query, [
            $scenario,
            $until,
            $type,
            $value,
            $current_time,
            $id
        ]);

        if($update_result) {
            $success = "Record updated successfully.";
            $result = pg_query_params($dbconn, $fetch_query, [$id]);
            $decision = pg_fetch_assoc($result);
        } else {
            $error = "Error updating record: " . pg_last_error($dbconn);
        }
    }

?>