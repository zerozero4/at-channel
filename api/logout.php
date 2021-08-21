<?php
    if(isset($_GET['username'])) {
        /**
         * If the requested username and the Session USER_ID Match.
         * Login the user out. and notify the client.
         */
        session_start();
        if($_SESSION['USER_ID'] == $_GET['username']) {
            session_unset();
            session_destroy();

            echo json_encode( ["result" => [ "success" => true, "response" => [ "reason" => "User u/" . $_GET['username'] . " logged out successfully" ] ]]  );
        } else {
            /**
             * The requested user has already logged out / session timed out.
             */
            echo json_encode( ["result" => [ "success" => false, "response" => [ "reason" => "User u/" . $_GET['username'] . " has already logged out / the session has timed out" ] ]]  );
        }
    } else {
        echo json_encode( ["result" => ["success" => false, "response" => ["reason" => "Invalid API Request"]]] );
    }
?>