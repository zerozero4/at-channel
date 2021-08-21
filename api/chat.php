<?php
    $Request = file_get_contents('php://input');
    $Request = json_decode($Request);

    
    include('functions.php');
    if(isSessionValid()) {
        $DB = new Database('localhost', 'root', '', '@channel');
        
            function UpdateChatActivity($ChatID, $DB) {
                $DB->Query("UPDATE chat_activity SET last_chat_activity = CURRENT_TIMESTAMP WHERE chat_id = ? AND chat_user = ?", $ChatID, $_SESSION['USER_ID']);
            }
        
            function GetChatID($Sender, $Receiver) {
                $ChatID = array($Sender, $Receiver);
                sort($ChatID);
                return $ChatID[0] . $ChatID[1];
            }
        
        switch($Request->request_type) {
            case 'create_group':
                                /**
                                 * The client wants to create a new message group
                                 * 
                                 * Parameters:
                                 *      (i)     The sender (the client)
                                 *      (ii)    The receiver
                                 */
                                $Request = $Request->request;
                                $ChatID = GetChatID($_SESSION['USER_ID'], $Request->receiver);

                                /**
                                 * This also records their respective last chat activitites
                                 * which is useful for notifications.
                                 */
                                $DB->Query("INSERT INTO chat_activity (`chat_id`, `chat_user`) VALUES (?, ?)", $ChatID, $_SESSION['USER_ID']);
                                $DB->Query("INSERT INTO chat_activity (`chat_id`, `chat_user`) VALUES (?, ?)", $ChatID, $Request->receiver);

                                respondJSON( [ "success" => true ] );
            case 'send_message':
                                /**
                                 * The client wants to send a message.
                                 * 
                                 * Parameters:
                                 *      (i)     The sender (the client)
                                 *      (ii)    The receiver.
                                 *      (iii)   The message to send.
                                 */
                                $Request = $Request->request;

                                //Create the Chat ID
                                $ChatID = array($_SESSION['USER_ID'], $Request->receiver);
                                sort($ChatID);
                                $ChatID = $ChatID[0] . $ChatID[1];
                                if($DB->Query("INSERT INTO chat (`id`, `sender`, `message`) VALUES (?, ?, ?)", $ChatID, $_SESSION['USER_ID'], $Request->message)->AffectedRows() != 1) {
                                    respondJSON(["success" => false, "response" => [ "reason" => "Unhandled Exception" ]]);
                                }
                                else {
                                    /**
                                     * Looks like the message has been handled successfully.
                                     * 
                                     * Now, Update the chat_activity
                                     */
                                    UpdateChatActivity($ChatID, $DB);
                                    respondJSON(["success" => true, "response" => [ "sender" => $_SESSION['USER_ID'], "receiver" => $Request->receiver, "time" => date("Y-m-d H:i:s") ]]);
                                }
                                break;
            case 'get_groups':
                                /**
                                 * Get groups by selecting unique chat activities
                                 */
                                $Groups = $DB->Query("SELECT DISTINCT(chat_id) FROM chat_activity WHERE chat_id LIKE CONCAT('%', ?, '%');", $_SESSION['USER_ID'])->AsArray();
                                respondJSON(["groups" => str_replace($_SESSION['USER_ID'], '', array_column($Groups, 'chat_id'))]);
                                break;
            case 'get_messages':
                                $Request = $Request->request;
                                /**
                                 * The client wants to get the messages.
                                 * 
                                 * Parameters:
                                 *          (i)     The sender and receiver info. (One of them must be logged in)
                                 *          (iii)   Number of messages required.
                                 */
                                $Messages = $DB->Query("SELECT `sender`, `message`, `sent_at` FROM chat WHERE id LIKE CONCAT('%', ?, '%') AND id like CONCAT('%', ?, '%') ORDER BY sent_at;", $_SESSION['USER_ID'], $Request->receiver)->AsArray();
                                UpdateChatActivity( GetChatID($_SESSION['USER_ID'], $Request->receiver), $DB);
                                respondJSON(["messages" => $Messages, "active_user" => $_SESSION['USER_ID']]);
            case 'get_latest_timestamp':
                                /**
                                 * The client wants the last message's timestamp.
                                 * 
                                 * Parameters:
                                 *          (i)     The sender and receiver info. (One of them must be logged in)
                                 */
                                $Request = $Request->request;
                                $Timestamp = $DB->Query("SELECT MAX(`sent_at`) as latest_time_stamp FROM chat WHERE id LIKE CONCAT('%', ?, '%') AND id LIKE CONCAT('%', ?, '%')", $_SESSION['USER_ID'], $Request->receiver)->AsArray();
                                respondJSON([ "success" => $Timestamp != null ? true : false, "latest_time_stamp" => $Timestamp[0]['latest_time_stamp']]);
            
            case 'get_users':
                                /**
                                 * The client wants the users who are communicatable.
                                 * 
                                 * Paramters:
                                 *          (i)     The Pattern to match.
                                 */
                                $Request    = $Request->request;
                                $Users      = $DB->Query("SELECT `username` as users FROM user WHERE username LIKE CONCAT('%', ?, '%') AND username != ?;", $Request->pattern, $_SESSION['USER_ID'])->AsArray();

                                respondJSON(["users" => array_column($Users, 'users')]);
                                break;
            
            case 'get_last_group':
                                /**
                                 * The client wants the last chat he attended.
                                 * 
                                 */
                                $LastChat   = $DB->Query("SELECT chat_id FROM chat_activity WHERE chat_user = ? ORDER BY last_chat_activity DESC LIMIT 1", $_SESSION['USER_ID'])->AsArray();
                                if(count($LastChat) == 1) {
                                    respondJSON(["last" => str_replace($_SESSION['USER_ID'], '', array_column($LastChat, 'chat_id')) ]);
                                } else {
                                    respondJSON(["last" => array()]);
                                }
                                respondJSON(["last" => array_column($LastChat, 'chat_id')]);
        }


    } else {
        respondJSON(["success" => false, "response" => [ "reason" => "An active session / user is not detected" ]]);
    }
?>