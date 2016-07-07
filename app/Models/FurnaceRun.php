<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use DB;
use DNS2D;

class FurnaceRun extends Model
{
   function __construct($furnacerunID = null)
   {
		if($furnacerunID)
		{
			$this -> buildFurnaceRun ($furnacerunID);
		}	
   }
   
function buildFurnaceRun($furnacerunID)
	{
		
		$temp = $this -> getfurnaceproperties ($furnacerunID);
	
	foreach ($temp as $key=>$value)
		{
			$this-> $key = $value;
		}
		
	$this -> steel = $this -> getfurnacesteel ($furnacerunID);
    $this -> operators = $this -> getfurnaceoperator ($furnacerunID);
	$this -> profile = $this -> getfurnaceprofile($this -> furnace_profile_id);
    $this -> datamatrix =  url('/').DNS2D::getBarcodePNGPath("QMFR-".$furnacerunID, "DATAMATRIX",8,8);
   	return;
   	
	}  

	
function getfurnacesteel($furnacerunID)
    {   
        $manu_furnace_runs_steel = DB::table('manu_furnace_runs_steel') 
		-> where('furnace_run_id', '=', $furnacerunID) 
		-> select('inventory_id', 'layer_id', 'order_id') 
		-> get();
		return $manu_furnace_runs_steel;
    }

	function getfurnaceoperator($furnacerunID)
    {   
        $manu_furnace_runs_operator = DB::table('manu_furnace_runs_operators') 
		-> where('furnace_run_id', '=', $furnacerunID) 
		-> join ('employees','employees.employeeid', '=', 'manu_furnace_runs_operators.operator_id')
		-> select('operator_id','firstname','lastname') 
		-> get();
		return $manu_furnace_runs_operator;
    }

	
	function getfurnaceproperties($furnacerunID)
    {   
        $manu_furnace_runs_properties = DB::table('manu_furnace_runs') 
		-> where('furnace_run_id', '=', $furnacerunID) 
		-> join ('manu_furance','manu_furance.furnace_id', '=', 'manu_furnace_runs.furnace_id')
		-> join ('manu_furnace_runs_type','manu_furnace_runs_type.furnace_run_type_id', '=', 'manu_furnace_runs.furnace_run_type_id')
		-> first();
      	return $manu_furnace_runs_properties;
    }
	
	function getfurnaceprofile($profileID)
    {   
        $manu_furnace_runs_profile = DB::table('manu_furnace_runs_profile') 
		-> where('profile_id', '=', $profileID) 
		-> first();
		return $manu_furnace_runs_profile;
    }
}

