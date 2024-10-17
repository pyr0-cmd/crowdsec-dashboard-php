<?php
    function count_decisions(){
        $dbconn = connect_db();
        $query_decisions = 'SELECT * FROM decisions';
        $rs_decisions = pg_query($dbconn, $query_decisions);

        if (!$rs_decisions) {
            echo json_encode(['error' => 'Error executing query: ' . pg_last_error()]);
            pg_close($dbconn);
            exit();
        }

        // Fetch data into an array
        $decisions = array();
        while ($row = pg_fetch_assoc($rs_decisions)) {
            $decisions[] = $row;
        }

        // Encode the data as JSON
        echo json_encode($decisions);
        // Free result set and close connection
        pg_free_result($rs_decisions);
        pg_close($dbconn);
    }
?>