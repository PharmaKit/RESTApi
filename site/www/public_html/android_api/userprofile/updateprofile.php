<?php

    $requestJson = file_get_contents('php://input');
    $request = json_decode($requestJson);
    
    require_once '../include/DB_Functions_UpdateProfile.php';
    $dbUpdateProfile = new DB_Functions_UpdateProfile();
    require_once '../include/DB_Functions_Patient.php';
    $dbPatient = new DB_Functions_Patient();
    
    $personId = $request->personId;
    $email = $request->email;
    $firstName = $request->firstName;
    $lastName = $request->lastName;
    $phone = $request->phone;
    $address = $request->address;
    
    $response = array("success" => FALSE, "error" => false);
    
    $isProfileUpdated = $dbUpdateProfile->updateProfile($personId, $email, $firstName, $lastName, $phone, $address);
    
    if($isProfileUpdated) {
        
        $user = $dbPatient->getPatientByEmail($email);
        
        if ($user != false) {
            
            $address = $dbPatient->getAddressByPersonId($user["person_id"]);
            // user found
            // echo json with success = 1
            $response["success"] = true;
            //$response["uid"] = $user["unique_id"];
            $response["user"]["name"] = $user["name"];
            $response["user"]["email"] = $user["email"];
            //$response["user"]["created_at"] = $user["created_at"];
            //$response["user"]["updated_at"] = $user["updated_at"];
            $response["user"]["person_id"] = $user["person_id"];
            $response["user"]["patient_id"] = $user["patient_id"];
            $response["user"]["telephone"] = $user["telephone"];
            $response["address"] = $address;
            
            echo json_encode($response);
        }  
    }
    else {
        $response["error"]=true;
        $response["errorCode"] = "UnableToUpdateProfile";
        $response["errorMessage"] = "An issue occurred while updating the user profile";
        
        echo json_encode($response);
    }
    
?>

