<?php
    if(isset($_GET['get'])) {
        /**
         * Match the required request.
        **/
        include(__DIR__ . "SQL.php");
        include(__DIR__ . "functions.php");

        switch($_GET['get']) {
            case "active_user": {
                /**
                 * The Client requested for the active user.
                 * 
                 * First, make sure that the user is logged in and his session is valid.
                 * Then, return his username.
                 */
                session_start();
                if(isset($_SESSION['USER_ID']) && isset($_SESSION['LAST_ACTIVITY_TIME'])) {
                    if(time() - $_SESSION['LAST_ACTIVITY_TIME'] < 1800) {
                        /**
                         * The user is active. Return his handle.
                         */
                        respondJSON(["success" => true, "username" => $_SESSION['USER_ID']]);
                    } else {
                        /**
                         * The active session has been expired.
                         */
                        respondJSON(["success" => false, "reason" => "The session for the active user has been expired."]);
                    }
                } else {
                    respondJSON(["success" => false, "reason" => "There is no active session."]);
                }
            }
            default: {
                /**
                 * The Client does not provide a vaild request.
                 */
                respondJSON(["success" => false, "reason" => "Invalid API Request. Parameters does not match."]);
            }
        }

    } else {
        echo json_encode( [ "result" => ["success" => false, "response" => ["reason" => "Invalid API Request"]] ] );
    }
?>