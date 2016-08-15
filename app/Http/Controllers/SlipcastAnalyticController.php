<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Models\Slipcasting;
use App\Models\Slip;
use DB;



class SlipcastAnalyticController extends Controller
{
    //
	function getSlipcastScatterSolventPercentViscosity(Request $request, $campaign_id)
		{
		
		
			
			$query = DB::table('manu_slipcasting_steel')
			->select(['campaign_id','manu_slipcasting_steel.manu_slipcasting_id','manu_slipcasting.datetime','heat_id','manu_slip_id'])
			->leftJoin('manu_inventory','manu_inventory.manu_inventory_id','=','manu_slipcasting_steel.inventory_id')
			->leftJoin('manu_slipcasting','manu_slipcasting.manu_slipcasting_id','=','manu_slipcasting_steel.manu_slipcasting_id')
			->where('campaign_id','=',$campaign_id)
			->orderBy('manu_slipcasting.datetime','asc')
			//->orderBy('manu_slipcasting_steel.inventory_id','asc')
			->groupBy('manu_slipcasting_steel.manu_slipcasting_id')

			->get();
		
		
			$returnArray = array();
			$returnArray['title'] = 'Slipcasting Analysis';
			$returnArray['sub_title'] = 'Viscosity vs Percent Solvent';
			$returnArray['y_name'] = 'Percent Solvent';
			$returnArray['y_unit'] = '%';
			$returnArray['x_name'] = 'Viscosity';
			$returnArray['x_unit'] = 'cps';
			
			
			$tempObj = array();
			
			foreach($query as $Obj)
			{
			
			$slip = (new Slip($Obj->manu_slip_id));
			
			
				//dd($slipcast);
			
			if($slip->viscosity)
			{
			
				//	dd($slip);
					$tempArray = array();	
					$excludeFromAverage = 0;
					$solvent_mass = 0;
					$slip_mass = 0;
					$percent_solvent = 0;
					$comment = null;
					
					
			
					
					$lastViscosityTestObjIndex = count($slip->viscosity)-1;
					
					if(isset($slip->viscosity) && isset($slip->viscosity[$lastViscosityTestObjIndex]->measurements))
									{
										// viscosity 
										
										$lastMeasurement = count($slip->viscosity[$lastViscosityTestObjIndex]->measurements) - 1;
									
										//toluene mass calc
										
										$solvent_mass = $solvent_mass + $slip->solvent_added_at_filtering; //fetech solvent added at filtering.
										
										foreach($slip->viscosity as $viscosityTest)
										{
												$solvent_mass = $solvent_mass + $viscosityTest->solvent_addition;  // fetch solvent added during tests
										}
										
										foreach($slip->measured as $measuredComponent)
										{
											if($measuredComponent->solvent)
											{
												$solvent_mass = $solvent_mass + $measuredComponent->measured; //measured solvent
											}
											
											if(!$measuredComponent->media)
											{
											$slip_mass = $slip_mass + $measuredComponent->measured;
											}
										}
										
										$percent_solvent = round(($solvent_mass / $slip_mass * 100),2);
										
										
							
										
										$tempArray['slipcastID'] = $Obj->manu_slipcasting_id;
										$tempArray['slipID'] = $Obj->manu_slip_id;
										$tempArray['identifier'] = 'QMSB-'.$Obj->manu_slip_id;
										$tempArray['x'] = $slip->viscosity[$lastViscosityTestObjIndex]->measurements[$lastMeasurement]->viscosity; //casted viscosity
										$tempArray['y'] = $percent_solvent; // percent colvent
										$tempArray['viscosity_slip_temp'] = $slip->viscosity[$lastViscosityTestObjIndex]->temperature; // percent colvent
										$tempArray['z'] = $slip->viscosity[$lastViscosityTestObjIndex]->temperature; // percent colvent
										$tempArray['slip_mass'] = $slip_mass;
										$tempArray['solvent_mass'] = $solvent_mass;
										$tempArray['dateTime'] = strtotime($Obj->datetime)*1000;
										$tempArray['readableDate'] = date('D, M d, Y',strtotime($Obj->datetime));
									
										
											
			
											 
								
		
										
										$returnArray['data'][] = $tempArray;
									
									
									}					
				
		
					
					
					}
			}
			$tempArray = array();
		
	
			$returnArray['trend_line'] = array();
			
		
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
	
	
	
	
	
	function getSlipcastPercentSolventData(Request $request, $campaign_id)
		{
		
		
			
			$query = DB::table('manu_slipcasting_steel')
			->select(['campaign_id','manu_slipcasting_steel.manu_slipcasting_id','manu_slipcasting.datetime','heat_id','manu_slip_id'])
			->leftJoin('manu_inventory','manu_inventory.manu_inventory_id','=','manu_slipcasting_steel.inventory_id')
			->leftJoin('manu_slipcasting','manu_slipcasting.manu_slipcasting_id','=','manu_slipcasting_steel.manu_slipcasting_id')
			->where('campaign_id','=',$campaign_id)
			->orderBy('manu_slipcasting.datetime','asc')
			//->orderBy('manu_slipcasting_steel.inventory_id','asc')
			->groupBy('manu_slipcasting_steel.manu_slipcasting_id')

			->get();
		
		
			$returnArray = array();
			$returnArray['title'] = 'Percent Solvent';
			$returnArray['sub_title'] = 'By Slip Batch';
			$returnArray['y_name'] = 'Percent Solvent';
			$returnArray['y_unit'] = '%';
			$returnArray['x_name'] = 'Measurement';
			$returnArray['x_unit'] = '';
			
			$previousValue = 0;
			$tempObj = array();
			
			foreach($query as $Obj)
			{
			
			$slip = (new Slip($Obj->manu_slip_id));
			
			
				//dd($slipcast);
			
			if($slip->viscosity)
			{
			
				//	dd($slip);
					$tempArray = array();	
					$excludeFromAverage = 0;
					$solvent_mass = 0;
					$slip_mass = 0;
					$percent_solvent = 0;
					$comment = null;
					
					
					
					if(isset($slip->viscosity))
									{
										//toluene mass calc
										
										$solvent_mass = $solvent_mass + $slip->solvent_added_at_filtering; //fetech solvent added at filtering.
										
										foreach($slip->viscosity as $viscosityTest)
										{
												$solvent_mass = $solvent_mass + $viscosityTest->solvent_addition;  // fetch solvent added during tests
										}
										
										foreach($slip->measured as $measuredComponent)
										{
											if($measuredComponent->solvent)
											{
												$solvent_mass = $solvent_mass + $measuredComponent->measured; //measured solvent
											}
											
											if(!$measuredComponent->media)
											{
											$slip_mass = $slip_mass + $measuredComponent->measured;
											}
										}
										
										$percent_solvent = round(($solvent_mass / $slip_mass * 100),2);
										
									/* 	if($slip->viscosity[$lastViscosityTestObjIndex]->measurements[$lastMeasurement]->control_chart_exclude)
													{
													$excludeFromAverage = 1;
													} */
												
										$tempArray['mr'] = null;
										$tempArray['slipcastID'] = $Obj->manu_slipcasting_id;
										$tempArray['slipID'] = $Obj->manu_slip_id;
										$tempArray['identifier'] = 'QMSB-'.$Obj->manu_slip_id;
										
										$tempArray['y'] = $percent_solvent;
										$tempArray['slip_mass'] = $slip_mass;
										$tempArray['solvent_mass'] = $solvent_mass;
										$tempArray['dateTime'] = strtotime($Obj->datetime)*1000;
										$tempArray['readableDate'] = date('D, M d, Y',strtotime($Obj->datetime));
									
										if($excludeFromAverage)$tempArray['color'] = '#8600fc'; // color excluded point
											

											 
									if($previousValue) 
										{
											$mr = round(abs($previousValue - $tempArray['y']),2);
											$tempArray['mr'] = $mr;
											if(!$excludeFromAverage) $mrArray[] = $mr;//exclude points that have been flagged from the average
										}
										
										if(!$excludeFromAverage) $xArray[] = $tempArray['y']; //exclude points that have been flagged from the average
										
										$returnArray['xSeries']['data'][] = $tempArray;
									
										$previousValue = $tempArray['y'];	 
									}					
				
		
					
					
					}
			}
			$tempArray = array();
			
			foreach($returnArray['xSeries']['data'] as $Obj)
			{
				$tempArray = $Obj;
				$tempArray['y'] = $Obj['mr'];
				$returnArray['mrSeries']['data'][] = $tempArray;
			
			}
			
			
	
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
	
	
	
		
		function getSlipcastCastedViscosityData(Request $request, $campaign_id)
		{
		
		
			
			$query = DB::table('manu_slipcasting_steel')
			->select(['campaign_id','manu_slipcasting_steel.manu_slipcasting_id','manu_slipcasting.datetime','heat_id','manu_slip_id'])
			->leftJoin('manu_inventory','manu_inventory.manu_inventory_id','=','manu_slipcasting_steel.inventory_id')
			->leftJoin('manu_slipcasting','manu_slipcasting.manu_slipcasting_id','=','manu_slipcasting_steel.manu_slipcasting_id')
			->where('campaign_id','=',$campaign_id)
			->orderBy('manu_slipcasting.datetime','asc')
			//->orderBy('manu_slipcasting_steel.inventory_id','asc')
			->groupBy('manu_slipcasting_steel.manu_slipcasting_id')

			->get();
		
		
			$returnArray = array();
			$returnArray['title'] = 'Casted Viscosity';
				$returnArray['sub_title'] = 'By Slip Batch';
			$returnArray['y_name'] = 'Casted Viscosity';
			$returnArray['y_unit'] = 'cps';
			$returnArray['x_name'] = 'Measurement';
			$returnArray['x_unit'] = '';
			
			$previousViscosity = 0;
			$tempObj = array();
			
			foreach($query as $Obj)
			{
			
			$slip = (new Slip($Obj->manu_slip_id));
			
			
				//dd($slipcast);
			
			if($slip->viscosity)
			{
					$tempArray = array();	
					$excludeFromAverage = 0;
					$comment = null;
					
					$lastViscosityTestObjIndex = count($slip->viscosity)-1;
					if(!empty($slip->viscosity[$lastViscosityTestObjIndex]->measurements))
									{
										$lastMeasurement = count($slip->viscosity[$lastViscosityTestObjIndex]->measurements) - 1;
										if($slip->viscosity[$lastViscosityTestObjIndex]->measurements[$lastMeasurement]->control_chart_exclude)
													{
													$excludeFromAverage = 1;
													$tempArray['excludeFromAverage'] = 1;
													}
												
										$tempArray['mr'] = null;
										$tempArray['slipcastID'] = $Obj->manu_slipcasting_id;
										$tempArray['slipID'] = $Obj->manu_slip_id;
										$tempArray['identifier'] = 'QMSB-'.$Obj->manu_slip_id;
										
										$tempArray['y'] = $slip->viscosity[$lastViscosityTestObjIndex]->measurements[$lastMeasurement]->viscosity;
										$tempArray['dateTime'] = strtotime($Obj->datetime)*1000;
										$tempArray['readableDate'] = date('D, M d, Y',strtotime($Obj->datetime));
										$tempArray['comment'] = $slip->viscosity[$lastViscosityTestObjIndex]->measurements[$lastMeasurement]->control_chart_comment;
										if($excludeFromAverage)$tempArray['color'] = '#8600fc'; // color excluded point
											

											 
									if($previousViscosity) 
										{
											$mr = round(abs($previousViscosity - $tempArray['y']),2);
											$tempArray['mr'] = $mr;
											if(!$excludeFromAverage) $mrArray[] = $mr;//exclude points that have been flagged from the average
										}
										
										if(!$excludeFromAverage) $xArray[] = $tempArray['y']; //exclude points that have been flagged from the average
										
										$returnArray['xSeries']['data'][] = $tempArray;
									
										$previousViscosity = $tempArray['y'];	 
									}					
				
		
					
					
					}
			}
			$tempArray = array();
			
			foreach($returnArray['xSeries']['data'] as $Obj)
			{
				$tempArray = $Obj;
				$tempArray['y'] = $Obj['mr'];
				$returnArray['mrSeries']['data'][] = $tempArray;
			
			}
			
			
	
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
	
	
	
		function getSlipcastSlipUsedData(Request $request, $campaign_id)
		{
		
		
			
			$query = DB::table('manu_slipcasting_steel')
			->select(['campaign_id','manu_slipcasting_steel.manu_slipcasting_id','manu_slipcasting.datetime','heat_id'])
			->leftJoin('manu_inventory','manu_inventory.manu_inventory_id','=','manu_slipcasting_steel.inventory_id')
			->leftJoin('manu_slipcasting','manu_slipcasting.manu_slipcasting_id','=','manu_slipcasting_steel.manu_slipcasting_id')
			->where('campaign_id','=',$campaign_id)
			->orderBy('manu_slipcasting.datetime','asc')
			//->orderBy('manu_slipcasting_steel.inventory_id','asc')
			->groupBy('manu_slipcasting_steel.manu_slipcasting_id')

			->get();
		
		
			$returnArray = array();
			$returnArray['title'] = 'Slip Deposited';
			$returnArray['sub_title'] = 'By Steel Object';
			$returnArray['y_name'] = 'Slip Deposited';
			$returnArray['y_unit'] = 'g';
			$returnArray['x_name'] = 'Measurement';
			$returnArray['x_unit'] = '';
			
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
					$excludeFromAverage = 0;
					$comment = null;
			  
					if(!empty($steelObj->container_weights)) 
					{
						foreach($steelObj->container_weights as $weighObj)
						{
						 if($weighObj->remainder) $slipOnSteel  = $slipOnSteel  + ($weighObj->slip - ( $weighObj->remainder -$weighObj->container));
						 if($weighObj->control_chart_exclude){

											$excludeFromAverage = 1;
											
											$comment = $weighObj->control_chart_exclude_comment;
										}
						}
					}
					
					//$tempArray['mr'] = null;
							if($slipOnSteel > 0)
							{
							
							
							
							$tempArray['mr'] = null;
							$tempArray['slipcastID'] = $Obj->manu_slipcasting_id;
							$tempArray['inventoryName'] = $steelObj->heat_id;
							$tempArray['identifier'] = 'QMIS-'.$steelObj->inventory_id;
							$tempArray['y'] = $slipOnSteel;
							$tempArray['dateTime'] = strtotime($Obj->datetime)*1000;
							$tempArray['readableDate'] = date('D, M d, Y',strtotime($Obj->datetime));
							$tempArray['comment'] = $comment;
							if($excludeFromAverage)$tempArray['color'] = '#8600fc'; // color excluded point
									
							
								if($previousSlipOnSteel) 
								{
									$mr = round(abs($previousSlipOnSteel - $slipOnSteel),2);
									$tempArray['mr'] = $mr;
									if(!$excludeFromAverage) $mrArray[] = $mr;//exclude points that have been flagged from the average
								
								}
							
								if(!$excludeFromAverage) $xArray[] = $slipOnSteel; //exclude points that have been flagged from the average
								
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
		
			// put into the database
		
			DB::table('process_control_limits')
			->where('operation','=','slipcast')
			->where('variable','=','sliponsteel')
			->where('campaign_id','=',$campaign_id)
			->delete();
			
			DB::table('process_control_limits')
			->insert([
			'operation' => 'slipcast',
			'variable' => 'sliponsteel',
			'campaign_id' => $campaign_id,
			'xUCL' => $returnArray['xSeries']['UCL'],
			'xLCL' => $returnArray['xSeries']['LCL'],
			'xAVG' => $returnArray['xSeries']['avg'],
			'mrUCL' => $returnArray['mrSeries']['UCL'],
			'mrLCL' => $returnArray['mrSeries']['LCL'],
			'mrAVG' => $returnArray['mrSeries']['avg'],
			
			]);
		
			
			return	$returnArray;
		
		
		
		}
		
		
		
		function convertOldViscosity ()
		{
		dd();
		$query = DB::table('manu_slip_solvent_additions')
		->select('*')
		->get();
		
		
		foreach($query as  $Obj)
		{
		
		 DB::table('manu_slip')->where('slip_id','=',$Obj->slip_id)->update(['solvent_added_at_filtering' => $Obj->amount]);
		}
	
		
		}
	
}
