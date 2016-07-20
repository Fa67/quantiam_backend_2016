<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

use DB; 
use DNS2D;
use App\models\SlipRecipe;
use App\models\SlipViscosity;

class Slip extends Model
{
    //
	
	function __construct($slipID = null)
	{
	
		if($slipID)
		{
		
			$this->buildSlipObj($slipID);
		}
	
	}
	
	
	function buildSlipObj ($slipID){
	
	
			
			$temp = $this->getSlipAtrr($slipID);
			$temp ->identifier =  "QMSB-".$slipID;
			$temp->recipe = new SlipRecipe($temp->slip_recipe_id);
			$temp->measured =  $this->getSlipMeasured($slipID);
			$temp->datamatrix =  url('/').DNS2D::getBarcodePNGPath("QMSB-".$slipID, "DATAMATRIX",8,8);
			$temp->viscosity =  (new SlipViscosity()) ->getSlipViscosity($slipID);;
			
				foreach($temp as $key => $value)
				{
				
					$this->$key = $value;
				}
				
			
		return $temp; 
	}
	
	function getSlipAtrr($slipID)
	{
			$query = DB::table('manu_slip')
			->select('*')
			->where('slip_id','=',$slipID)
			->get();
			
			return $query[0];
	}
	
	function getSlipMeasured ($slipID)
	{
	
		$query = DB::table('manu_slip_measured')
				->select('*')
				->where('slip_id','=',$slipID)
				->get();
				
				return $query;
	}
	
	function getSlipList($params)
	{
	
		$query = DB::table('manu_slip')
				->select(['slip_id']);
				
				
				if(isset($params['like']))
				{
					$query->where('slip_id','Like',$params['like'].'%');
			
				}
				
				$query = $query
				->take(10)
				->orderBy('slip_id','desc')
				->get();
				
				
				
				if(isset($params['guarantee']))
				{
					$guarantee = DB::table('manu_slip')
					->select(['slip_id'])
					->where('slip_id','=',$params['guarantee'])
					->first();
					$query[] = $guarantee;
				}
				
				
				
			
		$temp = array();
		foreach($query as $obj)
		{
		$temp[] = array('id' => $obj->slip_id, 'text'=>'QMSB-'.$obj->slip_id);
		
		}
		
		return $temp;
	
	}
	
	
	function updateSlip($params, $slipID)
    {
        DB::table('manu_slip')->where('slip_id', '=', $slipID)->update($params);
		$slipObj = $this->buildSlipObj($slipID);
		
		return $slipObj;
    }

	
}
