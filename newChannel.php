<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A network of communities where people create, share and react to others thoughts and ideas">
    <title>@channel</title>
    <link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body>
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
    <div class = 'col-8 card-container'>
        <div class = 'card'>
            <div class = 'header'>New Channel</div>
            <div class = 'content'>
                <form class='row'>
                    <div class="col-12">
                        <label class = 'col-12'>Channel Name</label>
                        <input type = 'text' name = 'channel-name' id = 'channel-name' class = 'col-12 input input-text' autocomplete="off" oninput="ValidateChannelName(this.value)" required/>
                        <div class = 'align-right' id = 'channel-name-hint'></div>
                    </div>
                    <div class="col-12">
                        <label class = 'col-12'>Channel Brief</label>
                        <input type = 'text' name = 'channel-brief' id = 'channel-brief' class = 'col-12 input input-text' autocomplete="off" required/>
                    </div>
                
                    <div class="col-12">
                        <label class = 'col-12'>Channel Description</label>
                        <textarea name = 'channel-description' id = 'channel-description' class = 'input input-text' autocomplete="off" required></textarea>
                    </div>

                    <div class = 'col-12 action-container'>
                        <button type = 'button' name = 'cancel' class="button align-right hover-warn" onclick="location.href='/'">Cancel</button>
                        <button type = 'button' name = 'register' class="button align-center hover-success" onclick="CreateChannel()">Create new channel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class = 'row col-4 card-container'>
        <div class  = 'card'>
            <div class="header">Welcome to @channel</div>
            <div class="row content">
                @channel is a network of communitites allowing
                users from all over the the world to create, share,
                develop their thoughts and ideas.
                <br/>
                <br/>
                Every channel has its own rules and regulations when it comes to contributing via posts.
                You can display such rules / messages on the side via adding cards here.
                <button class = 'col-12 button' style = "margin-top: var(--base-padding-2)" onclick="AddNewCard()">Add New Card</button>
            </div>
        </div>
        <div id = 'channel-info-container'></div>
    </div>

    <!-------ToolBar--------->
    <div class = 'col-12' style="position: sticky; top: 100%;">
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
    <script src="js/base.js"></script>
    <script src="js/notification.js"></script>
    <script>
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

        const CardContainer = document.getElementById('channel-info-container');
        const ChannelHint = document.getElementById('channel-name-hint');

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


        function AddNewCard() {
            CardContainer.insertAdjacentHTML('beforeend', 
            `
            <div class = 'card'>
                <div class = 'header' contenteditable>Card Title Here</div>
                <div class = 'content' contenteditable>Card Content Here</div>
            </div>
            `
            );
        }

        function ValidateChannelName(ChannelName) {
            if(!/^[a-zA-Z]*$/.test(ChannelName)) {
                ChannelHint.innerHTML = `<b class = 'hint-warn'>Channel name should contain only characters ( A - Z )</b>`;
                return false;
            }
            const xhr = new XMLHttpRequest();

            xhr.onreadystatechange = function(){
                if(this.readyState == 4 && this.status == 200) {
                    var Result = JSON.parse(this.responseText).result;
                    if(Result.success)  ChannelHint.innerHTML = `<b class = 'hint-info'>Channel name available</b>`;
                    else                ChannelHint.innerHTML = `<b class = 'hint-warn'>Channel name already taken</b>`;
                }
            }

            xhr.open('POST', '/api/channel', true);
            xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8')
            xhr.send( JSON.stringify( { 'request_type': 'validation', 'request' : { 'name' : ChannelName } } ) );

            return true;
        }

        function CreateChannel() {
            const ChannelName = document.getElementById('channel-name').value;
            const ChannelBrief = document.getElementById('channel-brief').value;
            const ChannelDescription = document.getElementById('channel-description').value;
            
            document.querySelectorAll("[contenteditable]").forEach(Element => {
                Element.removeAttribute('contenteditable');
            })
            warn(ChannelCards);

            if(!ValidateChannelName(ChannelName)) {
                Popup('Warning', `The channel name shall contain only characters ( A - Z ). Example 'askChannel', 'mildyintresting' ...`, Colors.warn);
                return;
            }

            const xhr = new XMLHttpRequest();

            xhr.onreadystatechange = function() {
                if(this.readyState == 4 && this.status == 200) {
                    var Result = JSON.parse(this.responseText).result;
                    if(Result.success) {
                        Popup('Success', `Hooray! You are the founder of <b>@${ChannelName}</b>. From now on people can create, share and contribute to your very own channel.`);
                        setTimeout( () => { location.href = `/@${ChannelName}/`; }, 10000 );
                    } else {
                        Popup('Failure', `Uh oh! Looks like somethings are not right. Don't worry, try again after some time.<br>Response from the server : ${Result.reason}`, Colors.error);
                    }
                }
            }

            xhr.open('POST', '/api/channel', true);
            xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
            xhr.send(JSON.stringify(
                                        { 
                                            'request_type' : 'registration',
                                            'request' : {
                                                'name' : ChannelName,
                                                'brief': ChannelBrief,
                                                'description': ChannelDescription,
                                                'cards': ChannelCards
                                            }
                                        }
            ));
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