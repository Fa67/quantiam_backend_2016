<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use DB;

class SlipcastingProfile extends Model
{
    //
	function __construct($slipcastProfileID){
	
	
			if($slipcastProfileID)
		{
		
			$this->buildSlipCastProfileObj($slipcastProfileID);
		}
	
		return $this;
	
	
	
	}
	
	function buildSlipCastProfileObj($slipcastProfileID)
	{
		$temp = $this->getSlipcastProfile($slipcastProfileID);
		foreach($temp as $key=>$value)
		{
			$this->$key = $value;
		
		}
	}
	
	function getSlipcastProfile ($slipcastProfileID){
	
			$query = DB::table('manu_slipcasting_profile')
			->select('*')
			->where('manu_slipcasting_profile_id', '=', $slipcastProfileID)
			->first();
			
			return $query;
			
			
	}
}
