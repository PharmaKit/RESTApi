<?php

    class ResponseHelper {
        
        public function RespondAndExitWithError($response,$errorCode,$errorMessage) {
            $response["error"] = true;
            $response["errorCode"] = $errorCode;
            $response["errorMessage"] = $errorMessage;
            
            echo json_encode($response);
            exit;
        }
    }

?>
