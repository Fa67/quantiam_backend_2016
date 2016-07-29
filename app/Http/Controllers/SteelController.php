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
	
	function getSteel ($inventoryID)
	{
		$query = (new Steel($inventoryID));
	   	return response() -> json($query, 200);
	
	}
	
	
	function getSteelDatatables (Request $request)
	{
		$params = $request->all();
		$response = (new Steel())->datatablesSteelList($params);
		return response() -> json($response, 200);
	
	}
}
