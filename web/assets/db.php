<?php
function connectDB() {
    $login = "";
    $pass = "";
    $server = "";
    $db = "";
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    $link = mysqli_connect($server, $login, $pass, $db);
    return $link;
}

function callApi($url) {
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    if ($httpCode != 200) {
        return null;
    }

    return json_decode($response, true);
}

function getCommunityById($link, $id) {
    if ($stmt = mysqli_prepare($link, "SELECT * FROM `communities` WHERE `id` LIKE ?")) {
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $arr = mysqli_fetch_assoc($result);
        return $arr;
    }
    return null;
}

function getEvent($link, $id) {
    if ($stmt = mysqli_prepare($link, "SELECT * FROM `events` WHERE `id` LIKE ?")) {
        mysqli_stmt_bind_param($stmt, "s", $id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $arr = mysqli_fetch_assoc($result);
        return $arr;
    }
    return null;
}


function timeUntil($timestamp) {
    $now = time();
    $diff = $timestamp - $now;
    if ($diff <= 0) {
        return "Время прошло";
    }
    $days = floor($diff / (60 * 60 * 24));
    $hours = floor(($diff % (60 * 60 * 24)) / (60 * 60));
    $minutes = floor(($diff % (60 * 60)) / 60);

    if ($days > 0) {
        return "Через " . getPluralForm($days, "день", "дня", "дней");
    } elseif ($hours > 0) {
        return "Через " . getPluralForm($hours, "час", "часа", "часов");
    } elseif ($minutes > 0) {
        return "Через " . getPluralForm($minutes, "минуту", "минуты", "минут");
    } else {
        return "Менее чем через минуту";
    }
}

function getPluralForm($number, $form1, $form2, $form5) {
    $n = abs($number) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) {
        return $number . " " . $form5;
    }
    if ($n1 > 1 && $n1 < 5) {
        return $number . " " . $form2;
    }
    if ($n1 == 1) {
        return $number . " " . $form1;
    }
    return $number . " " . $form5;
}

function formatTimestamp($timestamp) {
    $months = [
        1 => 'января', 2 => 'февраля', 3 => 'марта', 4 => 'апреля', 5 => 'мая', 6 => 'июня',
        7 => 'июля', 8 => 'августа', 9 => 'сентября', 10 => 'октября', 11 => 'ноября', 12 => 'декабря'
    ];
    $day = date('j', $timestamp);
    $month = date('n', $timestamp);
    $time = date('H:i', $timestamp);
    return $day . ' ' . $months[$month] . ' ' . $time;
}