<?php
function getHeader() {
    if (isset($_SESSION["username"])) {
        $p = '<a href="/profile">' . $_SESSION["username"] . '</a>';
    } else {
        $p = '<a href="/profile">Профиль</a>';
    }
    $header = '
    <div class="title">
	    <a href="/" class="icon">
            <img id="mosicon" height="20" src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRzMyrEbe-3sr-lO6wTRobD03_yg9jHKjpd0nzvPi_88_2m2YSfvZMS8u7tGwWGIhZSoQk&usqp=CAU" />
            EcoTime
        </a>
		<div class="menu">
			<a href="/">Мероприятия</a>
			<a href="/rating">Рейтинг</a>
			' . $p . '
		</div>
	</div>';
    return $header;
}
