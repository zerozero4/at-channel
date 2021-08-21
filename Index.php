<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A network of communities where people create, share and react to others thoughts and ideas">
    <title>@channel</title>
    <link rel="stylesheet" type="text/css" href="css/style.css" />
    <style>
        :root {
            --result-container-offset: calc( 2 * ( var(--base-padding)  + var(--input-padding)) + 8.25em)
        }
        .top {
            background-color: #cf69ff;
            opacity: 1;
            background-image:  repeating-radial-gradient( circle at 0 0, transparent 0, #cf69ff 21px ), repeating-linear-gradient( #9222fb55, #9222fb );
        }

        .at {
            font-size: inherit;
            color: limegreen;
            margin-right: calc(-4 * var(--base-padding));
        }
    </style>
</head>
<body>
    <header class = 'row top'>
        <div class = 'col-12' style = 'text-align: center; font-weight: 900; font-size: 58px'> <span class = 'at'>@</span> Channel</div>
        <div class = 'col-12' style = 'text-align: center; font-weight: 900; font-size: 24px'>A Network of communities</div>
    </header>
    <div class = 'row navbar'>
        <button class = 'col-1 button text-center navbar-element' onclick = "location.href  = '/'">@channel</button>
        <div class = 'col-2'></div>
        <div class = 'col-4 navbar-element no-padding no-clearfix'>
            <input type = 'text' name = 'search' placeholder="Search..." class = 'col-12 input input-text' oninput="GetSearchResults(this)" onfocus="DisplayResults(true)"/>
            <div class = 'col-4 result-container' id = 'result-container' onmouseenter="DisplayResults(true)" onmouseleave="DisplayResults(false)"></div>
        </div>
        <div class = 'col-5 navbar-element no-padding o-600'>
            <?php
                session_start();
                if(!isset($_SESSION['USER_ID'])) {
                    echo 
                    "
                    <button class = 'button align-right' onclick=\"location.href = '/login.html'\" aria-label = 'login'>Login</button>
                    <button class = 'button align-right' onclick=\"location.href = '/register.html'\" aria-label = 'sign up'>Sign up</button>
                    ";
                } else {
                    echo
                    "
                    <button class = 'button button-badge align-right' onclick =\"ToggleTheme()\" aria-label = 'toggle theme'><i class = 'fa fa-adjust'></i></button>
                    <button class = 'button button-badge align-right' onclick =\"Logout()\" aria-label = 'logout'><i class = 'fa fa-key'></i></button>
                    <button class = 'button button-badge align-right' onclick =\"location.href = '/chat'\" aria-label = 'chat'>
                        <i class = 'fa fa-envelope'></i>
                        <span class = 'indicator' id = 'chat-notification-indicator'></span>
                    </button>
                    ";
                }
            ?>
        </div>
    </div>
    <div class = 'row col-8 card-container' id = 'post-container' style = 'background: transparent'></div>
    <div class = 'row col-4 card-container'>
        <div class  = 'card'>
            <div class="header">Welcome to @channel</div>
            <div class="row content">
                @channel is a network of communitites allowing
                users from all over the the world to create, share,
                develop their thoughts and ideas. 
                <br/><br/>
                <button class = 'col-12 button' onclick = "location.href = '/newChannel'"><i class = 'fa fa-plus'></i> New Channel</button>
            </div>
        </div>
    </div>
    <div class = 'modal' id = 'modal'>
        <div class = "modal-content">
            <div class = 'row header'>
                <span class = 'col-11' id = 'modal-header'>H</span>
                <button class = 'button modal-close align-right' id = 'modal-close-button'>&times;</button>
            </div>
            <div class = 'body' id = 'modal-body'>H</div>
        </div>
    </div>
    <script src = "/js/base.js"></script>   
    <script src = "/js/notification.js"></script>  
    <script>
        var Exit = false; 
        const PostContainer = document.getElementById('post-container');
        Authenticate(false, '', '',
            (JSONResponse) => {
                success(`Authentication successful (u/${JSONResponse.username})`)
                LazyLoadPosts(4);
                window.addEventListener('scroll', (ScrollEvent) => {
                    if((document.documentElement.scrollTop + document.documentElement.clientHeight) / document.documentElement.scrollHeight > 0.75) {
                        if(PostContainer.lastElementChild) {
                            const LastTimeStamp = PostContainer.lastElementChild.getAttribute('data-post-timestamp');
                            LazyLoadPosts(4, LastTimeStamp);
                        }
                    }
                });
            },
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
                    Exit = true;
            }
        );
        
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
                    var Result = JSON.parse(this.responseText).result;
                    Result.posts.forEach((Post) => {
                        AddPostAtBottom(Post.id, Post.post_title, Post.post_user, Post.posted_at, Post.channel_name, Post.post_content, Post.likes, Post.comments, Post.user_like);
                    });
                }
            }
            xhr.open('POST', '/api/post', true);
            xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
            xhr.send( JSON.stringify({'request_type' : 'all_posts', 'request' : {'after_timestamp' : LastPost, 'count' : Count}}) )
        }

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
        <?php
            if(isset($_SESSION['USER_ID'])) {
                echo
                "
                setInterval(() => {
                    HandleGlobalChatNotification('chat-notification-indicator');
                }, 1000);
                ";
            }
        ?>
    </script>
</body>
</html>