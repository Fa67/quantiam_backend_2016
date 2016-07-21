<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\FurnaceRun;
use App\Models\FurnaceRunProfile;
use App\Models\Furnace;
use DB;


class FurnaceController extends Controller
{
   
   function buildFurnaceRun($furnacerunID)
	{
		$fullobject = (new FurnaceRun($furnacerunID));
		return response() -> json($fullobject, 200);
	} 
   
   
   
   
	function furnacesteelrun($furnacerunID)
	{
		
		$steel = (new FurnaceRun()) -> getfurnacesteel($furnacerunID);
	
			
		return response() -> json($steel, 200);
	} 
	
	function furnaceoperatorrun($furnacerunID)
	{
		
		$operator = (new FurnaceRun()) -> getfurnaceoperator($furnacerunID);
	
		
		return response() -> json($operator, 200);
	} 
	
	function furnacepropertiesrun($furnacerunID)
	{
		
		$properties = (new FurnaceRun()) -> getfurnaceproperties($furnacerunID);
	
		
		return response() -> json($properties, 200);
	} 
	
	
	function furnaceprofilerun($furnacerunID)
	{
		
		$profile = (new FurnaceRun()) -> getfurnaceprofile($furnacerunID);
		return response() -> json($profile, 200);
	} 
	
	
	function furnaceRunDatatables (Request $request)
	{
		$params = $request->all();
		$response = (new FurnaceRun())->datatablesFurnaceRunlist($params);
		return response() -> json($response, 200);
	
	}
	function getFurnaceList(Request $request)
	{
		$params = $request->all();
		$response = (new Furnace())->getFurnaceList($params);
		return response() -> json($response, 200);
	
	}	
	
	function getFurnaceProfileList(Request $request)
	{
		$params = $request->all();
		$response = (new FurnaceRunProfile())->getFurnaceProfileList($params);
		return response() -> json($response, 200);
	
	}
	function getFurnaceRunTypeList(Request $request)
	{
		$params = $request->all();
		$response = (new FurnaceRun())->getFurnaceRunTypeList($params);
		return response() -> json($response, 200);
	
	}
}

