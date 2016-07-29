<?php

namespace App\models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Ramp;
use App\Models\PathFinder;
use DB;
use DNS2D;
use stdClass;

class FurnaceRun extends Model
{
   function __construct($furnacerunID = null)
   {
		if($furnacerunID)
		{
			$this -> buildFurnaceRun ($furnacerunID);
		}	
   }
   
function buildFurnaceRun($furnacerunID, $details = null)
	{
		$temp = new StdClass();
		$temp = $this -> getfurnaceproperties ($furnacerunID);
		if(!$details) $temp -> steel = $this -> getfurnacesteel ($furnacerunID);
		$temp -> operators = $this -> getfurnaceoperator ($furnacerunID);
		$temp -> profile = $this -> getfurnaceprofile($temp -> furnace_profile_id);
		$temp -> ramp_profile = $this -> getfurnaceramp ($temp -> furnace_profile_id);
		$temp -> datamatrix =  url('/').DNS2D::getBarcodePNGPath("QMFR-".$furnacerunID, "DATAMATRIX",8,8);
		$temp -> identifier =  "QMFR-".$furnacerunID;
		
		
	foreach ($temp as $key=>$value)
		{
			$this-> $key = $value;
		}
		
	return $temp;
   	
	}  

	
	function getfurnaceproperties($furnacerunID)
    {   
        $query = DB::table('manu_furnace_runs') 
		-> where('furnace_run_id', '=', $furnacerunID) 
		-> leftjoin ('manu_furnace','manu_furnace.furnace_id', '=', 'manu_furnace_runs.furnace_id')
		-> leftjoin ('manu_furnace_runs_type','manu_furnace_runs_type.furnace_run_type_id', '=', 'manu_furnace_runs.furnace_run_type_id')
		-> first();
      	return $query;
    }
	
	
	function getfurnacesteel($furnacerunID,$inventoryID= null)
    {   
        $query = DB::table('manu_furnace_runs_steel') 
		-> where('furnace_run_id', '=', $furnacerunID) 
		-> select('inventory_id', 'layer_id', 'order_id','heat_id','rework','campaign_id') 
		->join('manu_inventory','manu_furnace_runs_steel.inventory_id','=','manu_inventory.manu_inventory_id');
	
		
		if($inventoryID)
		{
		
		$query -> where('inventory_id','=',$inventoryID);
		}
		
		$query = $query	-> get();
		
		
		foreach($query as $Obj)
		{
		
			$Obj->datamatrix = url('/').DNS2D::getBarcodePNGPath("QMIS-".$Obj->inventory_id, "DATAMATRIX",8,8);
		}
		
		//dd($query);
		
		return $query;
    }

	
	function getfurnaceoperator($furnacerunID,$employeeID = null)
    {   
        $query = DB::table('manu_furnace_runs_operators') 
		-> select('operator_id','firstname','lastname')
		-> join ('employees','employees.employeeid', '=', 'manu_furnace_runs_operators.operator_id')
		-> where('furnace_run_id', '=', $furnacerunID);
		
		if($employeeID)
		{
		$query->where('operator_id','=',$employeeID);
		}
		$query = $query -> get();
		
		return $query;
    }
	
	
    
	
	function getfurnaceprofile($profileID)
    {   
        $query = DB::table('manu_furnace_runs_profile') 
		-> where('profile_id', '=', $profileID) 
		-> first();
		return $query;
    }
	
	
	function getfurnaceramp($profileID)
    {   
        $fullramp = (new Ramp($profileID));
		return $fullramp;
    }

	
	
	function getactualramp ($furnacenmame, $furnacerunnmame)
	{
		$fullactualramp = (new PathFinder($furnacenmame, $furnacerunnmame));
		
		
		//return /*$fullactualramp*/;
		
		
		
		
		
		
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
				'furnace_profile_id' => 'manu_furnace_runs.furnace_profile_id',
				'furnace_run_type_id' => 'manu_furnace_runs.furnace_run_type_id',
				'inventory_id' => 'manu_furnace_runs_steel.inventory_id',
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
	
	function getFurnaceRunTypeList ($params)
	{
	
		$query = DB::table('manu_furnace_runs_type')
		->select(['furnace_run_type_id as id', 'furnace_run_type_name as text']); // set up intial table

	
		if(isset($params['like']))
		{
		
			$query->where('furnace_run_type_name','Like','%'.$params['like'].'%');
		}
		
			
	
		$result = $query
		->take(20)
		->orderBy('furnace_run_type_id','desc')
		->get();
		
		return $result;
		
	}
	
	
	function editFurnaceRun($furnacerunID,$input)
	{
	
		$filter = array('furnace_run_id','furnace_name','furnace_type','furnace_run_type_name','datamatrix','profile','identifier');
	
		
		foreach($input as $property => $value)
		{
			if(is_array($value) || in_array($property,$filter))
			{
				unset($input[$property]);
			}			
		
		}
		
       $query = DB::table('manu_furnace_runs')
		->where('furnace_run_id','=',$furnacerunID)
		->update($input);
	
		$response = $this->buildFurnaceRun($furnacerunID);

		return $response;
	}
	
	function editFurnaceRunSteel($furnacerunID,$inventoryID, $input)
	{
	
		$allowed = array('layer_id','order_id');
	
		
		foreach($input as $property => $value)
		{
			if(is_array($value) || !in_array($property,$allowed))
			{
				unset($input[$property]);
			}			
		
		}
		
		if($input)
		{
		   $query = DB::table('manu_furnace_runs_steel')
			->where('furnace_run_id','=',$furnacerunID)
			->where('inventory_id','=',$inventoryID)
			->update($input);
		}
		
		$steel = $this->getfurnacesteel($furnacerunID, $inventoryID);

        return ($steel[0]);
	}
	
	
   function addSteel($furnacerunID, $inventoryID)
    {
		
		
        $id = DB::table('manu_furnace_runs_steel')->insert(['inventory_id' => $inventoryID, 'furnace_run_id' => $furnacerunID]);

		$steel = $this->getfurnacesteel($furnacerunID, $inventoryID);

        return ($steel[0]);
    }

	

    function deleteSteel($furnacerunID, $inventoryID)
    {
        $query = DB::table('manu_furnace_runs_steel')->where('furnace_run_id', '=', $furnacerunID)->where('inventory_id', '=', $inventoryID)->delete();
        return $query;
    } 
	
	
	function addOperator($furnacerunID, $employeeID)
    {
		
		
        $id = DB::table('manu_furnace_runs_operators')->insert(['operator_id' => $employeeID, 'furnace_run_id' => $furnacerunID]);

		$operator = $this->getfurnaceoperator($furnacerunID, $employeeID);

        return ($operator[0]);
    }


	function deleteOperator($furnacerunID, $employeeID)
    {
        $query = DB::table('manu_furnace_runs_operators')->where('furnace_run_id', '=', $furnacerunID)->where('operator_id', '=', $employeeID)->delete();
        return $query;
    }

	function createFurnacerun ($creatorID)
	{
		$params = array('created_by'=>$creatorID);
        $id = DB::table('manu_furnace_runs')->insertGetID($params);
        $response = $this->buildFurnaceRun($id);
        return $response;
	}
	
}

