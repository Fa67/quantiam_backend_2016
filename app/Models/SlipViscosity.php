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
		->select('*')
		->where('viscosityID', '=', $viscosityID)
		->get();
		
		return $query;
		
	}
	
	
	function updateSlipViscosity ($input){
	
	
		foreach($input as $viscosity)
		{
			$slipID = $viscosity['slipID'];
		
				$tempObj = $viscosity;
				unset($tempObj['measurements']);

			//	dd($viscosity);
				$query = DB::table('manu_slip_viscosity')
				->where('viscosityID','=',$viscosity['viscosityID'])
				->update($tempObj);
				
				//dd($viscosity['measurements']);
				
				foreach($viscosity['measurements'] as $viscosityMeasurement)
				{
				
						if(isset($viscosityMeasurement['id']))
						{
						
						$query = DB::table('manu_slip_viscosity_measure')
						->where('id','=',$viscosityMeasurement['id'])
						->update($viscosityMeasurement);
						}
						else
						{
						//dd($viscosityMeasurement);
						
						$viscosityMeasurement['viscosityID'] = $viscosity['viscosityID'];
						$query = DB::table('manu_slip_viscosity_measure')
						->insertGetID($viscosityMeasurement);
						}
						
				}
		
		
		
		}
		
		
	
		$response = $this->getSlipViscosity($slipID);
		return $response;
	
	}
	
	function createSlipViscosity($slipID){
	
	
	
			$id = DB::table('manu_slip_viscosity')
					->insertGetID(['slipID' => $slipID]);
						
						
			$query = DB::table('manu_slip_viscosity')
			->where('viscosityID', '=',$id)
			->first();
					
			return $query;
	
	}
	
	

	
}
