<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use DB;


class TimesheetController extends Controller
{
  
  
  
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
		$return_array[$obj->employee_id]['alloted'][$obj->year]['ppl'] = $obj->ppl;
		
		$employee_list[] = $obj->employee_id;
		
		
													}
													
												
													

	try 
	{



	///////////////////////// PPL /////////////////


	/// Get all taken 2015 and 2014 hours spent currently. 
	$query = 
	DB::select("

	SELECT 


	year(date) as year,
	sum(hours) as sum, 
	employeeid


	 FROM quantiam.hours  where projectid = 1 and year(date) > 2014 and legacy is null ".$date_string."

	group by year(date), employeeid;
	");



	foreach($query as $obj)
	{
		$return_array[$obj->employeeid]['used'][$obj->year]['ppl'] = $obj->sum;

	}


													
	for ($year = 2015; $year <= $year_cap; $year++) 
	{
												

				// check carry overs
				foreach ($return_array as $employee_id => $temp_array)
				{

												
						$previous_year = $year - 1;			

												
						if($return_array[$employee_id]['carry_over'][$previous_year]['ppl'])
						{
						$return_array[$employee_id]['remaining']['ppl'] = $return_array[$employee_id]['alloted'][$year]['ppl'] - $return_array[$employee_id]['used'][$year]['ppl'] + $return_array[$employee_id]['carry_over'][$previous_year]['ppl'];
						$return_array[$employee_id]['carry_over'][$year]['ppl'] = $return_array[$employee_id]['remaining']['ppl'] ;
						}
						else
						{
						$return_array[$employee_id]['remaining']['ppl'] = $return_array[$employee_id]['alloted'][$year]['ppl'] - $return_array[$employee_id]['used'][$year]['ppl'];
						$return_array[$employee_id]['carry_over'][$year]['ppl'] = $return_array[$employee_id]['remaining']['ppl'] ;
						}
						
				
				}
	}


			//	print_r($ppl_usage);
	////////////////////////////////////////////////////////////////////

	}
	catch (Exception $e)
	{


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
		DB::select("SELECT  sum(hours) as sum, employeeid 
		
		FROM quantiam.hours

		where (type = 'banked_overtime' or type = 'cto' or type = 'cto_payout') and employeeid is not null  ".$date_string."

		group by employeeid;");
		
			
		foreach($query as $obj){
	  

														$used_hour_array[$obj->employeeid] = $obj->sum;
												
													
	  
													}
			

			
	foreach($employee_list as $employee_id)
	{
			
				$balance = $banked_hours[$employee_id]-$used_hour_array[$employee_id];
				$return_array[$employee_id]['remaining']['cto'] = $balance;
								
	}



	}
	catch (Exception $e)
	{

	}

	return $return_array;
	}  



}
