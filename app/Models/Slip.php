<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;

use DB; 
use DNS2D;
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
			$temp->datamatrix =  url('/').DNS2D::getBarcodePNGPath("QMSB-".$slipID, "DATAMATRIX",8,8);
			
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
	
	function getSlipList($like)
	{
	
		$query = DB::table('manu_slip')
				->select(['slip_id']);
				
				
				if($like)
				{
				$query->where('slip_id','Like',$like.'%');
			
				}
				
				$query = $query
				->take(10)
				->orderBy('slip_id','desc')
				->get();
			
		$temp = array();
		foreach($query as $obj)
		{
		$temp[] = array('id' => $obj->slip_id, 'text'=>'QMSB-'.$obj->slip_id);
		
		}
		
		return $temp;
	
	}
	
}
