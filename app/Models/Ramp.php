<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use DB;
use DNS2D;

class Ramp extends Model
{
    function __construct($rampprofileID = null)
    {
		if($rampprofileID)
		{
			$this -> buidRampProfile ($rampprofileID);
		}	
    }
   
	function buidRampProfile($rampprofileID)
	{
		
		$temp = $this -> getrampproperties ($rampprofileID);
	
	foreach ($temp as $key=>$value)
		{
			$this-> $key = $value;
		}
		
	$this -> ramp = $this -> getrampprofilesteps ($rampprofileID);
    $this -> datamatrix =  url('/').DNS2D::getBarcodePNGPath("QMRP-".$rampprofileID, "DATAMATRIX",8,8);
   	return;
   	
	}  

		
	function getrampproperties($rampprofileID)
    {   
        $manu_ramp_profile_properties = DB::table('manu_ramp_profile') 
		-> where('ramp_profile_id', '=', $rampprofileID) 
		-> first();
      	return $manu_ramp_profile_properties;
    }

	
	function getrampprofilesteps($rampprofileID)
    {   
        $manu_ramp_profile_steps = DB::table('manu_ramp_profile_steps') 
		-> where('ramp_profile_id', '=', $rampprofileID) 
		-> select('order_id','step_id', 'ramp', 'dwell') 
		-> get();
		return $manu_ramp_profile_steps;
    }

	
	
	
	
}

