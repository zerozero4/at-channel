/**
 * This file is intended to be used with chat page
 */

class Chat {
    constructor(InputElement) {
        this.InputElement = document.getElementById(InputElement);
        this.Receiver = undefined;
        this.AutoUpdate = false;

        if(InputElement == undefined) {
            error('Unable to grab the chat input field');
            return;
        }
        this.InputElement.addEventListener('keydown', (KeyDownEvent) => {
            if(KeyDownEvent.key == 'Enter') {
                this.SendMessage(this.InputElement.value, (JSONResponse) => {
                    /**
                     *  The Message has been sent successfully.
                     *  Call the registered callback to handle update.
                     * 
                     *  Then make sure to set the contents of input field to null
                    **/
                    this.UpdateCallback();
                    this.InputElement.value = '';
                }, (JSONResponse) => {
                    error(JSON.stringify(JSONResponse, null, 2));
                });
            }
        }, false)
        
    }

    /**
     * @brief Function to send message to the receiver.
     * 
     * @param {string} Receiver The receiver username
     * @param {string} Message The message to send
     * @param {Function} OnSuccess (OPTIONAL) The Function that should be executed on success [Params : JSONObject]
     * @param {Funciton} OnFailure (OPTIONAL) The Function that should be executed on failure [Params : JSONObject]
     */
    SendMessage(Message, OnSuccess = null, OnFailure = null) {
        if(this.Receiver == undefined) error('The Receiver is not set');
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if(this.readyState == 4 && this.status == 200) {
                warn(this.responseText);
                var Result = JSON.parse(this.responseText).result;
                if(Result.success)  OnSuccess(Result.response);
                else                OnFailure(Result.response);
            }
        }

        xhr.open('POST', '/api/chat', true);
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
        xhr.send(JSON.stringify( { 'request_type' : 'send_message', 'request' : { 'receiver' : this.Receiver, 'message' : Message } } ));
    }

    /**
     * 
     * @param {Function} OnSuccess A Function that iterates over the available groups.
     */
    GetGroups(OnSuccess) {
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if(this.readyState == 4 && this.status == 200) {
                warn('GetGroups : ' + this.responseText);
                var Result = JSON.parse(this.responseText).result;
                OnSuccess(Result.groups);
            }
        }

        xhr.open('POST', '/api/chat', true);
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
        xhr.send( JSON.stringify({'request_type' : 'get_groups'}) )
    }

    GetLatestTime(Receiver, OnSuccess) {
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if(this.readyState == 4 & this.status == 200) {
                var Result = JSON.parse(this.responseText).result;
                OnSuccess(Result.latest_time_stamp);
            }
        }

        xhr.open('POST', '/api/chat', true);
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
        xhr.send( JSON.stringify({'request_type' : 'get_latest_timestamp', 'request' : { 'receiver' : Receiver } }) );
    }

    GetMessages(Receiver, Offset, Count, OnSuccess) {
        const xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function() {
            if(this.readyState == 4 && this.status == 200) {
                warn(this.responseText);
                var Result = JSON.parse(this.responseText).result;
                OnSuccess(Result);
            }
        }

        xhr.open('POST', '/api/chat', true);
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
        xhr.send( JSON.stringify({'request_type' : 'get_messages', 'request' : { 'receiver' : Receiver, 'offset' : Offset, 'count' : Count } }) );
    }

    SetReceiver(Receiver) { this.Receiver = Receiver;   }
    RegisterUpdateCallback(Callback) {  this.UpdateCallback = Callback; }

    GetUsers(Like, OnSuccess) {
        const xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function(){
            if(this.readyState == 4 && this.status == 200) {
                warn(this.response);
                var Result = JSON.parse(this.responseText).result;
                OnSuccess(Result);
            }
        };

        xhr.open('POST', '/api/chat', true);
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
        xhr.send( JSON.stringify({'request_type' : 'get_users', 'request' : { 'pattern' : Like } }) );
    }

    GetLastChat(OnSuccess, OnFailure) {
        const xhr = new XMLHttpRequest();

        xhr.onreadystatechange = function() {
            if(this.readyState == 4 && this.status == 200) {
                warn(this.responseText);
                var Result = JSON.parse(this.responseText).result;

                if(Result.last.length > 0)
                    OnSuccess(Result.last);
                else
                    OnFailure(Result.last);
            }
        }

        xhr.open('POST', '/api/chat', true);
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
        xhr.send( JSON.stringify({'request_type' : 'get_last_group'}) );
    }

    ForceUpdateChat() { this.UpdateCallback();  }

    SetAutoUpdate(Callback, Time) {
        this.Update = Callback;
        this.AutoUpdateTime = Time;
    }

    AutoUpdate() {
    }
};