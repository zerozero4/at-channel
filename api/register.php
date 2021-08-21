<?php
    /**
     * Make sure that the client has provided required details.
     */
    if(isset($_GET["username"]) && isset($_GET["email"]) && isset($_GET["password"])) {        
        include("SQL.php");
        $DB = new Database('localhost', 'root', '', '@channel');

        /**
         * Insert the registration details.
         * 
         * Before inserting, create `Pepper` for password and hash.
         */
        $PasswordPepper = hash("sha256", $_GET['password']);
        $HashPepper     = hash("sha256", $_GET['username']);

        /**
         * Now, before proceeding any further.
         * Make sure that the credentials are not registered.
         */
        $NumRows = $DB->Query("SELECT * FROM user WHERE username = ? OR email = ?", $_GET['username'], $_GET['email'])->NumRows();
        if($NumRows > 0) {
            echo json_encode( ["result" => ["success" => false, "reason" => "$NumRows"]] );
            exit();
        }


        $AffectedRows = $DB->Query("INSERT INTO user (`username`, `password`, `email`, `hash`) VALUES (?, ?, ?, ?);",
                            $_GET['username'],
                            password_hash($PasswordPepper, PASSWORD_DEFAULT),
                            $_GET['email'],
                            password_hash($HashPepper, PASSWORD_DEFAULT)
                        )->AffectedRows();
        
        if($AffectedRows === 1) {
            /**
             * The username and password is registered successfully.
             */
            echo json_encode( [ "result" => [ "success" => true, "reason" => "User (u/" . $_GET['username'] . ") registered successfully" ] ] );
        } else {
            /**
             * Should not be met. Probably because validation failed miserably.
             */
            echo json_encode( [ "result" => [ "success" => false, "reason" => "Internal SQL Error" ] ] );
        }
    } else {
        /**
         * Well, he was not the client. He's the imposter.
         */
        echo json_encode( [ "result" => [ "success" => false, "reason" => "Invalid request" ]] );
    }

?>