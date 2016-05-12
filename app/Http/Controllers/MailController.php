<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests;

Use App\Resources\Views\Emails;

use Mail;

class MailController extends Controller
{
	public function send(){

		//include_once("C:\inetpub\wwwroot\quantiam\resources\emails/email.php");

		$mail = new \PHPMailer(true);

		                     // telling the class to use SMTP

		                 
			$mail->isSMTP();                                      // Set mailer to use SMTP

			$mail->Host = getenv('MAIL_HOST');  // this is the exchange mail  server 
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = 'cpetrone';                 // SMTP username
			$mail->Password = 'test';                           // SMTP password 
			$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = getenv('MAIL_PORT');                                    // TCP port to connect to
/*			$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
			);*/

			$mail->addAddress('cpetrone@quantiam.com', 'thishfoadshnfdskafdsa');

			$mail->Subject = "Here is the subject";
			$mail->Body    = 'C:\inetpub\wwwroot\quantiam\resources\emails/email.php';
			$mail->AltBody = "This is the body in plain text for non-HTML mail clients";
			$mail->IsHTML(true);



			$mail->setFrom('Christopher.Petrone@quantiam.com', 'Quantiam Apps');

		if(!$mail->Send()) 
		{
		        $error_message = "Mailer Error: " . $mail->ErrorInfo;
		} else 
		{
		        $error_message = "Successfully sent!";
		}
		return response() -> json(['error' => $error_message]);
	}
}