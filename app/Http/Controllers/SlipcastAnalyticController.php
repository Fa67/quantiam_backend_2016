<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Slipcasting;
use DB;


class SlipcastAnalyticController extends Controller
{
    //
		function getSlipcastSlipUsedData(Request $request, $campaign_id)
		{
			
			$query = DB::table('manu_slipcasting_steel')
			->select(['campaign_id','manu_slipcasting_steel.manu_slipcasting_id','manu_slipcasting.datetime'])
			->leftJoin('manu_inventory','manu_inventory.manu_inventory_id','=','manu_slipcasting_steel.inventory_id')
			->leftJoin('manu_slipcasting','manu_slipcasting.manu_slipcasting_id','=','manu_slipcasting_steel.manu_slipcasting_id')
			->where('campaign_id','=',$campaign_id)
			->groupBy('manu_slipcasting_steel.manu_slipcasting_id')
			->orderBy('manu_slipcasting_steel.manu_slipcasting_id','desc')
			->get();
		
		
			$returnArray = array();
			
			foreach($query as $Obj)
			{
			
			$slipcast = (new Slipcasting($Obj->manu_slipcasting_id));
	
				//dd($slipcast);
			
		       foreach($slipcast->steel as $steelObj)
			   {
					$tempArray = array();
					$slipOnSteel = 0;
			  
					if(!empty($steelObj->container_weights)) 
					{
						foreach($steelObj->container_weights as $weighObj)
						{
						$slipOnSteel  = $slipOnSteel  + ($weighObj->slip - ($weighObj->container - $weighObj->remainder));
						
						}
					
					
					}
					
					
					if($slipOnSteel > 0)
					{
					$tempArray['slipcastID'] = $Obj->manu_slipcasting_id;
					$tempArray['inventoryID'] = $steelObj->inventory_id;
					$tempArray['slipCasted'] = $slipOnSteel;
					$tempArray['dateTime'] = strtotime($Obj->datetime)*1000;
					
					$returnArray[] = $tempArray;
					}
				}
	
			
			
			}
			return	$returnArray;
		
		
		
		}
	
	
}
