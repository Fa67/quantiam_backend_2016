<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use DB;

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
		
		$temp = $this-> getfurnaceproperties ($furnacerunID);
		
	foreach ($temp as $key=>$value)
	
	{$this-> $key = $value;
	
		
		
	}
		
   $this-> steel = $this-> getfurnacesteel ($furnacerunID);
   
   $this-> operators = $this-> getfurnaceoperator ($furnacerunID);
   
   return;
   
	}  

function getfurnacesteel($furnacerunID)
    {   
        $manu_furnace_runs_steel = DB::table('manu_furnace_runs_steel') -> where('furnace_run_id', '=', $furnacerunID) ->select('*') ->get();

        return $manu_furnace_runs_steel;
    }

	function getfurnaceoperator($furnacerunID)
    {   
        $manu_furnace_runs_operator = DB::table('manu_furnace_runs_operators') -> where('furnace_run_id', '=', $furnacerunID) ->select('*') ->get();

        return $manu_furnace_runs_operator;
    }

	
	function getfurnaceproperties($furnacerunID)
    {   
        $manu_furnace_runs_properties = DB::table('manu_furnace_runs') -> where('furnace_run_id', '=', $furnacerunID) -> first();

        
		
		return $manu_furnace_runs_properties;
    }
}

