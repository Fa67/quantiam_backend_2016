<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;


class TimesheetController extends Controller
{
  
  
  
  function addAbsence ($userID, $type, $hours, $date){
  
  
	if(!$userID || !$type || !$hours || !$date)
	{
  
		return array('error' => 'Missing expected arguments'); 
	}
	
	$project_conversion = array('pto' => 6, 'vacation' => 2, 'unpaid' => 4, 'cto' =>3);
	
	
	$input = array(
	
	'employeeID' => $userID,
	'type' => $type,
	'hours' => $hours,
	'date' => $date,
	'projectID' => $project_conversion[$type]
	
	);
  
  	$entryID = DB::table('hours')->insertGetId($input);
  
	return array(['success' => $entryID.' - '.$hours.' hours of '.$type.' was created for employee '.$userID.' on '.$date.''], 200 ); 
  
  
  }  
  // Added by Chris on 13/06/2016
  function removeAbsence ($userID, $type, $hours, $date){
  
  
	if(!$userID || !$type || !$hours || !$date)
	{
  
		return array('error' => 'Missing expected arguments'); 
	}
	
	

  	DB::table('hours')	->where('employeeid', '=', $userID)
  				->where('type', '=', $type)
  				->where('hours', '=', $hours)
  				->where('date', '=', $date)
  				->delete();
  
	return array(['success' => "deleted associated times for ".$userID], 200 ); 
  
  
  }
  
  function addAbsenceRequest(Request $request){
  
  $input = $request->all();


  
  $validate = array('userID', 'type', 'hours', 'date'); //expected fields. 
	
	if($input){
		foreach($validate as $key)
		{
		
		
			if(!array_key_exists($key, $input) || $input[$key] == null)
			{
				return response() -> json(['error' => $key.' was not sent as a parameter or was empty'], 400);
			}		
		
		}
	}
	else
	{
		return response() -> json(['error' => 'Please include the expected body arguments for this request.'], 400);
		
	}
  
  $action = $this->addAbsence($input['userID'], $input['type'], $input['hours'], $input['date']);
  
  //dd($action);
			
			if(isset($action['error'])){
			return response() -> json(['error' => $action['error']], 400);
			}
			
	return response() -> json(['success' => $action['success']], 200);
  
  }
  
 function rto_existing_absences(Request $request){
 
$timeofftypes = array('cto','pto','vacation','unpaid'); 
$return_array = array();
$input = $request->all();

	 if(isset($input['dateArray']))
	 {
	 $query = 
	 DB::table('hours')
	->select(['firstname','lastname','type','date','hours'])
	->join('employees', 'employees.employeeid', '=', 'hours.employeeid')
	->whereIn('date', $input['dateArray'])
	->whereIn('type',$timeofftypes)
	 ->get();
	 
	 foreach($query as $obj)
	 {
		$return_array[$obj->date][] = $obj;
	 
	 }
	 

	 return $return_array;
	 
	 }
	 else
	 {
			return response () -> json(['error' => 'Missing expected argument "dateArray" '], 400);
	 }
	 
 }



// really old function converted to laravel, needs a user specific option. 
function rto_allotment($time_travel_date = null){

	/// function retuns current allotments for every individual in the system. optional date to show a moment in time 

	error_reporting(E_ALL ^ E_NOTICE);

	if($time_travel_date)
	{

	$date_string = ' and date <= "'.$time_travel_date.'"';
	$year_cap = date('Y', strtotime($time_travel_date));

	}
	else
	{
	$date_string = '';
	$year_cap = date('Y') + 1; 
	}
	$current_year = date('Y');

	//////////////////////////////// allotted ///////////////////////////////////

	$query = DB::table('rto_allotments')
	->select('*')
	->where('year','<=',$current_year)
	->get();

	foreach($query as $obj)
	{


		
		$return_array[$obj->employee_id]['alloted'][$obj->year]['vacation'] = $obj->vacation;
		$return_array[$obj->employee_id]['alloted'][$obj->year]['pto'] = $obj->pto;
	
		$employee_list[] = $obj->employee_id;
		
		
	}
													
												
													

	
	
	////////////////////////////////////////  PTO   ///////////////////////////////////
	try 
	{


	$query = 
	DB::select("
	SELECT 


	year(date) as year,
	sum(hours) as sum, 
	employeeid


	 FROM quantiam.hours  where projectid = 6 and year(date) > 2014 and legacy is null ".$date_string."

	group by year(date), employeeid;
	");

		foreach($query as $obj)
		{
		
		
														$return_array[$obj->employeeid]['used'][$obj->year]['pto'] = $obj->sum;
														

													}
													
												
													
	for ($year = 2015; $year <= $year_cap; $year++) 
	{
												

				// check carry overs
				foreach ($return_array as $employee_id => $temp_array)
				{

												
						$previous_year = $year - 1;						
												
						if($return_array[$employee_id]['carry_over'][$previous_year]['pto'])
						{
						$return_array[$employee_id]['remaining']['pto'] = $return_array[$employee_id]['alloted'][$year]['pto'] - $return_array[$employee_id]['used'][$year]['pto'] + $return_array[$employee_id]['carry_over'][$previous_year]['pto'];
						$return_array[$employee_id]['carry_over'][$year]['pto'] = $return_array[$employee_id]['remaining']['pto'] ;
						}
						else
						{
						$return_array[$employee_id]['remaining']['pto'] = $return_array[$employee_id]['alloted'][$year]['pto'] - $return_array[$employee_id]['used'][$year]['pto'];
						$return_array[$employee_id]['carry_over'][$year]['pto'] = $return_array[$employee_id]['remaining']['pto'] ;
						}
						
				
				}
	}

	}
	catch (Exception $e)
	{


	}
	/////////////////////////////////////  Vacation ////////////////////////////////////

	try 
	{

	$query = 
	DB::select("
	SELECT 


	year(date) as year,
	sum(hours) as sum, 
	employeeid


	 FROM quantiam.hours  where projectid = 2 and year(date) > 2014 and legacy is null  ".$date_string."

	group by year(date), employeeid;
	");

		foreach($query as $obj)
		{
		
		
														$return_array[$obj->employeeid]['used'][$obj->year]['vacation'] = $obj->sum;
														

													}
													
	for ($year = 2015; $year <= $year_cap; $year++) 
	{
												

				// check carry overs
				foreach ($return_array as $employee_id => $temp_array)
				{

									
						$previous_year = $year - 1;						
												
						if($return_array[$employee_id]['carry_over'][$previous_year]['vacation'])
						{
						$return_array[$employee_id]['remaining']['vacation'] = $return_array[$employee_id]['alloted'][$year]['vacation'] - $return_array[$employee_id]['used'][$year]['vacation'] + $return_array[$employee_id]['carry_over'][$previous_year]['vacation'];
						$return_array[$employee_id]['carry_over'][$year]['vacation'] = $return_array[$employee_id]['remaining']['vacation'] ;
						}
						else
						{
						$return_array[$employee_id]['remaining']['vacation'] = $return_array[$employee_id]['alloted'][$year]['vacation'] - $return_array[$employee_id]['used'][$year]['vacation'];
						$return_array[$employee_id]['carry_over'][$year]['vacation'] = $return_array[$employee_id]['remaining']['vacation'] ;
						}
						
			//$allotments[$employee_id]['2015']['ppl'] - $ppl_usage[$employee_id]['2015']
				
				}
	}



	}
	catch (Exception $e)
	{


	}
	//////////////////////////////////// CTO ////////////////////////////////////
	try 
	{




	// does not attempt to differentiate between years (should it?)






		$query = 
	DB::select("SELECT  sum(hours) as sum, employeeid  FROM quantiam.hours where type = 'bank' and employeeid is not null  ".$date_string." 
		
		group by employeeid");
		
		foreach($query as $obj){
	  
														$banked_hours[$obj->employeeid] = $obj->sum;
	  
													}
						

		
		// get used hours. 
		$query = DB::select("SELECT  sum(hours) as sum, employeeid 
		
		FROM quantiam.hours

		where (type = 'banked_overtime' or type = 'cto' or type = 'cto_payout') and employeeid is not null  ".$date_string."

		group by employeeid;");
		
			
		foreach($query as $obj){
	  

														$used_hour_array[$obj->employeeid] = $obj->sum;
												
													
	  
													}
			

		//	dd($used_hour_array);
		//	dd($banked_hours);
	foreach($employee_list as $employee_id)
	{
			
				$balance = $banked_hours[$employee_id] - $used_hour_array[$employee_id];
				$return_array[$employee_id]['remaining']['cto'] = $balance;
								
	}



	}
	catch (Exception $e)
	{

	}

//	dd($return_array[60]);
	
	return $return_array;
	}  



	
	function getHolidayList (Request $request)
	{
	
			$query = DB::table('timesheet_holidays')
			->select('*')
			->get();
			
			return response() -> json($query, 200);
	
	
	
	}
	
	function addHoliday (Request $request)
	{
	
		$input = $request->all();
		
		if(isset($input['holidayname']) && isset($input['date']))
		{	
		
		$insert = array('holidayname'=>$input['holidayname'], 'date'=>$input['date']);
		
		$query =  DB::table('timesheet_holidays')
			->insertGetId($insert);
			
		$insert['entryid']=$query;
			
			return response() -> json($insert, 200);
		}
		else
		{
		
		return response() -> json(['error'=>'missing expected input'], 400);
		}
	
	
	}
	
	
	function removeHoliday ($holidayID)
	{
	
		DB::table('timesheet_holidays')
			->where('entryid','=',$holidayID)
			->delete();
			
			return;
	
	}
	
}
