<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
class DB_Functions_Authorization {

    private $db;
    private $mysqli;

    //put your code here
    // constructor
    function __construct() {
        require_once 'DB_Connect.php';
        // connecting to database
        $this->db = new DB_Connect();
        $this->mysqli = $this->db->connect();
    }

    // destructor
    function __destruct() {
        
    }
    
    public function isAuthorizationHeaderSet($headers) {
        foreach (apache_request_headers() as $name => $value) {
            if($name == "Authorization") {
                return TRUE;
            } 
        }
        return FALSE;
    }
    
    public function getAuthorizedPharmacyUser($headers) {
        
        $isSessionIdProvided = false;
        
        foreach (apache_request_headers() as $name => $value) {
            if($name == "Authorization") {
            
            $result = sscanf($value, "Basic %s");
            
            $sessionId = $result[0];
            $isSessionIdProvided = true;
        } 
        }
        
        if($isSessionIdProvided == false)
        {
            return false;
        }
        
        $result = mysqli_query($this->mysqli, "select * from pharmacy_users_sessions where pharmacy_users_session_id = '$sessionId';") or die(mysqli_error($this->mysqli));
            // check for result 
        $no_of_rows = mysqli_num_rows($result);
        if ($no_of_rows > 0) {
            $result = mysqli_fetch_array($result);
            
            $returnValue["pharmacy_user_id"] = $result["pharmacy_user_id"];
            $returnValue["pharmacy_profile_id"] = $result["pharmacy_profile_id"];
            
            return $returnValue;
        } else {
        // user not found
            return false;
        }
    }
        
}

?>
