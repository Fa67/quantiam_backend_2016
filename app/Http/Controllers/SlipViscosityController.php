<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\SlipViscosity;

class SlipViscosityController extends Controller
{
     //
	 
	 function getSlipViscosity ($slipID)
	 {
	 
			$response = (new SlipViscosity())->getSlipViscosity($slipID);
			
			return response() -> json($response, 200);
	 
	 }
	 
	 function editSlipViscosity (Request $request)
	 {
		$input = $request->all();
		$response = (new SlipViscosity())->updateSlipViscosity($input);
		
		return response() -> json($response, 200);
	
	 }

	 function createSlipViscosity (Request $request, $slipID)
	 {
		
		$response = (new SlipViscosity())->createSlipViscosity($slipID);
		
		return response() -> json($response, 200);
	
	 }

	
	 
	 
}
