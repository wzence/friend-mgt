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
if(!isset($decodedPostData['requestor']) || !isset($decodedPostData['target'])){
    throw new Exception('Received content contained invalid JSON!');
}
    
if(isset($decodedPostData['requestor']) && isset($decodedPostData['target'])) {
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
    
    $requesterId = "";
    $targetId = "";
    
    //Get id of requester
    $sqlStr = "SELECT user_id ";
    $sqlStr = $sqlStr . "FROM user ";
    $sqlStr = $sqlStr . "WHERE email_address = '" . $decodedPostData['requestor'] . "'";
    $results = $mysqli->query($sqlStr);
    
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        $requesterId = $r['user_id'];
    }
    if($requesterId == "") {
        $returnStatus['success'] = false;
        $returnStatus['error_code'] = 2;
        $returnStatus['error_message'] = "Requestor email address " . $decodedPostData['requestor'] . " not found";
        echo json_encode($returnStatus);
        return;
    }
    
    //Get id of target
    $sqlStr = "SELECT user_id ";
    $sqlStr = $sqlStr . "FROM user ";
    $sqlStr = $sqlStr . "WHERE email_address = '" . $decodedPostData['target'] . "'";
    $results = $mysqli->query($sqlStr);
    
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        $targetId = $r['user_id'];
    }
    if($targetId == "") {
        $returnStatus['success'] = false;
        $returnStatus['error_code'] = 3;
        $returnStatus['error_message'] = "Target email address " . $decodedPostData['friends'] . " not found";
        echo json_encode($returnStatus);
        return;
    }
    
    //Check if requester already blocked target
    $sqlStr = "SELECT * ";
    $sqlStr = $sqlStr . "FROM user_relationship ";
    $sqlStr = $sqlStr . "WHERE user_id = '" . $requesterId . "' AND friend_id = '" . $targetId . "' AND block = 'Y'";
    $results = $mysqli->query($sqlStr);
    
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        $returnStatus['success'] = false;
        $returnStatus['error_code'] = 4;
        $returnStatus['error_message'] = "Requester already blocked by target";
        echo json_encode($returnStatus);
        return;
    }
    
    //Check if friend connection already exists and not blocked
    $sqlStr = "SELECT * ";
    $sqlStr = $sqlStr . "FROM user_relationship ";
    $sqlStr = $sqlStr . "WHERE user_id = '" . $requesterId . "' AND friend_id = '" . $targetId . "' AND block = 'N'";
    $results = $mysqli->query($sqlStr);
    
    $friendConnExists = false;
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        $friendConnExists = true;
    }
    
    if($friendConnExists == true) {
        //Block target
        $sqlStr = "UPDATE user_relationship SET block = 'Y' WHERE user_id = " . $requesterId . " AND friend_id = '" . $targetId . "'";
        
        $results = $mysqli->query($sqlStr);
        
        if($results) {
            $returnStatus['success'] = true;
        }
        else {
            $returnStatus['success'] = false;
            $returnStatus['error_code'] = 5;
            $returnStatus['error_message'] = "Problem blocking target";
        }
    }
    else {
        $sqlStr = "INSERT INTO user_relationship (user_id, friend_id, block) VALUES (";
        $sqlStr = $sqlStr . "'" . $requesterId . "', ";
        $sqlStr = $sqlStr . "'" . $targetId . "', ";
        $sqlStr = $sqlStr . "'Y') ";
        
        $results = $mysqli->query($sqlStr);
        
        if(is_numeric($mysqli->insert_id)) {
            $returnStatus['success'] = true;
        }
        else {
            $returnStatus['success'] = false;
            $returnStatus['error_code'] = 6;
            $returnStatus['error_message'] = "Problem blocking target";
        }
    }

    echo json_encode($returnStatus);
    
    // close connection 
    $mysqli->close();
}
?>
