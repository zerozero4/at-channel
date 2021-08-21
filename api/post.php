<?php
    $Request = file_get_contents('php://input');
    $Request = json_decode($Request);

    include('functions.php');
    include_once('SQL.php');
    $DB = new Database('localhost', 'root', '', '@channel');
    switch($Request->request_type) {
        case 'all_posts':
                            /**
                             * The client has requested all the posts.
                             * 
                             * Parameters:
                             *      (i)     Number of Posts to load
                             *      (ii)    The timestamp after which the posts should be loaded.
                             */
                            session_start();
                            $Request = $Request->request;
                            if($Request->after_timestamp == null) 
                                $Posts = $DB->Query("SELECT * FROM post ORDER BY posted_at DESC LIMIT ?", $Request->count)->AsArray();
                            else
                                $Posts = $DB->Query("SELECT * FROM post WHERE posted_at < ? ORDER BY posted_at DESC LIMIT ?", $Request->after_timestamp, $Request->count)->AsArray();
                            
                            foreach($Posts as $Key => $Post) {
                                $Likes = $DB->Query("SELECT post_id FROM post_like WHERE post_id = ?", $Post['id'])->NumRows();
                                $UserLike = $DB->Query("SELECT * FROM post_like WHERE post_id = ? AND username = ?", $Post['id'], $_SESSION['USER_ID'])->NumRows();
                                $Comments = $DB->Query("SELECT * FROM post_comment WHERE post_id = ? AND parent_id = 0", $Post['id'])->NumRows();
                                
                                $Posts[$Key]['likes'] = $Likes;
                                $Posts[$Key]['comments'] = $Comments;
                                $Posts[$Key]['user_like'] = $UserLike;
                            }

                            respondJSON( [ "posts" => $Posts ] );
                            break;
        case 'get_posts':
                            /**
                             * The client has requested posts that are available.
                             * 
                             * Parameters:
                             *      (i)     The channel name
                             *      (ii)    Number of posts to load
                             *      (iii)   Posts after certain time point
                             */
                            session_start();
                            $Request = $Request->request;
                            if($Request->after_timestamp == null)
                                $Posts = $DB->Query("SELECT * FROM post WHERE channel_name = ? ORDER BY posted_at DESC LIMIT ?", $Request->channel, $Request->count)->AsArray();
                            else
                                $Posts = $DB->Query("SELECT * FROM post WHERE channel_name = ? AND posted_at < ? ORDER BY posted_at DESC LIMIT ?", $Request->channel, $Request->after_timestamp, $Request->count)->AsArray();

                            /**
                             * Gather the like information.
                            **/
                            foreach($Posts as $Key => $Post) {
                                $Likes = $DB->Query("SELECT post_id FROM post_like WHERE post_id = ?", $Post['id'])->NumRows();
                                $UserLike = $DB->Query("SELECT * FROM post_like WHERE post_id = ? AND username = ?", $Post['id'], $_SESSION['USER_ID'])->NumRows();
                                $Comments = $DB->Query("SELECT * FROM post_comment WHERE post_id = ? AND parent_id = 0", $Post['id'])->NumRows();

                                $Posts[$Key]['likes'] = $Likes;
                                $Posts[$Key]['comments'] = $Comments;
                                $Posts[$Key]['user_like'] = $UserLike;
                            }
                            
                            respondJSON(["posts" => $Posts, "request" => $Request]);
                            break;
        case 'get_user_posts':
                            /**
                             * The client wants to get posts specific to a user.
                             * 
                             * Parameters:
                             *      (i)     The username
                             *      (ii)    Number of posts to load
                             *      (iii)   Posts after certain time point
                            **/
                            session_start();
                            $Request = $Request->request;
                            if($Request->after_timestamp == null) 
                                $Posts = $DB->Query("SELECT * FROM post WHERE post_user = ? ORDER BY posted_at DESC LIMIT ?", $Request->username, $Request->count)->AsArray();
                            else
                                $Posts = $DB->Query("SELECT * FROM post WHERE post_user = ? AND posted_at < ? ORDER BY posted_at DESC LIMIT ?", $Request->username, $Request->after_timestamp, $Request->count)->AsArray();
                            
                            foreach($Posts as $Key => $Post) {
                                $Likes = $DB->Query("SELECT post_id FROM post_like WHERE post_id = ?", $Post['id'])->NumRows();
                                $UserLike = $DB->Query("SELECT * FROM post_like WHERE post_id = ? AND username = ?", $Post['id'], $_SESSION['USER_ID'])->NumRows();
                                $Comments = $DB->Query("SELECT * FROM post_comment WHERE post_id = ? AND parent_id = 0", $Post['id'])->NumRows();
                                
                                $Posts[$Key]['likes'] = $Likes;
                                $Posts[$Key]['comments'] = $Comments;
                                $Posts[$Key]['user_like'] = $UserLike;
                            }

                            respondJSON( [ "posts" => $Posts ] );
                            break;
        case 'new_text_post':
                            /**
                             * The client has requested to add a new post.
                             * 
                             * Parameters:
                             *      (i)     The channel name
                             *      (ii)    The Post Title
                             *      (iii)   The Post Content
                             *      (iv)    The author
                             */
                            if(IsSessionValid()) {
                                $Request = $Request->request;
                                if($DB->Query("INSERT INTO post (`channel_name`, `post_user`, `post_title`, `post_content`) VALUES (?, ?, ?, ?)", $Request->channel_name, $_SESSION['USER_ID'], $Request->post_title, $Request->post_content)->AffectedRows() == 1) {
                                    respondJSON( [ "success" => true ] );
                                } else {
                                    respondJSON( [ "success" => false, "reason" => "Unexpected Internal Error Occured" ] );
                                }
                            } else {
                                respondJSON( [ "success" => false, "reason" => "An active session is not deteced" ] );
                            }
                            break;
        case 'reaction_like':
                            /**
                             * The client has requested to update the like status of the current user.
                             * 
                             * Parameters:
                             *      (i)     The client.
                            **/
                            $Request = $Request->request;
                            if(IsSessionValid()) {
                                if($DB->Query('SELECT * FROM post_like WHERE post_id = ? AND post_channel = ? AND username = ?', $Request->id, $Request->channel, $_SESSION['USER_ID'])->AffectedRows() === 1) {
                                    // The user has already liked the post.
                                    // unlike procedure.
                                    $DB->QUERY("DELETE FROM post_like WHERE post_id = ? AND post_channel = ? AND username = ?", $Request->id, $Request->channel, $_SESSION['USER_ID']);
                                    $LikeStatus = 'unlike';
                                } else {
                                    // The user has liked the post.
                                    // like procedure
                                    $DB->QUERY("INSERT INTO post_like VALUES (?, ?, ?)", $Request->id, $Request->channel, $_SESSION['USER_ID']);
                                    $LikeStatus = 'like';
                                }
                                //Get the new likes
                                $Likes = $DB->QUERY("SELECT * FROM post_like WHERE post_id = ? AND post_channel = ?", $Request->id, $Request->channel)->AffectedRows();
                                respondJSON( ["like_status" => $LikeStatus, "likes" => $Likes ] );
                            }
        case 'reply':
                            /**
                             *  The client has requested to create a new reply. 
                             * 
                             * Parameters:
                             *      (i)     The Post ID
                             *      (ii)    The Parent Reply ID
                             *      (iii)   The Reply Contents
                            **/
                            $Request = $Request->request;
                            if(IsSessionValid()) {
                                $AffectedRows = $DB->Query("INSERT INTO post_comment (`post_id`, `parent_id`, `comment_author`, `comment_content`) VALUES (?, ?, ?, ?)", $Request->post, $Request->parent, $_SESSION['USER_ID'], $Request->content)->AffectedRows();
                                if($AffectedRows == 1) {
                                    $Reply = $DB->Query("SELECT * FROM post_comment ORDER BY commented_at DESC LIMIT 1")->AsArray()[0];
                                    respondJSON( [ "id" => $Reply['comment_id'], "parent" => $Reply['parent_id'], "content" => $Reply['comment_content'], "author" => $Reply['comment_author'], "timestamp" => $Reply['commented_at'] ] );
                                } else {
                                    respondJSON( [ "success" => false ] );
                                }
                            }
        default:
                            /**
                             * Invalid API Request
                             */
                            respondJSON(["success" => false, "reason" => 'Invalid API Request']);
    }
?>