const Modal = document.getElementById('modal');
const ModalHeader = document.getElementById('modal-header');
const ModalBody = document.getElementById('modal-body');
const ModalClose = document.getElementById('modal-close-button');

var Colors = {
    info: 'limegreen',
    warn: 'orange',
    error: 'tomato'
};
/**
 * @brief Log message on the console as success event.
 * 
 * @param Message The message that has to logged.
**/
function success(Message) { console.log(`%c [ @channel ] ${Message}`, 'color: limegreen;'); }

/**
 * @brief Log message on the console as warning event.
 * 
 * @param Message The message that has to logged.
**/
function warn(Message) { console.log(`%c [ @channel ] ${Message}`, 'color: yellow;'); }

/**
 * @brief Log message on the console as error event.
 * 
 * @param Message The message that has to logged.
**/
function error(Message) { console.log(`%c [ @channel ] ${Message}`, 'color: tomato;'); }

/**
 * @brief Truncate a string to the given Length.
 * @param {string} String The string to truncate
 * @param {Number} Length The number of characters to be displayed.
 */
function truncate(String, Length) {
    if(String.length > Length)  return String.substring(0, Length) + '...';
    else                        return String;
}

/**
 * @brief Funcion to set / modify a cookie.
 * 
 * @param {string} Name Name of the cookie
 * @param {string} Value Value to be stored in the cookie
 * @param {Date} Expiry Time from the time of setting at which the cookie expires
 */
 function SetCookie(Name, Value, Expiry) {
    const Time = new Date();
    Time.setTime(Time.getTime() + Expiry);
    const Expires = "expires=" + Time.toUTCString();
    document.cookie = `${Name}=${Value};${Expires};path=/`;
}

/**
 * @brief Funcition to retrieve a cookie
 * @param {string} Name Name of the cookie
 */
function GetCookie(Name) {
    let name = Name + "=";
    let ca = document.cookie.split(';');
    for(let i = 0; i < ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) == ' ') {
            c = c.substring(1);
        }
        if (c.indexOf(name) == 0) {
            return c.substring(name.length, c.length);
        }
    }
    return "";
}

/**
 * @brief Conditionally execute based on availability of a cookie.
 * 
 * @param {string} Name Name of the cookie to check.
 * @param {Function} OnSucces Function to execute on success
 * @param {Funciton} OnFailure Function to execute on failure
 */
function OnCookie(Name, OnSucces, OnFailure) {
    if(GetCookie(Name) != "")   OnSucces();
    else                        OnFailure();
}

/**
 * @brief Authenticate the user.
 * 
 * Try to authenticate the user based on the options giver.
 * 
 * If ForceAuthentication is `true`, then the user will be taken to login page.
 * else the user with minimum privilages will be allowed to use the site.
 * 
 * @note If Force Authentication is set, then username and password should be specified.
 * 
 * @param {boolean}     ForceAuthentication Redirect user to login page for authentication
 * @param {string}      Username (Optional) Username for authentication.
 * @param {string}      Password (OPtional) Password for authentication.
 * @param {function}    OnSuccess (Optional) Function to execute on success
 * @param {function}    OnFailure (Optional) Function to execute on failure
**/
function Authenticate(ForceAuthentication, Username, Password, OnSuccess, OnFailure) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if(this.status == 200 && this.readyState == 4) {
            var result = JSON.parse(this.responseText).result;

            if(result.success)  OnSuccess(result.response);
            else                OnFailure(result.response);
        }
    }

    if(ForceAuthentication) {
        xhr.open('GET', `/api/authenticate?username=${Username}&password=${Password}&authenticate=true`, true);
    } else {
        xhr.open('GET', '/api/authenticate?authenticate=true', true);
    }
    xhr.send();
}

/**
 * @brief Function to set a given theme/color-scheme
 * 
 * @param ThemeName The CSS3 selector containing the theme related variables.
**/
function SetTheme(ThemeName) {
    document.getElementsByTagName('body')[0].style.display = 'block';
    document.documentElement.className = ThemeName;
}

/**
 * @brief Function to toggle theme between light & dark.
 */
function ToggleTheme() {
    switch(document.documentElement.className) {
        case 'theme-default': document.documentElement.className = 'theme-dark';  SetCookie("preferences-theme", "theme-dark", 30 * 24 * 60 * 60 ); break;
        case 'theme-dark' : document.documentElement.className = 'theme-default'; SetCookie("preferences-theme", "theme-default", 30 * 24 * 60 * 60 ); break;
    }
}

/**
 * @brief Return the most matching channel / user name from the list of available channel / user names.
 * 
**/
function GetSearchResults(InputField) {
    var value = InputField.value;
    var result_container = document.getElementById('result-container');

    const xhr = new XMLHttpRequest();

    xhr.open('GET', '/api/search?in=' + value, true);
    xhr.onreadystatechange = function() {
        if(this.status == 200 & this.readyState == 4) {
            var json = JSON.parse(this.responseText);
            var HTML = "";

            if(json.result.users.length > 0) {
                HTML += `<b class = 'col-1'>Users</b>`;
                HTML += "<div class = 'col-12'>";
                json.result.users.forEach(element => {
                    HTML += `<li class = 'col-12 no-list-style'><a href = '/u/${element.username}'> u/${element.username} </a></li>`;
                });
                HTML += "</div>";
            }

            if(json.result.channels.length > 0) {
                HTML += "<b class = 'col-1'>Channels</b>";
                HTML += "<div class = 'col-12'>"; 
                json.result.channels.forEach(element => {
                    HTML += `<li class = 'col-12 no-list-style'><a href = '/@${element.channel_name}'>@${element.channel_name}</a></l1>`;
                });
                if(json.result.channels.length > 0)
                HTML += "</div>";
            }

            if(HTML == '') result_container.style.display = 'none';
            else result_container.style.display = 'block';
            result_container.innerHTML = HTML;
        }
    }
    xhr.send();
}

function DisplayResults(Boolean) {
    document.getElementById('result-container').style.display = Boolean ? 'block' : 'none';
}

/**
 * @brief Function to validate the given username.
 * 
 * @param {string} Input The DOM input field containing username.
 * @param {string} Hint The DOM span element that houses the response.
 */
function ValidateUsername(Input, Hint) {
    var username = document.getElementById(Input).value;
    
    /**
     *  First, make sure that the following conditions are met.
     *  
     *  (i)     Username should be 8 - 20 characters.
     *  (ii)    Username may include alphabet, underscore (_), periods (.)
     *  (iii)   Underscore & period should not be followed by periods or underscores (__, _., ._, .. are prohibited)
     *  (iv)    Usernaame should not begin with undercore or period 
    **/
    if(!/^(?=.{8,20}$)/.test(username)) {
        document.getElementById(Hint).innerHTML = "<div class = 'hint-warn'><i class = 'fa fa-exclamation hint-warn'></i> Username should contain 8 - 20 characters</div>";
        return false;
    }
    if(!/^(?=.{8,20}$)(?![_.])/.test(username)) {
        document.getElementById(Hint).innerHTML = "<div class = 'hint-warn'><i class = 'fa fa-exclamation hint-warn'></i> Username should not start with (_) or (.)</div>";
        return false;
    }
    if(!/^(?=.{8,20}$)(?![_.])(?!.*[_.]{2})/.test(username)) {
        document.getElementById(Hint).innerHTML = "<div class = 'hint-warn'><i class = 'fa fa-exclamation hint-warn'></i> Username should not contain consecutive (_) or (.)</div>";
        return false;
    }
    if(!/(?!.*[_.]{2})/.test(username)) {
        document.getElementById(Hint).innerHTML = "<div class = 'hint-warn'><i class = 'fa fa-exclamation hint-warn'></i> Username should not contain consecutive (_) or (.)</div>";
        return false;
    }
    if(!/^(?=.{8,20}$)(?![_.])(?!.*[_.]{2})[a-zA-Z0-9._]+(?<![_.])$/.test(username)) {
        document.getElementById(Hint).innerHTML = "<div class = 'hint-warn'><i class = 'fa fa-exclamation hint-warn'></i> Username should contain only alpahabetical (a-z and A - Z) and numerical (0 - 9) characters</div>";
        return false;
    }
    {
        document.getElementById(Hint).innerHTML = "<b class = 'hint-info'><i class = 'fa fa-cog hint-info'></i></b>";
    }

    /**
     * Secondly, make sure that the username is not registered in the server,
    **/
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if(this.readyState == 4 && this.status == 200) {
            var result = JSON.parse(this.responseText).result;
            document.getElementById(Hint).innerHTML = ( result.success ? "<b class = 'hint-info'><i class = 'fa fa-check hint-info'></i> " : "<b class = 'hint-warn'><i class = 'fa fa-exclamation hint-warn'></i> " ) + result.reason + "</b>";

            return function() { return result.success;  }
        }
    };

    xhr.open('GET', `/api/userexists?username=${username}`, true)
    xhr.send();

    /**
     * Don't worry about username existance as it can be handled lazily in
     * registration process.
     */
    return true;
}

/**
 * @brief Function to validate email address
 * 
 * @param {string} EMail The verification email required for authentication
 * @param {string} Hint The DOM span element that houses the response
 */
function ValidateMail(Input, Hint) {
    var email = document.getElementById(Input).value;
    
    /**
     *  First, make sure that the email is "Actually" an email.
    **/
    if(! /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i.test(email)) {
        document.getElementById(Hint).innerHTML = "<b class = 'hint-warn'><i class = 'fa fa-exclamation hint-warn'></i> Email is of invalid pattern</b>";
        return false;
    } else {
        document.getElementById(Hint).innerHTML = "<b class = 'hint-info'><i class = 'fa fa-cog hint-info'></i></b>";
    }

    /**
     * Secondly, make sure that the email is not registered in the server,
    **/
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function() {
        if(this.readyState == 4 && this.status == 200) {
            var result = JSON.parse(this.responseText).result;
            document.getElementById(Hint).innerHTML = ( result.success ? "<b class = 'hint-info'><i class = 'fa fa-check hint-info'></i> " : "<b class = 'hint-warn'><i class = 'fa fa-exclamation hint-warn'></i> " ) + result.reason + "</b>";

            return function() { return result.success;  }
        }
    };

    xhr.open('GET', `/api/userexists?email=${email}`, true)
    xhr.send();

    /**
     * Don't worry about email existance as it can be handled lazily in
     * registration process.
     */
    return true;
}

/**
 * @brief Funtion to validate password
 * 
 * @param {string} Input The DOM Input element containing the password
 * @param {string} Hint The DOM span element that houses the response
 */
function ValidatePassword(Input, Hint) {
    var password = document.getElementById(Input).value;

    /**
     * Firstly, make sure that the password meets the following requirements.
     * 
     * (i)      Should be 6 - 16 characters long.
     * (ii)     Should contain alphabetical and numerical characters.
     * (iii)    Must contain atleast one numerical character.
     * (iv)     Must contain atleast one special character.
     */
    if(!/^[a-zA-Z0-9!@#$%^&*]{6,16}$/.test(password)) {
        document.getElementById(Hint).innerHTML = "<b class = 'hint-warn'><i class = 'fa fa-exclamation hint-warn'></i> Passwoord must be 6 - 16 characters long.</b>";
        return false;
    }
    if(!/^(?=.*[0-9])[a-zA-Z0-9!@#$%^&*]{6,16}$/.test(password)) {
        document.getElementById(Hint).innerHTML = "<b class = 'hint-warn'><i class = 'fa fa-exclamation hint-warn'></i> Password should contain atleast one numerical character.</b>";
        return false;
    }
    if(!/^(?=.*[!@#$%^&*])(?=.*[0-9])[a-zA-Z0-9!@#$%^&*]{6,16}$/) {
        document.getElementById(Hint).innerHTML = "<b class = 'hint-warn'><i class = 'fa fa-exclamation hint-warn'></i> Password should contain atleast one special (!, @, #, $, %, ^, &, *) character.</b>";
        return false;
    }
    
    document.getElementById(Hint).innerHTML = "<b class = 'hint-info'><i class = 'fa fa-check hint-info'></i></b>";
    /**
     * Well, it seems that the user has provided a valid password. Acknowlege it.
     */
    return true;
}

/**
 * @brief Function to make sure that repassword and password match.
 * 
 * @param {string} Password The DOM Input field containing the password
 * @param {string} RePassword The DOM Input field containing the repassword
 * @param {string} Hint The DOM span element that houses the response
 */
function ValidateRePassword(Password, RePassword, Hint) {
    if(document.getElementById(Password).value != document.getElementById(RePassword).value) {
        document.getElementById(Hint).innerHTML = "<b class = 'hint-warn'><i class = 'fa fa-exclamation hint-warn'></i> RePassword and Password does not match</b>";
        return false;
    }
    else {
        document.getElementById(Hint).innerHTML = "<b class = 'hint-warn'><i class = 'fa fa-check hint-info'></i></b>";
        return true;
    }
}

/**
 * @brief Function that validates and registers the user with provided credentials.
 * 
 * @param {string} Username_Input The DOM Input field containing username
 * @param {string} Username_Hint The DOM span element that houses the username hint.
 * @param {string} EMail_Input The DOM Input filed containing email
 * @param {string} Email_Hint THE DOM span element that houses the email hint
 * @param {string} Password_Input THE DOM Input field containing password
 * @param {string} Password_Hint The DOM span element that houses the password hint
 * @param {string} RePassword_Input The DOM Input field containing the re-password
 * @param {string} RePassword_Hint The DOM span element that houses the re-password hint
 */
function RegisterUser(Username_Input, Username_Hint, EMail_Input, Email_Hint, Password_Input, Password_Hint, RePassword_Input, RePassword_Hint) {
    var Validation_L1 = ValidateUsername(Username_Input, Username_Hint) && ValidateMail(EMail_Input, Email_Hint) && ValidatePassword(Password_Input, Password_Hint) && ValidateRePassword(Password_Input, RePassword_Input, RePassword_Hint);
    if(!Validation_L1) {
        /**
         * This user (Most likely a tester) tried to register
         * despite of the warning.
         * 
         */
         Popup('Registration Failed', `
         The entered registration details are not valid. Make sure you entered the correct ones.
         `, Colors.error);
        return false;
    }

    success("Provided credentials are sub-valid");
    /**
     * So, the credentials are valid "SYMMANTICALLY". Send them to the server for further processing.
    **/
   const xhr = new XMLHttpRequest();
   xhr.onreadystatechange = function() {
       if(this.readyState == 4 && this.status == 200) {
           warn(this.responseText);
           var result = JSON.parse(this.responseText).result;
           if(result.success) {
               /**
                * Turns that this guy was honest.
                * Reward him by taking him to further procedures.
                */
                //success(result.reason);
                Prompt('Registration Successful', `
                    Hooray! You have joined the @channel!<br>
                    Redirecting you to login page.
                `, Colors.success);

                setTimeout(()=>{location.href='/login'}, 2000);
           } else {
               /**
                * Turns out that this guy tried to cheat us by probing through the code.
                * Warn him.
                */
                Popup('Registration Failed', `
                Hmmm! Turns out that something went wrong. We'll address this issue as soon as possible.
                `, Colors.error);
           }
       }
   };
   xhr.open('GET', `/api/register?username=${document.getElementById(Username_Input).value}&email=${document.getElementById(EMail_Input).value}&password=${document.getElementById(Password_Input).value}`)
   xhr.send();

   /**
    * Update the Hints. So that user is not confused.
    */
   return !ValidateUsername(Username_Input, Username_Hint) && ValidateMail(EMail_Input, Email_Hint) && ValidatePassword(Password_Input, Password_Hint) && ValidateRePassword(Password_Input, RePassword_Input, RePassword_Hint);
}

/**
 * @brief Function that tried to authenticate user with username and password.
 * 
 * @param {string} Username The DOM Input field containing username
 * @param {string} Password The DOM Input field containing password
 */
function TryLogin(Username, Password) {
    var username = document.getElementById(Username).value,
        password = document.getElementById(Password).value;
    
    Authenticate(true, username, password, (JSONObject) => {
        success(`User u/${JSONObject.username} has logged in successfully. Redirecting to requested page in 5 seconds...`);
        Prompt('Login Success', `
        Success! Let's take you to the requested page.
        `);
        setTimeout(() => {
            location.href = "/";
        }, 2000);
    }, (JSONObject) => {
        error(`Login Failed. Response from server (${JSONObject.reason})`);
        Popup('Login Failed', 
        `Oops! Looks like the credentials you entered does not match with the ones in the server.<br>
        Don't you worry, try again.`,
        Colors.error);
    });
}

function Logout() {
    /**
     * In order to log out the user, first try to authenticate him
     * 
     * If the session is valid, log him out.
     */
    Authenticate(false, '', '', (JSONResponse) => {
        /**
         * The session is valid. Log him out.
         */
        const xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function() {
            if(this.readyState == 4 && this.status == 200) {
                warn(this.responseText);
                var result = JSON.parse(this.responseText).result;
                warn(JSON.stringify(result), null, 2);

                if(result.success) {
                    /**
                     * ReDirect him to login page.
                     */
                    location.href = "/Login.html";
                } else {
                    /**
                     * Somethings wrong. I can feel it.
                     */
                    error(result.response.reason);
                }
            }
        }

        xhr.open('GET', `/api/logout?username=${JSONResponse.username}`);
        xhr.send();
    }, (JSONResponse) => {
        /**
         * Well, his session is invalid anyway.
         * So, take him to login page.
         */
        warn("Redirecting to login after 5 seconds...");
        error(JSONResponse.reason);
        setTimeout(() => {
            location.href = "/Login.html";
        }, 5000);
    });
}

function Popup(Header, Content, HeaderColor = Colors.info) {
    ModalHeader.innerHTML = Header;
    ModalBody.innerHTML = Content;

    ModalHeader.parentElement.style.background = HeaderColor;
    ModalClose.style.display = 'block';
    Modal.style.display = 'block';
}

function Prompt(Header, Content, HeaderColor = Colors.info) {
    Popup(Header, Content, HeaderColor);
    ModalClose.style.display = 'none';
}

function ClosePopupPrompt() {
    Modal.style.display = 'none';
}

/**
 * Global Invocations
 */
document.getElementsByTagName("body")[0].onload = () => {
    OnCookie(   "preferences-theme",
                () => { 
                    SetTheme(GetCookie("preferences-theme"));
                    success("Successfully retrieved theme settings");
                }, 
                () => {
                    SetCookie("preferences-theme", "theme-default", 30 * 24 * 60 * 60);
                    SetTheme("theme-default");
                    warn("Couldn't retrieve theme settings. Fallback to `theme-default`");
                });
    
    ModalClose.addEventListener("click", () => { Modal.style.display = 'none'; });
};