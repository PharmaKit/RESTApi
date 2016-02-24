<?php

class DB_Functions_Pharmacy {

    private $db;
    private $mysqli;

    //put your code here
    // constructor
    function __construct() {
        require_once '../include/DB_Connect.php';
        // connecting to database
        $this->db = new DB_Connect();
        $this->mysqli = $this->db->connect();
    }

    // destructor
    function __destruct() {
        
    }
    
    /**
     * Get user by email and password
     */
    public function getUserByEmailAndPassword($email, $password) {
        $result = mysqli_query($this->mysqli, "SELECT * FROM pharmacy_users WHERE email_address = '$email'") or die(mysqli_error($this->mysqli));
        // check for result 
        $no_of_rows = mysqli_num_rows($result);
        if ($no_of_rows > 0) {
            $result = mysqli_fetch_array($result);
            $salt = $result['salt'];
            $encrypted_password = $result['encrypted_password'];
            $hash = $this->checkhashSSHA($salt, $password);
            // check for password equality
            if ($encrypted_password == $hash) {
                // user authentication details are correct
                return $result;
            }
            else
            {
                return false;
            }
        } else {
            // user not found
            return false;
        }
    }
    
    public function createOrGetSession($pharmacyUserId,$pharmacyProfileId) {
        
        $tempUsername = $this->randomString(20);
        $tempPassword = $this->randomString(20);
        
        $sessionId = base64_encode("$tempUsername:$tempPassword");
        
        $result = mysqli_query($this->mysqli, "call create_pharmacy_user_session($pharmacyUserId,$pharmacyProfileId,'$sessionId');") or die(mysqli_error($this->mysqli));
        
        if($result)
        {
            return $sessionId;
        }
        return FALSE;
    }
    
    public function randomString($length) {       
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ~!@#$%^&*()_+`"';
        $randstring = '';
        for ($i = 0; $i < $length; $i++) {
            $char = $characters[rand(0, strlen($characters) - 1)];
            $randstring = "$randstring$char";
        }
        return $randstring;
    }
    
    /**
     * Get user by email and password
     */
    public function getPharmacyProfileById($pharmacyProfileId) {
        $result = mysqli_query($this->mysqli, "SELECT * FROM pharmacy_profile where pharmacy_profile_id = $pharmacyProfileId;") or die(mysqli_error($this->mysqli));
        // check for result 
        $no_of_rows = mysqli_num_rows($result);
        if ($no_of_rows > 0) {
            $result = mysqli_fetch_array($result);
            return $result;
        } else {
            // user not found
            return false;
        }
    }
    
    public function getAllPrescriptionsByPharmacyProfileId($pharmacyProfileId, $pageNo, $limit)
    {
        $offset = $pageNo * $limit;
        
        $result = mysqli_query($this->mysqli, "select * from image_uploads where pharmacy_profile_id = $pharmacyProfileId order by created_date desc limit $offset,$limit;");
        $rows = array();
        while ($r = mysqli_fetch_assoc($result)) {
            $rows[] = $r;
        }
        if(sizeof($rows) > 0){
            return $rows;
        } else {
            return FALSE;
        }
    }
    
    public function getPrescriptionByOrderNo($pharmacyProfileId, $orderNo)
    {     
        $result = mysqli_query($this->mysqli, "select * from image_uploads where pharmacy_profile_id = $pharmacyProfileId and resource_id = $orderNo;");
        $rows = array();
        while ($r = mysqli_fetch_assoc($result)) {
            $rows[] = $r;
        }
        if(sizeof($rows) > 0){
            return $rows;
        } else {
            return FALSE;
        }
    }
    
    public function updatePrescriptionAsValid($pharmacyProfileId, $orderNo, $cost)
    {
        $result = mysqli_query($this->mysqli, "update image_uploads set is_valid = 1, order_status = 'ProcessingPrescription', cost = $cost where is_valid = 0 and pharmacy_profile_id = $pharmacyProfileId and resource_id = $orderNo;");
        
        return $this->mysqli->affected_rows == 1;
    }
    
    public function updatePrescriptionStatus($pharmacyProfileId, $orderNo, $status)
    {
        $result = mysqli_query($this->mysqli, "update image_uploads set order_status = '$status' where is_valid = 1 and pharmacy_profile_id = $pharmacyProfileId and resource_id = $orderNo;");
        
        return $this->mysqli->affected_rows == 1;
    }
    
    public function updatePrescriptionAsInValid($pharmacyProfileId, $orderNo, $rejectionCode, $rejectionDetails)
    {
        $result = mysqli_query($this->mysqli, "update image_uploads set is_valid = 0, order_status = 'VerificationFailed', rejection_code = '$rejectionCode', rejection_details = '$rejectionDetails' where is_valid = 0 and pharmacy_profile_id = $pharmacyProfileId and resource_id = $orderNo;");
        
        return $this->mysqli->affected_rows == 1;
    }
    
    public function validateUserByEmailAndPassword($email, $password) {
        
        $user = $this->getUserByEmailAndPassword($email,$password);
        
        if($user != false) {
            return true;
        }
        return false;
    }
    
    public function getAddressByPersonId($personId) {
        $result = mysqli_query($this->mysqli, "select * from address where person_id = $personId;");
                
        $no_of_rows = mysqli_num_rows($result);
        
        if($no_of_rows > 0) {
            $returnValue = mysqli_fetch_array($result);
            return $returnValue;
        }
        else {
            return false;
        }
    }

    /**
     * Encrypting password
     * @param password
     * returns salt and encrypted password
     */
    public function hashSSHA($password) {

        $salt = sha1(rand());
        $salt = substr($salt, 0, 10);
        $encrypted = base64_encode(sha1($password . $salt, true) . $salt);
        $hash = array("salt" => $salt, "encrypted" => $encrypted);
        return $hash;
    }

    /**
     * Decrypting password
     * @param salt, password
     * returns hash string
     */
    public function checkhashSSHA($salt, $password) {
        $hash = base64_encode(sha1($password . $salt, true) . $salt);
        return $hash;
    }

}

?>
