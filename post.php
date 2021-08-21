<?php
    include(__DIR__ . '/api/functions.php');
    include(__DIR__ . '/api/SQL.php');
    $DB = new Database('localhost', 'root', '', '@channel');

    $Post = $DB->Query("SELECT post.id, post.post_title, post.post_content, post.post_user, post.posted_at FROM post WHERE id = ?;", $_GET['post_id'])->AsArray();
    if(!isset($Post[0]))
         header('Location: /404page');
    $Post = $Post[0];
    $Post['likes'] = $DB->Query("SELECT * FROM post_like WHERE post_id = ?", $_GET['post_id'])->NumRows();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A network of communities where people create, share and react to others thoughts and ideas">
    <title>@<?php echo $_GET['channel_name'] ?> | Post</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css" />
</head>
<body>
    <!--The Navigation Bar-->
    <div class = 'row navbar'>
        <button class = 'col-1 button text-center navbar-element' onclick = "location.href  = '/'">@channel</button>
        <div class = 'col-4 navbar-element no-padding no-clearfix'>
            <input type = 'text' name = 'search' placeholder="Search..." class = 'col-12 input input-text' oninput="GetSearchResults(this)" onfocus="DisplayResults(true)"/>
            <div class = 'col-4 result-container' id = 'result-container' onmouseleave="DisplayResults(false)"></div>
        </div>
        <div class = 'col-7 navbar-element no-padding o-600'>
        <?php
                session_start();    
                if(!isset($_SESSION['USER_ID'])) {
                    echo 
                    "
                    <button class = 'button align-right' onclick=\"location.href = '/login.html'\">Login</button>
                    <button class = 'button align-right' onclick=\"location.href = '/register.html'\">Sign up</button>
                    ";
                } else {
                    echo
                    "
                    <button class = 'button button-badge align-right' onclick =\"ToggleTheme()\"><i class = 'fa fa-adjust'></i></button>
                    <button class = 'button button-badge align-right' onclick =\"Logout()\"><i class = 'fa fa-key'></i></button>
                    <button class = 'button button-badge align-right' onclick =\"location.href = '/chat'\">
                        <i class = 'fa fa-envelope'></i>
                        <span class = 'indicator' id = 'chat-notification-indicator'></span>
                    </button>
                    ";
                }
            ?>
        </div>
    </div>
    <!----------------------->
    
    <!-------The Main Content------->
    <div class = 'row main-content'>
        <div class = 'col-8 card-container' id = 'channel-feed-container'>
            <!--------------The Actual Post-------------->
            <div class = 'row card' data-post-id = '<?php echo $Post['id']; ?>'>
                <div class = 'row header'>
                    <div class = 'col-12'><?php echo $Post['post_title']?></div>
                    <div class = 'col-8 font-subtitle' style = 'padding-bottom: 0'> Posted by u/<?php echo $Post['post_user']?></div>
                    <div class = 'col-4 font-subtitle text-right' style = 'padding-bottom: 0'><?php echo $Post['posted_at']?></div>
                </div>
                <div class = 'content no-border-bottom-radius'>
                    <?php echo $Post['post_content']; ?>
                </div>
                <div class = 'flex'>
                    <button class = 'button button-reaction button-reaction-like flex-content' id = 'like-button'><i class = 'fa fa-thumbs-up'></i>Like<span class = 'counter'><?php echo $Post['likes']?></span></button>
                    <button class = 'button button-reaction button-reaction-comment flex-content' data-button-reply = "0"><i class = 'fa fa-comment'></i>Comment</button>
                    <button class = 'button button-reaction button-reaction-favourite flex-content'><i class = 'fa fa-heart'></i>Favourite</button>
                </div>
            </div>
            <!------------------------------------------>
            <!---------------The Comments--------------->
            <div class = 'row card'>
                <div class = 'row header'>Comments</div>
                <div class = 'row content' id = 'comment-0'>
                    <!----------------TEMPLATE------------------>
                    <?php
                        function AddComment($CommentID, $CommentAuthor, $CommentTimestamp, $Comment, $Post, $DB) {
                            echo
                            "
                            <div class = 'comment' id = 'comment-$CommentID'>
                                <div class = 'col-6 comment-author'>u/$CommentAuthor</div>
                                <div class = 'col-6 comment-timestamp'>$CommentTimestamp</div>
                                <div class = 'comment-content'>$Comment</div>
                                <button class = 'button-text-only' data-button-reply = '$CommentID'>Reply</button>
                            ";

                            /*
                            ** Get All replies fot this comment.
                            */
                            $Replies = $DB->Query("SELECT * FROM post_comment WHERE post_id = ? AND parent_id = ?", $Post, $CommentID)->AsArray();
                            
                            /*
                            ** For each reply proceed the same ritual.
                            */
                            foreach($Replies as $Reply)
                                AddComment($Reply['comment_id'], $Reply['comment_author'], $Reply['commented_at'], $Reply['comment_content'], $Post, $DB);
                            echo 
                            "
                            </div>
                            ";
                        }
                        /**
                         * Get All the comments belonging to this post.
                         */
                        $Comments = $DB->Query("SELECT * FROM post_comment WHERE post_id = ? AND parent_id = 0", $Post['id'])->AsArray();
                        foreach($Comments as $Comment)
                            AddComment($Comment['comment_id'], $Comment['comment_author'], $Comment['commented_at'], $Comment['comment_content'], $Post['id'], $DB);
                    ?>
                    <!--<div class = 'comment'>
                        <div class = 'col-6 comment-author'>u/zerozero3</div>
                        <div class = 'col-6 comment-timestamp'>2020-11-12 09:36:08</div>
                        <div class = 'comment-content'>This is worthless</div>
                        <button class = 'button-text-only' data-button-reply = '4'>Reply</button>
                    </div>-->
                    <!------------------------------------------>
                </div>
            </div>
            <!------------------------------------------>
        </div>
        <div class = 'col-4 card-container' id = 'channel-info-container'>
        <?php
                $ChannelInfo = $DB->Query("SELECT * FROM channel WHERE channel_name = ?", $_GET['channel_name'])->AsArray()[0];
                echo
                "
                <div class = 'card'>
                    <div class = 'header'>The @" . $ChannelInfo['channel_name'] . " Channel</div>
                    <div class = 'row content'>
                        <div class = 'col-12'>
                            <b>Brief</b><br/>
                            " . $ChannelInfo['channel_brief'] . "
                        </div>
                        <div class = 'col-12'>
                            <b>Description</b><br/>
                            " . $ChannelInfo['channel_description'] . "
                        </div>
                        <div class = 'col-12 flex'>
                            <b class = 'flex-content'>Founder</b>
                            <span class = 'flex-content'>" . $ChannelInfo['channel_founder'] . "</span>
                        </div>
                        <div class = 'col-12 flex'>
                            <b class = 'flex-content'>Founded on</b>
                            <span class = 'flex-content'>" . $ChannelInfo['created_at'] . "</span>
                        </div>
                        <button class = 'col-12 button' onclick = 'location.href = \"/@" . $ChannelInfo['channel_name'] . "/new\"'><i class = 'fa fa-plus'></i> New Post</button>
                    </div>
                </div>
                ";
                echo $ChannelInfo['channel_cards'];
            ?>
        </div>
    </div>
    <!------------------------------>

    <!--The Popup / Prompt Modal-->
    <div class = 'modal' id = 'modal'>
        <div class = "modal-content">
            <div class = 'row header'>
                <span class = 'col-11' id = 'modal-header'></span>
                <button class = 'button modal-close align-right' id = 'modal-close-button'><i class = 'fa fa-times'></i></button>
            </div>
            <div class = 'body' id = 'modal-body'></div>
        </div>
    </div>
    <!---------------------------->

    <script src = '/js/base.js'></script>
    <script src = '/js/notification.js'></script>
    <script>
        /**
         * The user must be authenticated to visit this site.
         */
        Authenticate(false, '', '',
            (JSONResponse) => { success(`Authentication successful (u/${JSONResponse.username})`) },
            (JSONResponse) => {
                error(`Failed to authenticate (Response from server : ${JSONResponse.reason})`);
                Prompt('Authentication Required', 
                    
                    `
                    <p>You are not authorized to visit this page. Please try after logging in.</p>
                    <p>Response From the server : <b> ${JSONResponse.reason} </b> </p>
                    <br/><br/>

                    <button class = 'col-6 button' onclick = "location.href = '/login'">Login</button><button class = 'col-6 button' onclick = "location.href = '/register'">Signup</button>
                    `,
                    Colors.warn); 
            }
        );
        function _(Command, Value) {   document.execCommand(Command, false, Value);    }

        function AddComment(ID, ParentID, CommentContents, CommentAuthor, CommentTimeStamp) {
            var HTML =
            `
                <div class = 'comment' id = 'comment-${ID}'>
                    <div class = 'col-6 comment-author'>u/${CommentAuthor}</div>
                    <div class = 'col-6 comment-timestamp'>${CommentTimeStamp}</div>
                    <div class = 'comment-content'>${CommentContents}</div>
                    <button class = 'button-text-only' data-button-reply = '${ID}'>Reply</button>
                </div>
            `;
            warn('Parent-id ' + ParentID);
            document.getElementById(ParentID).insertAdjacentHTML('beforeend', HTML);
        }

        const ChannelName = "<?php echo $_GET['channel_name']?>";
        const PostID = parseInt("<?php echo $Post['id']?>");
        document.getElementById('like-button').addEventListener('click', (ClickEvent) => {
            const Target = document.getElementById('like-button');
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if(this.readyState == 4 && this.status == 200) {
                    warn(this.responseText);
                    var Result = JSON.parse(this.response).result;
                    Target.setAttribute('data-reaction-status', Result.like_status);
                    Target.children[1].innerText = Result.likes;
                }
            }
            xhr.open('POST', '/api/post', true);
            xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
            xhr.send( JSON.stringify({'request_type' : 'reaction_like', 'request' : { 'channel' : ChannelName, 'id' : PostID}}) )
        });

        window.addEventListener('click', (MouseClickEvent) => {
            const Target = MouseClickEvent.target || MouseClickEvent.srcElement;
            if(!Target.hasAttribute('data-button-reply')) return;

            const ReplyID = Target.getAttribute('data-button-reply');
            if(!Target.hasAttribute('data-reply-confirm')) {
                if(!Target.hasAttribute('data-button-reply')) return;
                Popup('Replying to comment : ' + truncate(Target.parentElement.children[2].innerText, 32), 
                `
                    <div id = 'reply-editor' contenteditable>Your Reply Here</div>
                    <div class = 'row' style="position: sticky; top: 100%;">
                        <div class = 'col-12 tooltip' id = 'tooltip'></div>
                        <div class = 'col-12 flex'>
                            <button class = 'button button-badge' onclick="_('bold')" data-requires-tooltip data-tooltip = 'Bold Text'><i class = 'fa fa-bold'></i></button>
                            <button class = 'button button-badge' onclick="_('italic')" data-requires-tooltip data-tooltip = 'Italic Text'><i class = 'fa fa-italic'></i></button>
                            <button class = 'button button-badge' onclick="_('underline')" data-requires-tooltip data-tooltip = 'Underline Text'><i class = 'fa fa-underline'></i></button>
                            <button class = 'button button-badge' onclick="_('strikeThrough')" data-requires-tooltip data-tooltip = 'StrikeThrough Text'><i class = 'fa fa-strikethrough'></i></button>
                            
                            <span class = 'seperator'></span>
                            
                            <button class = 'button button-badge' onclick="_('justifyLeft')" data-requires-tooltip data-tooltip = 'Align Left'><i class = 'fa fa-align-left'></i></button>
                            <button class = 'button button-badge' onclick="_('justifyCenter')" data-requires-tooltip data-tooltip = 'Align Center'><i class = 'fa fa-align-center'></i></button>
                            <button class = 'button button-badge' onclick="_('justifyRight')" data-requires-tooltip data-tooltip = 'Align Right'><i class = 'fa fa-align-right'></i></button>
                            <button class = 'button button-badge' onclick="_('justifyFull')" data-requires-tooltip data-tooltip = 'Align Justify'><i class = 'fa fa-align-justify'></i></button>

                            <span class = 'seperator'></span>

                            <button class = 'button button-badge' onclick="_('indent')" data-requires-tooltip data-tooltip = 'Indent'><i class = 'fa fa-indent' ></i></button>
                            <button class = 'button button-badge' onclick="_('outdent')" data-requires-tooltip data-tooltip = 'Outdent'><i class = 'fa fa-outdent'></i></button>
                            
                            <span class = 'seperator'></span>

                            <button class = 'button button-badge' onclick="_('insertunorderedlist')" data-requires-tooltip data-tooltip = 'Unordered List (Bullets)'><i class = 'fa fa-list-ul'></i></button>
                            <button class = 'button button-badge' onclick="_('insertorderedlist')" data-requires-tooltip data-tooltip = 'Ordered List (Numeric)'><i class = 'fa fa-list-ol'></i></button>

                            <span class = 'seperator'></span>

                            <button class = 'button button-badge' onclick="_('undo')" data-requires-tooltip data-tooltip = 'Undo'><i class = 'fa fa-undo'></i></button>
                            <button class = 'button button-badge' onclick="_('redo')"' data-requires-tooltip data-tooltip = 'Redo'><i class = 'fa fa-redo'></i></button>
                        </div>
                    <div>
                    <button class = 'button' data-reply-confirm = '${ReplyID}' onclick = 'reply(this)'><i class = 'fa fa-reply'></i> Reply</button>
                `);
            };
        });

        function reply(ReplyButton) {
            const xhr = new XMLHttpRequest();
            xhr.onreadystatechange = function() {
                if(this.readyState == 4 && this.status == 200) {
                    var Result = JSON.parse(this.responseText).result;
                    var ParentContainer = document.getElementById(`comment-${Result.parent}`);
                    AddComment(Result.id, `comment-${Result.parent}`, Result.content, Result.author, Result.timestamp);
                    ClosePopupPrompt();
                }
            }
            xhr.open('POST', '/api/post', true);
            xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
            xhr.send( JSON.stringify({'request_type' : 'reply', 'request' : { 'post' : PostID, 'parent' : ReplyButton.getAttribute('data-reply-confirm'), 'content' : document.getElementById('reply-editor').innerHTML}}) ); 
        }

        /**
         * Global Invocations
         */
        setInterval(() => {
            HandleGlobalChatNotification('chat-notification-indicator');
        }, 1000);
    </script>
</body>
</html>