<?php
    
    require_once '../include/DB_Functions_Authorization.php';
    $dbAuthorization = new DB_Functions_Authorization();  
    
    $response = array("success" => false, "error" => false);
    require_once '../helpers/ResponseHelper.php';
    $responseHelper = new ResponseHelper();
    
    require '../composer/vendor/autoload.php';
    $logger = new Katzgrau\KLogger\Logger(__DIR__.'/logs');
    
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
    
    require_once '../include/pharmacy/DB_Functions_Pharmacy.php';
    $dbPharmacy = new DB_Functions_Pharmacy();
    
    $pharmacyProfileId = $authorizationResult["pharmacy_profile_id"];
    $pharmacyUserId = $authorizationResult["pharmacy_user_id"];
    $requestUri = $_SERVER['REQUEST_URI'];
    
    $requestJson = file_get_contents('php://input');
    $requestContent = \json_decode($requestJson);

    if(is_null($requestContent)) {
        $logger->info("EmptyRequestContent, pharmacy_user_id: '$pharmacyUserId', pharmacy_profile_id: '$pharmacyProfileId'");
        http_response_code(400);
        $responseHelper->RespondAndExitWithError($response, "EmptyRequestContent", "The request content is either null or invalid");
    }

    if(!property_exists($requestContent, "orderNo") || !property_exists($requestContent, "isValid")
            || is_null($requestContent->orderNo) || is_null($requestContent->isValid)) {
        $logger->info("InvalidRequest, pharmacy_user_id: '$pharmacyUserId', pharmacy_profile_id: '$pharmacyProfileId', requestContent: $requestJson");
        http_response_code(400);
        $responseHelper->RespondAndExitWithError($response, "InvalidRequest","Atleast one of the following inputs is null or not defined. orderNo, isValid");
    }

    $orderNo = $requestContent->orderNo;
    $isValid = $requestContent->isValid;
    
    if($isValid == TRUE) {
        
        if(!property_exists($requestContent, "cost") || is_null($requestContent->cost)) {
            $logger->info("CostNotDefined, pharmacy_user_id: '$pharmacyUserId', pharmacy_profile_id: '$pharmacyProfileId', requestContent: $requestJson");
            http_response_code(400);
            $responseHelper->RespondAndExitWithError($response, "CostNotDefined","cost is not correctly defined");
        }
        $cost = $requestContent->cost;
        
        $result = $dbPharmacy->updatePrescriptionAsValid($pharmacyProfileId, $orderNo, $cost);
        
        if($result == FALSE) {
            $logger->info("UnableToUpdatePrescriptionAsValid, pharmacy_user_id: '$pharmacyUserId', pharmacy_profile_id: '$pharmacyProfileId', requestContent: $requestJson");
            http_response_code(500);
            $responseHelper->RespondAndExitWithError($response, "UnableToUpdatePrescriptionAsValid","Unable to update prescription as valid. Please contact support or try again later.");
        }
        
        $response["success"] = TRUE;
        echo json_encode($response);
        
    } elseif ($isValid == FALSE) {
        if(!property_exists($requestContent, "rejectionCode") || is_null($requestContent->rejectionCode || !property_exists($requestContent, "rejectionDetails") || is_null($requestContent->rejectionDetails))) {
            $logger->info("RejectionDetailsNotDefined, pharmacy_user_id: '$pharmacyUserId', pharmacy_profile_id: '$pharmacyProfileId', requestContent: $requestJson");
            http_response_code(400);
            $responseHelper->RespondAndExitWithError($response, "RejectionDetailsNotDefined","rejectionCode or rejectionDetails is not defined.");
        }
        $rejectionCode = $requestContent->rejectionCode;
        $rejectionDetails = $requestContent->rejectionDetails;
        
        $result = $dbPharmacy->updatePrescriptionAsInValid($pharmacyProfileId, $orderNo, $rejectionCode, $rejectionDetails);
        
        if($result == FALSE) {
            $logger->info("UnableToUpdatePrescriptionAsInValid, pharmacy_user_id: '$pharmacyUserId', pharmacy_profile_id: '$pharmacyProfileId', requestContent: $requestJson");
            http_response_code(500);
            $responseHelper->RespondAndExitWithError($response, "UnableToUpdatePrescriptionAsInValid","Unable to update prescription as invalid. Please contact support or try again later.");
        }
        
        $response["success"] = TRUE;
        echo json_encode($response);
        
    } else {
        $logger->info("InvalidRequest, pharmacy_user_id: '$pharmacyUserId', pharmacy_profile_id: '$pharmacyProfileId', requestContent: $requestJson");
        http_response_code(400);
        $responseHelper->RespondAndExitWithError($response, "InvalidRequest","isValid os not correctly defined");
    }
    
?>