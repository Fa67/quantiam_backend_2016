<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Campaign;

class CampaignController extends Controller
{
    //
	
	function getCampaignList(Request $request)
	{
	
		$input = $request->all();
		
		$query = (new Campaign())->getCampaignList($input);

		return response() -> json($query, 200);
	}
}
