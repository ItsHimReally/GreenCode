<?php
error_reporting(0);
session_start();
require_once "../assets/db.php";
require_once "../assets/blocks.php";
$link = connectDB();
$stmt = $link->prepare("SELECT registrations.eventID, events.timeStart FROM registrations INNER JOIN events ON registrations.eventID = events.id WHERE registrations.userID = ?");
$stmt->bind_param('i', $_GET["id"]);
$stmt->execute();
$result = $stmt->get_result();
$dateEvents = [];
while ($row = $result->fetch_assoc()) {
    $dateEvents[] = $row['timeStart'];
}
$stmt->close();
$link->close();
$calendarData = [];
foreach ($dateEvents as $eventDate) {
    $date = new DateTime($eventDate);
    $year = $date->format('Y');
    $month = $date->format('m');
    $day = $date->format('d');
    $calendarData[] = "$year-$month-$day";
}
echo json_encode($calendarData);