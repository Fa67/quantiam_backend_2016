<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

use DB;

use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;

class JWT extends Model
{
	public function __construct(Request $request){
		$this -> token($request -> username, $request -> pass);
		return $this -> payload;
	}

	private function token($username, $upasswd){

		$ldaphost = getenv('LDAP_DOMAIN');
		$ldapport = getenv('LDAP_PORT');

		$ds = ldap_connect($ldaphost, $ldapport)
	 		or die ('ldap connect dead'); // or die function nonfunctional; else statement below returns errors for both conn and bind.
			
		if ($ds) 
		{
	   		$ldapusername   = $username."@".(getenv('LDAP_DOMAIN'));
	    	//$upasswd   = $request->input('pass');

	    	// In place of try/catch statement.
	     	@$ldapbind = ldap_bind($ds, $ldapusername, $upasswd);
	                               
	    	if ($ldapbind) 
	        {	/*	
	        	**	$username is authenticated4
	        	*/
	        	// Requests employee ID from User
	        	$user_id = DB::table('employees') -> select('employeeid') -> where ('ldap_username', '=', $username) -> first();

				// Using lcobucci framework.

				$signer = new Sha256();

				$token = (new Builder())->setIssuer($_SERVER['HTTP_HOST']) // Configures the issuer (iss claim)
						                ->setAudience($_SERVER['HTTP_ORIGIN']) // Configures the audience (aud claim)
						                ->setId('4f1g23a12aa', true) // Configures the id (jti claim), replicating as a header item
						                ->setIssuedAt(time()) // Configures the time that the token was issue (iat claim)
						                //->setNotBefore(time() + 1200) // Configures the time that the token can be used (nbf claim)
						                ->setExpiration(time() + 24*3600) // Configures the expiration time of the token (exp claim)
						               	->set('employeeID', $user_id) // Configures a new claim, called "uid"
						                ->sign($signer, getenv('JWT_SIGNED_TOKEN')) // creates a signature using "testing" as key
						                ->getToken(); // Retrieves the generated token

				$payload = $token -> __toString();  //  Retrieves payload with signature
				$this -> payload = $payload;
			}
	    	else 
	        {
	        	$this -> payload = 	'error';
	        }
		}
	}
}
