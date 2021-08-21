class CardManager {
    /**
     * @brief Constructor for CardManager.
     * @param {string} Container The DOM Element that houses all the cards.
     */
    constructor(Container, ChannelName) {
        this.Container = document.getElementById(Container);
        this.ChannelName = ChannelName;
    }

    AddCardAtBottom(CardTitle, CardContents) {
        const Card =
        `
            <div class = 'row card'>
                <div class = 'header'>${CardTitle}</div>
                <div class = 'content no-border-bottom-radius'>
                    ${CardContents}
                    <div class = 'flex' style = 'margin-top: var(--base-padding)'>
                        <button class = 'button flex-content'>Like</button>
                        <button class = 'button flex-content'>Reply</button>
                        <button class = 'button flex-content'>Favourite</button>
                    </div>
                </div>
            </div>
        `;

        this.Container.insertAdjacentHTML('beforeend', Card);
    }

    AddCardAtTop(CardTitle, CardContents, Likes, PostID) {
        const Like = `<span class = 'counter'>${Likes}</span>`;
        const Card =
        `
            <div class = 'row card' data-post-id = '${PostID}'>
                <div class = 'row header' style = 'cursor: pointer;' onclick = "location.href = '/@${this.ChannelName}/${PostID}/'">${CardTitle}</div>
                <div class = 'content no-border-bottom-radius'>
                    ${CardContents}
                    </div>
                <div class = 'flex'>
                    <button class = 'button button-reaction button-reaction-like flex-content'><i class = 'fa fa-thumbs-up'></i>Like ${Like} </button>
                    <button class = 'button button-reaction button-reaction-comment flex-content'><i class = 'fa fa-comment'></i>Comment</button>
                    <button class = 'button button-reaction button-reaction-favourite flex-content'><i class = 'fa fa-heart'></i>Favourite</button>
                </div>
            </div>
        `;
        this.Container.insertAdjacentHTML('afterbegin', Card);
    }

    GetPosts(PostCount, AfterTimestamp, OnSuccess) {
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if(this.readyState == 4 && this.status == 200) {
                warn(this.responseText);
                var Result = JSON.parse(this.responseText).result;
                OnSuccess(Result);
            }
        }

        xhr.open('POST', '/api/post', true);
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
        xhr.send( JSON.stringify({'request_type' : 'get_posts', 'request' : { 'channel' : this.ChannelName, 'count' : PostCount, 'after_timestamp' : AfterTimestamp}}) )
    }

    AddTextPost(ChannelName, Title, Content, OnSuccess) {
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if(this.readyState == 4 && this.status == 200) {
                var Result = JSON.parse(this.responseText).result;
                OnSuccess(Result);
            }
        }

        xhr.open('POST', '/api/post', true);
        xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8');
        xhr.send( JSON.stringify({'request_type' : 'new_text_post', 'request' : { 'channel' : ChannelName, 'post_title' : Title, 'post_content' : Content}}) )
    }
};