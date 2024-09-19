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

if ($stmt = mysqli_prepare($link, "SELECT * FROM `users` WHERE `id` LIKE ?")) {
    mysqli_stmt_bind_param($stmt, "s", $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
}
if ($stmt = mysqli_prepare($link, "SELECT * FROM `communities` WHERE `ownerUserID` = ? LIMIT 1;")) {
    mysqli_stmt_bind_param($stmt, "s", $_SESSION["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $org = mysqli_fetch_assoc($result);
}
if (empty($org["id"])) {
    header("Location: /profile");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sql = "INSERT INTO `events` 
            (`id`, `org`, `imageUrl`, `title`, `short`, `description`, `timeStart`, `timeEnd`, `isOnline`, `locationAddress`, `locationCoords`, `locationUrl`, `tags`, `ageLimit`, `isClosed`, `whiteList`, `price`, `Tickets`, `Form`, `bonusPoints`, `bonusTree`, `addXP`) 
            VALUES 
            (NULL, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $link->prepare($sql)) {
        $stmt->bind_param(
            "sssssssssssssssssssss",
            $org['id'],
            $_POST['imageUrl'],
            $_POST['title'],
            $_POST['short'],
            $_POST['description'],
            $_POST['timeStart'],
            $_POST['timeEnd'],
            $_POST['isOnline'],
            $_POST['locationAddress'],
            $_POST['locationCoords'],
            $_POST['locationUrl'],
            $_POST['tags'],
            $_POST['ageLimit'],
            $_POST['isClosed'],
            $_POST['whiteList'],
            $_POST['price'],
            $_POST['Tickets'],
            $_POST['Form'],
            $_POST['bonusPoints'],
            $_POST['bonusTree'],
            $_POST['addXP']
        );

        if ($stmt->execute()) {
            $new_event_id = $link->insert_id;
            header("Location: /event?id=" . $new_event_id);
            exit();
        }
        $stmt->close();
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
<div class="create">
    <div class="welcome_block_title">Создание мероприятия</div>
    <div class="event_block_info_cols">
        <div><img src="<?=$org["imageUrl"]?>" alt="img"></div>
        <div><span class="event_block_info_main"><?=$org["name"]?></span></div>
    </div>
    <form id="eventForm" method="POST">
        <div class="form-group">
            <label for="title">Название мероприятия</label>
            <input type="text" id="title" name="title" required>
        </div>

        <div class="form-group">
            <label for="short">Краткое описание</label>
            <input type="text" id="short" name="short" required>
        </div>

        <div class="form-group">
            <label for="description">Описание</label>
            <textarea id="description" name="description" required></textarea>
        </div>

        <div class="form-group">
            <label for="timeStart">Время начала</label>
            <input type="datetime-local" id="timeStart" name="timeStart" required>
        </div>

        <div class="form-group">
            <label for="timeEnd">Время окончания</label>
            <input type="datetime-local" id="timeEnd" name="timeEnd" required>
        </div>

        <div class="form-group">
            <label for="isOnline">Тип мероприятия</label>
            <select id="isOnline" name="isOnline" required>
                <option value="1">Онлайн</option>
                <option value="0">Офлайн</option>
            </select>
        </div>

        <div class="form-group">
            <label for="locationAddress">Адрес</label>
            <input type="text" id="locationAddress" name="locationAddress">
        </div>

        <div class="form-group">
            <label for="locationCoords">Координаты</label>
            <input type="text" id="locationCoords" name="locationCoords">
        </div>

        <div class="form-group">
            <label for="tags">Теги</label>
            <input type="text" id="tags" name="tags">
        </div>

        <div class="form-group">
            <label for="ageLimit">Возрастное ограничение</label>
            <input type="number" id="ageLimit" name="ageLimit" min="0">
        </div>

        <div class="form-group">
            <h2>Билеты</h2>
            <div id="ticketsContainer"></div>
            <button type="button" id="addTicketBtn">Добавить билет</button>
        </div>

        <div class="form-group">
            <h2>Поля формы</h2>
            <div id="formFieldsContainer"></div>
            <button type="button" id="addFormFieldBtn">Добавить поле</button>
        </div>

        <input type="hidden" id="Tickets" name="Tickets">
        <input type="hidden" id="Form" name="Form">

        <button type="submit">Создать мероприятие</button>
    </form>

    <script>
        let tickets = [];
        let formFields = [];

        document.getElementById('addTicketBtn').addEventListener('click', function() {
            const ticket = {
                name: prompt("Название билета:"),
                available: prompt("Количество доступных билетов:"),
                description: prompt("Описание билета:"),
                max_per_user: prompt("Максимум билетов на одного пользователя:")
            };

            tickets.push(ticket);
            updateTicketsView();
        });

        function updateTicketsView() {
            const ticketsContainer = document.getElementById('ticketsContainer');
            ticketsContainer.innerHTML = '';
            tickets.forEach((ticket, index) => {
                ticketsContainer.innerHTML += `<div>
                <strong>Название:</strong> ${ticket.name},
                <strong>Доступно:</strong> ${ticket.available},
                <strong>Описание:</strong> ${ticket.description},
                <strong>Макс. на пользователя:</strong> ${ticket.max_per_user}
                <button type="button" onclick="removeTicket(${index})">Удалить</button>
            </div>`;
            });
            document.getElementById('Tickets').value = JSON.stringify(tickets);
        }

        function removeTicket(index) {
            tickets.splice(index, 1);
            updateTicketsView();
        }

        document.getElementById('addFormFieldBtn').addEventListener('click', function() {
            const field = {
                label: prompt("Название поля:"),
                type: prompt("Тип поля (text, textarea, select и т.д.):"),
                name: prompt("Имя поля (name):"),
                placeholder: prompt("Плейсхолдер (необязательно):"),
                required: confirm("Обязательно для заполнения?")
            };

            formFields.push(field);
            updateFormFieldsView();
        });

        function updateFormFieldsView() {
            const formFieldsContainer = document.getElementById('formFieldsContainer');
            formFieldsContainer.innerHTML = '';
            formFields.forEach((field, index) => {
                formFieldsContainer.innerHTML += `<div>
                <strong>Название:</strong> ${field.label},
                <strong>Тип:</strong> ${field.type},
                <strong>Имя:</strong> ${field.name},
                <strong>Плейсхолдер:</strong> ${field.placeholder || "Нет"},
                <strong>Обязательно:</strong> ${field.required ? "Да" : "Нет"}
                <button type="button" onclick="removeFormField(${index})">Удалить</button>
            </div>`;
            });
            document.getElementById('Form').value = JSON.stringify(formFields);
        }

        function removeFormField(index) {
            formFields.splice(index, 1);
            updateFormFieldsView();
        }
    </script>
</div>
</body>
</html>
