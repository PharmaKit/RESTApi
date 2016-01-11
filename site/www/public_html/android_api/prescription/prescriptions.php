<?php

$personId = $_GET['id'];

$response = array("tag" => "getprescriptions", "success" => 0, "error" => 0);

if($personId == null)
{
    http_response_code(404);
    $response["error"] = 1;
            $response["error_msg"] = "Unable to find prescriptions";
            echo json_encode($response);
}
else
{
    require_once '../include/DB_Functions_Prescription.php';
    $dbFunctionsPrescription = new DB_Functions_Prescription();
    
    $prescriptions = $dbFunctionsPrescription->getPrescriptionsByPersonId($personId);
    
    if($prescriptions != FALSE) {
            $response["success"] = 1;
            $response['person_id'] = $personId;
            $response["prescriptions"] = $prescriptions;
            echo json_encode($response);
    }else {
            http_response_code(404);
            $response["error"] = 1;
            $response["error_msg"] = "Unable to find prescriptions";
            echo json_encode($response);
    }
}
?>