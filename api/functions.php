<?php

    /**
     * This file contains common functions that
     * are used throughout the project.
     */

     /**
      * @brief Function to create a JSON encoded response to be sent back to the client.
      * 
      * @param JSON The JSON object (Native array) to be sent.
      */
    function respondJSON($JSON) {   echo json_encode( [ "result" => $JSON] ); exit(); }

    /**
     * @brief A function to check whether the session is valid or not.
     */
    function IsSessionValid() {
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

                //respondJSON(["success" => false, "response" => [ "reason" => "The session user u/" . $_SESSION['USER_ID'] .  " logged in has expired"], "username" => $_SESSION['USER_ID'] ]);
                return false;
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

            /**
             * Now register the last_activity time in database.
             */
            include_once('SQL.php');
            $DB = new Database('localhost', 'root', '', '@channel');
            $DB->Query("UPDATE user SET last_activity = CURRENT_TIMESTAMP WHERE username = ?", $_SESSION['USER_ID']);

            return true;
        } else {
            return false;
            //respondJSON( ["success" => false, "response" => ["reason" => "An active session is not detected."]] );
        }
    }

?>