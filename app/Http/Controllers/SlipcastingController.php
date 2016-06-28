<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

Use App\Models\Slipcasting;
Use DB;

class SlipcastingController extends Controller
{
    public function __construct(Request $request)
    {
        $this -> slipcasting_id = substr($request -> input("slipcastID"), 5);

        $this -> slipcast = new Slipcasting();
    }

    public function tolueneData(Request $request)
    {
        try
        {
            $response = new Slipcasting($this -> slipcasting_id);
            return response() -> json ($response -> tolueneData, 200);
        } catch (\Exception $e)
        {
            return response() -> json (['error' => "could not find toluene data"], 404);
        }

    }

    public function slipData()
    {
        $response = new Slipcasting ($this -> slipcasting_id);

        return response() -> json($response);
    }


    public function humidityData()
    {

        $response = $this -> slipcast -> getHumidityData($this -> slipcasting_id);

        return response() -> json($response, 418);
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
				->join('manu_slipcasting_profile', 'manu_slipcasting.manu_slipcasting_profile_id', '=', 'manu_slipcasting_profile.manu_slipcasting_profile_id')
				->join('manu_slipcasting_steel', 'manu_slipcasting_steel.manu_slipcasting_id', '=', 'manu_slipcasting.manu_slipcasting_id')
				->Leftjoin('manu_slip_recipe', 'manu_slipcasting_steel.slip_id', '=', 'manu_slip_recipe.recipe_id')
				->join('manu_inventory', 'manu_slipcasting_steel.inventory_id', '=', 'manu_inventory.manu_inventory_id')
				->join('manu_campaign', 'manu_inventory.campaign_id', '=', 'manu_campaign.campaign_id');

				
				
				// What can we search or filter by?
				$SearchableConditionals = array('manu_slip_id', 'qti_id');
				$FilterableConditionals = array('campaign_id' => 'manu_campaign.campaign_id');
			
						
				$query  = DB::table('manu_slipcasting')
				->select(['manu_slipcasting.manu_slipcasting_id', 'inventory_id', 'heat_id', 'manu_slipcasting.datetime', 'campaign_name','manu_slipcasting.manu_slipcasting_profile_id','profile_name', 'manu_slip_recipe.recipe_id', 'manu_slip_recipe.recipe_name', 'manu_campaign.campaign_id'])
				->join('manu_slipcasting_profile', 'manu_slipcasting.manu_slipcasting_profile_id', '=', 'manu_slipcasting_profile.manu_slipcasting_profile_id')
				->join('manu_slipcasting_steel', 'manu_slipcasting_steel.manu_slipcasting_id', '=', 'manu_slipcasting.manu_slipcasting_id')
				->Leftjoin('manu_slip_recipe', 'manu_slipcasting_steel.slip_id', '=', 'manu_slip_recipe.recipe_id')
				->join('manu_inventory', 'manu_slipcasting_steel.inventory_id', '=', 'manu_inventory.manu_inventory_id')
				->join('manu_campaign', 'manu_inventory.campaign_id', '=', 'manu_campaign.campaign_id')
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
				
				foreach($query as $slipcast)
				{
				
					if($slipcast->manu_slipcasting_id != $last_slipcast_id)
					{
						$tempArray = array();
						
						
						foreach($slipcast as $key => $value)
						{
						
							if(!in_array($key,$omitt))
							{
								$tempArray[$key] = $value;
							}
							
						
						
						}
						
						$tempArray['steel'][] = array('id'=>$slipcast->inventory_id,'heat_id'=>$slipcast->heat_id, 'recipe_id' => $slipcast->recipe_id, 'recipe_name' => $slipcast->recipe_name);
					
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
				return response()->json($returnObj, 200);
	
	
	
	
	}

}
