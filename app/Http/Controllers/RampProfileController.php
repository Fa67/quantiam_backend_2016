<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

Use App\Models\RampProfile;

use App\Http\Requests;

class RampProfileController extends Controller
{
    //
	
	public function getRampProfileList (Request $request, $type, $active)
	{

		$query = (new RampProfile())->getRampProfileList($type,$active);

		return response() -> json($query, 200);
	}
}
