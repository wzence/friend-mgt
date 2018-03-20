<?php
include_once("../config.php");
    
//Make sure that it is a POST request.
if(strcasecmp($_SERVER['REQUEST_METHOD'], 'POST') != 0){
    throw new Exception('Request method must be POST!');
}
    
//Receive the RAW post data.
$content = trim(file_get_contents("php://input"));

//Attempt to decode the incoming RAW post data from JSON.
$decodedPostData = json_decode($content, true);
    
//If json_decode failed, the JSON is invalid.
if(!is_array($decodedPostData['friends'])){
    throw new Exception('Received content contained invalid JSON!');
}
    
if(count($decodedPostData['friends']) == 2) {
    $returnStatus = new ArrayObject();
    
    $mysqli = new mysqli(DB_Host,DB_User,DB_Pwd,DB_Name,DB_Port);
    //Output any connection error
    if ($mysqli->connect_error) {
        $returnStatus['success'] = false;
        $returnStatus['error_code'] = 1;
        $returnStatus['error_message'] = "Problem connecting to connecting to database";
        echo json_encode($returnStatus);
        return;
    }
    
    $userId = "";
    $friendId = "";
    
    //Get id of user
    $sqlStr = "SELECT user_id ";
    $sqlStr = $sqlStr . "FROM user ";
    $sqlStr = $sqlStr . "WHERE email_address = '" . $decodedPostData['friends'][0] . "'";
    $results = $mysqli->query($sqlStr);
    
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        $userId = $r['user_id'];
    }
    if($userId == "") {
        $returnStatus['success'] = false;
        $returnStatus['error_code'] = 2;
        $returnStatus['error_message'] = "User email address " . $decodedPostData['friends'][0] . " not found";
        echo json_encode($returnStatus);
        return;
    }
    
    //Get id of friend
    $sqlStr = "SELECT user_id ";
    $sqlStr = $sqlStr . "FROM user ";
    $sqlStr = $sqlStr . "WHERE email_address = '" . $decodedPostData['friends'][1] . "'";
    $results = $mysqli->query($sqlStr);
    
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        $friendId = $r['user_id'];
    }
    if($friendId == "") {
        $returnStatus['success'] = false;
        $returnStatus['error_code'] = 3;
        $returnStatus['error_message'] = "Friend email address " . $decodedPostData['friends'][1] . " not found";
        echo json_encode($returnStatus);
        return;
    }
    
    //Check if friend connection already exists
    $sqlStr = "SELECT * ";
    $sqlStr = $sqlStr . "FROM user_relationship ";
    $sqlStr = $sqlStr . "WHERE user_id = '" . $userId . "' AND friend_id = '" . $friendId . "' AND block = 'N'";
    $results = $mysqli->query($sqlStr);
    
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        $returnStatus['success'] = false;
        $returnStatus['error_code'] = 4;
        $returnStatus['error_message'] = "Friend connection already exists";
        echo json_encode($returnStatus);
        return;
    }
    
    //Check if friend connection is blocked
    $sqlStr = "SELECT * ";
    $sqlStr = $sqlStr . "FROM user_relationship ";
    $sqlStr = $sqlStr . "WHERE user_id = '" . $userId . "' AND friend_id = '" . $friendId . "' AND block = 'Y'";
    $results = $mysqli->query($sqlStr);
    
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        $returnStatus['success'] = false;
        $returnStatus['error_code'] = 5;
        $returnStatus['error_message'] = "Friend connection is blocked";
        echo json_encode($returnStatus);
        return;
    }
    
    //Create friend connection
    $sqlStr = "INSERT INTO user_relationship (user_id, friend_id, block) VALUES (";
    $sqlStr = $sqlStr . "'" . $userId . "', ";
    $sqlStr = $sqlStr . "'" . $friendId . "', ";
    $sqlStr = $sqlStr . "'N') ";
    
    $results = $mysqli->query($sqlStr);
    
    if(is_numeric($mysqli->insert_id)) {
        $returnStatus['success'] = true;
    }
    else {
        $returnStatus['success'] = false;
        $returnStatus['error_code'] = 6;
        $returnStatus['error_message'] = "Problem creating friend connection";
    }

    echo json_encode($returnStatus);
    
    // close connection 
    $mysqli->close();
}
?>
