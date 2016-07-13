<?php

namespace App\models;

use DB;
use Illuminate\Database\Eloquent\Model;

class SlipViscosity extends Model
{
    //
	
	
	
	
	
	function getSlipViscosity ($slipID)
	{
	
		$query = DB::table('manu_slip_viscosity')
		->where('slipID', '=', $slipID)
		->get();
		
	
		
		foreach($query as $obj)
		{
			
			$obj->measurements = $this->getViscosityValues($obj->viscosityID);
			
			$tempObj[] = $obj;
		}
		
		if(isset($tempObj))
		{
			return $tempObj;
		}
		return null;
		
	}
	
	
	function getViscosityValues($viscosityID)
	{
	
		$query = DB::table('manu_slip_viscosity_measure')
		->where('viscosityID', '=', $viscosityID)
		->get();
		
		return $query;
		
	
	}
	
}
