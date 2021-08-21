/**
 * A file to handle all notification related stuff
**/

function StripCount(Count, Limit) {
    if(Count > Limit) return `${Limit}+`;
}

function HandleGlobalChatNotification(NotificationIndicator) {
    const Indicator = document.getElementById(NotificationIndicator);

    const xhr = new XMLHttpRequest();

    xhr.onreadystatechange = function() {
        if(this.readyState == 4 && this.status == 200) {
            var Result = JSON.parse(this.responseText).result;
            Indicator.innerText = Result.notifications;
            if(Result.notifications > 0)    Indicator.style.display = 'inline';
            else                            Indicator.style.display = 'none';
        }
    }

    xhr.open('POST', '/api/notify', true);
    xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
    xhr.send( JSON.stringify( { 'request_type' : 'chat' } ) );
}

function HandleGroupNotification(OnSuccess) {

    const xhr = new XMLHttpRequest();

    xhr.onreadystatechange = function() {
        if(this.readyState == 4 && this.status == 200) {
            warn(this.responseText);
            var Result = JSON.parse(this.responseText).result;
            if(Result.success)
                OnSuccess(Result.notifications);
            else 
                error(Result.reason);
        }
    }

    xhr.open('POST', '/api/notify', true);
    xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
    xhr.send( JSON.stringify( { 'request_type' : 'chat_group' } ) );
}