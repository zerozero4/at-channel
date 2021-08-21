<?php 
    if(isset($_GET['channel_name'])) {
        include_once( __DIR__ . '/api/functions.php');
        include_once( __DIR__ . '/api/SQL.php');
        $DB = new Database('localhost', 'root', '', '@channel');

        if($DB->Query('SELECT * FROM channel WHERE channel_name = ?', $_GET['channel_name'])->AffectedRows() != 1)
            header('Location: /404page');
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A network of communities where people create, share and react to others thoughts and ideas">
    <title>@channel | <?php echo $_GET['channel_name'] ?></title>
    
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
    <!---------------------->

    <!-------The Main Content------->
    <div class = 'row main-content'>
        <div class = 'col-8 card-container' id = 'channel-feed-container'></div>
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
                    Popup('Attention', 
                        `
                        Looks like you have not logged in / your session has been expired.<br/>
                        This may reduce certian functionalities provided by the page.

                        <button class = 'col-6 button' onclick = "location.href = '/login'">Login</button><button class = 'col-6 button' onclick = "location.href = '/register'">Signup</button>
                        `,
                        Colors.warn); 
                }
            );
        
        const ChannelName = "<?php echo $_GET['channel_name'];?>";
        const PostContainer = document.getElementById('channel-feed-container');

        function AddPostAtBottom(PostID, PostTitle, PostAuthor, PostTimestamp, PostChannel, PostContent, PostLikes, PostComments, PostUserLike) {
            if(document.querySelector(`[data-post-id = '${PostID}']`) != undefined) {
                warn('Post Already Exists');
                return;
            } 
            PostLikes = PostLikes > 0 ? PostLikes : '';
            PostUserLike = PostUserLike == 1 ? 'like' : 'unlike';
            PostComments = PostComments > 0 ? PostComments : '';
            HTML =
            `
            <div class = 'row card' data-post-id = '${PostID}' data-post-timestamp = '${PostTimestamp}' data-post-channel='${PostChannel}'>
                <div class = 'row header' style = 'cursor: pointer;' onclick = "location.href = '/@${PostChannel}/${PostID}/'">
                    <div class = 'col-12'>${PostTitle}</div>
                    <div class = 'col-8 font-subtitle'>Posted by <a href = '/u/${PostAuthor}'>u/${PostAuthor}</a></div>
                    <div class = 'col-4 font-subtitle text-right'>${PostTimestamp}</div>
                </div>
                <div class = 'content no-border-bottom-radius'>
                    ${PostContent}
                </div>
                <div class = 'flex'>
                    <button class = 'button button-reaction button-reaction-like flex-content' data-button-like data-reaction-status = '${PostUserLike}'><i class = 'fa fa-thumbs-up'></i>Like <span class = 'counter'> ${PostLikes} </span> </button>
                    <button class = 'button button-reaction button-reaction-comment flex-content' onclick = "location.href = '/@${PostChannel}/${PostID}';"><i class = 'fa fa-comment'></i>Comment <span class = 'counter'> ${PostComments} </span> </button>
                    <button class = 'button button-reaction button-reaction-favourite flex-content'><i class = 'fa fa-heart'></i>Favourite</button>
                </div>
            </div>
            `;

            PostContainer.insertAdjacentHTML('beforeend', HTML);
        }

        function LazyLoadPosts(Count) {
            if(PostContainer.lastElementChild)
                var LastPost = PostContainer.lastElementChild.getAttribute('data-post-timestamp');
            else
                var LastPost = null;
            const xhr = new XMLHttpRequest();

            xhr.onreadystatechange = function() {
                if(this.readyState == 4 && this.status == 200) {
                    warn(this.responseText);
                    var Result = JSON.parse(this.responseText).result;
                    Result.posts.forEach((Post) => {
                        AddPostAtBottom(Post.id, Post.post_title, Post.post_user, Post.posted_at, Post.channel_name, Post.post_content, Post.likes, Post.comments, Post.user_like);
                    });
                }
            }
            xhr.open('POST', '/api/post', true);
            xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
            xhr.send( JSON.stringify({'request_type' : 'get_posts', 'request' : { 'channel' : ChannelName, 'after_timestamp' : LastPost, 'count' : Count}}) )
        }

        window.addEventListener('scroll', (ScrollEvent) => {
            if((document.documentElement.scrollTop + document.documentElement.clientHeight) / document.documentElement.scrollHeight > 0.75) {
               const LastTimeStamp = PostContainer.lastElementChild.getAttribute('data-post-timestamp');
               warn(LastTimeStamp);
               LazyLoadPosts(4, LastTimeStamp);
            }
        });

        LazyLoadPosts(4);

        window.addEventListener('click', (ClickEvent) => {
            var Target = ClickEvent.target;
            if(Target.classList.contains('button-reaction-like')) {
                // The user has clicked the like button.
                const xhr = new XMLHttpRequest();
                const PostID = Target.parentElement.parentElement.getAttribute('data-post-id');
                const ChannelName = Target.parentElement.parentElement.getAttribute('data-post-channel');
                xhr.onreadystatechange = function() {
                    if(this.readyState == 4 && this.status == 200) {
                        warn(this.responseText);
                        var Result = JSON.parse(this.response).result;
                        Target.setAttribute('data-reaction-status', Result.like_status);
                        Result.likes = Result.likes == 0 ? '' : Result.likes;
                        Target.children[1].innerText = Result.likes;
                    }
                }
                xhr.open('POST', '/api/post', true);
                xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
                xhr.send( JSON.stringify({'request_type' : 'reaction_like', 'request' : { 'channel' : ChannelName, 'id' : PostID}}) )
            }
        })

        /**
         * Global Invocations
         */
        setInterval(() => {
            HandleGlobalChatNotification('chat-notification-indicator');
        }, 1000);
    </script>
</body>
</html>