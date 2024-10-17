<?php
    include 'db.php';
    function alertList(){
        $dbconn = connect_db();
        $query_alerts = 'SELECT * FROM alerts';
        $rs_alerts = pg_query($dbconn, $query_alerts);

        if (!$rs_alerts) {
            echo json_encode(['error' => 'Error executing query: ' . pg_last_error()]);
            pg_close($dbconn);
            exit();
        }

        // Fetch data into an array
        $alerts = array();
        while ($row = pg_fetch_assoc($rs_alerts)) {
            $alerts[] = $row;
        }

        // Encode the data as JSON
        $alert_lst = json_encode($alerts);
        //$total_alerts = count($alerts);
        //echo "Total alerts: " . $total_alerts . "\n";
        // Free result set and close connection
        pg_free_result($rs_alerts);
        pg_close($dbconn);
        return $alert_lst;
    }
    
?>