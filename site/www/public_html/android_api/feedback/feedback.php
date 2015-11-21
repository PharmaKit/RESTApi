<?php
 
require '../Slim/Slim.php';
\Slim\Slim::registerAutoloader();
 
use \Slim\Slim AS Slim;

$app = new Slim();

$app->post('/', 'saveFeedback');

$app->run();

function saveFeedback() {
    $request = Slim::getInstance()->request();  
    
    $fileName = date('Y-m-d_H-i-s').'.txt';
    $file = fopen($fileName,'w') or die('Could not create report file: ' . $fileName);
    
    $reportLine = $request->getBody();
    fwrite($file, $reportLine) or die ('Could not write to report file ' . $reportLine);
    
    fclose($file);
    
    sendEmail($fileName);
}

function sendEmail($fileName) {
        //SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

require '../prescription/phpMailer/PHPMailerAutoload.php';

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

//Set the subject line
$mail->Subject = 'New feedback has been uploaded';

//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
//$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));

//Replace the plain text body with one created manually
//$mail->AltBody = 'This is a plain-text message body';
$recepientName = "recepient_name";
$recepientAddress = "recepient_address";
$recepientNumber = "recepient_number";
$offerType = "offer_type";

$mail->Body ="Filename: " + $fileName;

//Attach an image file
$mail->addAttachment($fileName);

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}


}
 
?>