<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Log;

use DB;
use Closure;

// Logs after request is fulfilled.
class RequestResponseLogger 
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
        return $next($request);
    }


    public function terminate($request)
    {
        

        $params = array(
                "method" => $request -> method(),
                "requestURL" => $request -> fullUrl(),
                );

        // Check to see if method contr

        if ($request -> user)
        {
             try { 
                $params["userID"] = $request -> user -> employeeid;
            } catch (\Exception $e)
            {
            }

            
           }

        if ($request -> has('pass'))
        {
            $params['params'] = (json_encode("Login Credentials"));
        }
        else
        {
            $params['params'] = json_encode($request -> all());
        }

        //DB::table('api_activity_log')->insert($params);

    }

}
