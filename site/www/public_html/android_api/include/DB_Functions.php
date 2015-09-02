<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of DB_Functions
 *
 * @author mustaqim
 */
class DB_Functions {
    
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
    
    public function saveImageUploadDetails($resource_type, $personId, $recepientName, $recepientAddress, $recepientNumber, $offerType){
        
        $result = mysqli_query($this->mysqli, "insert into image_uploads (resource_type,person_id, recepient_name, recepient_address, recepient_number, offer_type, created_date) "
                . "values('$resource_type', $personId, '$recepientName', '$recepientAddress', '$recepientNumber', '$offerType', now() );");
               
        if($result) {
            $resourceId = mysqli_insert_id($this->mysqli);
            return $resourceId;
        } else {
            return FALSE;
        }
    }
    
    public function getImageUploadDetails($resourceId){
        
        $result = mysqli_query($this->mysqli, "select * from image_uploads where resourceId = $resourceId;");
               
        $no_of_rows = mysqli_num_rows($result);
        
        if($no_of_rows > 0) {
            return mysqli_fetch_array($result);
        } else {
            return FALSE;
        }
    }
    
    public function isUserExisted($personId) {
        $result = mysqli_query($this->mysqli, "SELECT * from per_all_people_f where person_id = $personId");
        $no_of_rows = mysqli_num_rows($result);
        if ($no_of_rows > 0) {
            // user existed 
            return true;
        } else {
            // user not existed
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
}
