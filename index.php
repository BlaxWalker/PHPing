<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8" />
  <meta name="description" content="PHPing: The Pinger" />
  <meta name="theme-color" content="#0072C6" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="refresh" content="60">

  <link rel="shortcut icon" href="ico_favicon.ico" />
  <link rel="stylesheet" type="text/css" href="./css/style.css" />

  <title>PHPing: The Pinger</title>
</head>
<body>

<?php
require_once "./php/dbconfig.php";

function renderDevice($status, $name, $number, $lastUpdate)
{
    $since = "";

    if ($status != "blind") {
        if ($lastUpdate == "0000-00-00 00:00:00") {
            $since = "
              <span>&nbsp</span>
              <span class='sp_since'>" . $status . " since begining</span>
            ";
        } else {
            $now = new DateTime();
            $last = new DateTime($lastUpdate);
            $diff = $now->diff($last);

            if ($diff->y > 0) {
                $since = "<span class='sp_since'>" . $status . " since " . $diff->y . " years ago</span>";
            } else if ($diff->m > 0) {
                $since = "<span class='sp_since'>" . $status . " since " . $diff->m . " months ago</span>";
            } else if ($diff->d > 0) {
                $since = "<span class='sp_since'>" . $status . " since " . $diff->d . " days ago</span>";
            } else if ($diff->h > 0) {
                $since = "<span class='sp_since'>" . $status . " since " . $diff->h . " hours ago</span>";
            } else if ($diff->i > 0) {
                $since = "<span class='sp_since'>" . $status . " since " . $diff->i . " minutes ago</span>";
            }

            $since .= "<span class='sp_lastupdate'>(" . $lastUpdate . ")</span>";
        }
    } else {
        $since = "
          <span>&nbsp</span>
          <span class='sp_since'>(unmonitored)</span>
        ";
    }

    return "
      <div class='div_device " . $status . "'>
        <span class='sp_name'>" . $name . "</span>
        <span class='sp_name'>" . $number . "</span>" .
        $since .
        "</div>
    ";
}

$ipQuery = $conn->query("
  SELECT
    ip.number,
    ip.name,
    ip.status,
    ip.lastupdate,
    ip.priority
  FROM
    ip
  ORDER BY
    ip.name
  ");

$devices = array();

while ($row = $ipQuery->fetch_assoc()) {
    $device = array(
        "number" => $row["number"],
        "name" => $row["name"],
        "status" => "",
        "lastupdate" => $row["lastupdate"],
        "priority" => $row["priority"],
    );

    if ($row["priority"] == 0) {
        $device["status"] = "blind";
    } else {
        $device["status"] = ($row["status"] == 1) ? "up" : "down";
    }

    $devices[] = $device;
}

$upDevices = array_filter($devices, function ($device) {
    return ($device["status"] == "up");
});

$downDevices = array_filter($devices, function ($device) {
    return ($device["status"] == "down");
});

$blindDevices = array_filter($devices, function ($device) {
    return ($device["status"] == "blind");
});

echo "<div class='div_monitor'><div class='div_deviceGroup'><div class='div_deviceHeader'>";
echo "<a class='a_title'>Summary &#x25BA</a>";
echo "<a class='a_downCount' href='#downDevices'>" . count($downDevices) . " offline &#x25BC</a>";
echo "<a class='a_upCount'>" . count($upDevices) . " online</a>";
echo "<a class='a_blindCount' href='#blindDevices'>" . count($blindDevices) . " unmonitored</a>";
echo "<a id='a_add'><img src='img/img_add.gif' alt='img_add'/></a>";
echo "</div><div class='div_deviceItems' id='downDevices'>";

foreach ($downDevices as $device) {
    echo renderDevice($device["status"], $device["name"], $device["number"], $device["lastupdate"]);
}

echo "</div></div><div class='div_deviceGroup'><div class='div_deviceHeader'>";
echo "<a class='a_title'>All Devices (" . count($devices) . ") &#x25BC</a>";
echo "</div><div class='div_deviceItems'>";

foreach ($devices as $device) {
    echo renderDevice($device["status"], $device["name"], $device["number"], $device["lastupdate"]);
}

echo "</div></div><div class='div_deviceGroup'><div class='div_deviceHeader'>";
echo "<a class='a_title'>Unmonitored Devices (" . count($blindDevices) . ") &#x25BC</a>";
echo "</div><div class='div_deviceItems' id='blindDevices'>";

foreach ($blindDevices as $device) {
    echo renderDevice($device["status"], $device["name"], $device["number"], $device["lastupdate"]);
}

echo "</div></div></div><button class='bt_blue' id='bt_toTop'><img src='img/img_toTop.gif' alt='img_toTop'/></button>";
?>

    <script type="text/javascript" src="./js/jquery.js" defer></script>
    <script type="text/javascript" src="./js/script.js" defer></script>
</body>
