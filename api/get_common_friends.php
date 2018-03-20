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
    
    //Retrieve friends list of $userId
    $sqlStr = "SELECT USR.email_address ";
    $sqlStr = $sqlStr . "FROM user USR, user_relationship REL ";
    $sqlStr = $sqlStr . "WHERE REL.friend_id = USR.user_id AND REL.user_id = '" . $userId . "'";
    $results = $mysqli->query($sqlStr);
    
    $friendList1 = array();
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        $friendEmail = $r['email_address'];
        array_push($friendList1, $friendEmail);
    }
    
    //Retrieve friends list of $friendId
    $sqlStr = "SELECT USR.email_address ";
    $sqlStr = $sqlStr . "FROM user USR, user_relationship REL ";
    $sqlStr = $sqlStr . "WHERE REL.friend_id = USR.user_id AND REL.user_id = '" . $friendId . "'";
    $results = $mysqli->query($sqlStr);
    
    $friendList2 = array();
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        $friendEmail = $r['email_address'];
        array_push($friendList2, $friendEmail);
    }
    
    $numOfCommonFriends = 0;
    $commonFriendsList = array();
    foreach ($friendList1 as $value1) {
        foreach ($friendList2 as $value2) {
            if($value1 == $value2) {
                array_push($commonFriendsList, $value2);
                $numOfCommonFriends++;
            }
        }
    }
    
    $returnStatus['success'] = true;
    $returnStatus['friends'] = $commonFriendsList;
    $returnStatus['count'] = $numOfCommonFriends;

    echo json_encode($returnStatus);
    
    // close connection
    $mysqli->close();
}
?>
