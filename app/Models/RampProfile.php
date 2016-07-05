<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use DB; 

class RampProfile extends Model
{
    //
	function __construct(){
	
	
	}
	
	function getRampProfileList ($type, $active)
	{
	
		$query = DB::table('manu_ramp_profile')
		->select('*'); // set up intial table
		
		if($type)
		{
		$query->where('ramp_profile_type','=',$type); //conditional based on variable
		}
		
		if($active)
		{
			$query->where('ramp_profile_active','=',1); //conditional based on variable presence.
		}
	
		$result = $query->get();
		
		return $result;
		
	}
	
}
