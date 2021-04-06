<?php
header("Access-Control-Allow-Origin: https://safetysoft.org");

try {
    $PDO = new PDO("mysql:host=$ServerName;dbname=$DbName", $UserName, $UserPass);
} catch (PDOException $e) {
    exit($e->getMessage());
}

$imo = filter_input(INPUT_POST, "imo", FILTER_SANITIZE_SPECIAL_CHARS);

$stmt1 = $PDO->prepare("SELECT userid,vessel_type,imo FROM user_details WHERE imo=:imo");
$stmt1->bindParam(':imo', $imo);
$stmt1->execute();

$resArray = array();

while ($row = $stmt1->fetch()) {
    $stmt2 = $PDO->prepare("SELECT user,pass,company,nationality,photo FROM users WHERE code=:code");
    $stmt2->bindParam(':code', $row["userid"]);
    $stmt2->execute();
    $row2 = $stmt2->fetch();
    $resArray[] = array(
        "user_name" => $row2["user"],
        "user_pass" => $row2["pass"],
        "vessel_name" => $row2["company"],
        "vessel_flag" => $row2["nationality"],
        "vessel_photo" => $row2["photo"],
        "vessel_id" => $row["userid"],
        "vessel_imo" => $row["imo"],
        "vessel_type" => $row["vessel_type"]);
}

echo json_encode($resArray);
