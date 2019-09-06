<?php
require_once "dbconfig.php";

$param = new stdClass;
(isset($_POST["param"])) ? $param = json_decode($_POST["param"]) : $param->type = "";

if ($param->type == "insert") {
    $insertQuery = "INSERT INTO ip (";
    $insertQueryColumn = "";
    $insertQueryValue = "";
    $first = true;
    foreach ($param->data as $data) {
        $insertQueryColumn .= ($first) ? "" : ", ";
        $insertQueryValue .= ($first) ? "" : ", ";
        $insertQueryColumn .= $data->column;
        $insertQueryValue .= "'" . $data->value . "'";
        $first = false;
    }
    $insertQuery .= $insertQueryColumn . ") VALUES (" . $insertQueryValue . ")";

    $conn->query($insertQuery);
    echo $conn->error;
} else if ($param->type == "update") {
    $updateQuery = "UPDATE ip SET ";
    $first = true;
    foreach ($param->data as $data) {
        $updateQuery .= ($first) ? "" : ", ";
        $updateQuery .= $data->column . " = '" . $data->value . "' ";
        $first = false;
    }
    $updateQuery .= "WHERE ip.id = '" . $param->id . "'";

    $conn->query($updateQuery);
    echo $conn->error;
} else if ($param->type == "delete") {
    $conn->query("DELETE FROM ip WHERE ip.id = '" . $param->id ."'");
    echo $conn->error;
} else if ($param->type == "checkExist") {
    $existQuery = "SELECT 1 FROM " . $param->table . " WHERE " . $param->table . "." . $param->column . " = '" . $param->value . "'";

    if ($conn->query($existQuery)->num_rows) {
        echo "exist";
    }
} else {
    //redirect to main.html if type is invalid
    header("location: http://" . $_SERVER["HTTP_HOST"] . "/phping");
}
