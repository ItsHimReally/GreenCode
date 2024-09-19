<?php
$data = $_POST;
session_start();
if (!preg_match("/[A-z0-9-_]/u", $data["access_token"])) {
    echo "Unexpected error";
    exit();
}
$headers = ['Authorization: OAuth '.$data["access_token"]];
$curl = curl_init();
curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
curl_setopt($curl, CURLOPT_URL, 'https://login.yandex.ru/info?format=json');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
$out = curl_exec($curl);
curl_close($curl);

$json = json_decode($out, true);
if (!preg_match("/^[0-9]+$/u", $json["id"])) {
    exit();
}

require_once "../../assets/db.php";
$link = connectDB();

if ($stmt = mysqli_prepare($link, "SELECT * FROM `users` WHERE `yaID` LIKE ?")) {
    mysqli_stmt_bind_param($stmt, "s", $json["id"]);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);
}

if (!isset($user["id"])) {
    if ($stmt = mysqli_prepare($link, "SELECT * FROM `users` WHERE `nick` LIKE ?")) {
        mysqli_stmt_bind_param($stmt, "s", $json["login"]);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        while ($row = mysqli_fetch_assoc($result)) {
            if (isset($row["password"])) {
                echo "<span class='error'>Аккаунт с этим логином уже существует.</span>";
                exit();
            }
        }
        mysqli_stmt_close($stmt);
        if ($stmt = mysqli_prepare($link, "INSERT INTO `users` (`id`, `nick`, `name`, `imageUrl`, `status`, `password`, `regTime`, `links`, `bio`, `sex`, `birthDate`, `xp`, `bonusPoints`, `statusTree`, `co2kg`, `healthApp`, `tgID`, `yaID`) VALUES (NULL, ?, ?, NULL, '1', NULL, NOW(), NULL, NULL, ?, ?, '0', '0', NULL, '0', NULL, NULL, ?);")) {
            if ($json["sex"] == "male") {
                $sex = 0;
            } else {
                $sex = 1;
            }
            if (empty($json["birthday"])) {
                $json["birthday"] = null;
            }
            mysqli_stmt_bind_param($stmt, "sssss", $json["login"], $json["first_name"], $sex, $json["birthday"], $json["id"]);
            mysqli_stmt_execute($stmt);
            $id = mysqli_insert_id($link);
            $_SESSION["id"] = $id;
            $_SESSION["username"] = $json["login"];
            $_SESSION["isAdmin"] = 0;
            mysqli_stmt_close($stmt);
            mysqli_close($link);
            header("location: /profile");
            exit;
        }
    }
} else {
    $_SESSION["id"] = $user["id"];
    $_SESSION["username"] = $user["nick"];
    $_SESSION["isAdmin"] = $user["isAdmin"];
    header("location: /profile");
    exit;
}