<?php

class DB_Functions_UpdateProfile
{
    private $db;
    private $link;

    //put your code here
    // constructor
    function __construct() {
        require_once 'DB_Connect.php';
        // connecting to database
        $this->db = new DB_Connect();
        $this->link = $this->db->connect();
    }

    // destructor
    function __destruct() {
    }
    
    public function updateProfile($personId,$email,$firstName,$lastName,$phone,$address) {
        
        $result = mysqli_query($this->link, "SELECT * FROM users where person_id = $personId and email = '$email'");
        $noOfRows = mysqli_num_rows($result);
        
        if($noOfRows>0) {
            $user = mysqli_fetch_array($result);
            
            if($user["person_id"] == $personId) {
                
                $name = $lastName != null ? "$firstName $lastName" : $firstName;                
                $result = mysqli_query($this->link, "UPDATE per_all_people_f set name = '$name', telephone = $phone where person_id = $personId");
                    
                $isPeopleTableUpdated = $result;
                
                $result = mysqli_query($this->link, "UPDATE users set name = '$name' where person_id = $personId");
                    
                $isUserTableUpdated = $result;
                
                $result = mysqli_query($this->link, "UPDATE address set house_no = '$address' where person_id = $personId;");
                
                $isAddressUpdated = $result;
                
                //echo "peopleUpdated: $isPeopleTableUpdated, usersUpdated: $isUserTableUpdated, addressUpdated: $isAddressUpdated";
                
                if($isPeopleTableUpdated && $isUserTableUpdated && $isAddressUpdated) {
                    return true;
                }
            }
        }
        return false;
    }
}

?>