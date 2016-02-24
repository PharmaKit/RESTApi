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
    
    $limit = 9999999999;
    $pageNo = 0;
    
    if(isset($_GET['limit'])) {
        $limit = $_GET['limit'];
    }
        if(isset($_GET['pageno'])) {
        $pageNo = $_GET['pageno'];
    }
    
    require_once '../include/pharmacy/DB_Functions_Pharmacy.php';
    $dbPharmacy = new DB_Functions_Pharmacy();
    
    $pharmacyProfileId = $authorizationResult["pharmacy_profile_id"];
    $pharmacyUserId = $authorizationResult["pharmacy_user_id"];
    $requestUri = $_SERVER['REQUEST_URI'];
    
    if(isset($_GET['orderno'])) {
        $orderNo = $_GET['orderno'];
        
        $prescription = $dbPharmacy->getPrescriptionByOrderNo($pharmacyProfileId, $orderNo);
        
        if($prescription == FALSE) {
            $logger->error("prescription not found. pharmacy_user_id: '$pharmacyProfileId', pharmacy_profile_id: '$pharmacyUserId', uri: '$requestUri'");
            http_response_code(404);
            $responseHelper->RespondAndExitWithError($response, "PrescriptionNotFound", "Prescription with orderno = '$orderNo' was not found");
        }
        
        $logger->info("Successfullt got prescrition. pharmacy_user_id: '$pharmacyProfileId', pharmacy_profile_id: '$pharmacyUserId', uri: '$requestUri'");
    
        $response["success"] = TRUE;
        $response["pharmacy_profile_id"] = $authorizationResult["pharmacy_profile_id"];
        $response["prescription"] = $prescription;
        echo json_encode($response);
    }
    else {
        $prescriptionHistory = $dbPharmacy->getAllPrescriptionsByPharmacyProfileId($pharmacyProfileId, $pageNo, $limit);
    
        if($prescriptionHistory == FALSE) {
            $logger->error("Getting prescription history failed. pharmacy_user_id: '$pharmacyProfileId', pharmacy_profile_id: '$pharmacyUserId'");
            http_response_code(500);
            $responseHelper->RespondAndExitWithError($response, "GettingPrescriptionHistoryFailed", "Getting prescription history failed. Please contact support or try again later");
        }
            
        $logger->info("Successfullt got prescrition history. pharmacy_user_id: '$pharmacyProfileId', pharmacy_profile_id: '$pharmacyUserId', uri: '$requestUri'");
    
        $response["success"] = TRUE;
        $response["pharmacy_profile_id"] = $authorizationResult["pharmacy_profile_id"];
        $response["prescriptions"] = $prescriptionHistory;
        echo json_encode($response);
    }    
?>