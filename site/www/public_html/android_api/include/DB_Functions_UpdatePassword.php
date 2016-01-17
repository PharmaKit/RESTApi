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
    
    public function validateAndUpdatePassword($email,$currentPassword,$newPassword) {
        require_once 'DB_Functions_Patient.php';
        
        $dbPatieht = new DB_Functions_Patient();
        
        $isValid = $dbPatieht->validateUserByEmailAndPassword($email, $currentPassword);
        
        if($isValid) {
            $isUpdated = $this->updatePassoword($email, $newPassword);            
            return $isUpdated;
        }
        return false;
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
    
    public function emailPasswordResetCode($email,$resetCode) {
        date_default_timezone_set('Etc/UTC');
        require '../prescription/phpMailer/PHPMailerAutoload.php';

        $mail = new PHPMailer;

          $mail->isSMTP();

        //Enable SMTP debugging
        // 0 = off (for production use)
        // 1 = client messages
        // 2 = client and server messages
        $mail->SMTPDebug = 0;

        $mail->SMTPOptions = array(
                'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' =>true
                )
        );

        //Set the hostname of the mail server
        $mail->Host = 'smtp.gmail.com';

        //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
        $mail->Port = 587;

        //Set the encryption system to use - ssl (deprecated) or tls
        $mail->SMTPSecure = 'tls';

        //Whether to use SMTP authentication
        $mail->SMTPAuth = true;

        //Username to use for SMTP authentication - use full email address for gmail
        $mail->Username = "anup.pharmakit@gmail.com";

        //Password to use for SMTP authentication
        $mail->Password = "anuppharmakit";

        //Set who the message is to be sent from
        $mail->setFrom('anup.pharmakit@gmail.com', 'MediKeen');

        //Set who the message is to be sent to
        $mail->addAddress($email, $email);

        //Set the subject line
        $mail->Subject = 'MediKeen password reset code';

        $mail->Body ="\nMediKeen"
                . "\n\nYour MediKeen password reset code is: $resetCode"
                . "\nPlease note that the code will expire within 60 minutes.";

        //send the message, check for errors
        if (!$mail->send()) {
            return FALSE;
        } else {
            return TRUE;
        }
        }
    }
?>