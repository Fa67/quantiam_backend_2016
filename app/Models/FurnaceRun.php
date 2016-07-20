<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Ramp;
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
	$this -> ramp_profile = $this -> getfurnaceramp ($this -> furnace_profile_id);
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
		-> join ('manu_furnace','manu_furnace.furnace_id', '=', 'manu_furnace_runs.furnace_id')
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
	
	
	function getfurnaceramp($profileID)
    {   
        	
		$fullramp = (new Ramp($profileID));
				
		return $fullramp;
    }

	
	
	
	
	
	
	function datatablesFurnaceRunlist($input){   //respsone specific for datatables plugin needs
	
			
					
				$returnObj = array();
				
					if(!isset($input['draw']))
					{
						$input = array(
						'draw' => null,
						'start' => 0,
						'length' => 10,
						'search' => null,
						
						);
					}
						
					
				//$input['campaign_id'] = 7;
				$returnObj['draw'] = intval($input['draw']);
					
				$queryCount  = DB::table('manu_furnace_runs')
				->select('*')
				->Leftjoin('manu_furnace_runs_profile', 'manu_furnace_runs_profile.profile_id', '=', 'manu_furnace_runs.furnace_profile_id')
				->Leftjoin('manu_furnace_runs_steel', 'manu_furnace_runs_steel.furnace_run_id', '=', 'manu_furnace_runs.furnace_run_id')
				->Leftjoin('manu_furnace', 'manu_furnace_runs.furnace_id','=','manu_furnace.furnace_id')
				->Leftjoin ('manu_furnace_runs_type','manu_furnace_runs.furnace_run_type_id','=','manu_furnace_runs_type.furnace_run_type_id')
				
				->Leftjoin('manu_inventory', 'manu_furnace_runs_steel.inventory_id', '=', 'manu_inventory.manu_inventory_id')
				->Leftjoin('manu_campaign', 'manu_inventory.campaign_id', '=', 'manu_campaign.campaign_id')
				->groupBy('manu_furnace_runs.furnace_run_id');

				
				
				// What can we search or filter by?
				$SearchableConditionals = array('furnace_run_name');
				$FilterableConditionals = array(
				'campaign_id' => 'manu_campaign.campaign_id',
				'furnace_id' => 'manu_furnace.furnace_id',
				'furnace_profile_id' => 'furnace_profile_id',
				'furnace_run_type_id' => 'furnace_run_type_id',
				);
			
						
				$query  = DB::table('manu_furnace_runs')
				->select(['manu_furnace_runs_type.furnace_run_type_name', 'manu_campaign.campaign_name', 'manu_furnace_runs.created_date', 'manu_furnace_runs.furnace_run_name', 'manu_furnace_runs_profile.profile_name',
				'manu_furnace_runs.furnace_run_id'])
				//->distinct('furnace_run_id')
				->Leftjoin('manu_furnace_runs_profile', 'manu_furnace_runs_profile.profile_id', '=', 'manu_furnace_runs.furnace_profile_id')
				->Leftjoin('manu_furnace_runs_steel', 'manu_furnace_runs_steel.furnace_run_id', '=', 'manu_furnace_runs.furnace_run_id')
				->Leftjoin('manu_furnace', 'manu_furnace_runs.furnace_id','=','manu_furnace.furnace_id')
				->Leftjoin ('manu_furnace_runs_type','manu_furnace_runs.furnace_run_type_id','=','manu_furnace_runs_type.furnace_run_type_id')
				
				->Leftjoin('manu_inventory', 'manu_furnace_runs_steel.inventory_id', '=', 'manu_inventory.manu_inventory_id')
				->Leftjoin('manu_campaign', 'manu_inventory.campaign_id', '=', 'manu_campaign.campaign_id')
				->skip($input['start'])
				->take($input['length'])
				->groupBy('manu_furnace_runs.furnace_run_id')
				->orderBy('created_date','desc');
				
				
					
				
			
					 if($input['search']['value'])
							{
								foreach($SearchableConditionals as $key)
								{
									
								
									$query->orWhere($key,'Like','%'.$input['search']['value'].'%');
									$queryCount->orWhere($key,'Like','%'.$input['search']['value'].'%');
									
								}
								
								
							}

			
					foreach($FilterableConditionals as $key =>$value)
							{
								if(isset($input[$key]) && strlen($input[$key]) > 0)
								{
									
										$query->Where($FilterableConditionals[$key],'=',''.$input[$key].'');
										$queryCount->Where($FilterableConditionals[$key],'=',''.$input[$key].'');
									
								}
								
							} 
							
							
			
				
			
				
				$query = $query ->get();
				$queryCount = $queryCount ->get();
				$queryCount = count($queryCount);
				$resultCnt = count($query);
			
					
				$returnObj['recordsTotal'] = $queryCount;
				$returnObj['recordsFiltered'] = $queryCount;
			
				// add the datamatixstuff
				foreach($query as $obj)
				{
					$obj->datamatrix = url('/').DNS2D::getBarcodePNGPath("QMFR-".$obj->furnace_run_id, "DATAMATRIX",8,8);
				}				
					
				
				$returnObj['aoData'] = $query; 
				
		
				return $returnObj;
	
	
	}
	
	
	
	
	
}

