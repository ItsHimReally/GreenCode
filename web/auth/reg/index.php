<?php
session_start();
require_once "../../assets/db.php";
include "../../assets/blocks.php";

$acceptedSexes = [0,1];
if (isset($_POST["register"]) && preg_match("/^[A-z0-9]+$/u", $_POST["username"]) && preg_match("/^[A-zА-я0-9]+$/u", $_POST["name"]) && in_array($_POST["sex"], $acceptedSexes)) {
    $link = connectDB();
    if ($stmt = mysqli_prepare($link, "SELECT * FROM `users` WHERE `nick` LIKE ?")) {
        mysqli_stmt_bind_param($stmt, "s", $_POST["username"]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            if (isset($row["password"])) {
                $error = "<span class='error'>Аккаунт с этим логином уже существует.</span>";
            }
        }
        mysqli_stmt_close($stmt);
        if (is_null($error)) {
            if ($stmt = mysqli_prepare($link, "INSERT INTO `users` (`id`, `nick`, `name`, `imageUrl`, `status`, `password`, `regTime`, `links`, `bio`, `sex`, `birthDate`, `xp`, `bonusPoints`, `statusTree`, `co2kg`, `healthApp`, `tgID`) VALUES (NULL, ?, ?, NULL, '1', ?, NOW(), NULL, NULL, ?, ?, '0', '0', NULL, '0', NULL, NULL);")) {
				$pass = password_hash($_POST["password"], PASSWORD_BCRYPT);
                mysqli_stmt_bind_param($stmt, "sssss", $_POST["username"], $_POST["name"], $pass, $_POST["sex"], $_POST["datebirth"]);
                mysqli_stmt_execute($stmt);
                $id = mysqli_insert_id($link);
                $_SESSION["id"] = $id;
                $_SESSION["username"] = $_POST["username"];
				$_SESSION["isAdmin"] = 0;
                mysqli_stmt_close($stmt);
                mysqli_close($link);
                header("location: /profile");
                exit;
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
</head>
<body>
<?=getHeader()?>
<div class="auth">
    <div class="welcome_block">
        <span class="welcome_block_title">Сделайте первый шаг...</span>
        <span class="welcome_block_subtitle">чтобы поменять мир. Один аккаунт для экосистемы экологических сервисов.</span>
        <div class="welcome_block_form">
            <form method="post">
                <input name="username" type="text" id="username" placeholder="Логин">
	            <input name="name" type="text" id="name" placeholder="Отображаемое имя">
	            <input name="password" type="password" id="password" placeholder="Пароль">
	            <hr>
	            <label>
		            Дата рождения:
                <input name="datebirth" type="date" id="datebirth" placeholder="Дата рождения">
	            </label>
	            <label>
		            <input name="sex" type="radio" id="sex" value="0"> Мужчина
	            </label>
	            <label>
		            <input name="sex" type="radio" id="sex" value="1"> Женщина
	            </label>

                <input type="submit" name="register" value="Создать пароль">
                <?=$error?>
            </form>
        </div>
    </div>
</div>
</body>
</html>

