<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\FurnaceRun;
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
}

