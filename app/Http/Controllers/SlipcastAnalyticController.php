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
			->orderBy('manu_slipcasting_steel.inventory_id','asc')
			->get();
		
		
			$returnArray = array();
			$returnArray['title'] = 'Slip on Steel';
			$returnArray['y_name'] = 'Slip Deposited';
			$returnArray['y_unit'] = 'g';
			$returnArray['x_name'] = 'Steel Object';
			$returnArray['x_unit'] = 'QMIS';
			
			$previousSlipOnSteel = 0;
			$tempObj = array();
			
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
						 if($weighObj->remainder) $slipOnSteel  = $slipOnSteel  + ($weighObj->slip - ( $weighObj->remainder -$weighObj->container));
						
						}
					}
					
					//$tempArray['mr'] = null;
							if($slipOnSteel > 0)
							{
							$tempArray['mr'] = null;
							$tempArray['slipcastID'] = $Obj->manu_slipcasting_id;
							$tempArray['x'] = $steelObj->inventory_id;
							$tempArray['y'] = $slipOnSteel;
							$tempArray['dateTime'] = strtotime($Obj->datetime)*1000;
							
								if($previousSlipOnSteel) 
								{
									$mr = round(abs($previousSlipOnSteel - $slipOnSteel),2);
									$tempArray['mr'] = $mr;
									$mrArray[] = $mr;
								
								}
							
							
								$xArray[] = $slipOnSteel;
								$returnArray['xSeries']['data'][] = $tempArray;
								
								
							}
					$previousSlipOnSteel = $slipOnSteel;
				}
	
			
			
			}
			
			$tempArray = array();
			
			foreach($returnArray['xSeries']['data'] as $Obj)
			{
				$tempArray = $Obj;
				$tempArray['y'] = $Obj['mr'];
				$returnArray['mrSeries']['data'][] = $tempArray;
			
			}
			
			
			$xArray = remove_outliers($xArray);
			
			$mrArray = remove_outliers($mrArray);
			
			//dd($mrArray);
			$returnArray['xSeries']['avg'] = array_sum($xArray)/count($xArray);
			$returnArray['mrSeries']['avg'] = array_sum($mrArray)/count($mrArray);
			$returnArray['xSeries']['LCL'] = $returnArray['xSeries']['avg'] - (2.66*$returnArray['mrSeries']['avg']);
			$returnArray['xSeries']['UCL'] = $returnArray['xSeries']['avg'] + (2.66*$returnArray['mrSeries']['avg']);
			
			$returnArray['mrSeries']['UCL'] = (3.267 *$returnArray['mrSeries']['avg']);
			$returnArray['mrSeries']['LCL'] =  (0*$returnArray['mrSeries']['avg']);
			
			foreach($returnArray as $seriesKey => $series)
			{
				if(is_array($series)){
						foreach($series as $key => $value){
						if(!is_array($value)) $returnArray[$seriesKey][$key] = round($value,2);
						}
				}
			}
		
		
		
			
			return	$returnArray;
		
		
		
		}
	
	
}
