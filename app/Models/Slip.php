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
	
	
			$query = DB::table('manu_slip')
			->select('*')
			->where('slip_id','=',$slipID)
			->get();
			
			foreach($query as $obj)
			{
			
				foreach($obj as $key => $value)
				{
				
					$this->$key = $value;
				}
			
			
			}
			
			
			
			
	
	}
	
	
}
