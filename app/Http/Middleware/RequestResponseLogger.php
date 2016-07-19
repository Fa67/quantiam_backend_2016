<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Log;

use DB;
use Closure;
use Route;

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
  
       

		if($request -> method() != 'GET' && isset($request -> user -> employeeid) && $request->path() != 'auth')
		{
			$routeParams = Route::getCurrentRoute()->parameters();
			//dd($routeParams);
			$params = array(
			"method" => $request -> method(),
			"path" => $request ->path(),
			"route_parameters" => json_encode($routeParams),
			"payload" => json_encode($request -> all()),
			"userID" => $request -> user -> employeeid
			);

			
			DB::table('api_activity_log')->insert($params);
		}
    }

}
