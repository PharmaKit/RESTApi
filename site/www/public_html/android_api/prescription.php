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
    require_once 'include/DB_Functions_Prescription.php';
    $dbFunctionsPrescription = new DB_Functions_Prescription();

    // response Array
    $response = array("tag" => $tag, "success" => 0, "error" => 0);

    // check for tag type
    if ($tag == 'get_patient_history') {
        
        $patientId = $_POST['patient_id'];
        $doctorId = $_POST['doctor_id'];
        
        $patientHistoryList = $dbFunctionsPrescription->getPatientHistory($patientId,$doctorId);
        
        if($patientHistoryList != false) {
            
            $response["success"] = 1;
            $response["patient_history"] = $patientHistoryList;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in retrieving patient history.";
            echo json_encode($response);
        }
         
    } else if ($tag == 'check_unsaved_prescription') {
        
        $patientId = $_POST['patient_id'];
        $doctorId = $_POST['doctor_id'];
        
        $unsavedPrescription = $dbFunctionsPrescription->checkUnsavedPrescription($patientId,$doctorId);
        
        if($unsavedPrescription != false) {
            
            $response["success"] = 1;
            $response["unsaved_prescription"] = $unsavedPrescription;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in retrieving unsaved prescription.";
            echo json_encode($response);
        }
        
    } else if ($tag == 'create_new_prescription') {
        $patientId = $_POST['patient_id'];
        $doctorId = $_POST['doctor_id'];
        $personId = $_POST['person_id'];
        
        $newUnsavedPrescription = $dbFunctionsPrescription->createNewPrescription($patientId,$personId, $doctorId);
        
        if($newUnsavedPrescription != false) {
            
            $response["success"] = 1;
            $response["new_prescription"] = $newUnsavedPrescription;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in creating new prescription.";
            echo json_encode($response);
        }
    } else if ($tag == 'save_prescription') {
        $historyId = $_POST['history_id'];
        
        $saved = $dbFunctionsPrescription->savePrescriptoin($historyId);
        
        if($saved != false) {
            
            $response["success"] = 1;
            $response["saved"] = "true";
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in saving prescription.";
            echo json_encode($response);
        }
        
    } else if ($tag == 'get_symptoms_list') {
        $historyId = $_POST["history_id"];
        $symptoms = $dbFunctionsPrescription->getSymptoms($historyId);
        
        if($symptoms!= false) {
            $response["success"] = 1;
            $response["symptoms"] = $symptoms;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in getting symptoms.";
            echo json_encode($response);
        }
    } else if ($tag == 'save_symptom') {
        $historyId = $_POST["history_id"];
        $symptom = $_POST["symptom"];
        $symptomId = $dbFunctionsPrescription->saveSymptom($historyId,$symptom);
        
        if($symptomId!= false) {
            $response["success"] = 1;
            $response["symptom_id"] = $symptomId;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in saving symptom.";
            echo json_encode($response);
        }
    } else if ($tag == 'get_symptom_from_id') {
        $symptomId = $_POST["symptom_id"];
        $symptom = $dbFunctionsPrescription->getSymptom($symptomId);
        
        if($symptom!= false) {
            $response["success"] = 1;
            $response["symptom"] = $symptom;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in getting the symptom.";
            echo json_encode($response);
        }
    } else if ($tag == 'get_parameters_list') {
        $historyId = $_POST["history_id"];
        $parameters = $dbFunctionsPrescription->getParameters($historyId);
        
        if($parameters!= false) {
            $response["success"] = 1;
            $response["parameters"] = $parameters;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in getting parameters.";
            echo json_encode($response);
        }
    } else if ($tag == 'save_parameter') {
        $historyId = $_POST["history_id"];
        $parameterType = $_POST["parameter_type"];
        $value = $_POST["value"];
        $parameterId = $dbFunctionsPrescription->saveParameter($historyId,$parameterType,$value);
        
        if($parameterId!= false) {
            $response["success"] = 1;
            $response["parameter_id"] = $parameterId;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in saving parameter.";
            echo json_encode($response);
        }
    } else if ($tag == 'get_parameter_from_id') {
        $parameterId = $_POST["parameter_id"];
        $parameter = $dbFunctionsPrescription->getParameter($parameterId);
        
        if($parameter!= false) {
            $response["success"] = 1;
            $response["parameter"] = $parameter;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in getting the parameter.";
            echo json_encode($response);
        }
    } else if ($tag == 'get_disease_diagnosed_list') {
        $historyId = $_POST["history_id"];
        $diseases = $dbFunctionsPrescription->getDiseases($historyId);
        
        if($diseases!= false) {
            $response["success"] = 1;
            $response["diseases"] = $diseases;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in getting symptoms.";
            echo json_encode($response);
        }
    } else if ($tag == 'save_disease_diagnosed') {
        $historyId = $_POST["history_id"];
        $disease = $_POST["disease"];
        $diseaseId = $dbFunctionsPrescription->saveDisease($historyId,$disease);
        
        if($diseaseId!= false) {
            $response["success"] = 1;
            $response["disease_id"] = $diseaseId;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in saving symptom.";
            echo json_encode($response);
        }
    }
    else if ($tag == 'get_disease_diagnosed_from_id') {
        $diseaseId = $_POST["disease_id"];
        $disease = $dbFunctionsPrescription->getDisease($diseaseId);
        
        if($disease!= false) {
            $response["success"] = 1;
            $response["disease"] = $disease;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in getting the symptom.";
            echo json_encode($response);
        }
    }
    else if ($tag == 'get_prescription_medicine_list') {
        $historyId = $_POST["history_id"];
        $prescriptionMedicines = $dbFunctionsPrescription->getPrescriptionMedicineList($historyId);
        
        if($prescriptionMedicines!= false) {
            $response["success"] = 1;
            $response["medicines"] = $prescriptionMedicines;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in getting symptoms.";
            echo json_encode($response);
        }
    } else if ($tag == 'save_prescription_medicine') {
        $historyId = $_POST["history_id"];
        $medicineId = $_POST["medicine_id"];
        $medicineName = $_POST["medicine_name"];
        $morning = $_POST["morning"];
        $afternoong = $_POST["afternoon"];
        $evening= $_POST["evening"];
        $night = $_POST["night"];
        $medicineDataId = $dbFunctionsPrescription->savePrescriptionMedicine($historyId, $medicineId, $medicineName, $morning, $afternoong, $evening, $night);
        
        if($medicineDataId != false) {
            $response["success"] = 1;
            $response["medicine_data_id"] = $medicineDataId;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in saving prescription medicine.";
            echo json_encode($response);
        }
    }
    else if ($tag == 'get_prescription_medicine_from_id') {
        $medicineId = $_POST["medicine_data_id"];
        $medicine = $dbFunctionsPrescription->getPrescriptionMedicine($medicineId);
        
        if($medicine != false) {
            $response["success"] = 1;
            $response["medicine"] = $medicine;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in getting the medicine.";
            echo json_encode($response);
        }
    }
    else if ($tag == 'get_tests_list') {
        $historyId = $_POST["history_id"];
        $tests = $dbFunctionsPrescription->getTests($historyId);
        
        if($tests!= false) {
            $response["success"] = 1;
            $response["tests"] = $tests;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in getting tests.";
            echo json_encode($response);
        }
    } else if ($tag == 'save_test') {
        $historyId = $_POST["history_id"];
        $test = $_POST["test"];
        $testId = $dbFunctionsPrescription->saveTest($historyId,$test);
        
        if($testId!= false) {
            $response["success"] = 1;
            $response["test_id"] = $testId;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in saving symptom.";
            echo json_encode($response);
        }
    } else if ($tag == 'get_test_from_id') {
        $testId = $_POST["test_id"];
        $test = $dbFunctionsPrescription->getTest($testId);
        
        if($test!= false) {
            $response["success"] = 1;
            $response["test"] = $test;
            echo json_encode($response);
        }else {
            $response["error"] = 1;
            $response["error_msg"] = "Error in getting the symptom.";
            echo json_encode($response);
        }
    }
    else {
        print "Wrong tag!";
    }
} else {
    echo 'Access Denied!';
}
?>