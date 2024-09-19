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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
// Проверка на наличие пользовательского ID в сессии
if (!isset($_SESSION["id"])) {
    die("Ошибка: пользователь не авторизован.");
}

// Получаем userID из сессии
$userID = $_SESSION["id"];

// Определяем тип формы (phisic, nco, other)
    if (isset($_POST['form_type'])) {
        $type = $_POST['form_type'];
    } else {
        die("Ошибка: тип формы не определен.");
    }
    if (!in_array($type, ['phisic', 'nco', 'other'])) {
        die("Ошибка: неверный тип формы.");
    }
    $jsonData = json_encode($_POST, JSON_UNESCAPED_UNICODE);
    $sql = "INSERT INTO apply (userID, type, json) VALUES (?, ?, ?)";
    $stmt = $link->prepare($sql);
    if (!$stmt) {
        die("Ошибка подготовки запроса: " . $link->error);
    }
    $stmt->bind_param('iss', $userID, $type, $jsonData);
    if ($stmt->execute()) {
        echo "Данные успешно сохранены!";
        exit();
    } else {
        echo "Ошибка: " . $stmt->error;
    }
    $stmt->close();
}
?>
<html>
<head>
    <title>EcoTime</title>
    <link rel="stylesheet" href="/assets/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;900&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <style>
        .form-container, .extra-options {
            display: none;
        }
    </style>
</head>
<body>
<?=$header?>
<div class="apply">
    <div class="welcome_block">
        <span class="welcome_block_title">Стать организатором мероприятий</span>
        <span class="welcome_block_subtitle">Заполните анкету для дальнейшей обработки нашей командой. Мы свяжемся с вами в течение нескольких рабочих дней.</span>
        <div class="apply_block_form">
            <h3>Выберите тип лица:</h3>
            <button id="phisicBtn">Физическое лицо</button>
            <button id="legalBtn">Юр лицо</button>
            <div id="extraOptions" class="extra-options">
                <button id="ncoBtn">НКО</button>
                <button id="otherBtn">Другое</button>
            </div>
            <form id="phisic" class="form-container">
                <h3>Форма для Физического лица</h3>
                <label for="firstName">Имя:</label>
                <input type="text" id="firstName" name="firstName"><br><br>

                <label for="lastName">Фамилия:</label>
                <input type="text" id="lastName" name="lastName"><br><br>

                <label for="middleName">Отчество:</label>
                <input type="text" id="middleName" name="middleName"><br><br>

                <label for="birthDate">Дата рождения:</label>
                <input type="date" id="birthDate" name="birthDate"><br><br>

                <label for="socialNetwork">Социальная сеть:</label>
                <input type="text" id="socialNetwork" name="socialNetwork"><br><br>

                <label for="phone">Контактный телефон:</label>
                <input type="tel" id="phone" name="phone"><br><br>

                <p>Есть ли подтвержденные Госуслуги?</p>
                <label for="gosUslugiYes">Да</label>
                <input type="radio" id="gosUslugiYes" name="gosUslugi" value="yes">
                <label for="gosUslugiNo">Нет</label>
                <input type="radio" id="gosUslugiNo" name="gosUslugi" value="no"><br><br>

	            <input type="hidden" name="form_type" value="phisic">
                <button type="submit">Отправить</button>
            </form>

            <!-- Форма для НКО -->
            <form id="nco" class="form-container">
                <h3>Форма для НКО</h3>
                <label for="ncoFIO">ФИО ваша:</label>
                <input type="text" id="ncoFIO" name="ncoFIO"><br><br>

                <label for="ncoPhone">Контактный телефон:</label>
                <input type="tel" id="ncoPhone" name="ncoPhone"><br><br>

                <label for="ncoRegNumber">Уч. номер:</label>
                <input type="text" id="ncoRegNumber" name="ncoRegNumber"><br><br>

                <label for="ncoFullName">Полное наименование:</label>
                <input type="text" id="ncoFullName" name="ncoFullName"><br><br>

                <label for="ncoOGRN">ОГРН:</label>
                <input type="text" id="ncoOGRN" name="ncoOGRN"><br><br>

                <label for="ncoAddress">Адрес регистрации:</label>
                <input type="text" id="ncoAddress" name="ncoAddress"><br><br>

	            <input type="hidden" name="form_type" value="nco">
                <button type="submit">Отправить</button>
            </form>

            <!-- Форма для Другое -->
            <form id="other" class="form-container">
                <h3>Форма для Другое</h3>
                <label for="otherFIO">ФИО:</label>
                <input type="text" id="otherFIO" name="otherFIO"><br><br>

                <label for="otherPosition">Должность:</label>
                <input type="text" id="otherPosition" name="otherPosition"><br><br>

                <label for="otherCompany">Название компании:</label>
                <input type="text" id="otherCompany" name="otherCompany"><br><br>

                <label for="otherOGRN">ОГРН:</label>
                <input type="text" id="otherOGRN" name="otherOGRN"><br><br>

                <label for="otherINN">ИНН:</label>
                <input type="text" id="otherINN" name="otherINN"><br><br>

                <label for="otherAddress">Юр. адрес:</label>
                <input type="text" id="otherAddress" name="otherAddress"><br><br>

                <label for="otherPhone">Контактный телефон:</label>
                <input type="tel" id="otherPhone" name="otherPhone"><br><br>

	            <input type="hidden" name="form_type" value="other">
                <button type="submit">Отправить</button>
            </form>

            <script>
                const phisicBtn = document.getElementById('phisicBtn');
                const legalBtn = document.getElementById('legalBtn');
                const ncoBtn = document.getElementById('ncoBtn');
                const otherBtn = document.getElementById('otherBtn');
                const phisicForm = document.getElementById('phisic');
                const ncoForm = document.getElementById('nco');
                const otherForm = document.getElementById('other');
                const extraOptions = document.getElementById('extraOptions');
                function hideAllForms() {
                    phisicForm.style.display = 'none';
                    ncoForm.style.display = 'none';
                    otherForm.style.display = 'none';
                }
                phisicBtn.addEventListener('click', () => {
                    hideAllForms();
                    extraOptions.style.display = 'none';
                    phisicForm.style.display = 'block';
                });
                legalBtn.addEventListener('click', () => {
                    hideAllForms();
                    extraOptions.style.display = 'block';
                });
                ncoBtn.addEventListener('click', () => {
                    hideAllForms();
                    ncoForm.style.display = 'block';
                });
                otherBtn.addEventListener('click', () => {
                    hideAllForms();
                    otherForm.style.display = 'block';
                });
            </script>
        </div>
    </div>
</div>
</body>
</html>
