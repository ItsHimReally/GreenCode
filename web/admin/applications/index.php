<?php
session_start();
if ($_SESSION["isAdmin"] != 1) {
    header("location: /");
    exit();
}

include('../../assets/db.php');
$link = connectDB();
?>
<html>
<head>
    <title>EcoTime</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="/admin/css/style.css" media="all">
    <link rel="stylesheet" href="/admin/css/users.css" media="all">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="Description" content="EcoTime">
    <meta http-equiv="Content-language" content="ru-RU">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&family=Roboto&display=swap" rel="stylesheet">
</head>
<body>
<div class="wrapper">
    <div class="sideBar">
        <div class="menu">
	        <a href="/"><img class="icon" src="/admin/images/speedometer.svg" alt="Главная"></a>
	        <a href="/admin/profiles"><img class="icon" src="/admin/images/person-fill.svg" alt="Профили"></a>
	        <a href="/admin/applications"><img class="icon" src="/admin/images/person-lines-fill.svg" alt="Заявки"></a>
	        <a href="/admin/stats"><img class="icon" src="/admin/images/bar-chart-fill.svg" alt="Экспорт"></a>
        </div>
    </div>
    <div class="mheader">
	    <a href="/"><img class="icon" src="/admin/images/speedometer.svg" alt="Главная"></a>
	    <a href="/admin/profiles"><img class="icon" src="/admin/images/person-fill.svg" alt="Профили"></a>
	    <a href="/admin/applications"><img class="icon" src="/admin/images/person-lines-fill.svg" alt="Заявки"></a>
	    <a href="/admin/stats"><img class="icon" src="/admin/images/bar-chart-fill.svg" alt="Экспорт"></a>
    </div>
    <div class="page">
        <div class="titleFlex">
            <div class="title">Заявки на Создании мероприятий</div>
        </div>
        <div class="content">
            <div class="table">
                <table class="users">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>Пользователь</th>
                        <th>Тип</th>
                        <th>Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $query = mysqli_query($link, "SELECT apply.userID, users.nick, apply.type, apply.json FROM apply JOIN users ON apply.userID = users.id;");
                    while ($u = mysqli_fetch_array($query)) {
						switch ($u["type"]) {
							case "phisic":
								$type = "Физ.Лицо";
								break;
							case "nco":
                                $type = "НКО";
								break;
							case "other":
                                $type = "Юр.Лицо";
								break;
						}
                        echo '
		            <tr>
			            <td>'.$u["id"].'</td>
			            <td>'.$u["nick"].'</td>
			            <td>'.$type.'</td>
			            <td>
			                <a href="react.php?id='.$u['id'].'" class="button-edit">Отреагировать</a>
						</td>
		            </tr>'
                        ;
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>