<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Steel;

class SteelController extends Controller
{
    //
	
	function getSteelList (Request $request)
	{
		$input = $request->all();
	
		
	   $query = (new Steel())->getSteelList($input);
	   
	   	return response() -> json($query, 200);
	}
	
}
