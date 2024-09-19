<?php
error_reporting(0);
session_start();
require_once "../assets/db.php";
require_once "../assets/blocks.php";
$header = getHeader();
$link = connectDB();
?>
<html>
<head>
    <title>EcoTime</title>
    <link rel="stylesheet" href="/assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
</head>
<body>
<?=$header?>
<div class="rating">
    <table class="rateTable">
            <thead>
                <tr>
                    <th>Место</th>
                    <th>Ник</th>
                    <th>XP</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $query = mysqli_query($link, "SELECT * FROM `users` ORDER BY `users`.`xp` DESC");
            $c = 0;
            while ($u = mysqli_fetch_array($query)) {
                $c++;
                echo '
		            <tr>
			            <td>'.$c.'</td>
			            <td><a href="/profile?id='.$u["id"].'">'.$u["nick"].'</a></td>
			            <td>'.$u["xp"].'</td>
		            </tr>'
                ;
            }
            ?>
            </tbody>
        </table>
</div>
</body>
</html>
