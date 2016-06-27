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
            $response = new Slipcasting($request -> input('slipcastID'));
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
<<<<<<< Updated upstream

    public function humidityData()
    {

        $response = $this -> slipcast -> getHumidityData($this -> slipcasting_id);

        return response() -> json($response, 418);
    }
=======
	
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
						
					
				$returnObj['draw'] = intval($input['draw']);
					
				$queryCount = DB::table('manu_slipcasting')
				->select(['manu_slipcasting.created_datetime']);

				
				
				// What can we search or filter by?
				$SearchableConditionals = array('manu_slip_id', 'qti_id');
				$FilterableConditionals = array();
				
						
				$query  = DB::table('manu_slipcasting')
				->select(['manu_slipcasting.manu_slipcasting_id', 'inventory_id', 'heat_id', 'manu_slipcasting.created_datetime', 'campaign_name','manu_slipcasting.manu_slipcasting_profile_id','profile_name'])
				->join('manu_slipcasting_profile', 'manu_slipcasting.manu_slipcasting_profile_id', '=', 'manu_slipcasting_profile.manu_slipcasting_profile_id')
				->join('manu_slipcasting_steel', 'manu_slipcasting_steel.manu_slipcasting_id', '=', 'manu_slipcasting.manu_slipcasting_id')
				->Leftjoin('manu_slip_recipe', 'manu_slipcasting_steel.slip_id', '=', 'manu_slip_recipe.recipe_id')
				->join('manu_inventory', 'manu_slipcasting_steel.inventory_id', '=', 'manu_inventory.manu_inventory_id')
				->join('manu_campaign', 'manu_inventory.campaign_id', '=', 'manu_campaign.campaign_id')
				->skip($input['start'])
				->take($input['length']);
					
				
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
					foreach($FilterableConditionals as $key)
							{
								if(isset($input[$key]) && strlen($input[$key]) > 0)
								{
									
										$query->Where($key,'=',''.$input[$key].'');
										$queryCount->Where($key,'=',''.$input[$key].'');
									
								}
								
							} 
				
				//	$query->orWhere('characterName','Like','%Troyd%');
					$queryCount = $queryCount->count();
					$query = $query ->get();
					$resultCnt = count($query);
					
				$returnObj['recordsTotal'] = $queryCount;
				$returnObj['recordsFiltered'] = $queryCount;
					
					
				//Special Filtering for slipcast needs
				foreach($query as $slipcast)
				{
				
					foreach($slipcast as $key => $value)
					{
						if($key == 'inventory_id')
						{
						$returnObj['aoData'][$slipcast->manu_slipcasting_id]['steel'][] = array( 'id' => $value, 'name' => $slipcast->heat_id);
						
						
						}
						else if ($key == 'heat_id')
						{
						
						
						}
						else
						{
							$returnObj['aoData'][$slipcast->manu_slipcasting_id][$key] = $value;
						}
						
					
					
					}
				
				
				
				}
					
					
				$returnObj['aoData'] = $query; 
				return response()->json($returnObj, 200);
	
	
	
	
	}
	
	
>>>>>>> Stashed changes
}
