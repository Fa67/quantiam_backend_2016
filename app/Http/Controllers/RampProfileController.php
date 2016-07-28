<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
Use App\Models\RampProfile;
Use App\Models\Ramp;
Use App\Models\PathFinder;
use DB;

class RampProfileController extends Controller
{
    //
	
	public function getRampProfileList (Request $request, $type, $active)
	{

		$query = (new RampProfile())->getRampProfileList($type,$active);

		return response() -> json($query, 200);
	}

	//For creating the ramp profile
	
	function buidRampProfile($rampprofileID)
	{
		$fullobject = (new Ramp($rampprofileID));
		return response() -> json($fullobject, 200);
	} 	
	
	
	//For finding a file path
	
	function setPath ($furnaceName,$furnaceRunName)
	{
		$PathFinder = (new PathFinder ());
		$fullpath = $PathFinder -> getpath($furnaceName,$furnaceRunName);
		if ($fullpath)
			{
				return response() -> json($fullpath, 200);
			}
		else{
				return response() -> json($fullpath, 400);
			}
	} 	
	
}
