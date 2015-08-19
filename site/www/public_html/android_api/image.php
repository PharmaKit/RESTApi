<?php

//error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
/**
 * File to handle all API requests
 * Accepts GET and POST
 * 
 * Each request will be identified by TAG
 * Response will be JSON data

  /**
 * check for POST request 
 */
if (isset($_POST['tag']) && $_POST['tag'] != '') {
    // get tag
    $tag = $_POST['tag'];

    // include db handler
    require_once 'include/DB_Functions.php';
    $db = new DB_Functions();

    // response Array
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // check for tag type
    if ($tag == 'save') {
        // Request type is check Login
        $resourceType = $_POST['resourcetype'];
        $personId = $_POST['personid'];
        $recepientName = $_POST['recepientname'];
        $recepientAddress = $_POST['recepientAddress'];
        $offerType = $_POST['offertype'];
        
        // TODO put doctor login here.
        //$password = $_POST['password'];
        // check for user
        $result = $db->saveImageUploadDetails($resourceType, $personId, $recepientName, $recepientAddress, $offerType);
        if ($result != false) {
            // user found
            // echo json with success = 1
            $response["success"] = 1;
            $response["resourceid"] = $result;
            echo json_encode($response);            
        } else {
            // user not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["error_msg"] = "Failed. Please retry";
            echo json_encode($response);
        }
    } else {
        echo "Invalid Request";
    }
} else {
    echo "Access Denied";
}
?>