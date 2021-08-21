<?php
    include("SQL.php");

    if(isset($_GET['username'])) {
        if(strlen($_GET['username']) < 1) {
            echo json_encode( ["result" => ["success" => false, "reason" => "Username should not be blank"]] );
            exit();
        }
        $DB = new Database('localhost', 'root', '', '@channel');

        $Count = $DB->Query("SELECT * FROM user WHERE username = ?;", $_GET['username'])->NumRows();

        if($Count > 0) echo json_encode( ["result" => ["success" => false, "reason" => "Username already taken"]] );
        else echo json_encode( ["result" => ["success" => true, "reason" => "Username not registered"]] );
    }

    if(isset($_GET['email'])) {
        if(strlen($_GET['email']) < 1) {
            echo json_encode( ["result" => ["success" => false, "reason" => "E-Mail should not be blank"]] );
            exit();
        }
        $DB = new Database('localhost', 'root', '', '@channel');

        $Count = $DB->Query("SELECT * FROM user WHERE email = ?;", $_GET['email'])->NumRows();

        if($Count > 0) echo json_encode( ["result" => ["success" => false, "reason" => "Email already registered"]] );
        else echo json_encode( ["result" => ["success" => true, "reason" => "EMail not registered "]] );
    }
?>