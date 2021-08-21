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
    <title>@<?php echo $_GET['channel_name'] ?> | New Post</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css" />
</head>
<body>
    <!--The Navigation Bar-->
    <div class = 'row navbar'>
        <button class = 'col-1 button text-center navbar-element' onclick = "location.href  = '/'">@channel</button>
        <div class = 'col-4 navbar-element no-padding no-clearfix'>
            <input type = 'text' name = 'search' placeholder="Search..." class = 'col-12 input input-text' oninput="GetSearchResults(this)" onfocus="DisplayResults(true)"/>
            <div class = 'col-4 result-container' id = 'result-container' onblur="DisplayResults(false)"></div>
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

    <!----The Main Content--->
    <div class = 'row main-content'>
        <div class = 'col-8 card-container'>
            <div class = 'card'>
                <div class = 'header' id = 'post-title-editor' contenteditable placeholder='Post Title Here'>Post Title here</div>
                <div class = 'content' id = 'post-content-editor' contenteditable placeholder='Post Content Here'>Post Content here</div>
            </div>
        </div>
        <div class = 'col-4 card-container'>
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
    <!----------------------->


    <!-------ToolBar--------->
    <div class = 'row' style="position: fixed; top: 90%;">
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

            <span class = 'seperator'></span>

            <button class = 'col-12 button hover-success' onclick="Post()">Post</button>
            <button class = 'col-12 button hover-warn' onclick="Cancel()">Cancel</button>
        </div>
    <div>
    <!----------------------->

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
        function _(Command, Value) {   document.execCommand(Command, false, Value);    }

        const ToolTip = document.getElementById('tooltip');
        document.querySelectorAll("[data-requires-tooltip]").forEach((Action) => {
            Action.addEventListener('mouseover', (HoverEvent) => {
                ToolTip.innerHTML = Action.getAttribute('data-tooltip');
            })

            Action.addEventListener('mouseleave', (HoverEvent) => {
                ToolTip.innerHTML = '';
            })
        })

        function Post() {
            var PostTitle = document.getElementById('post-title-editor').innerText;
            var PostContent = document.getElementById('post-content-editor').innerHTML;

            const xhr = new XMLHttpRequest();

            xhr.onreadystatechange = function() {
                if(this.readyState == 4 && this.status == 200) {
                    Prompt('Success', `
                        Your post has benn created successfully. Taking you to login<br>
                        Response From server : <b>${this.responseText}</b>
                        `);
                    
                        setTimeout(() => {  location.href = "/@<?php echo $_GET['channel_name']?>";   }, 2000);
                }
            }
            xhr.open('POST', '/api/post', true);
            xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
            xhr.send( JSON.stringify({'request_type' : 'new_text_post', 'request' : { 'channel_name' : "<?php echo $_GET['channel_name']?>", 'post_title' : PostTitle, 'post_content' : PostContent}}) ) 
        }

        function Cancel() {
            Prompt('Abort', 'Taking you to home page', Colors.warn);
            location.href = "/@<?php echo $_GET['channel_name']?>";
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