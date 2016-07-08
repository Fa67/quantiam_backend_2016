<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
Use App\Models\RampProfile;
Use App\Models\Ramp;
use DB;

class RampProfileController extends Controller
{
    //
	
	public function getRampProfileList (Request $request, $type, $active)
	{

		$query = (new RampProfile())->getRampProfileList($type,$active);

		return response() -> json($query, 200);
	}

	
	
	function buidRampProfile($rampprofileID)
	{
		$fullobject = (new Ramp($rampprofileID));
		return response() -> json($fullobject, 200);
	} 	
	
	
	
	
	
	


}
