<?php
    if(isset($_GET['in']) && $_GET['in'] != '') {
        include('SQL.php');

        $DB = new Database('localhost', 'root', '', '@channel');
        $Channels   = $DB->Query("SELECT * FROM channel WHERE channel_name LIKE CONCAT('%', ?, '%');", $_GET['in'])->AsArray();
        $Users      = $DB->Query("SELECT * FROM user WHERE username LIKE CONCAT('%', ?, '%');", $_GET['in'])->AsArray();

        echo json_encode( array("result" => array("channels" => $Channels, "users" => $Users)) );
    } else {
        echo json_encode( array("result" => array("channels" => array(), "users" => array())) );
    }
?>