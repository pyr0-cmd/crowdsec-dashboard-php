<?php 
    include 'db_connect/decisions_list.php';

    $dec = decisionList();
    $test = json_encode($dec);

    echo $dec;
?>