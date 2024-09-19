<?php
session_start();
require_once "../assets/db.php";
include "../assets/blocks.php";

if (isset($_POST["auth"]) && preg_match("/^[A-z0-9]+$/u", $_POST["username"])) {
    $link = connectDB();
    if ($stmt = mysqli_prepare($link, "SELECT * FROM `users` WHERE `nick` LIKE ?")) {
        $pass = password_hash($_POST["password"], PASSWORD_BCRYPT);
        mysqli_stmt_bind_param($stmt, "s", $_POST["username"]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            if (password_verify($_POST["password"], $row["password"]) && $row["nick"] == $_POST["username"]) {
                $_SESSION["id"] = $row["id"];
                $_SESSION["username"] = $_POST["username"];
                $_SESSION["isAdmin"] = $row["isAdmin"];
                mysqli_stmt_close($stmt);
                mysqli_close($link);
                header("location: /profile");
                exit;
            } else {
                $error = "<span class='error'>Неверный логин или пароль</span>";
            }
        }
    }
}
?>
<html>
<head>
    <title>EcoTime</title>
    <link rel="stylesheet" href="/assets/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&family=Montserrat:wght@600&display=swap" rel="stylesheet">
	<script src="https://yastatic.net/s3/passport-sdk/autofill/v1/sdk-suggest-with-polyfills-latest.js"></script>
</head>
<body>
<?=getHeader()?>
<div class="auth">
    <div class="welcome_block">
        <span class="welcome_block_title">Добро пожаловать!</span>
        <span class="welcome_block_subtitle">Меняйте экологическую ситуацию вместе с нами. Один аккаунт для всей экосистемы экологических сервисов.</span>
        <div class="welcome_block_form">
            <form method="post">
                <input name="username" type="text" id="username" placeholder="Логин">
                <input name="password" type="password" id="password" placeholder="Пароль">
                <div class="flex">
	                <input type="submit" name="auth" value="Войти по паролю">
	                <a href="reg">Создать пароль</a>
                </div>
                <?=$error?>
            </form>
        </div>
	    <div class="welcome_block_yandex">
		    <div class="yandex" id="yandex">
			    <script>
                    window.onload = function() {
                        window.YaAuthSuggest.init({
                                client_id: '',
                                response_type: 'token',
                                redirect_uri: 'https://gcmos.tw1.su/auth/ya/ya.php'
                            },
                            'https://gcmos.tw1.su/', {
                                view: 'button',
                                parentId: 'yandex',
                                buttonView: 'main',
                                buttonTheme: 'light',
                                buttonSize: 'm',
                                buttonBorderRadius: 10
                            }
                        )
                            .then(function(result) {
                                return result.handler()
                            })
                            .then(function(data) {
                                const form = document.createElement("form");
                                form.method = "POST";
                                form.action = "https://gcmos.tw1.su/auth/ya/yandex.php";
                                for (const key in data) {
                                    const input = document.createElement("input");
                                    input.type = "hidden";
                                    input.name = key;
                                    input.value = data[key];
                                    form.appendChild(input);
                                }
                                document.body.appendChild(form);
                                form.submit();
                            })
                    };
			    </script>
		    </div>
	    </div>
    </div>
</div>
</body>
</html>

