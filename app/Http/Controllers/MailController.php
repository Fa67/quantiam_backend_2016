<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests;

use Mail;

class MailController extends Controller
{
	public function send(){

		$mail = new \PHPMailer();

		                     // telling the class to use SMTP

		                 
			$mail->isSMTP();                                      // Set mailer to use SMTP

			$mail->Host = getenv('MAIL_HOST');  // this is the exchange mail  server 
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->Username = 'phi600';                 // SMTP username
			$mail->Password = 'phi600';                           // SMTP password 
			$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = getenv('MAIL_PORT');                                    // TCP port to connect to
			$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
			);

			$mail->addAddress('cpetrone@quantiam.com', 'thishfoadshnfdskafdsa');

			$mail->Subject = "Here is the subject";
			$mail->Body    = "This is the HTML message body <b>in bold!</b>";
			$mail->AltBody = "This is the body in plain text for non-HTML mail clients";



			$mail->setFrom('PHI600@edm.quantiam.com', 'Quantiam Apps');

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
