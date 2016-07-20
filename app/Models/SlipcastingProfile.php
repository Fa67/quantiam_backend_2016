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

		$steps = DB::table('manu_slipcasting_profile_steps')->where('profile_id', '=', $slipcastProfileID)->get();
		$this -> steps = $steps;

	}
	
	function getSlipcastProfile ($slipcastProfileID){
	
			$query = DB::table('manu_slipcasting_profile')
			->select('*')
			->where('manu_slipcasting_profile_id', '=', $slipcastProfileID)
			->first();
			
			return $query;
			
			
	}
	
	
	function getSlipCastProfileList ($params)
	{
	
		$query = DB::table('manu_slipcasting_profile')
		->select(['manu_slipcasting_profile_id as id', 'profile_name as text']); // set up intial table
		
		
		
		if(isset($params['active']) && $params['active'] == true)
		{
			$query->where('active','=',1); //conditional based on variable presence.
		}
		
		if(isset($params['like']))
		{
		
			$query->where('profile_name','Like','%'.$params['like'].'%');
		}
	
		$result = $query->get();
		
		return $result;
		
	}
}
