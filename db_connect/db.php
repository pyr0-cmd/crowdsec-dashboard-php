<?php
    function connect_db(){
        $host = 'localhost';
        $port = 5432;
        $dbname = 'crowdsec';
        $username = 'crowdsec';
        $password = 'abc@123';
    
        $db_conn = pg_connect("host = $host port = $port dbname = $dbname user = $username password = $password");
        if(!$db_conn){
            die('Database connection failed: ' . pg_last_error());
        }
        return $db_conn;
    }

    //Count Alerts
    function count_alerts(){
        $dbconn = connect_db();
        $query_alerts = 'SELECT id FROM alerts';
        $rs_alerts = pg_query($dbconn, $query_alerts);

        if (!$rs_alerts) {
            echo json_encode(['error' => 'Error executing query: ' . pg_last_error()]);
            pg_close($dbconn);
            exit();
        }

        $alerts = array();
        while ($row = pg_fetch_assoc($rs_alerts)) {
            $alerts[] = $row;
        }

        $total_alerts = count($alerts);

        pg_free_result($rs_alerts);
        pg_close($dbconn);
        return $total_alerts;
    }

    //Count Decisions
    function count_decisions(){
        $dbconn = connect_db();
        $query_decisions = 'SELECT id FROM decisions';
        $rs_decisions = pg_query($dbconn, $query_decisions);

        if (!$rs_decisions) {
            echo json_encode(['error' => 'Error executing query: ' . pg_last_error()]);
            pg_close($dbconn);
            exit();
        }

        $decisions = array();
        while ($row = pg_fetch_assoc($rs_decisions)) {
            $decisions[] = $row;
        }

        $total_decisions = count($decisions);

        pg_free_result($rs_decisions);
        pg_close($dbconn);
        return $total_decisions;
    }

    //Count Bouncers
    function count_bouncers(){
        $dbconn = connect_db();
        $query_bouncers = 'SELECT id FROM bouncers';
        $rs_bouncers = pg_query($dbconn, $query_bouncers);
    
        if (!$rs_bouncers) {
            echo json_encode(['error' => 'Error executing query: ' . pg_last_error()]);
            pg_close($dbconn);
            exit();
        }
    
        $bouncers = array();
        while ($row = pg_fetch_assoc($rs_bouncers)) {
            $bouncers[] = $row;
        }
    
        $total_bouncers = count($bouncers);

        pg_free_result($rs_bouncers);
        pg_close($dbconn);
        return $total_bouncers;
    }
    
?>