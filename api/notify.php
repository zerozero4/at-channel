<?php
    $Request = file_get_contents('php://input');
    $Request = json_decode($Request);

    
    include('functions.php');
    include_once('SQL.php');
    $DB = new Database('localhost', 'root', '', '@channel');

    if(isSessionValid()) {
        switch($Request->request_type) {
            case 'chat':
                                /**
                                 * The client wants to know messages that are sent after his last chat activity.
                                 * 
                                 * Parameters:
                                 *      (i) The client
                                 */
                                $Notifications = 0;
                                
                                // First Query all of his last chat activity.
                                $LastActivities = $DB->Query("SELECT chat_id, last_chat_activity FROM chat_activity WHERE chat_user = ?", $_SESSION['USER_ID'])->AsArray();

                                /**
                                 * Now Query for all chat messages that
                                 * popped after his last activity.
                                 */
                                foreach($LastActivities as $Activity) {
                                    $Notifications += $DB->Query("SELECT * FROM chat WHERE id = ? AND sent_at > ?", $Activity['chat_id'], $Activity['last_chat_activity'])->NumRows();
                                }

                                respondJSON( [ "success" => true, "notifications" => $Notifications, "last_activities" => $LastActivities ] );
                                break;
            case 'chat_group':
                                /**
                                 * The client wants to know messages that are sent after his last chat activity for various different groups.
                                 * 
                                 * Parameters:
                                 *      (i) The client
                                 */
                                $Notifications = array();
                                
                                // First Query all of his last chat activity.
                                $LastActivities = $DB->Query("SELECT chat_id, last_chat_activity FROM chat_activity WHERE chat_user = ?", $_SESSION['USER_ID'])->AsArray();

                                /**
                                 * Now Query for all chat messages that
                                 * popped after his last activity.
                                 */
                                foreach($LastActivities as $Activity) {
                                    $Notifications[ str_replace( $_SESSION['USER_ID'], '', $Activity['chat_id'] ) ] = $DB->Query("SELECT * FROM chat WHERE id = ? AND sent_at > ?", $Activity['chat_id'], $Activity['last_chat_activity'])->NumRows();
                                }

                                respondJSON( [ "success" => true, "notifications" => $Notifications ] );
                                break;
            
            default:            
                                /**
                                 * Undefined request / Invalid API request.
                                 */
                                respondJSON( [ "success" => false, "reason" => "Invalid API Request"] );
        }
    } else {
        respondJSON( [ "success" => false, "reason" => "an active session is not detected" ] );
    }
?>