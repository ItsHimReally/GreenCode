<?php
error_reporting(0);
session_start();
if (!isset($_SESSION["id"])) {
    header("location: /auth");
    exit();
}

require_once "../assets/db.php";
require_once "../assets/blocks.php";
$header = getHeader();
$link = connectDB();
if (!isset($_GET["id"])) {
    $_GET["id"] = $_SESSION["id"];
    $ownProfile = true;
} else if ($_SESSION["id"] == $_GET["id"]) {
    $ownProfile = true;
} else if (is_numeric($_GET["id"])) {
    $ownProfile = false;
} else {
    header("Location: /");
    exit();
}
if ($stmt = mysqli_prepare($link, "SELECT * FROM `users` WHERE `id` LIKE ?")) {
    mysqli_stmt_bind_param($stmt, "s", $_GET["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
}
if ($stmt = mysqli_prepare($link, "SELECT * FROM `registrations` WHERE `userID` = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $_GET["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $events = [];
    while ($e = mysqli_fetch_assoc($result)) {
        $events[] = $e;
    }
}
// Здесь проверка и выдача достижений (код Глеба)

// Здесь проверка дерева
function checkTreeGrowth($conn, $userID) {
    // Получаем текущее дерево пользователя
    $sql = "SELECT * FROM user_trees WHERE userID = ? AND treeIsAlive = TRUE ORDER BY timeStartSeed DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $userID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $tree = $result->fetch_assoc();
        // Получаем текущее время
        $now = new DateTime();
        // Проверка, прошло ли больше месяца с последнего мероприятия
        $timeLastEvent = new DateTime($tree['timeLastEvent']);
        $interval = $now->diff($timeLastEvent);

        if ($interval->m >= 1) {
            // Дерево умерло
            $sqlUpdate = "UPDATE user_trees SET treeIsAlive = FALSE WHERE id = ?";
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param('i', $tree['id']);
            $stmtUpdate->execute();
            return 'dead';
        }
        if ($tree['currentStage'] == 'seed' && $tree['xp'] >= 300) {
            $sqlUpdate = "UPDATE user_trees SET currentStage = 'sprout', xp = xp - 300 WHERE id = ?";
            $output = "sprout"; $newStatus = true;
        } elseif ($tree['currentStage'] == 'sprout' && $tree['xp'] >= 300) {
            $sqlUpdate = "UPDATE user_trees SET currentStage = 'small_tree', xp = xp - 300 WHERE id = ?";
            $output = "small_tree"; $newStatus = true;
        } elseif ($tree['currentStage'] == 'small_tree' && $tree['xp'] >= 500) {
            $sqlUpdate = "UPDATE user_trees SET currentStage = 'medium_tree', xp = xp - 500 WHERE id = ?";
            $output = "medium_tree"; $newStatus = true;
        } elseif ($tree['currentStage'] == 'medium_tree' && $tree['xp'] >= 750) {
            $sqlUpdate = "UPDATE user_trees SET currentStage = 'big_tree', xp = xp - 750 WHERE id = ?";
            $output = "big_tree"; $newStatus = true;
        } else {
            $output = $tree["currentStage"]; $newStatus = false;
        }

        // Обновляем стадию дерева
        if ($newStatus) {
            $stmtUpdate = $conn->prepare($sqlUpdate);
            $stmtUpdate->bind_param('i', $tree['id']);
            $stmtUpdate->execute();
        }
        return $output;
    } else {
        return 'no_tree';
    }
}
function calculateYearsPassed($date) {
    $currentDate = new DateTime();
    $inputDate = new DateTime($date);
    $interval = $currentDate->diff($inputDate);
    return $interval->y;
}
if ($user["sex"] == 0) {
    $sex = "Муж";
} else {
    $sex = "Жен";
}
// Здесь проверка предстоящих мероприятий
$currentDateTime = date('Y-m-d H:i:s');
$sql = "
    SELECT r.id, r.userID, r.eventID, e.title, 
           DATE_FORMAT(e.timeStart, '%d.%m %H:%i') AS formatted_timeStart
    FROM registrations r
    JOIN events e ON r.eventID = e.id
    WHERE e.timeStart > ? AND r.isApproved = 1 AND r.userID = ?
";
$stmt = $link->prepare($sql);
$stmt->bind_param('ss', $currentDateTime, $_SESSION["id"]);
$stmt->execute();
$result = $stmt->get_result();

if ($ownProfile) {
    if ($stmt = mysqli_prepare($link, "SELECT COUNT(*) FROM `communities` WHERE `ownerUserID` = ?")) {
        mysqli_stmt_bind_param($stmt, "s", $_GET["id"]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $e = mysqli_fetch_row($result)[0];
		if ($e > 0) {
			$ownOrg = true;
		} else {
			$ownOrg = false;
		}
    }
}
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
<div class="profile_block">
    <div class="ava">
        <?php if (isset($user["imageUrl"])): ?>
        <img src="<?=$user["imageUrl"]?>" alt="Avatar">
        <?php else: ?>
        <img src="/assets/ava.webp" alt="Avatar">
        <?php endif; ?>
    </div>
    <div class="profile_info">
        <?php if ($ownProfile): ?>
        <a href="#" class="buttonedit">
            <img src="/assets/pencil-fill.svg" alt="Edit">
        </a>
        <?php if ($user["isAdmin"] == 1 || $ownOrg): ?>
        <div class="buttonsadmin">
        <?php if ($user["isAdmin"] == 1): ?>
        <a href="/admin" class="buttonadmin">
	        Админ-панель
        </a>
        <?php endif; ?>
        <?php if ($ownOrg): ?>
        <a href="org" class="buttonadmin">
	        Организатор мероприятий
        </a>
        <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
        <div class="pr_in_fl">
            <span class="profile_info_nick"><?=$user["nick"]?></span>
            <span class="profile_info_title"><?=$user["name"]?></span>
            <?php if (isset($sex, $user["birthDate"]) and $ownProfile): ?>
            <span class="profile_info_age"><?=$sex?>, <?=calculateYearsPassed($user["birthDate"])?></span>
            <?php endif; ?>
        </div>
        <div>
            <div class="profile_info_add">
                <img src="/assets/calendar-event.svg" alt="Joined">
                <span>Присоединился <?=$user["regTime"]?></span>
            </div>
            <div class="profile_info_add">
                <img src="/assets/bullseye.svg" alt="Joined">
                <span>Снизил количество выбросов Co2 на <?=$user["co2kg"]?> кг.</span>
            </div>
        </div>
    </div>
</div>
<div class="profile_grid">
    <div class="profile_grid_col">
        <?php if ($result->num_rows > 0 and $ownProfile): ?>
        <div class="profile_next_events">
            <?php while ($row = $result->fetch_assoc()): ?>
            <div class="profile_next_event">
                <span class="profile_next_events_t">Напоминаем, что вы записаны:</span><br>
                <span class="pne_date"><?=$row['formatted_timeStart']?></span>
                <a href="/event?id=<?=$row["eventID"]?>"><span class="pne_title"><?=htmlspecialchars($row['title'])?></span></a>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
        <div class="profile_stats">
            <span class="profile_title">Статистика</span>
            <?php if (isset($user["countTrees"])): ?>
            <div class="statss3">
            <?php else: ?>
            <div class="statss">
            <?php endif; ?>
                <div class="stats_block">
                    <span class="stats_block_value"><?=$user["xp"]?></span>
                    <span class="stats_block_text">XP за все время</span>
                </div>
                <div class="stats_block">
                    <span class="stats_block_value"><?=count($events)?></span>
                    <span class="stats_block_text">Мероприятий</span>
                </div>
                <?php if (isset($user["countTrees"])): ?>
                <div class="stats_block">
                    <span class="stats_block_value"><?=$user["countTrees"]?></span>
                    <span class="stats_block_text">Деревьев активности</span>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="cal">
            <span class="profile_title">Календарь мероприятий</span>
            <div id="calendar" class="calendar"></div>
            <script>
                function createEmptyCalendar() {
                    const calendar = document.getElementById('calendar');
                    const today = new Date();
                    const startOfYear = new Date(today.getFullYear(), 0, 1);
                    for (let i = 0; i < 365; i++) {
                        const day = document.createElement('div');
                        day.className = 'day';
                        const currentDate = new Date(startOfYear);
                        currentDate.setDate(startOfYear.getDate() + i);
                        const formattedDate = currentDate.toISOString().split('T')[0];
                        day.setAttribute('title', formattedDate);
                        calendar.appendChild(day);
                    }
                }
                function markEventDays(eventDates) {
                    const calendar = document.getElementById('calendar').children;
                    const today = new Date();
                    const startOfYear = new Date(today.getFullYear(), 0, 1);
                    for (let i = 0; i < calendar.length; i++) {
                        const dayElement = calendar[i];
                        const currentDate = new Date(startOfYear);
                        currentDate.setDate(startOfYear.getDate() + i);
                        const formattedDate = currentDate.toISOString().split('T')[0];  // Преобразуем дату в формат YYYY-MM-DD
                        if (eventDates.includes(formattedDate)) {
                            dayElement.classList.add('active');
                        }
                    }
                }
                fetch('get_events.php?id=<?=$_GET['id']?>')
                    .then(response => response.json())
                    .then(data => {
                        createEmptyCalendar();
                        markEventDays(data);
                    });
            </script>
        </div>
        <div class="achievements">
	        <div class="achtitle">
                <span class="profile_title">Достижения</span>
		        <div id="popup-button">[Все]</div>
	        </div>
	        <div class="ach">
	        <?php
	        //
            $q = mysqli_query($link, "
            SELECT a.id, a.userID, al.name, al.imageUrl FROM achievements a JOIN achievementsList al ON a.achieveID = al.id WHERE a.userID = ".$_GET["id"]." ORDER BY a.id DESC LIMIT 5;");
	        while ($a = mysqli_fetch_array($q)) {
				echo '<div class="a">
				<img src="/assets/achive/'.$a["imageUrl"].'" alt="Achieve">
				<span>'.$a["name"].'</span>
			</div>';
            }
	        ?>
	        </div>
        </div>
        <div class="bio">
            <span class="profile_title">Описание</span>
            <p><?=htmlspecialchars($user["bio"])?></p>
        </div>
    </div>
    <div class="profile_grid_col">
        <?php if ($ownProfile): ?>
        <div class="profile_tree">
            <?php
            $treeStatus = checkTreeGrowth($link, $_SESSION["id"]);
            if ($treeStatus == 'no_tree') {
                $sqlInsert = "INSERT INTO user_trees (userID, currentStage, timeStartSeed) VALUES (?, 'seed', NOW())";
                $stmtInsert = $link->prepare($sqlInsert);
                $stmtInsert->bind_param('i', $_SESSION["id"]);
                $stmtInsert->execute();
            } elseif ($treeStatus == 'dead') {
                $comment = "Ваше дерево умерло, потому что вы не посещали мероприятия больше, чем месяц.";
                $img = "dead.png";
            } elseif ($treeStatus == 'seed') {
                $comment = "Начало положено! Продолжайте участвовать в мероприятиях и заработайте 300 XP, чтобы дерево выросло.";
                $img = "seed.png";
            } elseif ($treeStatus == 'sprout') {
                $comment = "Ого! Продолжайте участвовать в мероприятиях и заработайте еще 300 XP, чтобы вырастить свое дерево.";
                $img = "seed.png";
            } elseif ($treeStatus == 'small_tree') {
                $comment = "Саженец. Продолжайте участвовать в мероприятиях и заработайте еще 500 XP, чтобы помочь дереву расти еще больше.";
                $img = "sapling.png";
            } elseif ($treeStatus == 'big_tree') {
                $comment = "Ваше дерево полностью выросло. Так держать!";
                $img = "tree.png";
            } else {
                $comment = "Участвуйте в мероприятиях, чтобы вырастить свое дерево активности.";
            }
            ?>
            <?php if (!empty($img)): ?>
            <img src="/assets/trees/<?=$img?>" alt="Tree">
            <?php endif; ?>
            <span><?=$comment?></span>
        </div>
        <div class="profile_active">
            <a href="apply.php">Стать организатором</a>
        </div>
        <?php endif; ?>
    </div>
</div>
<div id="popup" class="popup">
	<div class="popup-content">
		<div id="x" class="close">x</div>
		<div class="achs">
	<?php
	$q = mysqli_query($link, "SELECT * FROM `achievementsList`");
	while ($achiv = mysqli_fetch_array($q)) {
		$r = mysqli_query($link, "SELECT COUNT(*) FROM `achievements` WHERE userID = ".$_GET["id"]." AND achieveID = ".$achiv["id"]);
		$ca = mysqli_fetch_row($r)[0];
		if ($ca > 0) {
			$completed = true;
		} else {
			$completed = false;
			$style = ' style="filter: grayscale(1);"';
		}
		echo '
	<div class="ach" title="'.$achiv["text"].'">
		<img src="/assets/achive/'.$achiv["imageUrl"].'"'.$style.'>
		<span>'.$achiv["name"].'</span>
	</div>';
	}
	?>
		</div>
	</div>
</div>
<script>
    const popup = document.getElementById('popup');
    const popupButton = document.getElementById('popup-button');
    const closeButton = document.getElementById('x');
    popupButton.addEventListener('click', function() {
        popup.style.display = 'block';
    });
    closeButton.addEventListener('click', function() {
        popup.style.display = 'none';
    });
    window.addEventListener('click', function(event) {
        if (event.target === popup) {
            popup.style.display = 'none';
        }
    });
</script>
</body>
</html>
