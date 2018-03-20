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
if(!isset($decodedPostData['sender']) || !isset($decodedPostData['text'])){
    throw new Exception('Received content contained invalid JSON!');
}
    
if(isset($decodedPostData['sender']) && isset($decodedPostData['text'])) {
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
    
    $senderId = "";
    
    //Get id of sender
    $sqlStr = "SELECT user_id ";
    $sqlStr = $sqlStr . "FROM user ";
    $sqlStr = $sqlStr . "WHERE email_address = '" . $decodedPostData['sender'] . "'";
    $results = $mysqli->query($sqlStr);
    
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        $senderId = $r['user_id'];
    }
    if($senderId == "") {
        $returnStatus['success'] = false;
        $returnStatus['error_code'] = 2;
        $returnStatus['error_message'] = "Sender email address " . $decodedPostData['email'] . " not found";
        echo json_encode($returnStatus);
        return;
    }
    
    //Retrieve friend connection list that are not blocked
    $sqlStr = "SELECT USR.email_address ";
    $sqlStr = $sqlStr . "FROM user USR, user_relationship REL ";
    $sqlStr = $sqlStr . "WHERE REL.user_id = USR.user_id AND REL.friend_id = '" . $senderId . "' AND block = 'N'";
    $results = $mysqli->query($sqlStr);
    
    $unblockedFriendsList = array();
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        $friendEmail = $r['email_address'];
        array_push($unblockedFriendsList, $friendEmail);
    }
    
    //Retrieve subscriptions to updates from $senderId that are not blocked
    $sqlStr = "SELECT USR.user_id, USR.email_address ";
    $sqlStr = $sqlStr . "FROM user USR, user_subscribe_update SUB ";
    $sqlStr = $sqlStr . "WHERE SUB.user_id = USR.user_id AND subscribed_user_id = '" . $senderId . "'";
    $results = $mysqli->query($sqlStr);
    
    while($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
        //Filter out email address that are blocked from getting updates from $senderId
        $friendEmail = $r['email_address'];
        $friendUserId = $r['user_id'];
        $sqlStr1 = "SELECT block ";
        $sqlStr1 = $sqlStr1 . "FROM user_relationship ";
        $sqlStr1 = $sqlStr1 . "WHERE user_id = '" . $friendUserId . "' AND friend_id = '" . $senderId . "'";
        $results1 = $mysqli->query($sqlStr1);
        
        if($r1 = mysqli_fetch_array($results1, MYSQLI_ASSOC)) {
            if($r1['block'] == 'N') {
                if(!in_array($friendEmail, $unblockedFriendsList)) {
                    array_push($unblockedFriendsList, $friendEmail);
                }
            }
        }
        else {
            if(!in_array($friendEmail, $unblockedFriendsList)) {
                array_push($unblockedFriendsList, $friendEmail);
            }
        }
    }
    
    //Retrieve @mentioned in the update that are not blocked
    $mentionedEmailArray = array();
    $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
    preg_match_all($pattern, $decodedPostData['text'], $mentionedEmailArray);
    
    foreach ($mentionedEmailArray[0] as $mentionedEmail) {
        //Check if mentioned email exists
        $mentionedEmailExists = false;
        $sqlStr = "SELECT * ";
        $sqlStr = $sqlStr . "FROM user ";
        $sqlStr = $sqlStr . "WHERE email_address = '" . $mentionedEmail . "'";
        $results = $mysqli->query($sqlStr);
        if($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
             $mentionedEmailExists = true;
        }
        
        if($mentionedEmailExists == true) {
            //Filter out mentioned emails that are blocked from getting updates from $senderId
            $sqlStr = "SELECT block ";
            $sqlStr = $sqlStr . "FROM user USR, user_relationship REL ";
            $sqlStr = $sqlStr . "WHERE REL.user_id = USR.user_id AND USR.email_address = '" . $mentionedEmail . "' AND REL.friend_id = '" . $senderId . "'";
            $results = $mysqli->query($sqlStr);
            
            if($r = mysqli_fetch_array($results, MYSQLI_ASSOC)) {
                if($r['block'] == 'N') {
                    if(!in_array($mentionedEmail, $unblockedFriendsList)) {
                        array_push($unblockedFriendsList, $mentionedEmail);
                    }
                }
            }
            else {
                if(!in_array($mentionedEmail, $unblockedFriendsList)) {
                    array_push($unblockedFriendsList, $mentionedEmail);
                }
            }
        }
    }
    
    $returnStatus['success'] = true;
    $returnStatus['recipients'] = $unblockedFriendsList;

    echo json_encode($returnStatus);
    
    // close connection
    $mysqli->close();
}
?>
