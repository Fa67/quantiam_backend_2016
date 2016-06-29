<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\SlipRecipe;
use App\Models\Slip;

class SlipmakingController extends Controller
{
    //
	
	function getSlipRecipe($id){
	
		$slip = new SlipRecipe($id);
		return response() -> json($slip, 200);
		

	}
}
