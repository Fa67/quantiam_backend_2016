<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use DB;
use DNS2D;
use App\Models\Slipcasting;
use App\Models\FurnaceRun;

class Steel extends Model
{
    //
	
	function __construct($inventoryID = null){
	
			if($inventoryID)
			{
				$this->buildSteelObject($inventoryID);
			}
	}
	
	
	function buildSteelObject ($inventoryID, $detailed = null)
	{
		$temp = $this->getSteelProperties($inventoryID);
		$temp->slipcastRuns = $this->getSteelInSlipcastRuns($inventoryID);
		$temp->furnaceRuns = $this->getSteelInFurnaceRuns($inventoryID);
		$temp -> datamatrix =  url('/').DNS2D::getBarcodePNGPath("QMIS-".$inventoryID, "DATAMATRIX",8,8);
		$temp -> identifier =  "QMIS-".$inventoryID;
	
		
		foreach($temp as $key => $value)
		{
		
			$this->$key = $value;
		}
	
	
		return $temp; 
	
	}
	
	function getSteelProperties ($inventoryID)
	{
	
	$query = DB::table('manu_inventory')
	->select('*')
	->leftJoin('manu_campaign','manu_campaign.campaign_id','=','manu_inventory.campaign_id')
	->where('manu_inventory_id','=',$inventoryID)
	->first();
	
	//dd($query);
	return $query;
	
	}
	
	function getSteelFinishingRuns (){
	
	
	
	}
	
	
	function getSteelInSlipcastRuns($inventoryID)
	{
	
		$query = DB::table('manu_slipcasting_steel')
		->select(['manu_slipcasting_id as id'])
		->where('inventory_id','=',$inventoryID)
		->orderBy('manu_slipcasting_id')
		->get();
		
		$temp = array();
		foreach($query as $obj)
		{
		    $temp[] = (new Slipcasting())->buildSlipcastObj($obj->id,null,1);
		}
		
		return $temp;
	}
	
	function getSteelInFurnaceRuns ($inventoryID)
	{
		$query = DB::table('manu_furnace_runs_steel')
		->select(['furnace_run_id as id'])
		->where('inventory_id','=',$inventoryID)
		->orderBy('furnace_run_id')
		->get();
		
		$temp = array();
		foreach($query as $obj)
		{
		    $temp[] = (new FurnaceRun())->buildFurnaceRun($obj->id,1);
		}
		
		return $temp;
	
	}
	
	function getSteelList($params)
	{
	
		$query = DB::table('manu_inventory')
				->select(['manu_inventory_id','heat_id','rework']);
				
				
				if(!empty($params['like']))
				{
		
				$query->orWhere(function($query) use ($params){
							$query->orWhere('manu_inventory_id','Like',$params['like'].'%')
									 ->orWhere('heat_id','Like','%'.$params['like'].'%');
				});
			
				}
				
				if(!empty($params['campaign_id'])) $query->Where('campaign_id','=',$params['campaign_id']);
			
				
				
				$query = $query
				->take(10)
				->orderBy('manu_inventory_id','desc')
				->get();
			
		$temp = array();
		foreach($query as $obj)
		{
		
		$rework = null;
		
			if($obj->rework)
			{
			$rework = 'R:'.$obj->rework;
			}
			
			
		$temp[] = array('id' => $obj->manu_inventory_id, 'text'=>'QMSI-'.$obj->manu_inventory_id.', '.$obj->heat_id.' '.$rework);
		
		}
		
		return $temp;
	
	}
	
function datatablesSteelList($input){   //respsone specific for datatables plugin needs
	
			
					
				$returnObj = array();
				
				if(!isset($input['draw']))$input = array('draw' => null,'start' => 0,'length' => 10,'search' => null,);
					
						
					
				//$input['campaign_id'] = 7;
				$returnObj['draw'] = intval($input['draw']);
					
				$queryCount  = DB::table('manu_inventory')
				->select('*')
				->Leftjoin('manu_campaign', 'manu_inventory.campaign_id', '=', 'manu_campaign.campaign_id');
				

				
				
				// What can we search or filter by?
				$SearchableConditionals = array('qti_id','heat_id','manu_inventory_id');
				$FilterableConditionals = array(
				'campaign_id' => 'manu_campaign.campaign_id',
				'furnace_id' => 'manu_furnace.furnace_id',
				'furnace_profile_id' => 'manu_furnace_runs.furnace_profile_id',
				'furnace_run_type_id' => 'manu_furnace_runs.furnace_run_type_id',
				'inventory_id' => 'manu_furnace_runs_steel.inventory_id',
				);
			
						
				$query  = DB::table('manu_inventory')
				->select('*')
				->Leftjoin('manu_campaign', 'manu_inventory.campaign_id', '=', 'manu_campaign.campaign_id')
				->skip($input['start'])
				->take($input['length'])
				->orderBy('manu_inventory.manu_inventory_id','desc');
				
				
					
				
			
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
					$obj->datamatrix = url('/').DNS2D::getBarcodePNGPath("QMIS-".$obj->manu_inventory_id, "DATAMATRIX",8,8);
				}				
					
				
				$returnObj['aoData'] = $query; 
				
		
				return $returnObj;
	
	
	}
}
