<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

use DB; 
use App\models\SlipRecipe;

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
			$temp->recipe = new SlipRecipe($temp->slip_recipe_id);
			$temp->measured =  $this->getSlipMeasured($slipID);
			
			
				foreach($temp as $key => $value)
				{
				
					$this->$key = $value;
				}

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
	
}
