<?php

require_once '../include/DB_Functions_Authorization.php';
    $dbAuthorization = new DB_Functions_Authorization();  
    
    $response = array("success" => false, "error" => false);
    require_once '../helpers/ResponseHelper.php';
    $responseHelper = new ResponseHelper();
    
    require '../composer/vendor/autoload.php';
    $logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs');
    /*
    $isAuthorizationHeaderSet = $dbAuthorization->isAuthorizationHeaderSet(apache_request_headers());
    
    if($isAuthorizationHeaderSet == FALSE) {
        $logger->info("Authorization header not set.");
        http_response_code(403);
        $responseHelper->RespondAndExitWithError($response, "AuthorizationHeaderNotSet", "Authorization header is not set in the request");
    }
    
    $authorizationResult = $dbAuthorization->getAuthorizedPharmacyUser(apache_request_headers());
    
    if($authorizationResult == FALSE) {
        $logger->info("Authorization failed.");
        http_response_code(403);
        $responseHelper->RespondAndExitWithError($response, "AuthenticationFailed", "SessionId provided is not valid or expired. Please login again.");
    }
    */
    require_once '../include/DB_Functions_Patient.php';
    $dbPatient = new DB_Functions_Patient();
    
    $requestUri = $_SERVER['REQUEST_URI'];
    
    $requestJson = file_get_contents('php://input');
    $requestContent = \json_decode($requestJson);
    
    if(is_null($requestContent)) {
        $logger->info("EmptyRequestContent. requestUri: $requestUri");
        http_response_code(400);
        $responseHelper->RespondAndExitWithError($response, "EmptyRequestContent", "The request content is either null or invalid");
    }
    
    if(!property_exists($requestContent, "token") || is_null($requestContent->token) || !property_exists($requestContent, "userId") || is_null($requestContent->userId)) {
        $logger->info("InvalidRequest. requestUri: $requestUri");
        http_response_code(400);
        $responseHelper->RespondAndExitWithError($response, "InvalidRequest","Atleast one of the following inputs is null or not defined. token, userId");
    }
    
    $result = $dbPatient->addGcmToken($requestContent->userId, $requestContent->token);
    
    if($result == FALSE) {
            $logger->info("UnableToSaveGcmToken, userId: '$requestContent->userId', requestContent: $requestJson");
            http_response_code(500);
            $responseHelper->RespondAndExitWithError($response, "UnableToSaveGcmToken","Unable to save GCM registration token");
        }
        
    $logger->info("Successfully saved Gcm token, userId: '$requestContent->userId', requestContent: $requestJson");
        
    $response["success"] = TRUE;
    echo json_encode($response);

?>
