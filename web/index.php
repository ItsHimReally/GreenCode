<?php
error_reporting(0);
session_start();
require_once "assets/db.php";
require_once "assets/blocks.php";
$header = getHeader();
$link = connectDB(); $map = false;
if ($_GET["map"] == "1") {
	$map = true;
}
$query = mysqli_query($link, "SELECT * FROM `events` WHERE `isClosed` = 0 AND `ageLimit` != 18 AND `timeStart` > NOW();");
if ($map) {
    $queryForCoords = mysqli_query($link, "SELECT `locationCoords` FROM `events` WHERE `isClosed` = 0 AND `ageLimit` != 18 AND `timeStart` > NOW();");
    $eventsCoords = [];
    while ($event = mysqli_fetch_array($queryForCoords)) {
        $eventsCoords[] = $event["locationCoords"];
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
<div class="head_block">
	<div class="hblock">
		<span class="hsubtitle">Агрегатор экологических мероприятий</span>
		<span class="htitle">EcoTime</span>
		<span class="hlsubtitle">Ваш вклад в здоровое будущее Москвы!</span>
	</div>
	<div class="hblockimg">
		<img src="assets/mascot.png" alt="Mascot">
	</div>
</div>
<div class="map">
	<?php if (!$map): ?>
	<div class="map_block">
		<a href="?map=1">Отобразить на карте</a>
	</div>
	<?php else: ?>
	<div id="map" style="width: 100%; height:300px"></div>
	<?php endif; ?>
</div>
<div class="list">
	<?php
	while ($event = mysqli_fetch_array($query)) {
		$com = getCommunityById($link, $event["org"]);
		echo '
	<a class="event_block" href="/event?id='.$event["id"].'">
		<div class="event_block_img" style="background: url(\''.htmlspecialchars($event["imageUrl"]).'\') center/cover no-repeat;"></div>
		<div class="event_block_info">
			<div class="event_block_info_cols">
				<div><img src="'.htmlspecialchars($com["imageUrl"]).'" alt="'.htmlspecialchars($com["name"]).'"></div>
				<div><span class="event_block_info_title">'.htmlspecialchars($event["title"]).'</span></div>
			</div>
			<div class="event_block_info_main">
				<span>'.formatTimestamp(strtotime($event["timeStart"])).'</span>
				<span>'.timeUntil(strtotime($event["timeStart"])).'</span>
			</div>
		</div>
	</a>';
	}
	?>
</div>
<?php if ($map): ?>
<script src="https://api-maps.yandex.ru/2.1/?apikey=d8a986a4-f378-4252-9b65-7d3ff1655760&load=package.standard&lang=ru_RU" type="text/javascript"></script>
<script>
    ymaps.ready(function(){
        var moscow_map = new ymaps.Map("map", {
            center: [55.76, 37.64],
            zoom: 10
        });

        <?php
        $c = 0;
        foreach ($eventsCoords as $coord) {
            $c++;
            echo '
                var myPlacemark'.$c.' = new ymaps.Placemark(['.$coord.']);
                moscow_map.geoObjects.add(myPlacemark'.$c.');
                ';
        }
		?>
    });
</script>
<?php endif; ?>
</body>
</html>
