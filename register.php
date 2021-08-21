<?php
    session_start();
    if(isset($_SESSION['USER_ID']))
        header('Location: /');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="A network of communities where people create, share and react to others thoughts and ideas">
    <title>@channel | Register</title>
    <link rel="stylesheet" type="text/css" href="css/style.css" />
</head>
<body onload="SetTheme('theme-light')">
    <div class = 'row navbar'>
        <button class = 'col-1 button text-center navbar-element' onclick = "location.href  = '/'">@channel</button>
        <div class = 'col-4 navbar-element no-padding no-clearfix'>
            <input type = 'text' name = 'search' placeholder="Search..." class = 'col-12 input input-text' oninput="GetSearchResults(this)" onfocus="DisplayResults(true)"/>
            <div class = 'col-4 result-container' id = 'result-container' onmouseleave="DisplayResults(false)"></div>
        </div>
        <div class = 'col-7 navbar-element no-padding o-600'>
            <button class = 'button align-right' onclick="location.href = '/login.html'">Login</button>
            <button class = 'button button-badge align-right' onclick="ToggleTheme()"><i class = 'fa fa-adjust'></i></button>
        </div>
    </div>
    <div class = 'col-8 card-container'>
        <div class = 'card'>
            <div class = 'header'>Register</div>
            <div class = 'content'>
                <form class='row'>
                    <div class="col-12">
                        <label class = 'col-12'>Username</label>
                        <input type = 'text' name = 'username' id = 'username' class = 'col-12 input input-text' autocomplete="off" oninput="/*CredrentialTaken('username', this.value, 'username-hint')*/(ValidateUsername('username', 'username-hint'))" required/>
                        <div class = 'align-right' id = 'username-hint'></div>
                    </div>
                    <div class="col-12">
                        <label class = 'col-12'>E-Mail</label>
                        <input type = 'email' name = 'email' id = 'email' class = 'col-12 input input-text input-padding' autocomplete="off" oninput="/*CredrentialTaken('email', this.value, 'email-hint')*/(ValidateMail('email', 'email-hint'))" required/>
                        <div class = 'align-right' id = 'email-hint'></div>
                    </div>
                
                    <div class="col-12">
                        <label class = 'col-12'>Password</label>
                        <input type = 'password' name = 'password' id = 'password' class = 'col-12 input input-text input-padding' oninput="ValidatePassword('password', 'password-hint')" autocomplete="off" required/>
                        <div class = 'align-right' id = 'password-hint'></div>
                    </div>
                
                    <div class="col-12">
                        <label class = 'col-12'>Re: Password</label>
                        <input type = 'password' name = 're-password' id = 're-password' class = 'col-12 input input-text input-padding' autocomplete="off" oninput="ValidateRePassword('password', 're-password', 'repassword-hint')" required/>
                        <div class = 'align-right' id = 'repassword-hint'></div>  
                    </div>
                    <div class = 'col-12 action-container'>
                        <button type = 'button' name = 'login' class="button align-right" onclick="location.href='/Login.html'">Already have an account</button>
                        <button type = 'button' name = 'register' class="button align-center" onclick="RegisterUser('username', 'username-hint', 'email', 'email-hint', 'password', 'password-hint', 're-password', 'repassword-hint')">Register</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class = 'row col-4 card-container'>
        <div class  = 'card'>
            <div class="header">Welcome to @channel</div>
            <div class="content">
                @channel is a network of communitites allowing
                users from all over the the world to create, share,
                develop their thoughts and ideas. 
            </div>
        </div>
    </div>
    <div class = 'modal' id = 'modal'>
        <div class = "modal-content">
            <div class = 'row header'>
                <span class = 'col-11' id = 'modal-header'></span>
                <button class = 'button modal-close align-right' id = 'modal-close-button'>&times;</button>
            </div>
            <div class = 'body' id = 'modal-body'></div>
        </div>
    </div>
    <script src="js/base.js"></script>
    <script>

    </script>
</body>
</html>