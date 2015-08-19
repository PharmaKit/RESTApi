<?php

class DB_Functions_Doctor {

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

    /**
     * Storing new user
     * returns user details
     */
    public function storeUser($name, $email, $password, $address, $telephone) {

        $resultP = mysqli_query($this->mysqli, "INSERT INTO per_all_people_f(name, email, person_type, telephone ) VALUES('$name', '$email', 'D' , '$telephone');");

        if ($resultP) {

            $personId = mysqli_insert_id($this->mysqli); // last inserted id

            $uuid = uniqid('', true);
            $hash = $this->hashSSHA($password);
            $encrypted_password = $hash["encrypted"]; // encrypted password
            $salt = $hash["salt"]; // salt
            $resultU = mysqli_query($this->mysqli, "INSERT INTO users(unique_id, name, email, person_id, encrypted_password, salt, created_at) VALUES('$uuid', '$name', '$email', '$personId', '$encrypted_password', '$salt', NOW())");
            $resultDoctor = mysqli_query($this->mysqli, "INSERT INTO doctor(person_id) VALUES('$personId')");
            $doctorId = mysqli_insert_id($this->mysqli);
            $doctorIdUpdate = mysqli_query($this->mysqli, "update per_all_people_f set doctor_id = $doctorId  where person_id = $personId");
            $resultAddress = mysqli_query($this->mysqli, "INSERT INTO address(house_no,person_id) VALUES('$address','$personId')");
            // check for successful store
            if ($resultU && $resultDoctor && $resultAddress && $doctorIdUpdate) {
                // get user details 
                //$uid = mysqli_insert_id($this->mysqli); // last inserted id
                $result = mysqli_query($this->mysqli, "SELECT * FROM per_all_people_f WHERE person_id = $personId");
                $resultU = mysqli_query($this->mysqli, "SELECT * FROM users WHERE person_id = $personId");
                // return user details
                $result = mysqli_fetch_array($result);
                $resultU = mysqli_fetch_array($resultU); 
                $result['unique_id'] = $uuid;
                $result['created_at'] = $resultU['created_at'];
                $result['updated_at'] = $resultU['updated_at'];
                return $result;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Get user by email and password
     */
    public function getUserByEmailAndPassword($email, $password) {
        $resultU = mysqli_query($this->mysqli, "SELECT * FROM users WHERE email = '$email'") or die(mysqli_error($this->mysqli));
        // check for result 
        $no_of_rowsU = mysqli_num_rows($resultU);

        if ($no_of_rowsU > 0) {
            $result = mysqli_fetch_array($resultU);
            $salt = $result['salt'];
            $encrypted_password = $result['encrypted_password'];
            $hash = $this->checkhashSSHA($salt, $password);
            // check for password equality
            if ($encrypted_password == $hash) {
                $personId = $result["person_id"];
                $resultP = mysqli_query($this->mysqli, "select * from per_all_people_f where person_id = $personId and person_type = 'D'");
                $no_of_rowsP = mysqli_num_rows($resultP);
// user authentication details are correct
                if ($no_of_rowsP > 0) {
                    $resultP = mysqli_fetch_array($resultP);
                    $result['doctor_id'] = $resultP['doctor_id'];
                    return $result;
                } else {
                    return false;
                }
            }
        } else {
            // user not found
            return false;
        }
    }
    
    public function updateLastLoginAndLoginNumber($email) {
        return mysqli_query($this->mysqli, "update users set last_login = NOW(), login_number = login_number + 1 where email = '$email';") or die(mysqli_error($this->mysqli));
    }

    /**
     * Check user is existed or not
     */
    public function isUserExisted($email) {
        $result = mysqli_query($this->mysqli, "SELECT email from users WHERE email = '$email'");
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