<?php
defined('BASEPATH') or die ('No direct script access allowed');
?>
 <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <!-- <link rel="icon" href="/favicon.ico" type="image/x-icon" /> -->
        <!-- <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" /> -->
		<link rel="icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
		<link rel="shortcut icon" href="<?php echo (defined('FAVICON_ICO') ? FAVICON_ICO : 'favicon.ico'); ?>" type="image/x-icon" />
        <meta http-equiv="Content-Type" content="text/html; charset=<?php echo (defined('USE_UTF') && USE_UTF === true ? "utf-8" : 'windows-1251'); ?>" />
        <style type="text/css">
            input {
                font-size: large;
            }
        </style>
        <title>Портал СВАН</title>
    </head>
    <body>
        <noscript>
            <div style="margin: auto; width: 300px; color:red">
                <h1>Внимание!</h1>
                <h2>Для работы с системой необходим браузер с поддержкой Javascript</h2>
            </div>
        </noscript>
        <script language="JavaScript">
            function getXmlHttp(){
                var xmlhttp;
                try {
                    xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
                } 
                catch (e) {
                    try {
                        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                    } 
                    catch (E) {
                        xmlhttp = false;
                    }
                }
                if (!xmlhttp && typeof XMLHttpRequest != 'undefined') {
                    xmlhttp = new XMLHttpRequest();
                }
                return xmlhttp;
            }
            
            function checkPOSTauth(){
                var req = getXmlHttp();
                var s_ok = 1;
                
                var login = document.getElementById('login').value;
                var psw = document.getElementById('psw').value;
                
                var msg = document.getElementById('message');
                msg.innerHTML = 'Авторизация...';
                
                
                if ((login == '') || (psw == '')) {
                    s_ok = 0;
                    msg.innerHTML = "Не заполнены необходимые поля!";
                }
                
                if (s_ok != 1) 
                    document.getElementById('auth_submit').disabled = false;
                if (s_ok == 1) {
                    req.onreadystatechange = function(){
                        if (req.readyState == 4) {
                            answer = eval('(' + req.responseText + ')');
                            if (!answer.success) {
                                msg.innerHTML = "Ошибка авторизации!";
                                document.getElementById('auth_submit').disabled = false;
                            }
                            else {
                                location.replace('/?c=promed');
                            }
                        }
                    }
                    var params = 'login=' + encodeURIComponent(login) + '&psw=' + encodeURIComponent(psw);
                    
                    req.open('POST', '/?c=main&m=index&method=Logon', true);
                    req.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    
                    req.send(params);
                }
            }
        </script>
        <div style="margin: auto; width: 300px;">
            <img src="/img/portal/swan-logo.jpg" width="212" height="105" />
            <form id="login_form" onsubmit="return false;" method="post" action="/?c=promed">
                <label for="login">
                    Имя пользователя:
                </label>
                <br/>
                <input name="login" id="login" type="text" />
                <br/>
                <br/>
                <label for="login">
                    Пароль:
                </label>
                <br/>
                <input name="psw" id="psw" type="password" />
                <br/>
                <span style="color:red" id="message"></span>
                <br/>
                <input id="auth_submit" type="submit" value="Вход" onclick="this.disabled=true; checkPOSTauth(); return false;"/>
            </form>
            <br/>
            <br/>
            <a href="/forum">Форум поддержки</a>
        </div>
    </body>
</html>
