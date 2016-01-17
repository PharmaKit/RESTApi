<?php

$response = array("success" => false, "error" => false);
require_once '../helpers/ResponseHelper.php';
$responseHelper = new ResponseHelper();

$requestJson = file_get_contents('php://input');
$request = \json_decode($requestJson);

if(is_null($request)) {
    $responseHelper->RespondAndExitWithError($response, "MalformedRequest", "The request content is either null or invalid");
}

if(!property_exists($request, "email") || !property_exists($request, "currentPassword") || !property_exists($request, "newPassword")
        || is_null($request->email) || is_null($request->currentPassword) || is_null($request->newPassword)) {
    $responseHelper->RespondAndExitWithError($response, "InvalidRequest","Atleast one of the following inputs is null or not defined. email, currentPassword, newPassword");
}

$email = $request->email;
$currentPassword = $request->currentPassword;
$newPassword = $request->newPassword;

require_once '../include/DB_Functions_UpdatePassword.php';
$dbUpdatePassword = new DB_Functions_UpdatePassword();
require_once '../include/DB_Functions_Patient.php';
$dbPatient = new DB_Functions_Patient();

$isValid = $dbPatient->validateUserByEmailAndPassword($email, $currentPassword);

if(!$isValid) {
    $responseHelper->RespondAndExitWithError($response, "InvalidCredentials", "Incorrect username or password");
}

$isUpdated = $dbUpdatePassword->validateAndUpdatePassword($email, $currentPassword, $newPassword);

if($isUpdated) {
    $response["success"] = true;
    echo json_encode($response);
    exit;
}
else {
    $responseHelper->RespondAndExitWithError($response, "UnexpectedError", "Unexpected error occurred while updating password. Please contact support.");
}
        
?>