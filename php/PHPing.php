<?php
require_once "dbconfig.php";

$loc = realpath(dirname(__FILE__) . "/..");
$logLoc = $loc . "\\log\\";

// create log folder if not exist yet
if(!file_exists($logLoc)) {mkdir($logLoc);}

$hourlyMailLog = json_decode(file_get_contents($loc . "\\HourlyMailLog.json"));

define("SEND_TELEGRAM_NOTIFICATION", false); //[MODIFY THIS LINE to send Telegram notification (true)]
define("TELEGRAM_BOT_TOKEN", "bot123456789:ABC_-azQePpN9A4fRPNspn4NUczFtBTz5zT"); //[MODIFY THIS LINE for your Telegram bot token]
define("TELEGRAM_TARGET_USER", ["123456789", "987654321"]); //[MODIFY THIS LINE for all Telegram user id wich will receive notification]
define("PROXY_SERVER", "your_proxy_server"); //[MODIFY THIS LINE if using proxy for curl]
define("PROXY_PORT", "your_proxy_port");  //[MODIFY THIS LINE if using proxy for curl]
define("PROXY_CREDENTIAL", "username:password"); //[MODIFY THIS LINE if using proxy for curl]

define("SEND_EMAIL_NOTIFICATION", false); //[MODIFY THIS LINE to send email notification (true)]
define("EMAIL_FROM", "from.address@email.com"); //[MODIFY THIS LINE for your email sender address]
define("EMAIL_TO", "to.address1@email.com, to.address2@email.com"); //[MODIFY THIS LINE for your email recepients address]


function sendTelegram($message)
{
    if (SEND_TELEGRAM_NOTIFICATION) {
        foreach (TELEGRAM_TARGET_USER as $tgUserId) {
            $message = str_replace("</b>", "*", str_replace("<b>", "*", str_replace("<br/>", "\n", $message)));
            $tgUrl = 'https://api.telegram.org/' . TELEGRAM_BOT_TOKEN . '/sendMessage?chat_id=' . $tgUserId . '&parse_mode=markdown&text=' . urlencode($message);

            $curlSession = curl_init();
            curl_setopt($curlSession, CURLOPT_URL, $tgUrl);
            curl_setopt($curlSession, CURLOPT_TIMEOUT, 5); //error if no connection immidiately after 1 second
            curl_setopt($curlSession, CURLOPT_RETURNTRANSFER, 0);
            // curl_setopt($curlSession, CURLOPT_PROXY, PROXY_SERVER); //[MODIFY THIS LINE! uncomment if using proxy for curl]
            // curl_setopt($curlSession, CURLOPT_PROXYPORT, PROXY_PORT); //[MODIFY THIS LINE! uncomment if using proxy for curl]
            // curl_setopt($curlSession, CURLOPT_PROXYUSERPWD, PROXY_CREDENTIAL); //[MODIFY THIS LINE! uncomment if using proxy for curl]
            curl_exec($curlSession);
            curl_close($curlSession);
        }
    }
}

function sendEmail($subject, $body)
{
    if (SEND_EMAIL_NOTIFICATION) {
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html; charset=UTF-8" . "\r\n";
        $headers .= "from: " . EMAIL_FROM . "\r\n";

        mail(EMAIL_TO, $subject, $body, $headers);
    }
}

function updateStatus($type, $upDevices, $downDevices)
{
    global $conn;

    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html; charset=UTF-8" . "\r\n";
    $headers .= "from: eko.iswinarso@detmoldgroup.com" . "\r\n";
    $to = "eko.iswinarso@detmoldgroup.com";

    if ($type == "hour") {
        //TODO: hourly mail procedure for hourly down device
        //mail($to, "Device Still Down!", $body, $headers);
    } else if ($type == "minute") {
        $updateIpStatusValue = "";
        $updateIpLastValue = "";
        $updateIpWhereId = "";

        if (count($upDevices) > 0) {
            $subject = "PHPing Notification: Device Up";

            $tgMessage = "";
            $body = "Great!<br/><br/><br/>";
            $body .= count($upDevices) . " devices already UP:<br/>";
            foreach ($upDevices as $upDevice) {
                $updateIpStatusValue .= " WHEN ip.id = " . $upDevice["id"] . " THEN " . 1;
                $updateIpLastValue .= " WHEN ip.id = " . $upDevice["id"] . " THEN '" . $upDevice["time"] . "'";
                $updateIpWhereId .= $upDevice["id"] . ", ";

                $tgMessage .= "<b>" . $upDevice["name"] . "</b> (" . $upDevice["number"] . ") - UP!<br/>";
                $body .= "<b>" . $upDevice["name"] . "</b> (" . $upDevice["number"] . ")<br/>";
            }
            $body .= "<br/>Thank you...";

            sendTelegram($tgMessage);
            sendEmail($subject, $body);
        }

        if (count($downDevices) > 0) {
            $subject = "PHPing Notification: Device Down!";

            $tgMessage = "";
            $body = "Alert!!!<br/><br/><br/>";
            $body .= count($downDevices) . " devices have been DOWN:<br/>";
            foreach ($downDevices as $downDevice) {
                $updateIpStatusValue .= " WHEN ip.id = " . $downDevice["id"] . " THEN " . 0;
                $updateIpLastValue .= " WHEN ip.id = " . $downDevice["id"] . " THEN '" . $downDevice["time"] . "'";
                $updateIpWhereId .= $downDevice["id"] . ", ";

                $tgMessage .= "<b>" . $downDevice["name"] . "</b> (" . $downDevice["number"] . ") - DOWN!<br/>";
                $body .= "<b>" . $downDevice["name"] . "</b> (" . $downDevice["number"] . ")<br/>";
            }
            $body .= "<br/>Please check!";

            sendTelegram($tgMessage);
            sendEmail($subject, $body);
        }

        if (count($upDevices) > 0 || count($downDevices) > 0) {
            $conn->query("
                UPDATE
                    ip
                SET
                    ip.status = CASE " . $updateIpStatusValue . " END,
                    ip.lastupdate = CASE " . $updateIpLastValue . " END
                WHERE ip.id IN (" . chop($updateIpWhereId, ", ") . ")"
            );
        }
    }
}

//remove the "." and ".." as directory folder root target and reorgnize it
$logFiles = array_values(array_diff(scandir($logLoc), array(".", "..")));

$insertLogQuery = "INSERT INTO log (ip_id, time, result) VALUES ";
$updateLogTimeValue = "";
$updateLogResultValue = "";
$updateLogWhereId = "";
$upDevices = array();
$downDevices = array();

foreach ($logFiles as $logFile) {
    $locFile = $logLoc . $logFile;
    $logContent = file_get_contents($locFile);
    $logPart = explode("_", $logFile);
    $logIpId = $logPart[0];
    $logNumber = $logPart[1];
    $logName = $logPart[2];
    $logPrevResult = $logPart[3];
    $logTime = $logPart[4] . " " . $logPart[5];
    $logPriority = chop($logPart[6], ".txt");
    $device = ["id" => $logIpId, "number" => $logNumber, "name" => $logName, "time" => $logTime];

    $result = (!stripos($logContent, "Request timed out") && !stripos($logContent, "Destination host unreachable")) ? 1 : 0;

    $insertLogValue = "('" . $logIpId . "', '" . $logTime . "', " . $result . "), ";

    $logQuery = $conn->query("
        SELECT
            log.id,
            log.result
        FROM
            log
        WHERE
            log.ip_id = '" . $logIpId . "'
        ORDER BY
            log.time DESC
        LIMIT " . $logPriority
    );

    $lastLog = array();
    $oldestLogId = 0;
    $counter = 1;
    while ($row = $logQuery->fetch_assoc()) {
        $oldestLogId = ($counter == $logPriority) ? $row["id"] : $oldestLogId;
        $lastLog[] = $row["result"];
        $counter++;
    }

    if ($result == 0 && !in_array("1", $lastLog) && $logPrevResult == "1") { //check if ip become down for long time and previous status is up
        $downDevices[] = $device;
    } else if ($result == 1 && !in_array("0", $lastLog) && $logPrevResult == "0") { //check if ip become up for long time and previous status is down
        $upDevices[] = $device;
    }

    if (count($lastLog) == $logPriority) { //update oldest log with newest result
        $updateLogTimeValue .= " WHEN log.id = " . $oldestLogId . " THEN '" . $logTime . "'";
        $updateLogResultValue .= " WHEN log.id = " . $oldestLogId . " THEN " . $result;
        $updateLogWhereId .= $oldestLogId . ", ";
    } else {
        $insertLogQuery .= $insertLogValue;
    }

    //delete the log file
    unlink($locFile);
}

updateStatus("minute", $upDevices, $downDevices);

//insert new log
$conn->query(chop($insertLogQuery, ", "));

//update oldest log
$conn->query("
    UPDATE
        log
    SET
        log.time = CASE " . $updateLogTimeValue . " END,
        log.result = CASE " . $updateLogResultValue . " END
    WHERE log.id IN (" . chop($updateLogWhereId, ", ") . ")"
);

$ipQuery = $conn->query("
    SELECT
        ip.id,
        ip.number,
        ip.name,
        ip.status,
        ip.priority
    FROM
        ip
    WHERE
        ip.priority <> 0"
);

//ping each ip and put the result into each log file
while ($row = $ipQuery->fetch_assoc()) {
    $pingCommand = "cmd /C ping " . $row["number"] . " -n 1 -l 1 > " . $loc . "\\log\\" . $row["id"] . "_" . $row["number"] . "_" . $row["name"] . "_" . $row["status"] . "_" . date("Y-m-d_H-i-s") . "_" . $row["priority"] . ".txt";
    $WshShell = new COM("WScript.Shell");
    $oExec = $WshShell->Run($pingCommand, 0, false);
}
