<?php

class DB_Functions_UpdatePassword {

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
    public function generatePasswordResetCode($email) {

        require_once 'DB_Functions.php';
        $dbFunctions = new DB_Functions();
        
        $result = mysqli_query($this->mysqli, "SELECT * FROM users WHERE email = '$email'") or die(mysqli_error($this->mysqli));
        // check for result 
        $no_of_rows = mysqli_num_rows($result);
        
        if ($no_of_rows > 0) {
            $user = mysqli_fetch_array($result);
            $userId = $user['uid'];
            $passwordResetCodesResult = mysqli_query($this->mysqli, "SELECT * FROM password_reset_codes where userid = $userId") or die(mysqli_error($this->mysqli));
            
            $no_of_rows = mysqli_num_rows($passwordResetCodesResult);
            if ($no_of_rows > 0) {
                $passwordReserCodeRow = mysqli_fetch_array($passwordResetCodesResult);
                $date_from_db = $passwordReserCodeRow['expiry_date'];
                $now = time();
                $target = strtotime($date_from_db);
                $diff = $target - $now;
                if ( $diff < 0 ) {
                        mysqli_query($this->mysqli, "DELETE from password_reset_codes WHERE expiry_date < NOW();") or die(mysqli_error($this->mysqli));
                }
                else {
                    return $passwordReserCodeRow['reset_code'];
                }
            }
            $resetCode = rand(100000, 999999);
            mysqli_query($this->mysqli, "INSERT INTO password_reset_codes (userid, reset_code, expiry_date) values($userId, '$resetCode', DATE_ADD(NOW(), INTERVAL 60 MINUTE));") or die(mysqli_error($this->mysqli));
            return $resetCode;
        }
        else {
            return FALSE;
        }
    }
    
    public function validatePasswordResetCode($email, $resetCodeFromUser) {

        require_once 'DB_Functions.php';
        $dbFunctions = new DB_Functions();
        
        $result = mysqli_query($this->mysqli, "SELECT * FROM users WHERE email = '$email'") or die(mysqli_error($this->mysqli));
        // check for result 
        $no_of_rows = mysqli_num_rows($result);
        
        if ($no_of_rows > 0) {
            $user = mysqli_fetch_array($result);
            $userId = $user['uid'];
            $passwordResetCodesResult = mysqli_query($this->mysqli, "SELECT * FROM password_reset_codes where userid = $userId") or die(mysqli_error($this->mysqli));
            
            $no_of_rows = mysqli_num_rows($passwordResetCodesResult);
            if ($no_of_rows > 0) {
                $passwordReserCodeRow = mysqli_fetch_array($passwordResetCodesResult);
                $date_from_db = $passwordReserCodeRow['expiry_date'];
                $resetCode = $passwordReserCodeRow['reset_code'];
                $now = time();
                $target = strtotime($date_from_db);
                $diff = $target - $now;
                if ( $diff < 0 ) {
                        mysqli_query($this->mysqli, "DELETE from password_reset_codes WHERE expiry_date < NOW();") or die(mysqli_error($this->mysqli));
                        return FALSE;
                }
                else if ($resetCode == $resetCodeFromUser){
                    mysqli_query($this->mysqli, "UPDATE password_reset_codes set is_verified = 1 WHERE userid = $userId;") or die(mysqli_error($this->mysqli));
                    return TRUE;
                }
            }
            return false;
        }
        else {
            return FALSE;
        }
    }
    
    public function resetPassowrd($email, $resetCodeFromUser, $newPassword) {

        require_once 'DB_Functions.php';
        $dbFunctions = new DB_Functions();
        
        $result = mysqli_query($this->mysqli, "SELECT * FROM users WHERE email = '$email'") or die(mysqli_error($this->mysqli));
        // check for result 
        $no_of_rows = mysqli_num_rows($result);
        
        if ($no_of_rows > 0) {
            $user = mysqli_fetch_array($result);
            $userId = $user['uid'];
            $passwordResetCodesResult = mysqli_query($this->mysqli, "SELECT * FROM password_reset_codes where userid = $userId") or die(mysqli_error($this->mysqli));
            
            $no_of_rows = mysqli_num_rows($passwordResetCodesResult);
            if ($no_of_rows > 0) {
                $passwordReserCodeRow = mysqli_fetch_array($passwordResetCodesResult);
                $date_from_db = $passwordReserCodeRow['expiry_date'];
                $resetCode = $passwordReserCodeRow['reset_code'];
                $now = time();
                $target = strtotime($date_from_db);
                $diff = $target - $now;
                //We will update password only if reset code was validated within last 24 hours. 86400 is number of seconds in 24 hours
                if ( $diff < -86400 ) {
                        mysqli_query($this->mysqli, "DELETE from password_reset_codes WHERE expiry_date < NOW();") or die(mysqli_error($this->mysqli));
                        return FALSE;
                }
                else if ($resetCode == $resetCodeFromUser){
                    $result = mysqli_query($this->mysqli, "DELETE from password_reset_codes WHERE userid = '$userId';") or die(mysqli_error($this->mysqli));
                    return $this->updatePassoword($email, $newPassword);
                }
            }
            return false;
        }
        else {
            return FALSE;
        }
    }
    
    function updatePassoword($email, $newPassword) {
            $uuid = uniqid('', true);
            $hash = $this->hashSSHA($newPassword);
            $encrypted_password = $hash["encrypted"]; // encrypted password
            $salt = $hash["salt"]; // salt

            return mysqli_query($this->mysqli, "UPDATE users set encrypted_password = '$encrypted_password', salt = '$salt' WHERE email = '$email';");
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

?>