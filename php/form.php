<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="description" content="PHPing: The Pinger" />
  <meta name="theme-color" content="#0072C6" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link rel="shortcut icon" href="../ico_favicon.ico" />
  <link rel="stylesheet" type="text/css" href="../css/style.css" />

<?php
require_once "dbconfig.php";
$name = (isset($_GET["name"])) ? $_GET["name"] : "Add New Device";

echo "<title>PHPing - " . $name . "</title>";
?>

</head>
<body>

<?php

$ip = array(
    "id" => "",
    "name" => "",
    "number" => "",
    "status" => "",
    "lastupdate" => "",
    "priority" => "",
    "remarks" => "",
);
$statusStyle = "";
$disableDelete = "";

if ($name == "Add New Device") {
    $ip["status"] = "online";
    $statusStyle = "style='background: greenyellow; border: 0.1em solid green'";
    $ip["lastupdate"] = "0000-00-00 00:00:00";
    $disableDelete = "disabled";
} else {
    $ipQuery = $conn->query("
    SELECT
        ip.id,
        ip.name,
        ip.number,
        ip.status,
        ip.lastupdate,
        ip.priority,
        ip.remarks
    FROM
        ip
    WHERE
        ip.name = '" . $name . "'
  ");

    while ($row = $ipQuery->fetch_assoc()) {
        $ip["id"] = $row["id"];
        $ip["name"] = $row["name"];
        $ip["number"] = $row["number"];
        $ip["lastupdate"] = $row["lastupdate"];
        $ip["priority"] = $row["priority"];
        $ip["remarks"] = $row["remarks"];

        if ($row["status"] == 0) {
            $ip["status"] = "offline";
            $statusStyle = "style='background: pink; border: 0.1em solid red'";
        } else {
            $ip["status"] = "online";
            $statusStyle = "style='background: greenyellow; border: 0.1em solid green'";
        }
    }
}

echo "
    <form id='fr_device' novalidate>
        <div id='div_id'>" . $ip["id"] . "</div>
        <h1>" . strtoupper($name) . "</h1>
        <div class='div_field mandatory'>
            <label for='inTex_name'>Hostname / Asset Tag</label>
            <div><input type='text' id='inTex_name' unique='ip_name' initial='" . $ip["name"] . "' value='" . $ip["name"] . "'/></div>
            <div class='div_notif'></div>
        </div>
        <div class='div_field mandatory'>
            <label for='inTex_number'>IP Address</label>
            <div><input type='text' id='inTex_number' unique='ip_number' initial='" . $ip["number"] . "' value='" . $ip["number"] . "'/></div>
            <div class='div_notif'></div>
        </div>
        <div class='div_field bysystem'>
            <label for='inTex_status'>Last Status</label>
            <div><input type='text' id='inTex_status' value='" . $ip["status"] . "' " . $statusStyle . " disabled/></div>
            <div class='div_notif'></div>
        </div>
        <div class='div_field bysystem'>
            <label for='inTex_lastUpdate'>Last Update Time</label>
            <div><input type='text' id='inTex_lastUpdate' value='" . $ip["lastupdate"] . "' disabled/></div>
            <div class='div_notif'></div>
        </div>
        <div class='div_field mandatory'>
            <label for='inNum_priority'>Priority (0 = unmonitored)</label>
            <div><input type='number' id='inNum_priority' min='0' step='1' value='" . $ip["priority"] . "'/></div>
            <div class='div_notif'></div>
        </div>
        <div class='div_field optional'>
            <label for='ta_remarks'>Remarks</label>
            <div><textarea id='ta_remarks'>" . $ip["remarks"] . "</textarea></div>
        </div>
        <div class='div_buttonGroup'>
            <button type='button' class='bt_blue' id='bt_save'>
                <img class='img_spinner off' src='../img/img_spinner.gif' alt='img_spinner'/>
                <img class='img_label on'src='../img/img_save.gif' alt='img_save'/>
                <span class='on'>Save</span>
            </button>
            <button type='button' class='bt_blue' id='bt_delete' " . $disableDelete . ">
                <img class='img_spinner off' src='../img/img_spinner.gif' alt='img_spinner'/>
                <img class='img_label on'src='../img/img_delete.gif' alt='img_delete'/>
                <span class='on'>Delete</span>
            </button>
        </div>
    </form>
";

?>

    <script type="text/javascript" src="../js/jquery.js" defer></script>
    <script type="text/javascript" src="../js/script.js" defer></script>
</body>