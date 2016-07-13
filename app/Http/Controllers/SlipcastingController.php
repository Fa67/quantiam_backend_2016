<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

Use App\Models\Slipcasting;
Use App\Models\SlipcastingProfile;


Use DB;
use DNS2D;


class SlipcastingController extends Controller
{
   

    public function tolueneData($slipcast_id)
    {
        try
        {
			$response = (new Slipcasting()) -> getcsvData($slipcast_id);
            return response() -> json ($response, 200);

        } catch (\Exception $e)
        {
            return response() -> json (['error' => "could not find toluene data", 'id' => "QMSC-".$slipcast_id], 404);
        }

    }

   

    public function humidityData($slipcast_id)
    {
        try
        {
            $response = (new Slipcasting()) -> getHumidityData($slipcast_id);
            return response() -> json($response, 200);
        } catch (\Exception $e)
        {
            return response() -> json (['error' => "could not find humiditiy data", 'id' => "QMSC-".$slipcast_id], 400);
        }
    }

	
	public function slipDataList (Request $request)
	{
				$input = $request->all();
					
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
					
				$queryCount = DB::table('manu_slipcasting')
				->select(['manu_slipcasting.created_datetime','manu_inventory.campaign_id as campaign_id'])
				->Leftjoin('manu_slipcasting_profile', 'manu_slipcasting.manu_slipcasting_profile_id', '=', 'manu_slipcasting_profile.manu_slipcasting_profile_id')
				->Leftjoin('manu_slipcasting_steel', 'manu_slipcasting_steel.manu_slipcasting_id', '=', 'manu_slipcasting.manu_slipcasting_id')
				->Leftjoin('manu_slip_recipe', 'manu_slipcasting.manu_slip_id', '=', 'manu_slip_recipe.recipe_id')
				->Leftjoin('manu_inventory', 'manu_slipcasting_steel.inventory_id', '=', 'manu_inventory.manu_inventory_id')
				->Leftjoin('manu_campaign', 'manu_inventory.campaign_id', '=', 'manu_campaign.campaign_id');

				
				
				// What can we search or filter by?
				$SearchableConditionals = array('manu_slip_id', 'qti_id','manu_slipcasting.manu_slipcasting_id','manu_slipcasting_steel.inventory_id');
				$FilterableConditionals = array('campaign_id' => 'manu_campaign.campaign_id');
			
						
				$query  = DB::table('manu_slipcasting')
				->select(['manu_slipcasting.manu_slipcasting_id', 'inventory_id', 'heat_id', 'manu_slipcasting.datetime', 'campaign_name','manu_slipcasting.manu_slipcasting_profile_id','profile_name', 'manu_slip_recipe.recipe_id', 'manu_slip_recipe.recipe_name', 'manu_campaign.campaign_id'])
				->Leftjoin('manu_slipcasting_profile', 'manu_slipcasting.manu_slipcasting_profile_id', '=', 'manu_slipcasting_profile.manu_slipcasting_profile_id')
				->Leftjoin('manu_slipcasting_steel', 'manu_slipcasting_steel.manu_slipcasting_id', '=', 'manu_slipcasting.manu_slipcasting_id')
				->Leftjoin('manu_slip_recipe', 'manu_slipcasting.manu_slip_id', '=', 'manu_slip_recipe.recipe_id')
				->Leftjoin('manu_inventory', 'manu_slipcasting_steel.inventory_id', '=', 'manu_inventory.manu_inventory_id')
				->Leftjoin('manu_campaign', 'manu_inventory.campaign_id', '=', 'manu_campaign.campaign_id')
				->skip($input['start'])
				->take($input['length']*2)
				->orderBy('datetime','desc');
					
				
					//Search value functionality
					 if($input['search']['value'])
							{
								foreach($SearchableConditionals as $key)
								{
									
								
									$query->orWhere($key,'Like','%'.$input['search']['value'].'%');
									$queryCount->orWhere($key,'Like','%'.$input['search']['value'].'%');
									
								}
								
								
							}

					//custom field functionality 	
					foreach($FilterableConditionals as $key =>$value)
							{
								if(isset($input[$key]) && strlen($input[$key]) > 0)
								{
									
										$query->Where($FilterableConditionals[$key],'=',''.$input[$key].'');
										$queryCount->Where($FilterableConditionals[$key],'=',''.$input[$key].'');
									
								}
								
							} 
				
				//	$query->orWhere('characterName','Like','%Troyd%');
					$queryCount = $queryCount->count();
					$query = $query ->get();
					$resultCnt = count($query);
					
				$returnObj['recordsTotal'] = $queryCount;
				$returnObj['recordsFiltered'] = $queryCount;
					
					$last_slipcast_id = 0;
				//Special Filtering for slipcast needs
				
				$omitt = array('inventory_id', 'heat_id','recipe_id','recipe_name');
				try{
						foreach($query as $slipcast)
						{
						
						$datamatrix = url('/').DNS2D::getBarcodePNGPath("QMSC-".$slipcast->inventory_id, "DATAMATRIX",8,8);
				
						
							if($slipcast->manu_slipcasting_id != $last_slipcast_id)
							{
								$tempArray = array();
								
								
								foreach($slipcast as $key => $value)
								{
								
									if(!in_array($key,$omitt))
									{
										$tempArray[$key] = $value;
									}
									
								
								$tempArray['datamatrix'] = $datamatrix;
								}
								
								$tempArray['steel'][] = array( 'id'=>$slipcast->inventory_id,'heat_id'=>$slipcast->heat_id, 'recipe_id' => $slipcast->recipe_id, 'recipe_name' => $slipcast->recipe_name);
							
							}
							else
							{
							
								
								array_pop($tempObj);
								
								
								$tempArray['steel'][] = array('id'=>$slipcast->inventory_id,'heat_id'=>$slipcast->heat_id, 'recipe_id' => $slipcast->recipe_id, 'recipe_name' => $slipcast->recipe_name);
								
								
							
							}
							
								$tempObj[] = $tempArray;
							
							$last_slipcast_id = $slipcast->manu_slipcasting_id;
						}
					
					
				$returnObj['aoData'] = $tempObj; 
				}
				catch(\Exception  $e){}
				return response()->json($returnObj, 200);
	
	
	
	
	}

	
	public function addOperator($slipcastID, $operatorID)
	{
		$response =   (new Slipcasting ()) -> addOperator($slipcastID, $operatorID);
			return response() -> json ($response, 200);
	
	}
	
	public function removeOperator($slipcastID, $operatorID)
	{
		$response =   (new Slipcasting ()) -> removeOperator($slipcastID, $operatorID);
			return response() -> json ($response, 200);
	
	}
	
    public function addSteel($slipcast_id, $inventory_id)
    {
			$response =   (new Slipcasting ()) -> addSteel($slipcast_id, $inventory_id);
			return response() -> json ($response, 200);

    }

    public function editSteel(Request $request, $slipcast_id, $inventory_id)
    {
        $params = $request -> all();

        $response = (new Slipcasting ()) -> editSteel($params, $slipcast_id, $inventory_id);
		return response() -> json(['Message: ' => 'Tube '.$inventory_id.' updated', 'Data:' => $params], 200);
    }

	public function deleteSteel($slipcast_id, $inventory_id)
	{
		$response = (new Slipcasting ()) -> deleteSteel($slipcast_id, $inventory_id);

		return response() -> json($response, 200);
	}

	public function getSlipcast($slipcastID)
	{
		return (new Slipcasting($slipcastID));
		
	}

	public function deleteSlipcast(Request $request, $slipcastID)
	{
		$response = (new Slipcasting ()) -> deleteSlipcast($slipcastID);

		return response() -> json(['Success' => 'Deleted run with id QMSC-'.$slipcastID], 200);
	}

	public function createSlipcast(Request $request)
		{
			$response = (new Slipcasting ()) -> createSlipcast();

			return response() -> json(['Success' => 'created run with id QMSC-'.$response -> id,  'id'=>$response->id], 200);
		}


	
	public function editSlipcast(Request $request, $slipcast_id)
	{
		$params = $request -> all();
		
		
		foreach ($params as $key => $value)
		{
			if (!$value)
			{
				unset($params[$key]);
			}
		}

		$response = (new Slipcasting ()) -> editSlipcast($params, $slipcast_id);

		return response() -> json(['success' => $response], 200);
	}

	

	public function getSlipCastProfileList (Request $request, $active = null)
	{
	
		$input = $request->all();
		
		if(isset($input['active']))
		{
		$active = 1; 
		
		}

		$query = (new SlipcastingProfile())->getSlipCastProfileList($active);

		return response() -> json($query, 200);
	}
	
	
	public function getSlipcastTableList (Request $request)
	{

		$query = DB::table('manu_slipcasting_tables')
		->select('*')
		->get();

		return response() -> json($query, 200);
	}
	
	function editSlipcastSteelContainerWeight (Request $request, $slipcastID, $steelID, $containerID)
	{
		$input = $request->all();
	
		$response = (new Slipcasting ()) -> editSteelContainerWeight($slipcastID, $steelID,$containerID, $input);

		return response() -> json($response, 200);
	}
}
