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
                "params" => ($request -> all()),
                );

        if ($request -> user)
        {

             $params["userID"] = $request -> user -> employeeID;
        }

        if (isset($params['params']['username']) || isset($params['params']['pass']))
        {
            $params['params'] = "User Credentials";
        }
        $params['params'] = json_encode($params['params']);

        DB::table('activity_log')->insert($params);

    }

}
