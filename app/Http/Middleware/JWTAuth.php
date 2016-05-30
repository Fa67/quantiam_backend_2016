<?php

namespace App\Http\Middleware;

use Closure;

use Lcobucci\JWT\ValidationData;
use Lcobucci\JWT\Parser;
use App\Models\User;

class JWTAuth
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $token = $request->header('authorization');
        $token = str_replace('Bearer ', '', $token); //  Removes "Bearer " from token
        $token = (new Parser())->parse((string) $token); // Parses from a string

        $data = new ValidationData(); // It will use the current time to validate (iat, nbf and exp)
        $data->setIssuer($_SERVER['HTTP_HOST']);
        //dd($data);
        //$data->setAudience('http://example.org');
        //$data->setId('4f1g23a12aa');
		
        if ($token->validate($data)) // true, because validation information is equals to data contained on the token
        {   
            $employeeID = $token -> getClaim('employeeID');
            //Call User model to create a new user Object
            $user = new User($employeeID, true);
            // Store user object under $request->user
            $request -> user = $user;
            return $next($request);
        }
        else
        {
            return response() -> json(['error' => 'Invalid token'], 401);
        }
    }
}
