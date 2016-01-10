<?php
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
    require_once '../include/DB_Functions_UpdatePassword.php';
    $db = new DB_Functions_UpdatePassword();
 
    // response Array
    $response = array("tag" => $tag, "success" => 0, "error" => 0);
 
    // check for tag type
    if ($tag == 'generatecode') {
        // Request type is check Login
        $email = $_POST['email'];
 
        // check for user
        $resetCode = $db->generatePasswordResetCode($email);
        if ($resetCode != false) {
            // user found
            // echo json with success = 1
            $response["success"] = 1;
            $response["email"] = $email;
            
            //This is the place where we will send the email to the user registered email address.
            
            echo json_encode($response);
        } else {
            // user not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["error_msg"] = "Incorrect email or the email address is not registered!";
            echo json_encode($response);
        }
    } else if ($tag == 'validatecode') {
        // Request type is check Login
        $email = $_POST['email'];
        $resetCode = $_POST['reset_code'];
 
        // check for user
        $resetCode = $db->validatePasswordResetCode($email,$resetCode);
        if ($resetCode != false) {
            // user found
            // echo json with success = 1
            $response["success"] = 1;
            $response["email"] = $email;
            
            //This is the place where we will send the email to the user registered email address.
            
            echo json_encode($response);
        } else {
            // user not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["error_msg"] = "Incorrect password reset code!";
            echo json_encode($response);
        }
    } else if ($tag == 'resetpassword') {
        // Request type is check Login
        $email = $_POST['email'];
        $resetCode = $_POST['reset_code'];
        $newPassowrd = $_POST['new_password'];
 
        // check for user
        $updated = $db->resetPassowrd($email,$resetCode, $newPassowrd);
        if ($updated != false) {
            // user found
            // echo json with success = 1
            $response["success"] = 1;
            $response["email"] = $email;
            
            //This is the place where we will send the email to the user registered email address.
            
            echo json_encode($response);
        } else {
            // user not found
            // echo json with error = 1
            $response["error"] = 1;
            $response["error_msg"] = "Error occurred while reseting password. Please try again!";
            echo json_encode($response);
        }
    } else {
        echo "Invalid Request";
    }
} else {
    echo "Access Denied";
}
?>