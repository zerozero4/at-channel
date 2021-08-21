<?php
    $Request = file_get_contents('php://input');
    $Request = json_decode($Request);

    include('functions.php');
    include_once('SQL.php');
    $DB = new Database('localhost', 'root', '', '@channel');

    switch($Request->request_type) {
        case 'validation':
                            /**
                             * The client wants to validate the channel name.
                             */
                            $Request = $Request->request;
                            respondJSON( [ "success" => $DB->Query("SELECT * FROM channel WHERE channel_name = ?", $Request->name)->NumRows() == 0 ] );
        case 'registration':
                            /**
                             * The client wants to register a new channel.
                             * 
                             * Parameters:
                             *      (i)     Channel name
                             *      (ii)    Channel brief
                             *      (iii)   Channel description
                             *      (iv)    (Optional) Channel cards
                             */
                            $Request = $Request->request;
                            if(IsSessionValid())
                                respondJSON(
                                    [
                                        "success" => $DB->Query("INSERT INTO channel (`channel_name`, `channel_founder`, `channel_brief`, `channel_description`, `channel_cards`) VALUES (?, ?, ?, ?, ?)", $Request->name, $_SESSION['USER_ID'], $Request->brief, $Request->description, $Request->cards)->AffectedRows() == 1
                                    ]
                                );
                            else
                                respondJSON(
                                    [
                                        "success" => false,
                                        "reason"  => "An active session is not detected"
                                    ]
                                );
        default:
                            repondJSON(
                                [
                                    "success" => false,
                                    "reason"  => "Invalid API Request"
                                ]
                            );
    }
?>