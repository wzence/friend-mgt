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
if(!isset($decodedPostData['email'])){
    throw new Exception('Received content contained invalid JSON!');
}
    
if(isset($decodedPostData['email'])) {
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
    
    //Get id of user
    $sqlStr = "SELECT user_id ";
    $sqlStr = $sqlStr . "FROM user ";
    $sqlStr = $sqlStr . "WHERE email_address = '" . $decodedPostData['email'] . "'";
    $results = $mysqli->query($sqlStr);
    
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        $userId = $r['user_id'];
    }
    if($userId == "") {
        $returnStatus['success'] = false;
        $returnStatus['error_code'] = 2;
        $returnStatus['error_message'] = "User email address " . $decodedPostData['email'] . " not found";
        echo json_encode($returnStatus);
        return;
    }
    
    //Retrieve friends list
    $sqlStr = "SELECT USR.email_address ";
    $sqlStr = $sqlStr . "FROM user USR, user_relationship REL ";
    $sqlStr = $sqlStr . "WHERE REL.friend_id = USR.user_id AND REL.user_id = '" . $userId . "'";
    $results = $mysqli->query($sqlStr);
    
    $numOfFriends = 0;
    $friendList = array();
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        $friendEmail = $r['email_address'];
        array_push($friendList, $friendEmail);
        $numOfFriends++;
    }
    
    $returnStatus['success'] = true;
    $returnStatus['friends'] = $friendList;
    $returnStatus['count'] = $numOfFriends;

    echo json_encode($returnStatus);
    
    // close connection
    $mysqli->close();
}
?>
