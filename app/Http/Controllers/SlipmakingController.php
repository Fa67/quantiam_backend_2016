<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\SlipRecipe;
use App\Models\Slip;

class SlipmakingController extends Controller
{
    //
	
	function getSlip($id)
	{
		$slip = new Slip($id);
		return response() -> json($slip, 200);
	
	}
	
	
	
	function getSlipRecipe($id){
	
		$slipRecipe = new SlipRecipe($id);
		return response() -> json($slipRecipe, 200);
		

	}
}
