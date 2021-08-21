<?php
    include('SQL.php');
    header('Content-type: application/json');

    function AuthenticateWithCredentials($Username, $Password) {
        $UsersDB = new Database('localhost', 'root', '', '@channel');

        /**
         * Before Querying, make sure to "pepper" the password.
         */
        $Password = hash("sha256", $Password);

        $User    = $UsersDB->Query('SELECT `password`, `hash` FROM user WHERE username = ?;', $Username)->AsArray();

        if(count($User) != 1)
            return json_encode( ["result" => ["success" => false, "response" => ["reason" => "Username does not exists"]] ] );

        if(password_verify($Password, $User[0]['password'])) {
            session_start();

            $_SESSION['USER_ID'] = $Username;
            $_SESSION['LAST_ACTIVITY_TIME'] = time();
            $_SESSION['CREATION_TIME'] = time();
            
            return json_encode( ["result" => ["success" => true, "response" => [ "reason" => "Username and password match", "username" => $Username]]] );
        } else {
            return json_encode( ["result" => ["success" => false, "response" => [ "reason" => "Username and password does not match { Username : $Username, Password : $Password }"]]] );
        }
    }

    function AuthenticateWithSessionInfo() {
        /**
         * If a session has already started,
         * try to verify him via USER_ID.
         */
        session_start();
        if(isset($_SESSION['USER_ID'])) {
            /**
             * Expire the current session if the user was inactive for long time.
            **/
            if (isset($_SESSION['LAST_ACTIVITY_TIME']) && (time() - $_SESSION['LAST_ACTIVITY_TIME'] > 1800)) {
                // The last request was more than 30 minutes (1800s) ago.
                session_unset();     // unset $_SESSION variable for the run-time.
                session_destroy();   // destroy session data in storage.

                return json_encode([ "result" => ["success" => false, "response" => [ "reason" => "The session user u/" . $_SESSION['USER_ID'] .  " logged in has expired"], "username" => $_SESSION['USER_ID'] ] ]);
            }

            $_SESSION['LAST_ACTIVITY_TIME'] = time(); // update last activity time stamp

            /**
             * Change the ID of the active session,
             * as a single fixed session Id is vulnarable for
             * `Session fixation` exploitation.
            **/
            if (!isset($_SESSION['CREATION_TIME']))
                $_SESSION['CREATION_TIME'] = time();
            else if (time() - $_SESSION['CREATION_TIME'] > 1800) {
                // session started more than 30 minutes (1800s) ago
                session_regenerate_id(true);            // change session ID for the current session and invalidate old session ID
                $_SESSION['CREATION_TIME'] = time();    // update creation time
            }

            return json_encode( ["result" => ["success" => true, "response" => [ "reason" => "User u/" . $_SESSION["USER_ID"] . " is valid", "username" => $_SESSION['USER_ID']]] ]);
        } else {
            return json_encode( ["result" => ["success" => false, "response" => ["reason" => "An active session is not detected."]]] );
        }
    }

    /**
     * The page requests for authentication.
    **/
    if(isset($_GET['authenticate'])) {
        if(isset($_GET['username']) && isset($_GET['password'])) {
            /**
             * The authnetication service must be offered via 
             * validating username and password.
            **/
            echo AuthenticateWithCredentials($_GET['username'], $_GET['password']);
        } else {
            /**
             * Since the request does not contian any credentials,
             * the authentication must be done via session info.
             * 
             * This comes in handly when the pages requires you to be logged in first.
            **/
            echo AuthenticateWithSessionInfo();
        }

        if(isset($_GET['to'])) {
            /**
             * The user tried to visit a page that requires authentication.
             * Take him back to the same page.
            **/
            $_SESSION['REDIRECT_TO'] = $_GET['to'];
        }
    } else {
        echo json_encode( ["result" => ["success" => false, "response" => ["result" => "Invalid API call"]]] );
    }
?>