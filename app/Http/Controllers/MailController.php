<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Http\Requests;

Use App\Resources\Views\Emails;

use Mail;

use App\Models\User;

class MailController extends Controller
{
	public function send(Request $request, $recipientID = null, $subject = null, $body = null){


		if ($recipientID == null )
		{
		$recipientID = $request -> input('recipientID');
		$targetEmail = (new User($recipientID)) -> email;
		$body = $request -> input("body");
		$subject = $request -> input ('subject');
		}
		else 
		{
			$targetEmail = (new User($recipientID)) -> email;
		}

		$mail = new \PHPMailer(true);

		                     // telling the class to use SMTP

		                 
			$mail->isSMTP();                                      // Set mailer to use SMTP

			$mail->Host = getenv('MAIL_HOST');  // this is the exchange mail  server 
			$mail->SMTPAuth = true;                               // Enable SMTP authentication
			$mail->From = 'Quantiam Apps';                 // SMTP username
			$mail->Username = getenv('emailUser');
			$mail->Password = getenv('emailPass');                           // SMTP password 
			$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
			$mail->Port = getenv('MAIL_PORT');                                    // TCP port to connect to
			$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
			);

			$mail->addAddress($targetEmail);

			$mail->Subject = $subject;
			$mail->Body    =  $body;
			$mail->AltBody = "This is the body in plain text for non-HTML mail clients";
			$mail->IsHTML(true);

			$mail->setFrom(getenv('emailUser').getenv('MAIL_SUFFIX'),'Quantiam Apps', TRUE);

		if(!$mail->Send()) 
		{
		       	$error_message = "Mailer Error: " . $mail->ErrorInfo;
		 	return response() -> json(['error' => $error_message]);
		} else 
		{
			$error_message = "Successfully sent!";
			return response() -> json(['success' => $error_message]);
		}
	}
}