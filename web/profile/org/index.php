<?php
error_reporting(0);
session_start();
if (!isset($_SESSION["id"])) {
    header("location: /auth");
    exit();
}

require_once "../../assets/db.php";
require_once "../../assets/blocks.php";
$header = getHeader();
$link = connectDB();
if (!isset($_GET["id"])) {
    $_GET["id"] = $_SESSION["id"];
    $ownProfile = true;
} else if ($_SESSION["id"] == $_GET["id"]) {
    $ownProfile = true;
} else if (is_numeric($_GET["id"])) {
    header("Location: /");
    exit();
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
if ($stmt = mysqli_prepare($link, "SELECT * FROM `communities` WHERE `ownerUserID` = ? LIMIT 1;")) {
    mysqli_stmt_bind_param($stmt, "s", $_GET["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $org = mysqli_fetch_assoc($result);
}
if ($stmt = mysqli_prepare($link, "SELECT * FROM `events` WHERE `org` = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $org["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $events = [];
	while ($e = mysqli_fetch_array($result)) {
		$events[] = $e;
	}
}

if (empty($org["id"])) {
    header("Location: /profile");
    exit();
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
        <?php if (isset($org["imageUrl"])): ?>
            <img src="<?=$org["imageUrl"]?>" alt="Avatar">
        <?php else: ?>
            <img src="/assets/ava.webp" alt="Avatar">
        <?php endif; ?>
    </div>
    <div class="profile_info">
        <?php if ($ownProfile): ?>
            <a href="#" class="buttonedit">
                <img src="/assets/pencil-fill.svg" alt="Edit">
            </a>
        <?php endif; ?>
        <div class="pr_in_fl">
            <span class="profile_info_title"><?=$org["name"]?></span>
            <?php if (isset($sex, $org["birthDate"]) and $ownProfile): ?>
                <span class="profile_info_age"><?=$sex?>, <?=calculateYearsPassed($org["birthDate"])?></span>
            <?php endif; ?>
        </div>
        <div>
            <div class="profile_info_add">
                <img src="/assets/calendar-event.svg" alt="Joined">
                <span>Создание аккаунта: <?=$org["creationDate"]?></span>
            </div>
            <div class="profile_info_add">
                <img src="/assets/bullseye.svg" alt="Joined">
                <span>Провел <?=count($events)?> мероприятий.</span>
            </div>
        </div>
    </div>
</div>
<div class="profile_grid">
    <div class="profile_grid_col">
        <?php foreach ($events as $e): ?>
	    <a class="event_block" href="/event?id=<?=$e["id"]?>">
		    <div class="event_block_img" style="background: url('<?=htmlspecialchars($e["imageUrl"])?>') center/cover no-repeat;"></div>
			<div class="event_block_info">
				<div class="event_block_info_cols">
					<div><img src="<?=htmlspecialchars($org["imageUrl"])?>" alt="<?=htmlspecialchars($org["name"])?>"></div>
					<div><span class="event_block_info_title"><?=htmlspecialchars($e["title"])?></span></div>
				</div>
				<div class="event_block_info_main">
					<span><?=formatTimestamp(strtotime($e["timeStart"]))?></span>
					<span><?=timeUntil(strtotime($e["timeStart"]))?></span>
				</div>
			</div>
		</a>
	    <?php endforeach; ?>
    </div>
    <div class="profile_grid_col">
	    <a href="create.php">Создать мероприятие</a>
    </div>
</body>
</html>
