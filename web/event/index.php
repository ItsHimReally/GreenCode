<?php
error_reporting(0);
session_start();
require_once "../assets/db.php";
require_once "../assets/blocks.php";
require_once "../assets/Parsedown.php";
$header = getHeader();
$link = connectDB();
if (!isset($_GET["id"]) or !is_numeric($_GET["id"])) {
    header("location: /");
    exit();
}
$event = getEvent($link, $_GET["id"]);
$org = getCommunityById($link, $event["org"]);
$current_time = time();
$event_time = strtotime($event["timeStart"]);
$one_hour_in_seconds = 3600;
$time_difference = $event_time - $current_time;
$isWithinOneHour = $time_difference <= $one_hour_in_seconds;
$reactions = getLikes($link, $_GET["id"]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_data = [];
    $tickets_data = [];
    $form_structure = json_decode($event["Form"], true);
    foreach ($form_structure['fields'] as $field) {
        $field_name = $field['name'];
        if (isset($_POST[$field_name])) {
            $user_data[$field_name] = $_POST[$field_name];
        }
    }
    foreach ($_POST as $key => $value) {
        if (strpos($key, 'Tickets_') === 0 && intval($value) > 0) {
            $ticket_name = str_replace('Tickets_', '', $key);
            $tickets_data[$ticket_name] = intval($value);
        }
    }
    $json_data = json_encode($user_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    $user_id = $_SESSION['id'];
    $event_id = $event['id'];
    $stmt = $link->prepare("INSERT INTO registrations (userID, eventID, Ticket, Form, timeReg, isPresence, isPaid, isApproved) VALUES (?, ?, ?, ?, NOW(), 0, 0, 1)");
    foreach ($tickets_data as $ticket_name => $ticket_count) {
        for ($i = 0; $i < $ticket_count; $i++) {
            $stmt->bind_param('ssss', $user_id, $event_id, $ticket_name, $json_data);
            $stmt->execute();
        }
    }
    $stmt->close();
    header("location: /profile");
    exit();
}

$tickets = json_decode($event["Tickets"], true)['tickets'];
$event_id = $event["id"];
$has_available_tickets = false;
foreach ($tickets as $key => $ticket) {
    $ticket_name = $ticket['name'];
    $query = "SELECT COUNT(*) as count FROM registrations WHERE eventID = ? AND Ticket = ?";
    $stmt = $link->prepare($query);
    $stmt->bind_param("is", $event_id, $ticket_name);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    if ($row['count'] >= $ticket['available']) {
        unset($tickets[$key]);
    } else {
        $has_available_tickets = true;
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
<div class="event" style="background: url('<?=$event["imageUrl"]?>') center/cover no-repeat;">
	<div class="event_info">
		<span class="event_info_title"><?=$event["title"]?></span>
		<div class="event_info_org">
			<img src="<?=$org["imageUrl"]?>" alt="Avatar">
			<span><?=$org["name"]?></span>
		</div>
	</div>
</div>
<div class="event_details">
	<div class="event_details_col">
		<div class="event_details_row">
			<span class="age"><?=$event["ageLimit"]?>+</span>
			<div class="categories">
                <?php
                $tags = json_decode($event["tags"], true);
                foreach ($tags as $t) {
                    echo '<div class="tag">'.$t.'</div>';
                }
                ?>
			</div>
		</div>
		<div class="event_details_blocks">
			<div class="event_details_block">
				<div class="icon">
					<img src="/assets/calendar-event.svg" alt="Date and Time">
				</div>
				<div class="event_details_block_info">
					<span class="toDate"><?=timeUntil(strtotime($event["timeStart"]))?></span>
					<span class="time"><?=formatTimestamp(strtotime($event["timeStart"]))?></span>
				</div>
			</div>
			<div class="event_details_block">
				<div class="icon">
					<img src="/assets/geo-alt.svg" alt="Location">
				</div>
				<div class="event_details_block_info">
                    <?php if ($event["isOnline"]): ?>
						<span class="location">Мероприятие проходит онлайн</span>
                        <?php if (!empty($event["locationUrl"])): ?>
                            <?php if ($isWithinOneHour): ?>
								<a href="<?=$event["locationUrl"]?>" target="_blank" class="link">Присоединиться</a>
                            <?php else: ?>
								<div class="link_blocked">
									Ссылка появиться за час до начала мероприятия
								</div>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php else: ?>
						<span class="location"><?= $event["locationAddress"] ?></span>
                        <?php if (!empty($locationCoords)): ?>
                            <?php $yandexMapsLink = "https://yandex.ru/maps/?ll=".urlencode($event["locationCoords"])."&z=16"; ?>
							<a href="<?= $yandexMapsLink ?>" target="_blank">Открыть в Яндекс Карты</a>
                        <?php endif; ?>
                    <?php endif; ?>
				</div>
			</div>
			<div class="event_details_block">
				<div class="icon">
					<img src="/assets/patch-question.svg" alt="Question">
				</div>
				<div class="event_details_block_info">
					<span class="toDate">Есть вопрос?</span>
					<span class="time"><a href="ask.php?id=<?=$event["id"]?>" style="text-decoration: underline;">Задайте</a> его нашему ассистенту или организаторам</span>
				</div>
			</div>
			<div class="like-dislike-container">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hand-thumbs-up" viewBox="0 0 16 16">
					<path d="M8.864.046C7.908-.193 7.02.53 6.956 1.466c-.072 1.051-.23 2.016-.428 2.59-.125.36-.479 1.013-1.04 1.639-.557.623-1.282 1.178-2.131 1.41C2.685 7.288 2 7.87 2 8.72v4.001c0 .845.682 1.464 1.448 1.545 1.07.114 1.564.415 2.068.723l.048.03c.272.165.578.348.97.484.397.136.861.217 1.466.217h3.5c.937 0 1.599-.477 1.934-1.064a1.86 1.86 0 0 0 .254-.912c0-.152-.023-.312-.077-.464.201-.263.38-.578.488-.901.11-.33.172-.762.004-1.149.069-.13.12-.269.159-.403.077-.27.113-.568.113-.857 0-.288-.036-.585-.113-.856a2 2 0 0 0-.138-.362 1.9 1.9 0 0 0 .234-1.734c-.206-.592-.682-1.1-1.2-1.272-.847-.282-1.803-.276-2.516-.211a10 10 0 0 0-.443.05 9.4 9.4 0 0 0-.062-4.509A1.38 1.38 0 0 0 9.125.111zM11.5 14.721H8c-.51 0-.863-.069-1.14-.164-.281-.097-.506-.228-.776-.393l-.04-.024c-.555-.339-1.198-.731-2.49-.868-.333-.036-.554-.29-.554-.55V8.72c0-.254.226-.543.62-.65 1.095-.3 1.977-.996 2.614-1.708.635-.71 1.064-1.475 1.238-1.978.243-.7.407-1.768.482-2.85.025-.362.36-.594.667-.518l.262.066c.16.04.258.143.288.255a8.34 8.34 0 0 1-.145 4.725.5.5 0 0 0 .595.644l.003-.001.014-.003.058-.014a9 9 0 0 1 1.036-.157c.663-.06 1.457-.054 2.11.164.175.058.45.3.57.65.107.308.087.67-.266 1.022l-.353.353.353.354c.043.043.105.141.154.315.048.167.075.37.075.581 0 .212-.027.414-.075.582-.05.174-.111.272-.154.315l-.353.353.353.354c.047.047.109.177.005.488a2.2 2.2 0 0 1-.505.805l-.353.353.353.354c.006.005.041.05.041.17a.9.9 0 0 1-.121.416c-.165.288-.503.56-1.066.56z"/>
				</svg>
                <?php echo $reactions['likes']; ?>
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-hand-thumbs-down" viewBox="0 0 16 16">
					<path d="M8.864 15.674c-.956.24-1.843-.484-1.908-1.42-.072-1.05-.23-2.015-.428-2.59-.125-.36-.479-1.012-1.04-1.638-.557-.624-1.282-1.179-2.131-1.41C2.685 8.432 2 7.85 2 7V3c0-.845.682-1.464 1.448-1.546 1.07-.113 1.564-.415 2.068-.723l.048-.029c.272-.166.578-.349.97-.484C6.931.08 7.395 0 8 0h3.5c.937 0 1.599.478 1.934 1.064.164.287.254.607.254.913 0 .152-.023.312-.077.464.201.262.38.577.488.9.11.33.172.762.004 1.15.069.13.12.268.159.403.077.27.113.567.113.856s-.036.586-.113.856c-.035.12-.08.244-.138.363.394.571.418 1.2.234 1.733-.206.592-.682 1.1-1.2 1.272-.847.283-1.803.276-2.516.211a10 10 0 0 1-.443-.05 9.36 9.36 0 0 1-.062 4.51c-.138.508-.55.848-1.012.964zM11.5 1H8c-.51 0-.863.068-1.14.163-.281.097-.506.229-.776.393l-.04.025c-.555.338-1.198.73-2.49.868-.333.035-.554.29-.554.55V7c0 .255.226.543.62.65 1.095.3 1.977.997 2.614 1.709.635.71 1.064 1.475 1.238 1.977.243.7.407 1.768.482 2.85.025.362.36.595.667.518l.262-.065c.16-.04.258-.144.288-.255a8.34 8.34 0 0 0-.145-4.726.5.5 0 0 1 .595-.643h.003l.014.004.058.013a9 9 0 0 0 1.036.157c.663.06 1.457.054 2.11-.163.175-.059.45-.301.57-.651.107-.308.087-.67-.266-1.021L12.793 7l.353-.354c.043-.042.105-.14.154-.315.048-.167.075-.37.075-.581s-.027-.414-.075-.581c-.05-.174-.111-.273-.154-.315l-.353-.354.353-.354c.047-.047.109-.176.005-.488a2.2 2.2 0 0 0-.505-.804l-.353-.354.353-.354c.006-.005.041-.05.041-.17a.9.9 0 0 0-.121-.415C12.4 1.272 12.063 1 11.5 1"/>
				</svg>
                <?php echo $reactions['dislikes']; ?>
			</div>
		</div>
	</div>
	<div class="event_details_col">
		<div class="event_details_col_article">
			<div class="short"><?=$event["short"]?></div>
			<div class="article">
                <?php
                $safeArticle = htmlspecialchars($event["description"], ENT_QUOTES | ENT_HTML5, 'UTF-8');
                $parsedown = new Parsedown();
                $articleHtml = $parsedown->text($safeArticle);
                echo $articleHtml;
                ?>
			</div>
		</div>
		<div class="event_details_col_reg">
			<form method="POST" action="">
				<span class="event_details_col_reg_title">Регистрация</span>
				<div class="event_details_col_tickets">
                    <?php
                    if (!$has_available_tickets) {
                        echo "<div class='no-tickets'>Регистрация закрыта. Возможно, билеты на это мероприятие закончились.</div>";
                        exit();
                    }
                    ?>
					<div class="ticket-selection">
                        <?php foreach ($tickets as $ticket): ?>
							<div class="ticket">
								<span class="ticket-name"><?= htmlspecialchars($ticket['name']) ?></span>
								<div class="ticket_buttons">
									<button class="minus-btn" data-ticket="<?= htmlspecialchars($ticket['name']) ?>" data-max="<?= $ticket['max_per_user'] ?>">-</button>
									<span class="ticket-count" id="count-<?= htmlspecialchars($ticket['name']) ?>">0</span>
									<input type="hidden" name="Tickets_<?= htmlspecialchars($ticket['name']) ?>" value="0" id="input-<?= htmlspecialchars($ticket['name']) ?>">
									<button class="plus-btn" data-ticket="<?= htmlspecialchars($ticket['name']) ?>" data-max="<?= $ticket['max_per_user'] ?>">+</button>
								</div>
							</div>
                        <?php endforeach; ?>
					</div>
				</div>
				<div class="event_details_col_form">
                    <?php
                    $form_data = json_decode($event["Form"], true);
                    function generate_form($form_data) {
                        foreach ($form_data['fields'] as $field) {
                            $required = isset($field['required']) && $field['required'] ? 'required' : '';
                            switch ($field['type']) {
                                case 'text':
                                    echo '<label>' . $field['label'] . '</label>';
                                    echo '<input type="text" name="' . $field['name'] . '" placeholder="' . $field['placeholder'] . '" ' . $required . '>';
                                    break;
                                case 'date':
                                    echo '<label>' . $field['label'] . '</label>';
                                    echo '<input type="date" name="' . $field['name'] . '" ' . $required . '>';
                                    break;
                                case 'radio':
                                    echo '<label>' . $field['label'] . '</label><br>';
                                    foreach ($field['options'] as $option) {
                                        echo '<label>';
                                        echo '<input type="radio" name="' . $field['name'] . '" value="' . $option['value'] . '" ' . $required . '>';
                                        echo $option['label'];
                                        echo '</label><br>';
                                    }
                                    break;
                                default:
                                    break;
                            }
                        }
                        echo '<button type="submit" name="submit">Продолжить</button>';
                    }
                    generate_form($form_data);
                    ?>
				</div>
			</form>
		</div>
	</div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const plusButtons = document.querySelectorAll('.plus-btn');
        const minusButtons = document.querySelectorAll('.minus-btn');

        plusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const ticketName = this.getAttribute('data-ticket');
                const max = parseInt(this.getAttribute('data-max'));
                const countElement = document.getElementById(`count-${ticketName}`);
                const inputElement = document.getElementById(`input-${ticketName}`);
                let currentCount = parseInt(countElement.textContent);

                if (currentCount < max) {
                    currentCount++;
                    countElement.textContent = currentCount;
                    inputElement.value = currentCount;
                }
            });
        });

        minusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const ticketName = this.getAttribute('data-ticket');
                const countElement = document.getElementById(`count-${ticketName}`);
                const inputElement = document.getElementById(`input-${ticketName}`);
                let currentCount = parseInt(countElement.textContent);

                if (currentCount > 0) {
                    currentCount--;
                    countElement.textContent = currentCount;
                    inputElement.value = currentCount;
                }
            });
        });
    });
</script>
</body>
</html>
