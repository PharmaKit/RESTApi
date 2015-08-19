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
    require_once 'include/DB_Functions_Patient.php';
    $db = new DB_Functions_Patient();

    // response Array
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // check for tag type
    if ($tag == 'login') {
        // Request type is check Login
        $email = $_POST['email'];
        $password = $_POST['password'];
        // TODO put doctor login here.
        //$password = $_POST['password'];
        // check for user
        $user = $db->getPatientByEmail($email);
        if ($user != false) {
            // user found
            // echo json with success = 1
            $response["success"] = 1;
            //$response["uid"] = $user["unique_id"];
            $response["user"]["name"] = $user["name"];
            $response["user"]["email"] = $user["email"];
            //$response["user"]["created_at"] = $user["created_at"];
            //$response["user"]["updated_at"] = $user["updated_at"];
            $response["user"]["person_id"] = $user["person_id"];
            $response["user"]["patient_id"] = $user["patient_id"];
            echo json_encode($response);
        } else {
            // user not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["error_msg"] = "Incorrect email!";
            echo json_encode($response);
        }
    } else if ($tag == 'register') {
        // Request type is Register new user
        $name = $_POST['name'];
        $email = $_POST['email'];
        $password = $_POST['password'];
        $address = $_POST['address'];
        $telephone = $_POST['telephone'];

        // check if user is already existed
        if ($db->isPatientExisted($email)) {
            // user is already existed - error response
            $response["error"] = 2;
            $response["error_msg"] = "Patient already existed";
            echo json_encode($response);
        } else {
            // store user
            $user = $db->storePatient($name, $email, $password, $address, $telephone);
            if ($user) {
                // user stored successfully
                $response["success"] = 1;
                $response["patient"]["patient_id"] = $user["patient_id"];
                $response["patient"]["person_id"] = $user["person_id"];
                $response["patient"]["name"] = $user["name"];
                $response["patient"]["email"] = $user["email"];
                echo json_encode($response);
            } else {
                // user failed to store
                $response["error"] = 1;
                $response["error_msg"] = "Error occured in Registartion";
                echo json_encode($response);
            }
        }
    } else {
        echo "Invalid Request";
    }
} else {
    echo "Access Denied";
}
?>