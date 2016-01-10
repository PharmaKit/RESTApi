<?php
$target_dir = "uploads/";
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

$resourceName = $_FILES["fileToUpload"]["name"];
$resourceNameSplit = explode(".", $resourceName);
$resourceId = $resourceNameSplit[0];
        
$uploadOk = 1;
$imageUploaded = 0;
$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
if(isset($_POST["submit"])) {
    $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
    if($check !== false) {
        echo "File is an image - " . $check["mime"] . ".";
        $uploadOk = 1;
    } else {
        echo "File is not an image.";
        $uploadOk = 0;
    }
}
// Check if file already exists
if (file_exists($target_file)) {
    echo "Sorry, file already exists."; 
    $uploadOk = 0;
}
// Check file size
if ($_FILES["fileToUpload"]["size"] > 5000000000) {
    echo "Sorry, your file is too large.";
    $uploadOk = 0;
}
// Allow certain file formats
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
    echo "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    $uploadOk = 0;
}
// Check if $uploadOk is set to 0 by an error
if ($uploadOk == 0) {
    echo "Sorry, your file was not uploaded.";
// if everything is ok, try to upload file
} else {
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
		$imageUploaded = 1;
    } else {
        echo "Sorry, there was an error uploading your file.";
		$imageUploaded = 0;
    }
}

if($imageUploaded == 1){
/**
 * This example shows settings to use when sending via Google's Gmail servers.
 */

    require_once '../include/DB_Connect.php';
        // connecting to database
    $db = new DB_Connect();
    $mysqli = $db->connect();
    
    $result = mysqli_query($mysqli, "SELECT * from image_uploads WHERE resource_id = $resourceId;");
        $no_of_rows = mysqli_num_rows($result);
        if ($no_of_rows > 0) {
            // user existed 
            $imageDetails = mysqli_fetch_array($result);
            
            echo json_encode($imageDetails);
            
        } else {
            // user not existed
            return;
        }
    
//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

require 'phpMailer/PHPMailerAutoload.php';

//Create a new PHPMailer instance
$mail = new PHPMailer;

//Tell PHPMailer to use SMTP
$mail->isSMTP();

//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 2;

$mail->SMTPOptions = array(
	'ssl' => array(
	'verify_peer' => false,
	'verify_peer_name' => false,
	'allow_self_signed' =>true
	)
);

//Ask for HTML-friendly debug output
$mail->Debugoutput = 'html';

//Set the hostname of the mail server
$mail->Host = 'smtp.gmail.com';
// use
// $mail->Host = gethostbyname('smtp.gmail.com');
// if your network does not support SMTP over IPv6

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
$mail->setFrom('anup.pharmakit@gmail.com', 'Anup Pharma');

//Set an alternative reply-to address
//$mail->addReplyTo('anuprathi321@gmail.com', 'Anup Rathi');

//Set who the message is to be sent to
$mail->addAddress('bestmust@gmail.com', 'John Doe');
$mail->addAddress('suryansh.vnit@gmail.com ', 'Suryansh');
$mail->addAddress('anuprathi321@gmail.com', 'Anup');
//$mail->addAddress('adv.niharika@gmail.com', 'Niharika');
$mail->addAddress('rathi.archana1011@gmail.com', 'Archana');
$mail->addAddress('varun7691@gmail.com', 'Varun');

//Set the subject line
$mail->Subject = 'PHPMailer GMail SMTP test';

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));

//Replace the plain text body with one created manually
//$mail->AltBody = 'This is a plain-text message body';
$recepientName = "recepient_name";
$recepientAddress = "recepient_address";
$recepientNumber = "recepient_number";
$offerType = "offer_type";

$mail->Body ="\nPharmaKit"
        . "\n\nPlease deliver the following medicines"
        . "\n\nName: $imageDetails[$recepientName]"
        . "\nAddress: $imageDetails[$recepientAddress]"
        . "\nPhone Number: $imageDetails[$recepientNumber]"
        . "\nOffer Type: $imageDetails[$offerType]";

//Attach an image file
$mail->addAttachment($target_file);

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}
}

?>