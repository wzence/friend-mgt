<?php
    //Add friend connections
    //addFriendConn('andy@example.com', 'john@example.com');
    //addFriendConn('andy@example.com', 'sandy@example.com');
    //addFriendConn('andy@example.com', 'alvin@example.com');
    //addFriendConn('andy@example.com', 'victor@example.com');
    //addFriendConn('andy@example.com', 'brandon@example.com');
    //addFriendConn('john@example.com', 'bill@example.com');
    //addFriendConn('john@example.com', 'alvin@example.com');
    //addFriendConn('john@example.com', 'victor@example.com');
    //addFriendConn('john@example.com', 'jonathan@example.com');
    
    //getFriendList('andy@example.com');
    //getFriendList('nouser@example.com');
    //getFriendList('brandon@example.com');
    
    //getCommonFriendList('andy@example.com', 'john@example.com');
    
    //subscribeToUpdate('andy@example.com', 'john@example.com');
    //subscribeToUpdate('bill@example.com', 'john@example.com');
    
    //blockTarget('andy@example.com', 'john@example.com');
    
    //getUpdatesList('john@example.com', "Hello World! victor@example.com and jonathan@example.com");
    
    function addFriendConn($emailAddr1, $emailAddr2) {
        $friend_conn = array();
        array_push($friend_conn, $emailAddr1);
        array_push($friend_conn, $emailAddr2);
        
        $relationship = new ArrayObject();
        $relationship['friends'] = $friend_conn;
        
        $url = 'http://localhost:8888/friend-mgt/api/create_friend_conn.php';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($relationship));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        //Execute the request
        $response = curl_exec($ch);
        echo "Adding friend connection for " . $emailAddr1 . " and " . $emailAddr2 . ": " . $response . "<br>";
    }
    
    function getFriendList($emailAddr) {
        $userEmail = new ArrayObject();
        $userEmail['email'] = $emailAddr;
        
        $url = 'http://localhost:8888/friend-mgt/api/get_friends_list.php';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userEmail));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        //Execute the request
        $response = curl_exec($ch);
        echo "Get friends list for " . $emailAddr . ": " . $response . "<br>";
    }
    
    function getCommonFriendList($emailAddr1, $emailAddr2) {
        $friend_conn = array();
        array_push($friend_conn, $emailAddr1);
        array_push($friend_conn, $emailAddr2);
        
        $friendsArray = new ArrayObject();
        $friendsArray['friends'] = $friend_conn;
        
        $url = 'http://localhost:8888/friend-mgt/api/get_common_friends.php';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($friendsArray));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        //Execute the request
        $response = curl_exec($ch);
        echo "Get common friends list for " . $emailAddr1 . " and " . $emailAddr2 . ": " . $response . "<br>";
    }
    
    function subscribeToUpdate($emailAddr1, $emailAddr2) {
        $subscribe_update = new ArrayObject();
        $subscribe_update['requestor'] = $emailAddr1;
        $subscribe_update['target'] = $emailAddr2;
        
        $url = 'http://localhost:8888/friend-mgt/api/subscribe_update.php';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($subscribe_update));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        //Execute the request
        $response = curl_exec($ch);
        echo "Subscribing to update from " . $emailAddr2 . " from requestor " . $emailAddr1 . ": " . $response . "<br>";
    }
    
    function blockTarget($emailAddr1, $emailAddr2) {
        $block_target = new ArrayObject();
        $block_target['requestor'] = $emailAddr1;
        $block_target['target'] = $emailAddr2;
        
        $url = 'http://localhost:8888/friend-mgt/api/block_target.php';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($block_target));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        //Execute the request
        $response = curl_exec($ch);
        echo "Blocking target " . $emailAddr2 . " from requestor " . $emailAddr1 . ": " . $response . "<br>";
    }
    
    function getUpdatesList($sender, $text) {
        $params = new ArrayObject();
        $params['sender'] = $sender;
        $params['text'] = $text;
        
        $url = 'http://localhost:8888/friend-mgt/api/get_updates_list.php';
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        
        //Execute the request
        $response = curl_exec($ch);
        echo "Get email addresses that can receive updates from " . $sender . ": " . $response . "<br>";
    }
?>
