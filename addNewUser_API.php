<?php

header("Access-Control-Allow-Origin: https://xxxx.com");

$PDO = new PDO("mysql:host=$ServerName;dbname=$DbName", $UserName, $UserPass);

function generateUserId() {
    $symbols = "0123456789qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM";
    $timestamp = date("YmdHis");
    $userid = "user_" . str_shuffle($symbols) . $timestamp;
    while (userIdExists($userid)) {
        $timestamp = date("YmdHis");
        $userid = "user_" . str_shuffle($symbols) . $timestamp;
    }
    return $userid;
}

function userIdExists($userid) {
    global $PDO;
    $stmt = $PDO->prepare("SELECT COUNT(id) AS total FROM users WHERE code=:userid");
    $stmt->bindParam(':userid', $userid);
    $stmt->execute();
    $data = $stmt->fetch();
    if ($data["total"] > 0) {
        return true;
    }
    return false;
}

$request = file_get_contents('php://input');
$input = json_decode($request);


$vessel_name = $input->vessel_name;
$vessel_imo = $input->vessel_imo;
$vessel_type = $input->vessel_type;
$vessel_flag = $input->vessel_flag;
$userid = generateUserId();
$user_name = str_replace(" ", "", $vessel_name);

$passString = '0123456789qwertyuiopasdfghjklzxcvbnm';
$password = '';
for ($i = 0; $i < 7; $i++) {
    $randVal = rand(0, strlen($passString) - 1);
    $password .= $passString[$randVal];
}




//----------------------- CHK IF VSL WITH THIS IMO EXISTS ALREADY
$stmt0 = $PDO->prepare("SELECT COUNT(id) AS total FROM user_details WHERE imo=:imo");
$stmt0->bindParam(':imo', $vessel_imo);
$stmt0->execute();
$data = $stmt0->fetch();
if ($data["total"] > 0) {
    echo "error";
    exit();
}


//----------------------- INSERT NEW USER
$stmt1 = $PDO->prepare("INSERT INTO users"
        . " (user,code,user_type,first_name,company,nick_name,nationality,photo,pass,signature)"
        . " VALUES"
        . " (:user,:code,'Vessel',:first_name,:company,:company,:nationality,:photo,'$password','notSet')");
$photo = "default_company.png";
$stmt1->bindParam(':user', $user_name);
$stmt1->bindParam(':code', $userid);
$stmt1->bindParam(':first_name', $user_name);
$stmt1->bindParam(':company', $user_name);
$stmt1->bindParam(':nationality', $vessel_flag);
$stmt1->bindParam(':photo', $photo);
$stmt1->execute();

$stmt2 = $PDO->prepare("INSERT INTO user_details (userid,imo,mmsi,call_sign,vessel_type,reg_number,classif_society,ro,vessel_reg_group,tonnage,net_tonnage,summer_deadweight,length,breadth_moulded,summer_draft,propulsion_machinery,engine_type,main_engines,main_propulsion,bow_tructers,generator,spee) VALUES (:userid,:imo,:mmsi,:call_sign,:vessel_type,:reg_number,:classif_society,:ro,:vessel_reg_group,:tonnage,:net_tonnage,:summer_deadweight,:length,:breadth_moulded,:summer_draft,:propulsion_machinery,:engine_type,:main_engines,:main_propulsion,:bow_tructers,:generator,:spee)");
$stmt2->bindParam(':userid', $userid);
$stmt2->bindParam(':imo', $vessel_imo);
$stmt2->bindParam(':mmsi', $mmsi);
$stmt2->bindParam(':call_sign', $call_sign);
$stmt2->bindParam(':vessel_type', $vessel_type);
$stmt2->bindParam(':reg_number', $reg_number);
$stmt2->bindParam(':classif_society', $classif_society);
$stmt2->bindParam(':ro', $ro);
$stmt2->bindParam(':vessel_reg_group', $vessel_reg_group);
$stmt2->bindParam(':tonnage', $tonnage);
$stmt2->bindParam(':net_tonnage', $net_tonnage);
$stmt2->bindParam(':summer_deadweight', $summer_deadweight);
$stmt2->bindParam(':length', $length);
$stmt2->bindParam(':breadth_moulded', $breadth_moulded);
$stmt2->bindParam(':summer_draft', $summer_draft);
$stmt2->bindParam(':propulsion_machinery', $propulsion_machinery);
$stmt2->bindParam(':engine_type', $engine_type);
$stmt2->bindParam(':main_engines', $main_engines);
$stmt2->bindParam(':main_propulsion', $main_propulsion);
$stmt2->bindParam(':bow_tructers', $bow_tructers);
$stmt2->bindParam(':generator', $generator);
$stmt2->bindParam(':spee', $spee);
$stmt2->execute();



//----------------------- GET NEW USER FROM DB
$stmt3 = $PDO->prepare("SELECT user,nationality,pass FROM users WHERE code=:userid");
$stmt3->bindParam(':userid', $userid);
$stmt3->execute();
$row = $stmt3->fetch();


$resArray[] = array(
    "vessel_id" => $userid,
    "vessel_name" => $vessel_name,    
    "vessel_flag" => $row["nationality"],
    "user_name" => $row["user"],
    "vessel_password" => $row["pass"],
    "vessel_imo" => $vessel_imo,
    "vessel_type" => $vessel_type
);


echo json_encode($resArray);