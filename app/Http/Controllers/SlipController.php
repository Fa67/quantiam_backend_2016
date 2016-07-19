<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Models\SlipRecipe;
use App\Models\Slip;

class SlipController extends Controller
{
    //
	
	function getSlipList (Request $request, $like = null)
	{
		$input = $request->all();
	
	   $query = (new Slip())->getSlipList($input);
	   
	   	return response() -> json($query, 200);
	}
	
		
	function getSlip($id)
	{
		$slip = new Slip($id);
		return response() -> json($slip, 200);
	
	}
	
	
	
	function getSlipRecipe($id){
	
		$slipRecipe = new SlipRecipe($id);
		return response() -> json($slipRecipe, 200);
		

	}
	
	function updateSlip(Request $request, $slipID)
	{
	
		$input = $request->all();
		$response = (new Slip())->updateSlip($input,$slipID);
		return response() -> json($response, 200);
		
		
	}
}
