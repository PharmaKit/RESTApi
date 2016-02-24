<?php

$response = array("success" => false, "error" => false);
require_once '../helpers/ResponseHelper.php';
$responseHelper = new ResponseHelper();

$requestJson = file_get_contents('php://input');
$request = \json_decode($requestJson);

if (is_null($request)) {
    http_response_code(403);
    $responseHelper->RespondAndExitWithError($response, "MalformedRequest", "The request content is either null or invalid");
}

if (!property_exists($request, "email") || !property_exists($request, "password") || is_null($request->email) || is_null($request->password)) {
    http_response_code(403);
    $responseHelper->RespondAndExitWithError($response, "InvalidRequest", "Atleast one of the following inputs is null or not defined. email, password");
}

$email = $request->email;
$password = $request->password;

require_once '../include/pharmacy/DB_Functions_Pharmacy.php';
$dbPharmacy = new DB_Functions_Pharmacy();

//validate user from database

$user = $dbPharmacy->getUserByEmailAndPassword($email, $password);

if (!$user) {
    http_response_code(403);
    $responseHelper->RespondAndExitWithError($response, "InvalidCredentials", "Incorrect username or password");
}

if ($user["is_active"] == 0) {
    http_response_code(403);
    $responseHelper->RespondAndExitWithError($response, "UserNotEnabled", "The user is not enabled. Please contact support");
}

$session = $dbPharmacy->createOrGetSession($user["pharmacy_user_id"], $user["pharmacy_profile_id"]);

if (!$session) {
    http_response_code(500);
    $responseHelper->RespondAndExitWithError($response, "InternalServerError", "Unable to create login session. Please contact support or try again later.");
}

$pharmacy = $dbPharmacy->getPharmacyProfileById($user["pharmacy_profile_id"]);

if (!$pharmacy) {
    http_response_code(500);
    $responseHelper->RespondAndExitWithError($response, "InternalServerError", "Unable to get pharmacy information. Please contact support or try again later.");
}

if ($pharmacy["is_active"] == 0) {
    http_response_code(500);
    $responseHelper->RespondAndExitWithError($response, "PharmacyNotEnabled", "The pharmacy is not enabled. Please contact support.");
}

    $response["success"] = true;
    $response["parmacyUser"]["pharmacy_user_id"] = $user["pharmacy_user_id"];
    $response["parmacyUser"]["first_name"] = $user["first_name"];
    $response["parmacyUser"]["last_name"] = $user["last_name"];
    $response["parmacyUser"]["email_address"] = $user["email_address"];
    $response["parmacyUser"]["is_active"] = $user["is_active"];
    $response["parmacyUser"]["authentication_session_id"] = $session;
    $response["pharmacyProfile"]["pharmacy_profile_id"] = $pharmacy["pharmacy_profile_id"];
    $response["pharmacyProfile"]["pharmacy_name"] = $pharmacy["pharmacy_name"];
    $response["pharmacyProfile"]["pharmacy_email_address"] = $pharmacy["pharmacy_email_address"];
    $response["pharmacyProfile"]["pharmacy_address"] = $pharmacy["pharmacy_address"];
    $response["pharmacyProfile"]["pharmacy_phone_number"] = $pharmacy["pharmacy_phone_number"];
    $response["pharmacyProfile"]["is_active"] = $pharmacy["is_active"];
    
    echo json_encode($response);
?>
