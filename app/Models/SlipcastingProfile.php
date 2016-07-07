<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use DB;

class SlipcastingProfile extends Model
{
    //
	function __construct($slipcastProfileID = null){
	
	
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
	
	
	function getSlipCastProfileList ($active)
	{
	
		$query = DB::table('manu_slipcasting_profile')
		->select('*'); // set up intial table
		
		
		
		if($active)
		{
			$query->where('active','=',1); //conditional based on variable presence.
		}
	
		$result = $query->get();
		
		return $result;
		
	}
}
