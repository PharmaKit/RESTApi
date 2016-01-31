<?php

class DB_Functions_Patient {

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
    public function storePatient($name, $email, $password, $address, $telephone) {

        require_once 'DB_Functions.php';
        $dbFunctions = new DB_Functions();

        $resultP = mysqli_query($this->mysqli, "INSERT INTO per_all_people_f(name, email, person_type, telephone ) VALUES('$name', '$email', 'P' , '$telephone');");

        // check for successful store
        if ($resultP) {
            // get user details 
            $personId = mysqli_insert_id($this->mysqli); // last inserted id

            $uuid = uniqid('', true);
            $hash = $dbFunctions->hashSSHA($password);
            $encrypted_password = $hash["encrypted"]; // encrypted password
            $salt = $hash["salt"]; // salt

            $resultU = mysqli_query($this->mysqli, "INSERT INTO users(unique_id, name, email, person_id, encrypted_password, salt, created_at) VALUES('$uuid', '$name', '$email', '$personId', '$encrypted_password', '$salt', NOW())");
            $userId = mysqli_insert_id($this->mysqli);
            $resultPatient = mysqli_query($this->mysqli, "INSERT INTO patient(person_id) VALUES('$personId')");
            $patientId = mysqli_insert_id($this->mysqli); // last inserted id
            $patientIdUpdate = mysqli_query($this->mysqli, "update per_all_people_f set patient_id = $patientId  where person_id = $personId");
            $resultAddress = mysqli_query($this->mysqli, "INSERT INTO address(house_no,person_id) VALUES('$address','$personId')");
            $result = mysqli_query($this->mysqli, "SELECT * FROM per_all_people_f WHERE person_id = $personId");
            $otp_result = $this->createOtp($userId);
            $sendSMS_result = $this->SendSms($telephone, $otp_result);
            
            if ($resultU && $resultPatient && $resultAddress && $patientIdUpdate) {
                return mysqli_fetch_array($result);
            } else {
                return FALSE;
            }
        } else {
            return false;
        }
    }
    
    /**
     *  Create OTP when user is successfully created and send SMS to USER
     */
    public function createOtp($user_id) {

		$otp = rand(100000, 999999);
        // delete the old otp if exists
        $resultD = mysqli_query($this->mysqli, "DELETE FROM sms_codes where user_id = $user_id");
		$resultI = mysqli_query($this->mysqli, "INSERT INTO sms_codes(user_id, code, isActivated) values($user_id, '$otp', 0)");
 
        return $otp;
    }
     
     
     function sendSms($mobile, $otp) {
     
		$otp_prefix = ':';
	 
		//Your message to send, Add URL encoding here.
		$message = urlencode("Hello! Welcome to MediKeen. Your one time password is $otp_prefix $otp");
	 
		$response_type = 'json';
	 
		//Define route 
		$route = "4";
		 
		//Prepare you post parameters
		$postData = array(
			'authkey' => MSG91_AUTH_KEY,
			'mobiles' => $mobile,
			'message' => $message,
			'sender' => MSG91_SENDER_ID,
			'route' => $route,
			'response' => $response_type
		);
	 
		//API URL
			$url = "https://control.msg91.com/sendhttp.php";
		 
		// init the resource
			$ch = curl_init();
			curl_setopt_array($ch, array(
				CURLOPT_URL => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $postData
					//,CURLOPT_FOLLOWLOCATION => true
			));
		 
		 
			//Ignore SSL certificate verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		 
		 
			//get response
			$output = curl_exec($ch);
		 
			//Print error if any
			if (curl_errno($ch)) {
				echo 'error:' . curl_error($ch);
			}
		 
			curl_close($ch);
}

    /**
     * Get user by email and password
     */
    public function getUserByEmailAndPassword($email, $password) {
        $result = mysqli_query($this->mysqli, "SELECT * FROM users WHERE email = '$email'") or die(mysqli_error($this->mysqli));
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
    
    public function verifyOTP($otp, $key) {
		
		//todo verify user passes correct session key. Key is sent to user during login 
		$userData = mysqli_query($this->mysqli, "SELECT u.uid FROM users u, sms_codes WHERE sms_codes.code = '$otp' AND sms_codes.user_id = u.uid") or die(mysqli_error($this->mysqli));
		if (mysqli_num_rows($userData) > 0) {
			$row = $userData->fetch_assoc();
                        $uid = $row["uid"];
			$resultU = mysqli_query($this->mysqli, "UPDATE users set isActivated = 1 where uid = $uid");
			$resultS = mysqli_query($this->mysqli, "UPDATE sms_codes set isActivated = 1 where user_id = $uid");
			
			if($resultU && $resultS)
				return true;
		}
		else
			return false;
	}
    
    public function validateUserByEmailAndPassword($email, $password) {
        
        $user = $this->getUserByEmailAndPassword($email,$password);
        
        if($user != false) {
            return true;
        }
        return false;
    }

    /**
     * Get user by email and password
     */
    public function getPatientByEmail($email) {
        $result = mysqli_query($this->mysqli, "SELECT * FROM per_all_people_f WHERE email = '$email' and person_type = 'P'") or die(mysqli_error($this->mysqli));
        // check for result 
        $no_of_rows = mysqli_num_rows($result);
        if ($no_of_rows > 0) {
            $result = mysqli_fetch_array($result);
            $personId = $result["person_id"];
            $resultP = mysqli_query($this->mysqli, "select * from per_all_people_f where person_id = $personId and person_type = 'P'");
            $no_of_rowsP = mysqli_num_rows($resultP);
// user authentication details are correct
            if ($no_of_rowsP > 0) {
                return $result;
            } else {
                return false;
            }
        } else {
            // user not found
            return false;
        }
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
     * Check user is existed or not
     */
    public function isPatientExisted($email) {
        $result = mysqli_query($this->mysqli, "SELECT email from per_all_people_f WHERE email = '$email' AND person_type = 'P'");
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
