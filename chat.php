<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A network of communities where people create, share and react to others thoughts and ideas">
    <title>@channel | chat</title>
    <link rel="stylesheet" type="text/css" href="/css/style.css" />
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
    <div class = 'row col-4 card-container'>
        <div class = 'card'>
            <div class = 'header'>Chats</div>
            <div class = 'content row'>
                <div class = 'col-12 no-padding' id = 'chat-group-container'></div>
                <button class = 'button col-12' id='new-user-button'> + New Chat</button>
            </div>
        </div>
    </div>
    <div class = 'row col-8 card-container full-height'>
        <div class = 'row card full-height'>
            <div class = 'header'>Chat Data</div>
            <div class = 'row content full-height' style="display: flex; flex-direction: column;">
                <div class = 'row col-12 full-height full-height-scroll' id = 'chat-display'></div>
                <div class = 'row col-12 flex' style = 'position: sticky; top: 100%'>
                    <button class = 'button button-badge' onclick="Popup('Hello There', 'Insertion')"><i class = 'fa fa-paperclip'></i></button>
                    <input type = 'text' class = 'col-12 input input-text' style = 'position: sticky; top: 100%;' name = 'message' id = 'chat-input' placeholder="Write your message here..."/>
                </div>
            </div>
        </div>
    </div>
    <div class = 'modal' id = 'modal'>
        <div class = "modal-content">
            <div class = 'row header'>
                <span class = 'col-11' id = 'modal-header'></span>
                <button class = 'button modal-close align-right' id = 'modal-close-button'><i class = 'fa fa-times'></i></button>
            </div>
            <div class = 'body' id = 'modal-body'></div>
        </div>
    </div>
    <script src="/js/base.js" ></script>
    <script src="/js/chat.js" ></script>
    <script src="/js/notification.js" ></script>
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
        var ChatManager = new Chat('chat-input');

        /**
         *  First Register Update Callback
         * 
        **/
        ChatManager.RegisterUpdateCallback(() => {
            const DivElement = document.querySelector("[class='badge badge-chat-user badge-chat-user-active']");
            const ChatDisplay = document.getElementById('chat-display');
            var HTML = '';
            ChatManager.GetMessages(DivElement.getAttribute('data-value'), 0, 10, (JSONResponse) => {
                JSONResponse.messages.forEach((Entity) => {

                    HTML += `
                        <div class = 'col-12 message' data-type = 'message' data-value = '${ JSONResponse.active_user == Entity.sender ? 'message-user' : 'message-group' }'>
                            <div class = 'message-content'>${Entity.message}</div>
                            <div class = 'col-12 message-timestamp'>${Entity.sent_at}</div>
                            
                        </div>
                    `;

                })

                ChatDisplay.innerHTML = HTML;
            })
        });

        ChatManager.GetGroups((Groups) => {
            const GroupContainer = document.getElementById('chat-group-container');
            Groups.forEach(Group => {
                GroupContainer.innerHTML += `<div class = 'badge badge-chat-user' data-type = 'chat-username' data-value = '${Group}'>u/${Group}<span class = 'indicator' data-indicator-id = '${Group}'></span></div>`; 
            });

            document.querySelectorAll('[data-type="chat-username"]').forEach((DivElement) => {
                DivElement.addEventListener('click', ClickEvent => {
                    document.querySelectorAll('[data-type="chat-username"]').forEach(Badge => {
                        Badge.classList.remove('badge-chat-user-active');
                    })
                    DivElement.classList.add('badge-chat-user-active');
                    
                    const ChatDisplay = document.getElementById('chat-display');
                    var HTML = '';

                    ChatManager.GetMessages(DivElement.getAttribute('data-value'), 0, 10, (JSONResponse) => {
                        JSONResponse.messages.forEach((Entity) => {
                            HTML += `
                                <div class = 'col-12 message' data-type = 'message' data-value = '${ JSONResponse.active_user == Entity.sender ? 'message-user' : 'message-group' }'>
                                    <div class = 'message-content'>${Entity.message}</div>
                                    <div class = 'col-12 message-timestamp'>${Entity.sent_at}</div>
                                </div>
                            `;

                        })

                        ChatDisplay.innerHTML = HTML;
                    })

                    ChatManager.SetReceiver(DivElement.getAttribute('data-value'));
                })
            });
            /**
             * Then Get the last chat the user has interacted
             * And set that chat as active
             * 
            **/
            ChatManager.GetLastChat(Group => {
                warn('Last Chat' + Group[0]);
                ChatManager.SetReceiver(Group[0]);
                document.querySelector(`[data-value='${Group[0]}']`).classList.add('badge-chat-user-active');
                ChatManager.ForceUpdateChat();
            },
                Group => {
                    error('This person is never spoke to anyone');
                }

            );
            setInterval(ChatManager.UpdateCallback, 5000);
        });

        function ShowUsers(Input) {
            var Like = Input.value;
            var ResultContainer = document.getElementById(Input.getAttribute('data-value'));
            ChatManager.GetUsers(Like, (JSONResponse) => {
                var HTML = '';
                JSONResponse.users.forEach((User) => {
                    HTML += `<div class = 'col-12 badge badge-chat-user' data-type = 'new-chat-username' data-value = '${User}' onclick = "AddNewChatGroup('${User}')">u/${User}</div>`
                })
                success(HTML);
                ResultContainer.innerHTML = HTML;
            });
        }

        function AddNewChatGroup(Username) {
            /**
             * Make sure that the chat group does not exists already.
             * 
             * If so. Warn them.
            **/
            if(document.querySelectorAll(`[data-type = 'chat-username'][data-value = '${Username}']`).length > 0 ) {
                ClosePopupPrompt();
                Popup('Attention!',`
                    The user u/${Username} already is in your chat list.
                `, Colors.warn);
                return;
            }

            /**
             * Send an request to store intel regarding the last activity
             */
            const xhr = new XMLHttpRequest();

            xhr.onreadystatechange = function() {
                if(this.readyState == 4 && this.status == 200) {
                    warn(this.responseText);
                    var Result = JSON.parse(this.responseText).result;
                    if(!Result.success) {
                        Popup('Oops!', 'Oops! Looks like something went wrong. Reloading the page for the better', Colors.warn);
                        location.href = '/chat';
                    }
                }
            }

            xhr.open('POST', '/api/chat', true);
            xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
            xhr.send( JSON.stringify({'request_type' : 'create_group', 'request' : { 'receiver' : Username } }) );
    
            /**
             * Update the Chat UI
            **/
            document.querySelectorAll("[class = 'badge badge-chat-user badge-chat-user-active']").forEach((Badge) => {
                Badge.classList.remove('badge-chat-user-active');
            });
            document.getElementById('chat-group-container').innerHTML += `<div class = 'badge badge-chat-user badge-chat-user-active' data-type = 'chat-username' data-value = '${Username}'>u/${Username}<span class = 'indicator' data-indicator-id = '${Username}'></span></div>`;
            
            //Set the receiver to the newly added user.
            ChatManager.SetReceiver(Username);

            //Update the contents of chat data.
            ChatManager.ForceUpdateChat();

            //  Make sure to close the popup
            ClosePopupPrompt();
        }

        /**
         * Global Invocations.
         **/
        const NewUserButton = document.getElementById('new-user-button');
        NewUserButton.addEventListener('click', (ClickEvent) => {
            Popup('New Chat', `
                <input type = 'text' name = 'new-chat-user' class = 'col-12 input input-text' data-type = 'result-container-id' data-value='new-chat-result-container' oninput='ShowUsers(this)'/>
                <div class = 'col-12' id='new-chat-result-container'></div>
            `, Colors.success);
        })

        /**
         * Global Invocations
         */
        setInterval(() => {
            HandleGlobalChatNotification('chat-notification-indicator');
            
            HandleGroupNotification((Notifications) => {
                document.querySelectorAll("[data-indicator-id]").forEach(Indicator => {
                    const Username = Indicator.getAttribute('data-indicator-id');
                    success(`Notifications u/${Username} : ${Notifications[Username]}`)
                    if(Notifications[Username] > 0) {
                        success(Notifications[Username] + ' ' + Username);
                        Indicator.innerText = Notifications[Username];
                        Indicator.style.display = 'inline';
                    } else {
                        Indicator.style.display = 'none';
                    }
                });
            })
            
        }, 1000);

    </script>
</body>
</html>